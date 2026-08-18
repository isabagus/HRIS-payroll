<?php

namespace App\Http\Controllers;

use App\Models\Lembur;
use App\Models\Karyawan;
use App\Models\KategoriLembur;
use App\Models\GajiKaryawan;
use App\Models\DetailGajiKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LemburController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    private function getKategoriHarianId()
    {
        $kat = KategoriLembur::where('tipe_lembur', 'like', '%harian%')->orWhere('tipe_lembur', 'Lembur Reguler')->first();
        if (!$kat) {
            $kat = KategoriLembur::create(['tipe_lembur' => 'Lembur Harian']);
        }
        return $kat->ID_kategori_lembur;
    }

    private function getKategoriLiburId()
    {
        $kat = KategoriLembur::where('tipe_lembur', 'like', '%libur%')->first();
        if (!$kat) {
            $kat = KategoriLembur::create(['tipe_lembur' => 'Lembur Hari Libur']);
        }
        return $kat->ID_kategori_lembur;
    }

    /**
     * Cek apakah user yang login adalah Manager atau Super Admin.
     * Jika ya → lembur langsung disetujui (auto-approve).
     */
    private function isAutoApproveRole(): bool
    {
        $user = auth()->user();
        if (!$user || !$user->role) return false;

        $roleName = strtolower($user->role->nama_role ?? '');
        return str_contains($roleName, 'manager') || str_contains($roleName, 'super admin');
    }

    /**
     * Bangun data approval otomatis untuk Manager/Super Admin.
     */
    private function buildAutoApprovalData(): array
    {
        if ($this->isAutoApproveRole()) {
            return [
                'status_persetujuan' => 'Disetujui',
                'id_approved_by'     => auth()->id(),
                'tanggal_approval'   => Carbon::now(),
                'catatan_approval'   => null,
            ];
        }

        return [
            'status_persetujuan' => 'Menunggu',
            'id_approved_by'     => null,
            'tanggal_approval'   => null,
            'catatan_approval'   => null,
        ];
    }

    /**
     * Ambil rate gaji per jam dan uang makan per hari dari detail_gaji_karyawans.
     * Digunakan untuk kalkulasi Lembur Hari Libur:
     *   - uang_lembur = 8 jam × tarif_per_jam × 2
     *   - uang_makan  = tarif_uang_makan_per_hari × 2
     */
    public function getEmployeeRate($karyawanId, ?string $tanggalLembur = null)
    {
        $gajiHeader = GajiKaryawan::where('ID_karyawan', $karyawanId)
            ->where('status', 'Aktif')
            ->latest()
            ->first();

        $gajiPokokPerJam  = 0;
        $uangMakanPerHari = 0;

        if ($gajiHeader) {
            $details = DetailGajiKaryawan::with('komponenGaji')
                ->where('ID_gaji', $gajiHeader->ID_gaji)
                ->get();

            foreach ($details as $det) {
                $n   = strtolower($det->komponenGaji->nama_gaji ?? '');
                $val = floatval($det->nominal_gaji);

                if (str_contains($n, 'pokok')) {
                    $gajiPokokPerJam = $val;   // ini adalah tarif per jam
                } elseif (str_contains($n, 'makan')) {
                    $uangMakanPerHari = $val;  // ini adalah tarif per hari
                }
            }
        }

        // Lembur Hari Libur: 8 jam × tarif per jam × 2
        $lemburLiburGajiPokok = max(0, 8 * $gajiPokokPerJam * 2);
        // Uang makan hari libur: tarif harian × 2
        $lemburLiburUangMakan = max(0, $uangMakanPerHari * 2);

        return [
            'gaji_pokok_per_jam'        => $gajiPokokPerJam,
            'uang_makan_per_hari'       => $uangMakanPerHari,
            'lembur_libur_gaji_pokok'   => $lemburLiburGajiPokok,
            'lembur_libur_uang_makan'   => $lemburLiburUangMakan,
            // Legacy fields untuk backward-compat
            'gaji_pokok'                => $gajiPokokPerJam,
            'uang_makan'                => $uangMakanPerHari,
            'base_total'                => $gajiPokokPerJam + $uangMakanPerHari,
            'holiday_nominal'           => $lemburLiburGajiPokok,
        ];
    }

    public function getKaryawanRate($id)
    {
        return response()->json($this->getEmployeeRate($id));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // INDEX & APPROVAL INDEX
    // ──────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search        = $request->get('search');
        $filterStatus  = $request->get('status');
        $filterJenis   = $request->get('jenis');
        $filterBulan   = $request->get('bulan', date('m'));
        $filterTahun   = $request->get('tahun', date('Y'));

        $lemburs = Lembur::select('lemburs.*')
            ->leftJoin('karyawans', 'lemburs.ID_karyawan', '=', 'karyawans.ID_karyawan')
            ->with(['karyawan.divisi', 'karyawan.jabatan', 'kategoriLembur', 'approvedBy'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('karyawan', function ($q2) use ($search) {
                    $q2->where('nama_karyawan', 'like', "%{$search}%")
                       ->orWhere('Kode_karyawan', 'like', "%{$search}%");
                });
            })
            ->when($filterStatus, fn($q) => $q->where('status_persetujuan', $filterStatus))
            ->when($filterJenis, function ($q) use ($filterJenis) {
                $q->whereHas('kategoriLembur', function ($q2) use ($filterJenis) {
                    if ($filterJenis === 'libur') {
                        $q2->where('tipe_lembur', 'like', '%libur%');
                    } else {
                        $q2->where('tipe_lembur', 'not like', '%libur%');
                    }
                });
            })
            ->when($filterBulan, fn($q) => $q->whereMonth('lemburs.date', $filterBulan))
            ->when($filterTahun, fn($q) => $q->whereYear('lemburs.date', $filterTahun))
            ->orderBy('karyawans.Kode_karyawan', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Stats
        $statsQuery = Lembur::whereMonth('date', $filterBulan)->whereYear('date', $filterTahun);

        $totalDurasiJam    = (clone $statsQuery)->where('status_persetujuan', 'Disetujui')->sum('durasi_lembur');
        $totalNominal      = (clone $statsQuery)->where('status_persetujuan', 'Disetujui')->selectRaw('SUM(COALESCE(uang_lembur,0) + COALESCE(uang_makan,0)) as total')->value('total') ?? 0;
        $totalDisetujui    = (clone $statsQuery)->where('status_persetujuan', 'Disetujui')->count();
        $totalMenunggu     = (clone $statsQuery)->where('status_persetujuan', 'Menunggu')->count();
        $totalDitolak      = (clone $statsQuery)->where('status_persetujuan', 'Ditolak')->count();

        $tahunList = range(date('Y'), date('Y') - 5);

        return view('layouts.lembur.index', compact(
            'lemburs', 'search', 'filterStatus', 'filterJenis', 'filterBulan', 'filterTahun',
            'totalDurasiJam', 'totalNominal', 'totalDisetujui', 'totalMenunggu', 'totalDitolak',
            'tahunList'
        ));
    }

    /**
     * Halaman Persetujuan Lembur — khusus Manager & Super Admin.
     */
    public function approvalIndex(Request $request)
    {
        $search      = $request->get('search');
        $filterJenis = $request->get('jenis');
        $filterBulan = $request->get('bulan', date('m'));
        $filterTahun = $request->get('tahun', date('Y'));

        $pendingLemburs = Lembur::select('lemburs.*')
            ->leftJoin('karyawans', 'lemburs.ID_karyawan', '=', 'karyawans.ID_karyawan')
            ->with(['karyawan.divisi', 'karyawan.jabatan', 'kategoriLembur'])
            ->where('status_persetujuan', 'Menunggu')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('karyawan', function ($q2) use ($search) {
                    $q2->where('nama_karyawan', 'like', "%{$search}%")
                       ->orWhere('Kode_karyawan', 'like', "%{$search}%");
                });
            })
            ->when($filterJenis, function ($q) use ($filterJenis) {
                $q->whereHas('kategoriLembur', function ($q2) use ($filterJenis) {
                    if ($filterJenis === 'libur') {
                        $q2->where('tipe_lembur', 'like', '%libur%');
                    } else {
                        $q2->where('tipe_lembur', 'not like', '%libur%');
                    }
                });
            })
            ->when($filterBulan, fn($q) => $q->whereMonth('lemburs.date', $filterBulan))
            ->when($filterTahun, fn($q) => $q->whereYear('lemburs.date', $filterTahun))
            ->orderBy('karyawans.Kode_karyawan', 'asc')
            ->paginate(15)
            ->withQueryString();

        $totalPending = Lembur::where('status_persetujuan', 'Menunggu')->count();

        $tahunList = range(date('Y'), date('Y') - 5);

        return view('layouts.lembur.approval', compact(
            'pendingLemburs', 'search', 'filterJenis', 'filterBulan', 'filterTahun',
            'totalPending', 'tahunList'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CREATE & STORE
    // ──────────────────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $this->getKategoriHarianId();
        $this->getKategoriLiburId();

        $jenis           = $request->get('jenis', 'harian');
        $karyawans       = Karyawan::orderBy('nama_karyawan')->get();
        $kategoriLemburs = KategoriLembur::orderBy('ID_kategori_lembur')->get();

        return view('layouts.lembur.create_edit', [
            'lembur'          => null,
            'karyawans'       => $karyawans,
            'kategoriLemburs' => $kategoriLemburs,
            'defaultJenis'    => $jenis,
        ]);
    }

    public function store(Request $request)
    {
        $katHarianId = $this->getKategoriHarianId();
        $katLiburId  = $this->getKategoriLiburId();
        $approvalData = $this->buildAutoApprovalData();

        // Support Dynamic Multi-Row input
        if ($request->has('items') && is_array($request->items)) {
            $request->validate([
                'items.*.ID_karyawan' => 'required|exists:karyawans,ID_karyawan',
                'items.*.tanggal'     => 'required|date',
            ], [
                'items.*.ID_karyawan.required' => 'Karyawan wajib dipilih pada setiap baris.',
                'items.*.tanggal.required'     => 'Tanggal lembur wajib diisi pada setiap baris.',
            ]);

            $inserted = 0;
            foreach ($request->items as $item) {
                if (empty($item['ID_karyawan']) || empty($item['tanggal'])) continue;

                $jenisLembur = $item['jenis_lembur'] ?? 'harian';
                $kategoriId  = ($jenisLembur === 'libur') ? $katLiburId : $katHarianId;

                [$jamMulai, $jamSelesai, $durasi, $tarifLemburPerJam, $totalUangMakan, $totalUangLembur]
                    = $this->calculateLemburValues($jenisLembur, $item, $item['ID_karyawan'], $item['tanggal'] ?? null);

                Lembur::create(array_merge([
                    'ID_karyawan'        => $item['ID_karyawan'],
                    'ID_kategori_lembur' => $kategoriId,
                    'date'               => $item['tanggal'],
                    'jam_mulai'          => $jamMulai,
                    'jam_selesai'        => $jamSelesai,
                    'durasi_lembur'      => $durasi,
                    'tarif_per_jam'      => $tarifLemburPerJam,
                    'uang_makan'         => $totalUangMakan,
                    'uang_lembur'        => $totalUangLembur,
                    'deskripsi'          => $item['keterangan'] ?? '-',
                ], $approvalData));

                $inserted++;
            }

            $statusMsg = $approvalData['status_persetujuan'] === 'Disetujui'
                ? 'dan langsung disetujui'
                : '(menunggu persetujuan Manager)';

            return redirect()->route('lembur.index')
                ->with('success', "{$inserted} Data pengajuan lembur berhasil ditambahkan {$statusMsg}.");
        }

        // Single Input Fallback
        $request->validate([
            'ID_karyawan' => 'required|exists:karyawans,ID_karyawan',
            'tanggal'     => 'required|date',
        ]);

        $jenisLembur = $request->input('jenis_lembur', 'harian');
        $kategoriId  = ($jenisLembur === 'libur') ? $katLiburId : $katHarianId;

        [$jamMulai, $jamSelesai, $durasi, $tarifLemburPerJam, $totalUangMakan, $totalUangLembur]
            = $this->calculateLemburValues($jenisLembur, $request->all(), $request->ID_karyawan, $request->tanggal);

        Lembur::create(array_merge([
            'ID_karyawan'        => $request->ID_karyawan,
            'ID_kategori_lembur' => $kategoriId,
            'date'               => $request->tanggal,
            'jam_mulai'          => $jamMulai,
            'jam_selesai'        => $jamSelesai,
            'durasi_lembur'      => $durasi,
            'tarif_per_jam'      => $tarifLemburPerJam,
            'uang_makan'         => $totalUangMakan,
            'uang_lembur'        => $totalUangLembur,
            'deskripsi'          => $request->keterangan ?? '-',
        ], $approvalData));

        $statusMsg = $approvalData['status_persetujuan'] === 'Disetujui'
            ? 'dan langsung disetujui'
            : '(menunggu persetujuan Manager)';

        return redirect()->route('lembur.index')
            ->with('success', "Data lembur berhasil ditambahkan {$statusMsg}.");
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EDIT & UPDATE
    // ──────────────────────────────────────────────────────────────────────────

    public function edit(Lembur $lembur)
    {
        $this->getKategoriHarianId();
        $this->getKategoriLiburId();

        $karyawans       = Karyawan::orderBy('nama_karyawan')->get();
        $kategoriLemburs = KategoriLembur::orderBy('ID_kategori_lembur')->get();

        return view('layouts.lembur.create_edit', compact('lembur', 'karyawans', 'kategoriLemburs'));
    }

    public function update(Request $request, Lembur $lembur)
    {
        $request->validate([
            'ID_karyawan' => 'required|exists:karyawans,ID_karyawan',
            'tanggal'     => 'required|date',
        ]);

        $katHarianId = $this->getKategoriHarianId();
        $katLiburId  = $this->getKategoriLiburId();

        $jenisLembur = $request->input('jenis_lembur', $lembur->jenis_lembur ?? 'harian');
        $kategoriId  = ($jenisLembur === 'libur') ? $katLiburId : $katHarianId;

        [$jamMulai, $jamSelesai, $durasi, $tarifLemburPerJam, $totalUangMakan, $totalUangLembur]
            = $this->calculateLemburValues($jenisLembur, $request->all(), $request->ID_karyawan, $request->tanggal);

        $lembur->update([
            'ID_karyawan'        => $request->ID_karyawan,
            'ID_kategori_lembur' => $kategoriId,
            'date'               => $request->tanggal,
            'jam_mulai'          => $jamMulai,
            'jam_selesai'        => $jamSelesai,
            'durasi_lembur'      => $durasi,
            'tarif_per_jam'      => $tarifLemburPerJam,
            'uang_makan'         => $totalUangMakan,
            'uang_lembur'        => $totalUangLembur,
            'deskripsi'          => $request->keterangan ?? '-',
        ]);

        return redirect()->route('lembur.index')
            ->with('success', 'Data lembur berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // APPROVE & REJECT
    // ──────────────────────────────────────────────────────────────────────────

    public function approve(Request $request, Lembur $lembur)
    {
        $lembur->update([
            'status_persetujuan' => 'Disetujui',
            'id_approved_by'     => auth()->id(),
            'tanggal_approval'   => Carbon::now(),
            'catatan_approval'   => null,
        ]);

        $redirectRoute = $request->input('from') === 'approval'
            ? 'lembur.approval'
            : 'lembur.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Pengajuan lembur ' . ($lembur->karyawan->nama_karyawan ?? '') . ' berhasil disetujui.');
    }

    public function reject(Request $request, Lembur $lembur)
    {
        $request->validate([
            'catatan_approval' => 'required|string|min:3|max:500',
        ], [
            'catatan_approval.required' => 'Alasan penolakan wajib diisi.',
            'catatan_approval.min'      => 'Alasan penolakan minimal 3 karakter.',
        ]);

        $lembur->update([
            'status_persetujuan' => 'Ditolak',
            'id_approved_by'     => auth()->id(),
            'tanggal_approval'   => Carbon::now(),
            'catatan_approval'   => $request->catatan_approval,
        ]);

        $redirectRoute = $request->input('from') === 'approval'
            ? 'lembur.approval'
            : 'lembur.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Pengajuan lembur ditolak dengan alasan: ' . $request->catatan_approval);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────────────────

    public function destroy(Lembur $lembur)
    {
        $lembur->delete();
        return redirect()->route('lembur.index')
            ->with('success', 'Data lembur berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE: KALKULASI NILAI LEMBUR (dengan guard nilai negatif)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Hitung jam mulai, jam selesai, durasi, tarif, uang_makan, uang_lembur.
     * Semua hasil dipastikan >= 0 (guard nilai negatif).
     *
     * @return array [$jamMulai, $jamSelesai, $durasi, $tarifPerJam, $totalUangMakan, $totalUangLembur]
     */
    private function calculateLemburValues(string $jenisLembur, array $data, $karyawanId, ?string $tanggal): array
    {
        if ($jenisLembur === 'libur') {
            // ── LEMBUR HARI LIBUR ──────────────────────────────────────────
            // Rumus BENAR: 8 jam × tarif_per_jam × 2 (bukan jam aktual)
            $rates = $this->getEmployeeRate($karyawanId, $tanggal);

            $jamMulai   = !empty($data['jam_mulai'])   ? $data['jam_mulai']   : '08:00';
            $jamSelesai = !empty($data['jam_selesai']) ? $data['jam_selesai'] : '17:00';
            $durasi     = 9.0; // default tampilan, tidak dipakai untuk kalkulasi uang

            if ($jamMulai && $jamSelesai) {
                try {
                    $t1 = Carbon::parse($jamMulai);
                    $t2 = Carbon::parse($jamSelesai);
                    if ($t2->lt($t1)) $t2->addDay();
                    $durasi = max(0, round($t2->diffInMinutes($t1) / 60, 1));
                } catch (\Exception $e) {
                    Log::warning("Lembur: gagal parse jam libur karyawan {$karyawanId}", ['jam_mulai' => $jamMulai, 'jam_selesai' => $jamSelesai]);
                }
            }

            $totalUangLembur  = max(0, $rates['lembur_libur_gaji_pokok']);
            $totalUangMakan   = max(0, $rates['lembur_libur_uang_makan']);
            $tarifLemburPerJam = 0;

        } else {
            // ── LEMBUR HARIAN ─────────────────────────────────────────────
            $jamMulai   = $data['jam_mulai'] ?? null;
            $jamSelesai = $data['jam_selesai'] ?? null;
            $durasi     = floatval($data['durasi_jam'] ?? 0);

            if ($jamMulai && $jamSelesai) {
                try {
                    $t1 = Carbon::parse($jamMulai);
                    $t2 = Carbon::parse($jamSelesai);
                    // Gunakan absolute diff, lalu tambah hari jika jam selesai < jam mulai (crossday)
                    if ($t2->lt($t1)) $t2->addDay();
                    $diffMenit = (int) $t2->diffInMinutes($t1); // absolute, selalu positif

                    if ($diffMenit <= 0) {
                        Log::warning("Lembur: durasi 0 atau negatif karyawan {$karyawanId}", [
                            'jam_mulai'   => $jamMulai,
                            'jam_selesai' => $jamSelesai,
                        ]);
                        $durasi = 0;
                    } else {
                        $durasi = round($diffMenit / 60, 1);
                    }
                } catch (\Exception $e) {
                    Log::warning("Lembur: gagal parse jam harian karyawan {$karyawanId}", ['jam_mulai' => $jamMulai, 'jam_selesai' => $jamSelesai]);
                    $durasi = 0;
                }
            }

            $durasi = max(0, $durasi);

            $tarifLemburPerJam = max(0, floatval($data['tarif_per_jam'] ?? 20000));
            $uangMakanPerJam   = max(0, floatval($data['uang_makan'] ?? 5000));

            $totalUangMakan  = max(0, $durasi * $uangMakanPerJam);
            $totalUangLembur = max(0, ($durasi * $tarifLemburPerJam) + $totalUangMakan);
        }

        return [$jamMulai, $jamSelesai, $durasi, $tarifLemburPerJam, $totalUangMakan, $totalUangLembur];
    }
}
