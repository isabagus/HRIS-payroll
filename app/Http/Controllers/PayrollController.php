<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\DetailPayroll;
use App\Models\Karyawan;
use App\Models\GajiKaryawan;
use App\Models\DetailGajiKaryawan;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\Kasbon;
use App\Exports\PayrollExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;


class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $search      = $request->get('search');
        $filterBulan = $request->get('bulan', date('m'));
        $filterTahun = $request->get('tahun', date('Y'));

        $payrolls = Payroll::select('payrolls.*')
            ->leftJoin('karyawans', 'payrolls.ID_karyawan', '=', 'karyawans.ID_karyawan')
            ->with(['karyawan.divisi', 'karyawan.jabatan', 'karyawan.instansi'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('karyawan', function ($q2) use ($search) {
                    $q2->where('nama_karyawan', 'like', "%{$search}%")
                       ->orWhere('Kode_karyawan', 'like', "%{$search}%");
                });
            })
            ->when($filterBulan, fn($q) => $q->whereMonth('payrolls.date', $filterBulan))
            ->when($filterTahun, fn($q) => $q->whereYear('payrolls.date', $filterTahun))
            ->orderBy('karyawans.Kode_karyawan', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Calculate Stats
        $allPayrollPeriod = Payroll::whereMonth('date', $filterBulan)
            ->whereYear('date', $filterTahun)
            ->get();

        $totalGajiPokok   = $allPayrollPeriod->sum(fn($p) => $p->gaji_pokok_kalkulasi);
        $totalLemburan    = $allPayrollPeriod->sum(fn($p) => $p->total_lemburan);
        $totalKasbon      = $allPayrollPeriod->sum(fn($p) => $p->total_kasbon);
        $totalPengeluaran = $allPayrollPeriod->sum(fn($p) => $p->gaji_bersih);
        $totalKaryawanDug = $allPayrollPeriod->count();

        $tahunList = range(date('Y'), date('Y') - 5);

        return view('layouts.payroll.index', compact(
            'payrolls', 'search', 'filterBulan', 'filterTahun',
            'totalGajiPokok', 'totalLemburan', 'totalKasbon', 'totalPengeluaran', 'totalKaryawanDug',
            'tahunList'
        ));
    }

    public function review(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $allKaryawans = Karyawan::with(['divisi', 'jabatan'])->get();

        if ($allKaryawans->isEmpty()) {
            return redirect()->route('payroll.index')
                ->with('error', 'Belum ada data karyawan di database.');
        }

        // STEP 1: VALIDASI SEBELUM PROSES (Check existing payroll rows)
        $existingPayrollEmpIds = Payroll::whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->pluck('ID_karyawan')
            ->toArray();

        $skippedEmployees = [];
        $noRateEmployees  = [];
        $reviewItems      = [];

        foreach ($allKaryawans as $karyawan) {
            $id = $karyawan->ID_karyawan;

            if (in_array($id, $existingPayrollEmpIds)) {
                $skippedEmployees[] = $karyawan;
                continue;
            }

            $gajiHeader = GajiKaryawan::where('ID_karyawan', $id)
                ->where('status', 'Aktif')
                ->latest()
                ->first();

            if (!$gajiHeader) {
                $noRateEmployees[] = $karyawan;
                continue;
            }

            $details = DetailGajiKaryawan::with('komponenGaji')
                ->where('ID_gaji', $gajiHeader->ID_gaji)
                ->get();

            $gajiPokokRate        = 0;
            $uangMakanRate        = 0;
            $mingguanRate         = 0;
            $kerajinanRate        = 0;
            $bulananRate          = 0;
            $tunjanganJabatanRate = 0;

            foreach ($details as $det) {
                $n   = strtolower($det->komponenGaji->nama_gaji ?? '');
                $val = floatval($det->nominal_gaji);

                if (str_contains($n, 'pokok')) {
                    $gajiPokokRate = $val;
                } elseif (str_contains($n, 'makan')) {
                    $uangMakanRate = $val;
                } elseif (str_contains($n, 'mingguan')) {
                    $mingguanRate = $val;
                } elseif (str_contains($n, 'kerajinan')) {
                    $kerajinanRate = $val;
                } elseif (str_contains($n, 'bulanan')) {
                    $bulananRate = $val;
                } elseif (str_contains($n, 'jabatan')) {
                    $tunjanganJabatanRate = $val;
                }
            }

            if ($gajiPokokRate <= 0 && $karyawan->gaji_pokok > 0) {
                $gajiPokokRate = floatval($karyawan->gaji_pokok);
            }

            // 1. Calculate Attendance using AbsensiService
            $absensis = Absensi::where('ID_karyawan', $id)
                ->whereMonth('date', $bulan)
                ->whereYear('date', $tahun)
                ->get();

            $countHadir         = 0;
            $countTelat         = 0;
            $countSetengahHari  = 0;
            $totalIzin          = 0;
            $totalAlpha         = 0;
            $totalJamKerjaBulan = 0.0;
            $daysPresentCount   = 0;

            foreach ($absensis as $a) {
                $calc = \App\Services\AbsensiService::hitungJamKerja($karyawan, $a->jam_masuk, $a->jam_pulang, $a->status_absensi);

                $jamKerjaHari        = $calc['total_jam_kerja'];
                $totalJamKerjaBulan += $jamKerjaHari;

                if ($jamKerjaHari > 0) {
                    $daysPresentCount++;
                }

                $st = $calc['status_absensi'];
                if ($st === 'Hadir') $countHadir++;
                elseif ($st === 'Telat') $countTelat++;
                elseif ($st === 'Setengah Hari') $countSetengahHari++;
                elseif (in_array($st, ['Izin', 'Sakit'])) $totalIzin++;
                elseif ($st === 'Alpha') $totalAlpha++;
            }

            // Total Masuk represents count of days present (for Uang Makan flat daily rate)
            $totalMasuk = $daysPresentCount;

            // Weekly attendance check for Mingguan & Kerajinan
            // Bonus Mingguan: syarat = 5+ hari hadir, TIDAK ADA Alpha (boleh Telat)
            // Bonus Kerajinan: syarat LEBIH KETAT = 5+ hari hadir, TIDAK ADA Alpha, TIDAK ADA Telat, TIDAK ADA SetengahHari
            $fullWeeksCount      = 0; // dapat Mingguan
            $kerajinanWeeksCount = 0; // dapat Kerajinan

            for ($w = 1; $w <= 4; $w++) {
                $startDay = ($w - 1) * 7 + 1;
                $endDay   = min($w * 7, 31);

                $weekAbs = $absensis->filter(function ($a) use ($startDay, $endDay) {
                    $d = $a->date ? $a->date->day : 1;
                    return $d >= $startDay && $d <= $endDay;
                });

                $weekHadirCount        = 0;
                $weekAlphaCount        = 0;
                $weekLateCount         = 0;
                $weekSetengahHariCount = 0;

                foreach ($weekAbs as $wa) {
                    $wCalc = \App\Services\AbsensiService::hitungJamKerja($karyawan, $wa->jam_masuk, $wa->jam_pulang, $wa->status_absensi);
                    $wSt   = $wCalc['status_absensi'];

                    if ($wSt === 'Hadir') {
                        $weekHadirCount++;
                    } elseif ($wSt === 'Telat') {
                        $weekHadirCount++;  // tetap hadir, tidak Alpha
                        $weekLateCount++;
                    } elseif ($wSt === 'Setengah Hari') {
                        $weekHadirCount++;  // tetap hadir, tidak Alpha
                        $weekSetengahHariCount++;
                    } elseif ($wSt === 'Alpha' || $wSt === 'Alpa') {
                        $weekAlphaCount++;
                    }
                }

                // Bonus MINGGUAN: tidak ada Alpha (Telat masih boleh)
                if ($weekHadirCount >= 5 && $weekAlphaCount == 0) {
                    $fullWeeksCount++;

                    // Bonus KERAJINAN: tidak ada Alpha, tidak ada Telat, tidak ada Setengah Hari
                    if ($weekLateCount == 0 && $weekSetengahHariCount == 0) {
                        $kerajinanWeeksCount++;
                    }
                }
            }

            // Calculated Income Components
            $totalGajiPokokHarian  = $totalJamKerjaBulan * $gajiPokokRate;
            $totalUangMakan        = $totalMasuk * $uangMakanRate;
            $totalMingguan         = $fullWeeksCount * $mingguanRate;
            $totalKerajinan        = $kerajinanWeeksCount * $kerajinanRate;
            $totalBulanan          = $bulananRate;
            $totalTunjanganJabatan = ($karyawan->id_jabatan && $tunjanganJabatanRate > 0) ? $tunjanganJabatanRate : 0;

            // 2. Overtime Calculation (UNPAID ONLY: whereDoesntHave detailPayrolls)
            $unpaidLemburs = Lembur::where('ID_karyawan', $id)
                ->whereMonth('date', $bulan)
                ->whereYear('date', $tahun)
                ->where('status_persetujuan', 'Disetujui')
                ->whereDoesntHave('detailPayrolls')
                ->get();

            $totalLemburan = $unpaidLemburs->sum(fn($l) => floatval($l->uang_lembur) + floatval($l->uang_makan));

            // 3. Kasbon Active Reference — hanya kasbon yang SUDAH disetujui approval
            $activeKasbons = Kasbon::where('ID_karyawan', $id)
                ->where('status_approval', 'disetujui')
                ->where('status_pelunasan', 'belum_lunas')
                ->get();
            $sisaKasbonAktif = 0;
            foreach ($activeKasbons as $ksb) {
                $sisa = $ksb->getSisaKasbon();
                if ($sisa <= 0) {
                    $ksb->syncStatusPelunasan();
                } else {
                    $sisaKasbonAktif += $sisa;
                }
            }

            $potonganBpjs = 40000; // Flat BPJS
            $potonganLain = 0;     // Optional custom penalty deduction

            $totalPendapatan = $totalGajiPokokHarian + $totalUangMakan + $totalMingguan + $totalKerajinan + $totalBulanan + $totalTunjanganJabatan + $totalLemburan;
            $initialThp      = max(0, $totalPendapatan - ($potonganBpjs + $potonganLain));

            $reviewItems[] = [
                'karyawan'                => $karyawan,
                'ID_karyawan'             => $id,
                'ID_gaji'                 => $gajiHeader->ID_gaji,
                'gaji_pokok_rate'         => $gajiPokokRate,
                'total_masuk'             => $totalMasuk,
                'total_jam_kerja'         => $totalJamKerjaBulan,
                'count_hadir'             => $countHadir,
                'count_telat'             => $countTelat,
                'count_setengah_hari'     => $countSetengahHari,
                'total_izin'              => $totalIzin,
                'total_alpha'             => $totalAlpha,
                'total_gaji_pokok_harian' => $totalGajiPokokHarian,
                'total_uang_makan'        => $totalUangMakan,
                'total_mingguan'          => $totalMingguan,
                'total_kerajinan'         => $totalKerajinan,
                'total_bulanan'           => $totalBulanan,
                'total_tunjangan_jabatan'  => $totalTunjanganJabatan,
                'total_lemburan'          => $totalLemburan,
                'sisa_kasbon_aktif'       => $sisaKasbonAktif,
                'potongan_kasbon'         => 0,
                'potongan_bpjs'           => $potonganBpjs,
                'bpjs_perusahaan'         => 96000,
                'potongan_lain'           => $potonganLain,
                'total_pendapatan'        => $totalPendapatan,
                'gaji_bersih'             => $initialThp,
            ];
        }

        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $namaBulanSelected = $namaBulan[str_pad($bulan, 2, '0', STR_PAD_LEFT)] ?? $bulan;

        return view('layouts.payroll.review', compact(
            'reviewItems', 'skippedEmployees', 'noRateEmployees',
            'bulan', 'tahun', 'namaBulanSelected'
        ));
    }

    public function store(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $items = $request->input('items', []);

        if (empty($items)) {
            return redirect()->route('payroll.index')
                ->with('error', 'Tidak ada data karyawan yang diproses.');
        }

        $payDate = Carbon::createFromDate($tahun, $bulan, 25)->format('Y-m-d');
        $processedCount = 0;

        // Pre-validation: kasbon yang bisa dipotong HANYA yang status_approval = 'disetujui'
        foreach ($items as $item) {
            $idKaryawan = $item['ID_karyawan'] ?? null;
            $potonganKasbon = floatval($item['potongan_kasbon'] ?? 0);

            if ($idKaryawan && $potonganKasbon > 0) {
                $activeKasbons = Kasbon::where('ID_karyawan', $idKaryawan)
                    ->where('status_approval', 'disetujui')
                    ->where('status_pelunasan', 'belum_lunas')
                    ->get();
                $sisaMax = $activeKasbons->sum(fn($k) => $k->getSisaKasbon());

                if ($potonganKasbon > ($sisaMax + 0.01)) {
                    $emp = Karyawan::find($idKaryawan);
                    $empName = $emp->nama_karyawan ?? 'Karyawan';
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Gagal memproses payroll: Potongan kasbon untuk {$empName} (Rp " . number_format($potonganKasbon, 0, ',', '.') . ") melebihi sisa kasbon yang tersisa (Rp " . number_format($sisaMax, 0, ',', '.') . ").");
                }
            }
        }

        $hasGajiPokokCol   = Schema::hasColumn('payrolls', 'gaji_pokok');
        $hasPotonganLainCol = Schema::hasColumn('payrolls', 'potongan_lain');

        // STEP 3: PROSES SIMPAN IN DATABASE TRANSACTION
        DB::transaction(function () use ($items, $bulan, $tahun, $payDate, $hasGajiPokokCol, $hasPotonganLainCol, &$processedCount) {
            foreach ($items as $item) {
                $idKaryawan = $item['ID_karyawan'];

                $exists = Payroll::where('ID_karyawan', $idKaryawan)
                    ->whereMonth('date', $bulan)
                    ->whereYear('date', $tahun)
                    ->exists();

                if ($exists) continue;

                $gajiPokokRate        = floatval($item['gaji_pokok_rate'] ?? 0);
                $totalMasuk           = floatval($item['total_masuk'] ?? 0);
                $totalIzin            = intval($item['total_izin'] ?? 0);
                $totalAlpha           = intval($item['total_alpha'] ?? 0);
                $totalGajiPokokHarian = floatval($item['total_gaji_pokok_harian'] ?? 0);
                $totalLemburan        = floatval($item['total_lemburan'] ?? 0);
                $potonganKasbon       = floatval($item['potongan_kasbon'] ?? 0);
                $potonganBpjs         = floatval($item['potongan_bpjs'] ?? 40000);
                $bpjsPerusahaan       = floatval($item['bpjs_perusahaan'] ?? 40000);
                $potonganLain         = floatval($item['potongan_lain'] ?? 0);

                $totalPendapatan = floatval($item['total_pendapatan'] ?? 0);
                $gajiBersih      = max(0, $totalPendapatan - ($potonganKasbon + $potonganBpjs + $potonganLain));

                $payrollData = [
                    'ID_karyawan'      => $idKaryawan,
                    'ID_gaji'          => $item['ID_gaji'] ?? null,
                    'date'             => $payDate,
                    'total_masuk'      => $totalMasuk,
                    'total_jam_kerja'  => floatval($item['total_jam_kerja'] ?? 0),  // simpan jam kerja
                    'total_izin'       => $totalIzin,
                    'total_alpha'      => $totalAlpha,
                    'total_gaji'       => $totalGajiPokokHarian,
                    'total_lemburan'   => $totalLemburan,
                    'total_kasbon'     => $potonganKasbon,
                    'potongan_bpjs'    => $potonganBpjs,
                    'bpjs_perusahaan'  => $bpjsPerusahaan,
                    'gaji_bersih'      => $gajiBersih,
                    'status_payroll'   => 'Dibayar',
                ];

                if ($hasGajiPokokCol) {
                    $payrollData['gaji_pokok'] = $gajiPokokRate;
                }
                if ($hasPotonganLainCol) {
                    $payrollData['potongan_lain'] = $potonganLain;
                }

                $payroll = Payroll::create($payrollData);

                // Insert detail_payroll for unpaid lembur
                $unpaidLemburs = Lembur::where('ID_karyawan', $idKaryawan)
                    ->whereMonth('date', $bulan)
                    ->whereYear('date', $tahun)
                    ->where('status_persetujuan', 'Disetujui')
                    ->whereDoesntHave('detailPayrolls')
                    ->get();

                foreach ($unpaidLemburs as $lmb) {
                    DetailPayroll::create([
                        'ID_payroll' => $payroll->ID_payroll,
                        'ID_lembur'  => $lmb->ID_lembur,
                        'ID_kasbon'  => null,
                        'jenis'      => 'lembur',
                        'subtotal'   => floatval($lmb->uang_lembur) + floatval($lmb->uang_makan),
                        'keterangan' => 'Lembur: ' . ($lmb->tanggal ? $lmb->tanggal->format('d/m/Y') : '-'),
                    ]);
                }

                // Insert detail_payroll for kasbon — hanya kasbon yang SUDAH disetujui approval
                if ($potonganKasbon > 0) {
                    $activeKasbons = Kasbon::where('ID_karyawan', $idKaryawan)
                        ->where('status_approval', 'disetujui')
                        ->where('status_pelunasan', 'belum_lunas')
                        ->orderBy('date', 'asc')
                        ->get();

                    $rem = $potonganKasbon;
                    foreach ($activeKasbons as $ksb) {
                        if ($rem <= 0) break;

                        $sisaKasbon = $ksb->getSisaKasbon();

                        if ($sisaKasbon <= 0) {
                            $ksb->syncStatusPelunasan($payroll->ID_payroll);
                            continue;
                        }

                        $sub = min($rem, $sisaKasbon);
                        DetailPayroll::create([
                            'ID_payroll' => $payroll->ID_payroll,
                            'ID_lembur'  => null,
                            'ID_kasbon'  => $ksb->ID_kasbon,
                            'jenis'      => 'kasbon',
                            'subtotal'   => -$sub,
                            'keterangan' => 'Potongan Kasbon',
                        ]);

                        $rem -= $sub;

                        $ksb->syncStatusPelunasan($payroll->ID_payroll);
                    }
                }

                // Insert detail_payroll for BPJS Karyawan
                if ($potonganBpjs > 0) {
                    DetailPayroll::create([
                        'ID_payroll' => $payroll->ID_payroll,
                        'ID_lembur'  => null,
                        'ID_kasbon'  => null,
                        'jenis'      => 'bpjs',
                        'subtotal'   => -$potonganBpjs,
                        'keterangan' => 'Potongan BPJS Karyawan',
                    ]);
                }

                // Insert detail_payroll for BPJS Perusahaan (Information row)
                if ($bpjsPerusahaan > 0) {
                    DetailPayroll::create([
                        'ID_payroll' => $payroll->ID_payroll,
                        'ID_lembur'  => null,
                        'ID_kasbon'  => null,
                        'jenis'      => 'bpjs',
                        'subtotal'   => 0,
                        'keterangan' => 'BPJS Dibayar Perusahaan: Rp ' . number_format($bpjsPerusahaan, 0, ',', '.') . ' (informasi)',
                    ]);
                }

                // Insert detail_payroll for Manual Penalty/Deduction
                if ($potonganLain > 0) {
                    DetailPayroll::create([
                        'ID_payroll' => $payroll->ID_payroll,
                        'ID_lembur'  => null,
                        'ID_kasbon'  => null,
                        'jenis'      => 'manual',
                        'subtotal'   => -$potonganLain,
                        'keterangan' => 'Potongan Manual / Sanksi',
                    ]);
                }

                $processedCount++;
            }
        });

        return redirect()->route('payroll.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', "Proses simpan payroll selesai! {$processedCount} data penggajian karyawan berhasil disimpan.");
    }

    public function slip(Payroll $payroll)
    {
        return $this->slipPdf($payroll);
    }

    public function slipPdf(Payroll $payroll)
    {
        $payroll->load([
            'karyawan.divisi',
            'karyawan.jabatan',
            'karyawan.instansi',
            'karyawan.status',
            'details.lembur',
            'details.kasbon'
        ]);

        $gajiHeader = GajiKaryawan::where('ID_karyawan', $payroll->ID_karyawan)
            ->where('ID_gaji', $payroll->ID_gaji)
            ->first();

        if (!$gajiHeader) {
            $gajiHeader = GajiKaryawan::where('ID_karyawan', $payroll->ID_karyawan)->latest()->first();
        }

        // 1. Logo Instansi Dinamis
        $instansi = $payroll->karyawan->instansi ?? null;
        $logoBase64 = null;

        if ($instansi && !empty($instansi->logo)) {
            $instansiLogoPath = public_path('images/' . $instansi->logo);
            if (file_exists($instansiLogoPath)) {
                $ext = strtolower(pathinfo($instansiLogoPath, PATHINFO_EXTENSION));
                $mime = ($ext === 'svg') ? 'image/svg+xml' : 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($instansiLogoPath));
            }
        }

        // Fallback by slug if logo column was empty or file missing
        if (!$logoBase64 && $instansi) {
            $slug = Str::slug($instansi->nama_instansi);
            foreach (['png', 'jpg', 'jpeg', 'svg', 'webp'] as $ext) {
                $fallbackPath = public_path("images/logo_{$slug}.{$ext}");
                if (file_exists($fallbackPath)) {
                    $mime = ($ext === 'svg') ? 'image/svg+xml' : 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
                    $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fallbackPath));
                    break;
                }
            }
        }

        // Ultimate fallback ke logo.png HANYA jika karyawan memiliki instansi terdaftar.
        // Jika id_instansi NULL/kosong, JANGAN tampilkan logo apapun di PDF.
        if (!$logoBase64 && $instansi && $payroll->karyawan->id_instansi && file_exists(public_path('images/logo.png'))) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo.png')));
        }

        // 2. Dates & Rates
        $tglCetak = Carbon::now()->translatedFormat('d F Y');

        $totalMasuk           = $payroll->total_masuk;
        $gajiPokokPerJam      = 0;
        $uangMakanPerHari     = 0;
        $nominalMingguan      = 0;
        $nominalBulanan       = 0;
        $nominalKerajinan     = 0;
        $nominalTunjanganJabatan = 0;

        if ($gajiHeader) {
            $details = DetailGajiKaryawan::with('komponenGaji')
                ->where('ID_gaji', $gajiHeader->ID_gaji)
                ->get();

            foreach ($details as $det) {
                $n   = strtolower($det->komponenGaji->nama_gaji ?? '');
                $val = floatval($det->nominal_gaji);

                if (str_contains($n, 'pokok')) {
                    $gajiPokokPerJam = $val;
                } elseif (str_contains($n, 'makan')) {
                    $uangMakanPerHari = $val;
                } elseif (str_contains($n, 'mingguan')) {
                    $nominalMingguan = $val;
                } elseif (str_contains($n, 'bulanan')) {
                    $nominalBulanan = $val;
                } elseif (str_contains($n, 'kerajinan')) {
                    $nominalKerajinan = $val;
                } elseif (str_contains($n, 'jabatan')) {
                    $nominalTunjanganJabatan = $val;
                }
            }
        }

        if ($gajiPokokPerJam <= 0 && floatval($payroll->getAttributes()['gaji_pokok'] ?? 0) > 0) {
            $gajiPokokPerJam = floatval($payroll->getAttributes()['gaji_pokok']);
        }

        // Attendance weeks & total hours
        $bulan = $payroll->date ? $payroll->date->month : date('m');
        $tahun = $payroll->date ? $payroll->date->year : date('Y');

        $absensis = Absensi::where('ID_karyawan', $payroll->ID_karyawan)
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->get();

        // Gunakan total_jam_kerja yang tersimpan di DB jika ada
        $totalJamKerja = floatval($payroll->total_jam_kerja ?? 0);

        // Hitung ulang dari absensi jika kolom DB kosong (fallback untuk payroll lama)
        if ($totalJamKerja <= 0) {
            foreach ($absensis as $a) {
                $calc = \App\Services\AbsensiService::hitungJamKerja($payroll->karyawan, $a->jam_masuk, $a->jam_pulang, $a->status_absensi);
                $totalJamKerja += $calc['total_jam_kerja'];
            }
        }

        if ($totalJamKerja <= 0 && $totalMasuk > 0) {
            $totalJamKerja = $totalMasuk * 8.0;
        }

        $jumlahMingguPenuh = 0;
        $jumlahMingguRajin = 0;

        for ($w = 1; $w <= 4; $w++) {
            $startDay = ($w - 1) * 7 + 1;
            $endDay   = min($w * 7, 31);

            $weekAbs = $absensis->filter(function ($a) use ($startDay, $endDay) {
                $d = $a->date ? $a->date->day : 1;
                return $d >= $startDay && $d <= $endDay;
            });

            // Pakai AbsensiService (konsisten dengan review())
            $weekHadirCount        = 0;
            $weekAlphaCount        = 0;
            $weekLateCount         = 0;
            $weekSetengahHariCount = 0;

            foreach ($weekAbs as $wa) {
                $wCalc = \App\Services\AbsensiService::hitungJamKerja($payroll->karyawan, $wa->jam_masuk, $wa->jam_pulang, $wa->status_absensi);
                $wSt   = $wCalc['status_absensi'];

                if ($wSt === 'Hadir') {
                    $weekHadirCount++;
                } elseif ($wSt === 'Telat') {
                    $weekHadirCount++;
                    $weekLateCount++;
                } elseif ($wSt === 'Setengah Hari') {
                    $weekHadirCount++;
                    $weekSetengahHariCount++;
                } elseif ($wSt === 'Alpha' || $wSt === 'Alpa') {
                    $weekAlphaCount++;
                }
            }

            // Bonus MINGGUAN: tidak ada Alpha (Telat masih boleh)
            if ($weekHadirCount >= 5 && $weekAlphaCount == 0) {
                $jumlahMingguPenuh++;
                // Bonus KERAJINAN: tidak ada Alpha, tidak ada Telat, tidak ada Setengah Hari
                if ($weekLateCount == 0 && $weekSetengahHariCount == 0) {
                    $jumlahMingguRajin++;
                }
            }
        }

        $hasilHarian          = $totalJamKerja * $gajiPokokPerJam;
        $hasilUangMakan        = $totalMasuk * $uangMakanPerHari;
        $hasilMingguan         = $jumlahMingguPenuh * $nominalMingguan;
        $hasilBulanan          = 1 * $nominalBulanan;
        $hasilKerajinan        = $jumlahMingguRajin * $nominalKerajinan;
        $hasilTunjanganJabatan = ($payroll->karyawan->id_jabatan && $nominalTunjanganJabatan > 0) ? $nominalTunjanganJabatan : 0;

        // 3. Overtime details — filter berdasarkan relasi kategoriLembur
        $lemburs = Lembur::with('kategoriLembur')
            ->where('ID_karyawan', $payroll->ID_karyawan)
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->where('status_persetujuan', 'Disetujui')
            ->get();

        // Filter Lembur Harian (bukan libur)
        $lembursHarian    = $lemburs->filter(fn($l) => !str_contains(strtolower($l->kategoriLembur?->tipe_lembur ?? ''), 'libur'));
        $totalJamHarian   = max(0, $lembursHarian->sum('durasi_lembur'));
        $tarifJamHarian   = $lembursHarian->first()?->tarif_per_jam ?? 0;
        $hasilJamHarian   = max(0, $lembursHarian->sum(fn($l) => floatval($l->durasi_lembur) * floatval($l->tarif_per_jam)));
        $kejadianHarian   = $lembursHarian->count();
        $hasilMakanHarian = max(0, $lembursHarian->sum(fn($l) => floatval($l->uang_makan)));
        $tarifMakanLemburHarian = $kejadianHarian > 0 ? round($hasilMakanHarian / $kejadianHarian) : 0;

        // Filter Lembur Hari Libur
        // Rumus BENAR: setiap kejadian = 8 jam × tarif_per_jam_karyawan × 2 (sudah tersimpan di uang_lembur)
        $lembursLibur          = $lemburs->filter(fn($l) => str_contains(strtolower($l->kategoriLembur?->tipe_lembur ?? ''), 'libur'));
        $kejadianLibur         = $lembursLibur->count();
        $hasilLiburTotal       = max(0, $lembursLibur->sum('uang_lembur'));  // sudah tersimpan dengan rumus 8×tarif×2
        $hasilMakanLiburTotal  = max(0, $lembursLibur->sum('uang_makan')); // sudah tersimpan dengan tarif_makan×2
        // Harga per kejadian untuk tampilan di slip
        $hasilLiburPerKejadian = $kejadianLibur > 0 ? round($hasilLiburTotal / $kejadianLibur) : ($gajiPokokPerJam * 8 * 2);
        $tarifMakanLiburPerKejadian = $kejadianLibur > 0 && $hasilMakanLiburTotal > 0
            ? round($hasilMakanLiburTotal / $kejadianLibur)
            : $uangMakanPerHari * 2;

        $totalLemburSistem = max(0, $hasilJamHarian + $hasilMakanHarian + $hasilLiburTotal + $hasilMakanLiburTotal);
        if ($totalLemburSistem <= 0 && $payroll->total_lemburan > 0) {
            $totalLemburSistem = floatval($payroll->total_lemburan);
        }

        $subtotalGajiSampaiLembur = $hasilHarian + $hasilUangMakan + $hasilMingguan + $hasilBulanan + $hasilKerajinan + $hasilTunjanganJabatan + $totalLemburSistem;

        // 4. BPJS
        $bpjsKantor   = floatval($payroll->bpjs_perusahaan ?? 95000);
        $bpjsKaryawan = floatval($payroll->potongan_bpjs ?? 40000);
        $totalBpjs    = $bpjsKantor + $bpjsKaryawan;

        // 5. Kasbon
        $detailKasbonRows = $payroll->details->whereNotNull('ID_kasbon');
        $detailKasbonRow  = $detailKasbonRows->first();

        $angsuranKe = 1;
        $subtotalKasbonBulanIni = floatval($payroll->total_kasbon);
        $totalKasbonSisa = 0;

        if ($detailKasbonRow && $detailKasbonRow->kasbon) {
            $kasbon = $detailKasbonRow->kasbon;
            $totalKasbonSisa = $kasbon->getSisaKasbon($payroll->ID_payroll);

            $angsuranKe = DetailPayroll::where('ID_kasbon', $kasbon->ID_kasbon)
                ->where('jenis', 'kasbon')
                ->where('ID_payroll', '<=', $payroll->ID_payroll)
                ->count();

            if ($subtotalKasbonBulanIni <= 0) {
                $subtotalKasbonBulanIni = abs(floatval($detailKasbonRow->subtotal));
            }
        } else {
            $kasbons = Kasbon::where('ID_karyawan', $payroll->ID_karyawan)->get();
            $totalKasbonSisa = $kasbons->sum(fn($k) => $k->getSisaKasbon($payroll->ID_payroll));
        }

        $pdf = Pdf::loadView('layouts.payroll.pdf_slip', compact(
            'payroll', 'logoBase64', 'tglCetak',
            'totalMasuk', 'totalJamKerja', 'gajiPokokPerJam', 'uangMakanPerHari', 'nominalMingguan', 'nominalBulanan', 'nominalKerajinan', 'nominalTunjanganJabatan',
            'jumlahMingguPenuh', 'jumlahMingguRajin',
            'hasilHarian', 'hasilUangMakan', 'hasilMingguan', 'hasilBulanan', 'hasilKerajinan', 'hasilTunjanganJabatan',
            'totalJamHarian', 'tarifJamHarian', 'hasilJamHarian', 'kejadianHarian', 'tarifMakanLemburHarian', 'hasilMakanHarian',
            'kejadianLibur', 'hasilLiburPerKejadian', 'hasilLiburTotal', 'hasilMakanLiburTotal', 'tarifMakanLiburPerKejadian', 'totalLemburSistem',
            'subtotalGajiSampaiLembur',
            'bpjsKantor', 'bpjsKaryawan', 'totalBpjs',
            'totalKasbonSisa', 'angsuranKe', 'subtotalKasbonBulanIni'
        ))->setPaper('a4', 'portrait');

        $filename = 'Slip_Gaji_' . preg_replace('/[^A-Za-z0-9_]/', '_', $payroll->karyawan->nama_karyawan ?? 'Karyawan') . '_' . date('Ymd') . '.pdf';

        return $pdf->stream($filename);
    }

    public function export(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $namaBulan = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];
        $bulanLabel = $namaBulan[str_pad($bulan, 2, '0', STR_PAD_LEFT)] ?? $bulan;
        $filename   = "Rekap_Payroll_{$bulanLabel}_{$tahun}.xlsx";

        return Excel::download(new PayrollExport($bulan, $tahun), $filename);
    }

    public function destroy(Payroll $payroll)
    {
        DB::transaction(function () use ($payroll) {
            // 1. Ambil semua detail_payroll jenis='kasbon' milik payroll ini
            $kasbonDetails = $payroll->details()->where('jenis', 'kasbon')->get();

            foreach ($kasbonDetails as $detail) {
                $kasbonId = $detail->ID_kasbon;

                // 2. Hapus baris detail_payroll (sisa kasbon otomatis bertambah kembali)
                $detail->delete();

                // 3. Jika kasbon terkait statusnya 'Lunas', kembalikan ke 'Disetujui'
                if ($kasbonId) {
                    $kasbon = Kasbon::find($kasbonId);
                    if ($kasbon && $kasbon->status_pelunasan === 'lunas') {
                        if ($kasbon->getSisaKasbon() > 0) {
                            $kasbon->update(['status_pelunasan' => 'belum_lunas']);
                        }
                    }
                }
            }

            // 4. Hapus semua detail_payroll jenis='lembur' milik payroll ini
            // Agar lembur kembali berstatus belum tertagih dan bisa diproses ulang
            $payroll->details()->where('jenis', 'lembur')->delete();

            // 5. Hapus payroll utama
            $payroll->delete();
        });

        return redirect()->route('payroll.index')
            ->with('success', 'Data payroll berhasil dihapus. Cicilan kasbon dan lembur terkait telah di-rollback.');
    }
}
