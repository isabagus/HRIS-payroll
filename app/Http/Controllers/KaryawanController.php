<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Status;
use App\Models\Instansi;
use App\Imports\KaryawanImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        try {
            Status::where('nama_status', 'Aktif')->update(['nama_status' => 'Karyawan Tetap']);

            // Auto-heal existing scientific notation NIK & Phone values in database
            $scientificNiks = Karyawan::where('nik', 'like', '%E+%')->orWhere('nik', 'like', '%e+%')->get();
            foreach ($scientificNiks as $k) {
                $cleanNik = sprintf('%.0f', (float)$k->nik);
                $k->update(['nik' => $cleanNik]);
            }
            $scientificTlps = Karyawan::where('no_tlp', 'like', '%E+%')->orWhere('no_tlp', 'like', '%e+%')->get();
            foreach ($scientificTlps as $k) {
                $cleanTlp = sprintf('%.0f', (float)$k->no_tlp);
                $k->update(['no_tlp' => $cleanTlp]);
            }
        } catch (\Exception $e) {}

        $search        = $request->get('search');
        $filterDivisi  = $request->get('divisi');
        $filterJabatan = $request->get('jabatan');
        $filterStatus  = $request->get('status');

        $karyawans = Karyawan::with(['divisi', 'jabatan', 'status', 'instansi'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('nama_karyawan', 'like', "%{$search}%")
                       ->orWhere('Kode_karyawan', 'like', "%{$search}%")
                       ->orWhere('nik', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filterDivisi, fn($q) => $q->where('id_divisi', $filterDivisi))
            ->when($filterJabatan, fn($q) => $q->where('id_jabatan', $filterJabatan))
            ->when($filterStatus, fn($q) => $q->where('id_status', $filterStatus))
            ->orderBy('Kode_karyawan', 'asc')
            ->paginate(10)
            ->withQueryString();

        $divisis   = Divisi::orderBy('nama_divisi')->get();
        $jabatans  = Jabatan::orderBy('nama_jabatan')->get();
        $statuses  = Status::orderBy('nama_status')->get();
        $instansis = Instansi::orderBy('nama_instansi')->get();

        // Calculate card statistics
        $totalKaryawan  = Karyawan::count();
        $totalTetap     = Karyawan::whereHas('status', fn($q) => $q->where('nama_status', 'like', '%tetap%')->orWhere('nama_status', 'like', '%aktif%'))->count();
        $totalTraining  = Karyawan::whereHas('status', fn($q) => $q->where('nama_status', 'like', '%train%')->orWhere('nama_status', 'like', '%kontrak%')->orWhere('nama_status', 'like', '%probation%'))->count();
        $totalFreelance = Karyawan::whereHas('status', fn($q) => $q->where('nama_status', 'like', '%free%')->orWhere('nama_status', 'like', '%magang%')->orWhere('nama_status', 'like', '%part%'))->count();
        $totalNonAktif  = Karyawan::onlyTrashed()->count();

        return view('layouts.karyawan.index', compact(
            'karyawans', 'search', 'divisis', 'jabatans', 'statuses', 'instansis',
            'filterDivisi', 'filterJabatan', 'filterStatus',
            'totalKaryawan', 'totalTetap', 'totalTraining', 'totalFreelance', 'totalNonAktif'
        ));
    }

    public function nonaktif(Request $request)
    {
        $search = $request->get('search');

        $karyawans = Karyawan::onlyTrashed()
            ->with(['divisi', 'jabatan', 'status', 'instansi'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('nama_karyawan', 'like', "%{$search}%")
                       ->orWhere('Kode_karyawan', 'like', "%{$search}%")
                       ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('layouts.karyawan.nonaktif', compact('karyawans', 'search'));
    }

    public function create()
    {
        $divisis   = Divisi::orderBy('nama_divisi')->get();
        $jabatans  = Jabatan::orderBy('nama_jabatan')->get();
        $statuses  = Status::orderBy('nama_status')->get();
        $instansis = Instansi::orderBy('nama_instansi')->get();
        return view('layouts.karyawan.create_edit', compact('divisis', 'jabatans', 'statuses', 'instansis') + ['karyawan' => null]);
    }

    public function store(Request $request)
    {
        // Support dynamic multi-row input (karyawans array) OR single row input
        if ($request->has('karyawans') && is_array($request->karyawans)) {
            $request->validate([
                'karyawans.*.nama_karyawan'   => 'required|string|max:150',
                'karyawans.*.jenis_kelamin'   => 'required|in:L,P',
                'karyawans.*.tanggal_gajian'  => 'required|integer|between:1,31',
            ], [
                'karyawans.*.nama_karyawan.required'  => 'Nama karyawan pada setiap baris wajib diisi.',
                'karyawans.*.tanggal_gajian.required' => 'Tanggal Gajian wajib dipilih pada setiap baris.',
                'karyawans.*.tanggal_gajian.between'  => 'Tanggal Gajian harus antara 1 sampai 31.',
            ]);

            $inserted = 0;
            foreach ($request->karyawans as $item) {
                if (empty($item['nama_karyawan'])) continue;

                $kode = !empty($item['Kode_karyawan']) ? $item['Kode_karyawan'] : 'KRY-' . strtoupper(Str::random(6));

                Karyawan::create([
                    'Kode_karyawan'       => $kode,
                    'nama_karyawan'       => $item['nama_karyawan'],
                    'email'               => $item['email'] ?? null,
                    'nik'                 => $item['nik'] ?? null,
                    'tempat_lahir'        => $item['tempat_lahir'] ?? null,
                    'tanggal_lahir'       => $item['tanggal_lahir'] ?? null,
                    'jenis_kelamin'       => $item['jenis_kelamin'] ?? 'L',
                    'alamat'              => $item['alamat'] ?? null,
                    'no_tlp'              => $item['no_tlp'] ?? null,
                    'pendidikan_terakhir' => $item['pendidikan_terakhir'] ?? null,
                    'prodi'               => $item['prodi'] ?? null,
                    'tahun_lulus'         => $item['tahun_lulus'] ?? null,
                    'perusahaan'          => $item['perusahaan'] ?? null,
                    'id_instansi'         => $item['id_instansi'] ?? null,
                    'awal_masuk'          => $item['awal_masuk'] ?? null,
                    'tanggal_gajian'      => $item['tanggal_gajian'] ?? null,
                    'id_divisi'           => $item['id_divisi'] ?? 1,
                    'id_jabatan'          => $item['id_jabatan'] ?? 1,
                    'id_status'           => $item['id_status'] ?? 1,
                ]);
                $inserted++;
            }

            return redirect()->route('karyawan.index')
                ->with('success', "{$inserted} Data karyawan berhasil ditambahkan.");
        }

        // Single input fallback
        $request->validate([
            'Kode_karyawan'  => 'required|string|max:50|unique:karyawans,Kode_karyawan',
            'nama_karyawan'  => 'required|string|max:150',
            'jenis_kelamin'  => 'required|in:L,P',
            'tanggal_gajian' => 'required|integer|between:1,31',
        ], [
            'tanggal_gajian.required' => 'Tanggal Gajian wajib dipilih.',
            'tanggal_gajian.between'  => 'Tanggal Gajian harus antara 1 sampai 31.',
        ]);

        Karyawan::create($request->only([
            'Kode_karyawan', 'nama_karyawan', 'email', 'nik',
            'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat',
            'no_tlp', 'pendidikan_terakhir', 'prodi', 'tahun_lulus',
            'perusahaan', 'id_instansi', 'awal_masuk', 'tanggal_gajian',
            'id_divisi', 'id_jabatan', 'id_status',
        ]));

        return redirect()->route('karyawan.index')
            ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $karyawan = ($id instanceof Karyawan) ? $id : Karyawan::withTrashed()->with(['divisi', 'jabatan', 'status', 'instansi'])->findOrFail($id);

        $salaryHistories = \App\Models\GajiKaryawan::where('ID_karyawan', $karyawan->ID_karyawan)
            ->with('details.komponenGaji')
            ->orderBy('date', 'asc')
            ->orderBy('ID_gaji', 'asc')
            ->get();

        return view('layouts.karyawan.show', compact('karyawan', 'salaryHistories'));
    }

    public function edit($id)
    {
        $karyawan  = ($id instanceof Karyawan) ? $id : Karyawan::findOrFail($id);
        $divisis   = Divisi::orderBy('nama_divisi')->get();
        $jabatans  = Jabatan::orderBy('nama_jabatan')->get();
        $statuses  = Status::orderBy('nama_status')->get();
        $instansis = Instansi::orderBy('nama_instansi')->get();
        return view('layouts.karyawan.create_edit', compact('karyawan', 'divisis', 'jabatans', 'statuses', 'instansis'));
    }

    public function update(Request $request, $id)
    {
        $karyawan = ($id instanceof Karyawan) ? $id : Karyawan::findOrFail($id);
        $request->validate([
            'Kode_karyawan' => 'required|string|max:50|unique:karyawans,Kode_karyawan,' . $karyawan->ID_karyawan . ',ID_karyawan',
            'nama_karyawan' => 'required|string|max:150',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        $karyawan->update($request->only([
            'Kode_karyawan', 'nama_karyawan', 'email', 'nik',
            'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat',
            'no_tlp', 'pendidikan_terakhir', 'prodi', 'tahun_lulus',
            'perusahaan', 'id_instansi', 'awal_masuk', 'tanggal_gajian',
            'id_divisi', 'id_jabatan', 'id_status',
        ]));

        return redirect()->route('karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $karyawan = ($id instanceof Karyawan) ? $id : Karyawan::findOrFail($id);
        $karyawan->update([
            'tanggal_resign' => $request->input('tanggal_resign', date('Y-m-d')),
            'alasan_resign'  => $request->input('alasan_resign', 'Karyawan Resign / Non-aktif'),
        ]);
        $karyawan->delete(); // Soft Delete

        return redirect()->route('karyawan.index')
            ->with('success', "Karyawan {$karyawan->nama_karyawan} berhasil dinonaktifkan (diarsipkan).");
    }

    public function restore($id)
    {
        $karyawan = ($id instanceof Karyawan) ? $id : Karyawan::onlyTrashed()->findOrFail($id);
        $karyawan->restore();
        $karyawan->update([
            'tanggal_resign' => null,
            'alasan_resign'  => null,
        ]);

        return redirect()->route('karyawan.nonaktif')
            ->with('success', "Karyawan {$karyawan->nama_karyawan} berhasil diaktifkan kembali.");
    }

    /**
     * Hapus permanen (hard delete) karyawan non-aktif.
     * Safety check: HANYA diperbolehkan jika karyawan tidak punya riwayat data apapun.
     */
    public function permanentDelete($id)
    {
        $karyawan = Karyawan::onlyTrashed()->findOrFail($id);

        // Cek riwayat data terkait
        $hasPayroll  = \App\Models\Payroll::where('ID_karyawan', $karyawan->ID_karyawan)->exists();
        $hasAbsensi  = \App\Models\Absensi::where('ID_karyawan', $karyawan->ID_karyawan)->exists();
        $hasLembur   = \App\Models\Lembur::where('ID_karyawan', $karyawan->ID_karyawan)->exists();
        $hasKasbon   = \App\Models\Kasbon::where('ID_karyawan', $karyawan->ID_karyawan)->exists();
        $hasGajiKary = \App\Models\GajiKaryawan::where('ID_karyawan', $karyawan->ID_karyawan)->exists();

        if ($hasPayroll || $hasAbsensi || $hasLembur || $hasKasbon || $hasGajiKary) {
            $riwayatDitemukan = collect([
                $hasPayroll  ? 'Payroll'  : null,
                $hasAbsensi  ? 'Absensi'  : null,
                $hasLembur   ? 'Lembur'   : null,
                $hasKasbon   ? 'Kasbon'   : null,
                $hasGajiKary ? 'Gaji Karyawan' : null,
            ])->filter()->implode(', ');

            return redirect()->route('karyawan.nonaktif')
                ->with('error', "Karyawan \"{$karyawan->nama_karyawan}\" tidak dapat dihapus permanen karena memiliki riwayat data: {$riwayatDitemukan}. Data ini akan tetap tersimpan sebagai arsip non-aktif untuk menjaga integritas histori.");
        }

        // Tidak ada riwayat apapun → boleh hapus permanen
        $nama = $karyawan->nama_karyawan;
        $karyawan->forceDelete();

        return redirect()->route('karyawan.nonaktif')
            ->with('success', "Karyawan \"{$nama}\" berhasil dihapus permanen dari database.");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file_excel.required' => 'File Excel wajib dipilih.',
            'file_excel.mimes'    => 'Format file harus xlsx, xls, atau csv.',
            'file_excel.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new KaryawanImport();
        Excel::import($import, $request->file('file_excel'));

        $msg = "Import selesai: {$import->importedCount} data berhasil diproses.";
        if ($import->skippedCount > 0) {
            $msg .= " {$import->skippedCount} data dilewati/gagal.";
        }

        return redirect()->route('karyawan.index')
            ->with('success', $msg)
            ->with('import_errors', $import->importErrors);
    }

    public function export()
    {
        return Excel::download(new \App\Exports\KaryawanExport, 'Data_Karyawan_' . date('Ymd_His') . '.xlsx');
    }
}
