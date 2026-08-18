<?php

namespace App\Http\Controllers;

use App\Models\Kasbon;
use App\Models\Karyawan;
use App\Models\DetailPayroll;
use Illuminate\Http\Request;

class KasbonController extends Controller
{
    public function index(Request $request)
    {
        $search        = $request->get('search');
        $filterStatus  = $request->get('status');
        $filterBulan   = $request->get('bulan', date('m'));
        $filterTahun   = $request->get('tahun', date('Y'));

        $kasbons = Kasbon::select('kasbons.*')
            ->leftJoin('karyawans', 'kasbons.ID_karyawan', '=', 'karyawans.ID_karyawan')
            ->with(['karyawan.divisi', 'karyawan.jabatan'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('karyawan', function ($q2) use ($search) {
                    $q2->where('nama_karyawan', 'like', "%{$search}%")
                       ->orWhere('Kode_karyawan', 'like', "%{$search}%");
                });
            })
            ->when($filterStatus, fn($q) => $q->where('status', $filterStatus))
            ->when($filterBulan, fn($q) => $q->whereMonth('kasbons.date', $filterBulan))
            ->when($filterTahun, fn($q) => $q->whereYear('kasbons.date', $filterTahun))
            ->orderBy('karyawans.Kode_karyawan', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Calculate Stats
        $statsQuery = Kasbon::whereMonth('date', $filterBulan)->whereYear('date', $filterTahun);

        $totalNominalKasbon = (clone $statsQuery)->whereIn('status', ['Disetujui', 'Lunas'])->sum('total_kasbon');
        $totalPending       = (clone $statsQuery)->where('status', 'Pending')->count();
        $totalDisetujui     = (clone $statsQuery)->where('status', 'Disetujui')->count();
        $totalLunas         = (clone $statsQuery)->where('status', 'Lunas')->count();

        $tahunList = range(date('Y'), date('Y') - 5);

        return view('layouts.kasbon.index', compact(
            'kasbons', 'search', 'filterStatus', 'filterBulan', 'filterTahun',
            'totalNominalKasbon', 'totalPending', 'totalDisetujui', 'totalLunas',
            'tahunList'
        ));
    }

    public function create()
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        return view('layouts.kasbon.create_edit', ['kasbon' => null, 'karyawans' => $karyawans]);
    }

    public function store(Request $request)
    {
        // Support Dynamic Multi-Row input
        if ($request->has('items') && is_array($request->items)) {
            $request->validate([
                'items.*.ID_karyawan'  => 'required|exists:karyawans,ID_karyawan',
                'items.*.tanggal'      => 'required|date',
                'items.*.total_kasbon' => 'required|numeric|min:0',
            ], [
                'items.*.ID_karyawan.required'  => 'Karyawan wajib dipilih pada setiap baris.',
                'items.*.tanggal.required'      => 'Tanggal kasbon wajib diisi.',
                'items.*.total_kasbon.required' => 'Nominal kasbon wajib diisi.',
            ]);

            $inserted = 0;
            foreach ($request->items as $item) {
                if (empty($item['ID_karyawan']) || empty($item['tanggal'])) continue;

                Kasbon::create([
                    'ID_karyawan'  => $item['ID_karyawan'],
                    'date'         => $item['tanggal'],
                    'total_kasbon' => floatval($item['total_kasbon'] ?? 0),
                    'deskripsi'    => $item['deskripsi'] ?? '-',
                    'status'       => $item['status'] ?? 'Disetujui',
                ]);
                $inserted++;
            }

            return redirect()->route('kasbon.index')
                ->with('success', "{$inserted} Data pengajuan kasbon berhasil ditambahkan.");
        }

        // Single Input Fallback
        $request->validate([
            'ID_karyawan'  => 'required|exists:karyawans,ID_karyawan',
            'tanggal'      => 'required|date',
            'total_kasbon' => 'required|numeric|min:0',
        ]);

        Kasbon::create([
            'ID_karyawan'  => $request->ID_karyawan,
            'date'         => $request->tanggal,
            'total_kasbon' => floatval($request->total_kasbon),
            'deskripsi'    => $request->deskripsi ?? '-',
            'status'       => $request->status ?? 'Disetujui',
        ]);

        return redirect()->route('kasbon.index')
            ->with('success', 'Data kasbon berhasil ditambahkan.');
    }

