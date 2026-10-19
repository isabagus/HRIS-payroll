<?php

namespace App\Http\Controllers;

use App\Models\Kasbon;
use App\Models\Karyawan;
use App\Models\DetailPayroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KasbonController extends Controller
{
    private function isAutoApproveRole(): bool
    {
        $user = Auth::user();
        if (!$user || !$user->role) return false;

        $roleName = strtolower($user->role->nama_role ?? '');
        return str_contains($roleName, 'manager') || str_contains($roleName, 'super admin');
    }

    private function canApprove(): bool
    {
        return $this->isAutoApproveRole();
    }

    public function index(Request $request)
    {
        $search               = $request->get('search');
        $filterStatusApproval = $request->get('status_approval');
        $filterStatusPelunasan = $request->get('status_pelunasan');
        $filterBulan          = $request->get('bulan', date('m'));
        $filterTahun          = $request->get('tahun', date('Y'));

        $kasbons = Kasbon::select('kasbons.*')
            ->leftJoin('karyawans', 'kasbons.ID_karyawan', '=', 'karyawans.ID_karyawan')
            ->with(['karyawan.divisi', 'karyawan.jabatan'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('karyawan', function ($q2) use ($search) {
                    $q2->where('nama_karyawan', 'like', "%{$search}%")
                       ->orWhere('Kode_karyawan', 'like', "%{$search}%");
                });
            })
            ->when($filterStatusApproval, fn($q) => $q->where('kasbons.status_approval', $filterStatusApproval))
            ->when($filterStatusPelunasan, fn($q) => $q->where('kasbons.status_pelunasan', $filterStatusPelunasan))
            ->when($filterBulan, fn($q) => $q->whereMonth('kasbons.date', $filterBulan))
            ->when($filterTahun, fn($q) => $q->whereYear('kasbons.date', $filterTahun))
            ->orderBy('karyawans.Kode_karyawan', 'asc')
            ->paginate(10)
            ->withQueryString();

        $statsQuery = Kasbon::whereMonth('date', $filterBulan)->whereYear('date', $filterTahun);

        $totalNominalKasbon     = (clone $statsQuery)->where('status_approval', 'disetujui')->sum('total_kasbon');
        $totalPending           = (clone $statsQuery)->where('status_approval', 'pending')->count();
        $totalDisetujuiApproval = (clone $statsQuery)->where('status_approval', 'disetujui')->count();
        $totalLunas             = (clone $statsQuery)->where('status_pelunasan', 'lunas')->count();

        $tahunList = range(date('Y'), date('Y') - 5);

        return view('layouts.kasbon.index', compact(
            'kasbons', 'search', 'filterStatusApproval', 'filterStatusPelunasan', 'filterBulan', 'filterTahun',
            'totalNominalKasbon', 'totalPending', 'totalDisetujuiApproval', 'totalLunas',
            'tahunList'
        ));
    }

    public function approvalIndex(Request $request)
    {
        if (!$this->canApprove()) {
            abort(403, 'Akses hanya untuk Manager dan Super Admin.');
        }

        $search      = $request->get('search');
        $filterBulan = $request->get('bulan', date('m'));
        $filterTahun = $request->get('tahun', date('Y'));

        $pendingKasbons = Kasbon::select('kasbons.*')
            ->leftJoin('karyawans', 'kasbons.ID_karyawan', '=', 'karyawans.ID_karyawan')
            ->with(['karyawan.divisi', 'karyawan.jabatan'])
            ->where('kasbons.status_approval', 'pending')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('karyawan', function ($q2) use ($search) {
                    $q2->where('nama_karyawan', 'like', "%{$search}%")
                       ->orWhere('Kode_karyawan', 'like', "%{$search}%");
                });
            })
            ->when($filterBulan, fn($q) => $q->whereMonth('kasbons.date', $filterBulan))
            ->when($filterTahun, fn($q) => $q->whereYear('kasbons.date', $filterTahun))
            ->orderBy('karyawans.Kode_karyawan', 'asc')
            ->paginate(15)
            ->withQueryString();

        $totalPending = Kasbon::where('status_approval', 'pending')->count();
        $tahunList    = range(date('Y'), date('Y') - 5);

        return view('layouts.kasbon.approval', compact(
            'pendingKasbons', 'search', 'filterBulan', 'filterTahun',
            'totalPending', 'tahunList'
        ));
    }

    public function approvalSetujui(Request $request, Kasbon $kasbon)
    {
        if (!$this->canApprove()) {
            abort(403, 'Akses hanya untuk Manager dan Super Admin.');
        }

        $kasbon->update([
            'status_approval'  => 'disetujui',
            'id_approved_by'   => Auth::id(),
            'tanggal_approval' => Carbon::now(),
            'catatan_approval' => null,
        ]);

        $redirectRoute = $request->input('from') === 'approval' ? 'kasbon.approval' : 'kasbon.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Kasbon ' . ($kasbon->karyawan->nama_karyawan ?? '') . ' berhasil disetujui.');
    }

    public function approvalTolak(Request $request, Kasbon $kasbon)
    {
        if (!$this->canApprove()) {
            abort(403, 'Akses hanya untuk Manager dan Super Admin.');
        }

        $request->validate([
            'catatan_approval' => 'nullable|string|max:500',
        ]);

        $kasbon->update([
            'status_approval'  => 'ditolak',
            'id_approved_by'   => Auth::id(),
            'tanggal_approval' => Carbon::now(),
            'catatan_approval' => $request->input('catatan_approval') ?: null,
        ]);

        $redirectRoute = $request->input('from') === 'approval' ? 'kasbon.approval' : 'kasbon.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Kasbon ' . ($kasbon->karyawan->nama_karyawan ?? '') . ' ditolak.');
    }

    public function create()
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        return view('layouts.kasbon.create_edit', ['kasbon' => null, 'karyawans' => $karyawans]);
    }

    public function store(Request $request)
    {
        $isAutoApprove = $this->isAutoApproveRole();
        $approvalData  = $isAutoApprove
            ? [
                'status_approval'  => 'disetujui',
                'id_approved_by'   => Auth::id(),
                'tanggal_approval' => Carbon::now(),
                'catatan_approval' => null,
            ]
            : [
                'status_approval'  => 'pending',
                'id_approved_by'   => null,
                'tanggal_approval' => null,
                'catatan_approval' => null,
            ];

        if ($request->has('items') && is_array($request->items)) {
            $items = $request->items;
            foreach ($items as &$it) {
                if (isset($it['total_kasbon'])) {
                    $it['total_kasbon'] = str_replace(['.', ','], '', (string)$it['total_kasbon']);
                }
            }
            unset($it);
            $request->merge(['items' => $items]);

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

                Kasbon::create(array_merge([
                    'ID_karyawan'       => $item['ID_karyawan'],
                    'date'              => $item['tanggal'],
                    'total_kasbon'      => floatval($item['total_kasbon'] ?? 0),
                    'deskripsi'         => $item['deskripsi'] ?? '-',
                    'status_pelunasan'  => 'belum_lunas',
                ], $approvalData));
                $inserted++;
            }

            $statusMsg = $isAutoApprove ? 'dan langsung disetujui' : '(menunggu persetujuan Manager)';
            return redirect()->route('kasbon.index')
                ->with('success', "{$inserted} Data pengajuan kasbon berhasil ditambahkan {$statusMsg}.");
        }

        if ($request->has('total_kasbon')) {
            $request->merge([
                'total_kasbon' => str_replace(['.', ','], '', (string)$request->total_kasbon)
            ]);
        }

        $request->validate([
            'ID_karyawan'  => 'required|exists:karyawans,ID_karyawan',
            'tanggal'      => 'required|date',
            'total_kasbon' => 'required|numeric|min:0',
        ]);

        Kasbon::create(array_merge([
            'ID_karyawan'      => $request->ID_karyawan,
            'date'             => $request->tanggal,
            'total_kasbon'     => floatval($request->total_kasbon),
            'deskripsi'        => $request->deskripsi ?? '-',
            'status_pelunasan' => 'belum_lunas',
        ], $approvalData));

        $statusMsg = $isAutoApprove ? 'dan langsung disetujui' : '(menunggu persetujuan Manager)';
        return redirect()->route('kasbon.index')
            ->with('success', "Data kasbon berhasil ditambahkan {$statusMsg}.");
    }

    public function edit(Kasbon $kasbon)
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        return view('layouts.kasbon.create_edit', compact('kasbon', 'karyawans'));
    }

    public function update(Request $request, Kasbon $kasbon)
    {
        if ($request->has('total_kasbon')) {
            $request->merge([
                'total_kasbon' => str_replace(['.', ','], '', (string)$request->total_kasbon)
            ]);
        }

        $request->validate([
            'ID_karyawan'      => 'required|exists:karyawans,ID_karyawan',
            'tanggal'          => 'required|date',
            'total_kasbon'     => 'required|numeric|min:0',
            'status_approval'  => 'nullable|in:pending,disetujui,ditolak',
        ]);

        $kasbon->update([
            'ID_karyawan'      => $request->ID_karyawan,
            'date'             => $request->tanggal,
            'total_kasbon'     => floatval($request->total_kasbon),
            'deskripsi'        => $request->deskripsi ?? '-',
            'status_approval'  => $request->status_approval ?? $kasbon->status_approval,
        ]);

        $kasbon->syncStatusPelunasan();

        return redirect()->route('kasbon.index')
            ->with('success', 'Data kasbon berhasil diperbarui.');
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

        $kasbon->syncStatusPelunasan();

        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $history = $details->map(function ($d, $idx) use ($namaBulan) {
            $pDate    = $d->payroll?->date;
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
                'status_approval'     => $kasbon->status_approval,
                'status_pelunasan'    => $kasbon->status_pelunasan,
                'deskripsi'           => $kasbon->deskripsi ?? '-',
            ],
            'riwayat' => $history,
        ]);
    }
}