    public function edit(Kasbon $kasbon)
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        return view('layouts.kasbon.create_edit', compact('kasbon', 'karyawans'));
    }

    public function update(Request $request, Kasbon $kasbon)
    {
        $request->validate([
            'ID_karyawan'  => 'required|exists:karyawans,ID_karyawan',
            'tanggal'      => 'required|date',
            'total_kasbon' => 'required|numeric|min:0',
        ]);

        $kasbon->update([
            'ID_karyawan'  => $request->ID_karyawan,
            'date'         => $request->tanggal,
            'total_kasbon' => floatval($request->total_kasbon),
            'deskripsi'    => $request->deskripsi ?? '-',
            'status'       => $request->status ?? 'Disetujui',
        ]);

        return redirect()->route('kasbon.index')
            ->with('success', 'Data kasbon berhasil diperbarui.');
    }

    public function approve(Kasbon $kasbon)
    {
        $kasbon->update(['status' => 'Disetujui']);
        return redirect()->route('kasbon.index')
            ->with('success', 'Pengajuan kasbon berhasil disetujui.');
    }

    public function reject(Kasbon $kasbon)
    {
        $kasbon->update(['status' => 'Ditolak']);
        return redirect()->route('kasbon.index')
            ->with('success', 'Pengajuan kasbon ditolak.');
    }

    public function lunas(Kasbon $kasbon)
    {
        $kasbon->update(['status' => 'Lunas']);
        return redirect()->route('kasbon.index')
            ->with('success', 'Status kasbon berhasil diubah menjadi Lunas.');
    }

    public function destroy(Kasbon $kasbon)
    {
        $kasbon->delete();
        return redirect()->route('kasbon.index')
            ->with('success', 'Data kasbon berhasil dihapus.');
    }

    public function riwayat(Request $request, Kasbon $kasbon)
    {
        $kasbon->load(['karyawan.divisi', 'karyawan.jabatan']);

        $details = DetailPayroll::with('payroll')
            ->where('ID_kasbon', $kasbon->ID_kasbon)
            ->where('jenis', 'kasbon')
            ->orderBy('id', 'asc')
            ->get();

        $totalKasbon       = floatval($kasbon->total_kasbon);
        $totalSudahDibayar = $kasbon->getTotalSudahDibayar();
        $sisaKasbon        = $kasbon->getSisaKasbon();

        if ($sisaKasbon <= 0 && $totalSudahDibayar > 0 && $kasbon->status !== 'Lunas') {
            $kasbon->update(['status' => 'Lunas']);
        }

        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $history = $details->map(function($d, $idx) use ($namaBulan) {
            $pDate = $d->payroll?->date;
            $bulanStr = $pDate ? ($namaBulan[$pDate->format('m')] ?? $pDate->format('F')) : '-';
            $tahunStr = $pDate ? $pDate->format('Y') : '';
            $angsuranKe = $idx + 1;
            return [
                'ID_detail_payroll' => $d->id ?? $d->ID_detail_payroll ?? null,
                'angsuran_ke'       => $angsuranKe,
                'periode'           => "Payroll {$bulanStr} {$tahunStr}",
                'tanggal'           => $pDate ? $pDate->format('d/m/Y') : '-',
                'nominal'           => abs(floatval($d->subtotal)),
                'keterangan'        => "Angsuran ke-{$angsuranKe}" . ($d->keterangan ? " ({$d->keterangan})" : ""),
            ];
        });

        return response()->json([
            'success' => true,
            'kasbon'  => [
                'ID_kasbon'           => $kasbon->ID_kasbon,
                'nama_karyawan'       => $kasbon->karyawan->nama_karyawan ?? '-',
                'Kode_karyawan'       => $kasbon->karyawan->Kode_karyawan ?? '-',
                'tanggal_pengambilan' => $kasbon->date ? $kasbon->date->format('d/m/Y') : '-',
                'total_kasbon'        => $totalKasbon,
                'total_sudah_dibayar' => $totalSudahDibayar,
                'sisa_kasbon'         => $sisaKasbon,
                'status'              => $kasbon->status,
                'deskripsi'           => $kasbon->deskripsi ?? '-',
            ],
            'riwayat' => $history,
        ]);
    }
}
