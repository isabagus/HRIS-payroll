<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Imports\AbsensiImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public static function determineStatus(?string $jamMasuk, ?string $manualStatus = null, ?string $keterangan = null): string
    {
        // If manual status is explicitly set to Izin, Sakit, or Libur, respect it!
        if ($manualStatus && in_array($manualStatus, ['Izin', 'Sakit', 'Libur'])) {
            return $manualStatus;
        }

        if (empty($jamMasuk) || $jamMasuk === '-' || $jamMasuk === '00:00' || $jamMasuk === '00:00:00') {
            if ($manualStatus === 'Libur' || strtolower($keterangan ?? '') === 'libur') {
                return 'Libur';
            }
            if ($manualStatus && in_array($manualStatus, ['Izin', 'Sakit'])) {
                return $manualStatus;
            }
            return 'Alpha';
        }

        $timeStr = trim($jamMasuk);
        if (strlen($timeStr) === 5) {
            $timeStr .= ':00';
        }

        try {
            $carbonTime = Carbon::createFromTimeString($timeStr);
            $eightAM    = Carbon::createFromTimeString('08:00:00');
            $twelvePM   = Carbon::createFromTimeString('12:00:00');

            if ($carbonTime->lte($eightAM)) {
                return 'Hadir';
            } elseif ($carbonTime->gt($eightAM) && $carbonTime->lte($twelvePM)) {
                return 'Telat';
            } else {
                return 'Setengah Hari';
            }
        } catch (\Exception $e) {
            return $manualStatus ?: 'Hadir';
        }
    }

    public function index(Request $request)
    {
        $search        = $request->get('search');
        $filterStatus  = $request->get('status');
        $filterBulan   = $request->get('bulan', date('m'));
        $filterTahun   = $request->get('tahun', date('Y'));

        // Query Employees list with their monthly attendance rekap
        $karyawans = Karyawan::with(['divisi', 'jabatan', 'status', 'instansi'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('nama_karyawan', 'like', "%{$search}%")
                       ->orWhere('Kode_karyawan', 'like', "%{$search}%")
                       ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->orderBy('Kode_karyawan', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Calculate monthly attendance summary for each employee on the current page
        foreach ($karyawans as $karyawan) {
            $monthlyAbsensi = Absensi::where('ID_karyawan', $karyawan->ID_karyawan)
                ->whereMonth('date', $filterBulan)
                ->whereYear('date', $filterTahun)
                ->get();

            $totalJamBulan = 0.0;
            $countHadir    = 0;
            $countTelat    = 0;
            $countSetengah = 0;
            $countIzin     = 0;
            $countSakit    = 0;
            $countAlpha    = 0;
            $countLibur    = 0;

            foreach ($monthlyAbsensi as $a) {
                $calc = \App\Services\AbsensiService::hitungJamKerja($karyawan, $a->jam_masuk, $a->jam_pulang, $a->status_absensi);
                $totalJamBulan += $calc['total_jam_kerja'];

                $st = $calc['status_absensi'];
                if ($st === 'Hadir') $countHadir++;
                elseif ($st === 'Telat') $countTelat++;
                elseif ($st === 'Setengah Hari') $countSetengah++;
                elseif ($st === 'Izin') $countIzin++;
                elseif ($st === 'Sakit') $countSakit++;
                elseif ($st === 'Alpha') $countAlpha++;
                elseif ($st === 'Libur') $countLibur++;
            }

            $karyawan->count_hadir        = $countHadir;
            $karyawan->count_telat        = $countTelat;
            $karyawan->count_setengah_hari = $countSetengah;
            $karyawan->count_izin         = $countIzin;
            $karyawan->count_sakit        = $countSakit;
            $karyawan->count_alpha        = $countAlpha;
            $karyawan->count_libur        = $countLibur;
            $karyawan->total_jam_bulan    = round($totalJamBulan, 2);
            $karyawan->total_records      = $monthlyAbsensi->count();
        }

        // Global Statistics for selected Month & Year
        $statsQuery = Absensi::whereMonth('date', $filterBulan)->whereYear('date', $filterTahun);
        $totalHadir        = (clone $statsQuery)->where('status_absensi', 'Hadir')->count();
        $totalTelat        = (clone $statsQuery)->where('status_absensi', 'Telat')->count();
        $totalSetengahHari = (clone $statsQuery)->where('status_absensi', 'Setengah Hari')->count();
        $totalIzin         = (clone $statsQuery)->where('status_absensi', 'Izin')->count();
        $totalSakit        = (clone $statsQuery)->where('status_absensi', 'Sakit')->count();
        $totalAlpha        = (clone $statsQuery)->where('status_absensi', 'Alpha')->count();
        $totalLibur        = (clone $statsQuery)->where('status_absensi', 'Libur')->count();
        $totalRecords      = $totalHadir + $totalTelat + $totalSetengahHari + $totalIzin + $totalSakit + $totalAlpha + $totalLibur;

        $tahunList = range(date('Y'), date('Y') - 5);

        return view('layouts.absensi.index', compact(
            'karyawans', 'search', 'filterStatus', 'filterBulan', 'filterTahun',
            'totalHadir', 'totalTelat', 'totalSetengahHari', 'totalIzin', 'totalSakit', 'totalAlpha', 'totalLibur', 'totalRecords',
            'tahunList'
        ));
    }

    public function show($id, Request $request)
    {
        $karyawan = Karyawan::with(['divisi', 'jabatan', 'status', 'instansi'])->findOrFail($id);

        $filterBulan = $request->get('bulan', date('m'));
        $filterTahun = $request->get('tahun', date('Y'));

        // Query 1-month daily attendance records for this employee
        $absensisRaw = Absensi::where('ID_karyawan', $karyawan->ID_karyawan)
            ->whereMonth('date', $filterBulan)
            ->whereYear('date', $filterTahun)
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        // Build complete monthly calendar days (1 to end of month)
        $daysInMonth = Carbon::createFromDate($filterTahun, $filterBulan, 1)->daysInMonth;
        $monthlyDays = [];

        $statHadir        = 0;
        $statTelat        = 0;
        $statSetengahHari = 0;
        $statIzin         = 0;
        $statSakit        = 0;
        $statAlpha        = 0;
        $statLibur        = 0;
        $totalJamBulanan  = 0.0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateObj = Carbon::createFromDate($filterTahun, $filterBulan, $day);
            $dateStr = $dateObj->format('Y-m-d');

            if (isset($absensisRaw[$dateStr])) {
                $record = $absensisRaw[$dateStr];
                $calc   = \App\Services\AbsensiService::hitungJamKerja($karyawan, $record->jam_masuk, $record->jam_pulang, $record->status_absensi);

                $totalJamBulanan += $calc['total_jam_kerja'];

                $monthlyDays[] = [
                    'date'            => $dateObj,
                    'record'          => $record,
                    'status_absensi'  => $calc['status_absensi'],
                    'total_jam_kerja' => $calc['total_jam_kerja'],
                    'terlambat_menit' => $calc['terlambat_menit'],
                    'is_exist'        => true,
                ];

                if ($calc['status_absensi'] === 'Hadir') $statHadir++;
                elseif ($calc['status_absensi'] === 'Telat') $statTelat++;
                elseif ($calc['status_absensi'] === 'Setengah Hari') $statSetengahHari++;
                elseif ($calc['status_absensi'] === 'Izin') $statIzin++;
                elseif ($calc['status_absensi'] === 'Sakit') $statSakit++;
                elseif ($calc['status_absensi'] === 'Alpha') $statAlpha++;
                elseif ($calc['status_absensi'] === 'Libur') $statLibur++;
            } else {
                // Day with no log record (e.g. Sunday / Off day)
                $isSunday = $dateObj->isSunday();
                $statusDefault = $isSunday ? 'Libur' : '-';
                if ($isSunday) $statLibur++;

                $monthlyDays[] = [
                    'date'            => $dateObj,
                    'record'          => null,
                    'status_absensi'  => $statusDefault,
                    'total_jam_kerja' => 0,
                    'terlambat_menit' => 0,
                    'is_exist'        => false,
                ];
            }
        }

        $tahunList = range(date('Y'), date('Y') - 5);

        return view('layouts.absensi.show', compact(
            'karyawan', 'monthlyDays', 'filterBulan', 'filterTahun', 'tahunList',
            'statHadir', 'statTelat', 'statSetengahHari', 'statIzin', 'statSakit', 'statAlpha', 'statLibur', 'daysInMonth'
        ));
    }

    public function create()
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        return view('layouts.absensi.create_edit', ['absensi' => null, 'karyawans' => $karyawans]);
    }

    public function store(Request $request)
    {
        // Dynamic multi-row input
        if ($request->has('absensis') && is_array($request->absensis)) {
            $request->validate([
                'absensis.*.ID_karyawan'    => 'required|exists:karyawans,ID_karyawan',
                'absensis.*.date'           => 'required|date',
                'absensis.*.status_absensi' => 'nullable|in:Hadir,Telat,Setengah Hari,Izin,Sakit,Alpha,Libur',
            ]);

            $inserted = 0;
            foreach ($request->absensis as $item) {
                if (empty($item['ID_karyawan']) || empty($item['date'])) continue;

                $status = self::determineStatus(
                    $item['jam_masuk'] ?? null,
                    $item['status_absensi'] ?? null,
                    $item['keterangan'] ?? null
                );

                Absensi::updateOrCreate(
                    [
                        'ID_karyawan' => $item['ID_karyawan'],
                        'date'        => $item['date'],
                    ],
                    [
                        'jam_masuk'        => $item['jam_masuk'] ?? null,
                        'jam_pulang'       => $item['jam_pulang'] ?? null,
                        'status_absensi'   => $status,
                        'keterangan'       => $item['keterangan'] ?? '-',
                        'jam_kerja_masuk'  => $item['jam_kerja_masuk'] ?? null,
                        'jam_kerja_pulang' => $item['jam_kerja_pulang'] ?? null,
                        'terlambat'        => $item['terlambat'] ?? null,
                        'total_jam_kerja'  => $item['total_jam_kerja'] ?? null,
                    ]
                );
                $inserted++;
            }

            return redirect()->route('absensi.index')
                ->with('success', "{$inserted} Data absensi berhasil disimpan.");
        }

        // Single input fallback
        $request->validate([
            'ID_karyawan'    => 'required|exists:karyawans,ID_karyawan',
            'date'           => 'required|date',
            'status_absensi' => 'nullable|in:Hadir,Telat,Setengah Hari,Izin,Sakit,Alpha,Libur',
        ]);

        $status = self::determineStatus(
            $request->jam_masuk,
            $request->status_absensi,
            $request->keterangan
        );

        $data = $request->only([
            'jam_masuk', 'jam_pulang', 'keterangan',
            'jam_kerja_masuk', 'jam_kerja_pulang', 'durasi_kerja',
            'terlambat', 'pulang_cepat', 'total_jam_kerja', 'istirahat',
            'lembur_awal', 'lembur_akhir', 'total_lembur', 'masuk_kerja'
        ]);
        $data['status_absensi'] = $status;

        Absensi::updateOrCreate(
            [
                'ID_karyawan' => $request->ID_karyawan,
                'date'        => $request->date,
            ],
            $data
        );

        return redirect()->route('absensi.index')
            ->with('success', 'Data absensi berhasil disimpan.');
    }

    public function edit(Absensi $absensi)
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        return view('layouts.absensi.create_edit', compact('absensi', 'karyawans'));
    }

    public function update(Request $request, Absensi $absensi)
    {
        $request->validate([
            'ID_karyawan'    => 'required|exists:karyawans,ID_karyawan',
            'date'           => 'required|date',
            'status_absensi' => 'nullable|in:Hadir,Telat,Setengah Hari,Izin,Sakit,Alpha,Libur',
        ]);

        $status = self::determineStatus(
            $request->jam_masuk,
            $request->status_absensi,
            $request->keterangan
        );

        $data = $request->only([
            'ID_karyawan', 'date', 'jam_masuk', 'jam_pulang', 'keterangan',
            'jam_kerja_masuk', 'jam_kerja_pulang', 'durasi_kerja',
            'terlambat', 'pulang_cepat', 'total_jam_kerja', 'istirahat',
            'lembur_awal', 'lembur_akhir', 'total_lembur', 'masuk_kerja'
        ]);
        $data['status_absensi'] = $status;

        $absensi->update($data);

        $dateObj = Carbon::parse($absensi->date);
        return redirect()->route('absensi.show', [
            'absensi' => $absensi->ID_karyawan,
            'bulan'   => $dateObj->format('m'),
            'tahun'   => $dateObj->format('Y'),
        ])->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function destroy(Absensi $absensi)
    {
        $idKaryawan = $absensi->ID_karyawan;
        $dateObj    = Carbon::parse($absensi->date);

        $absensi->delete();

        return redirect()->route('absensi.show', [
            'absensi' => $idKaryawan,
            'bulan'   => $dateObj->format('m'),
            'tahun'   => $dateObj->format('Y'),
        ])->with('success', 'Data absensi berhasil dihapus.');
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

        $import = new AbsensiImport();
        Excel::import($import, $request->file('file_excel'));

        $msg = "Import selesai: {$import->importedCount} data absensi berhasil diproses.";
        if ($import->skippedCount > 0) {
            $msg .= " {$import->skippedCount} data dilewati.";
        }

        $flashType = ($import->skippedCount > 0 || $import->importedCount === 0) ? 'warning' : 'success';

        return redirect()->route('absensi.index')
            ->with($flashType, $msg)
            ->with('import_errors', $import->importErrors);
    }

    public function tahunan($id, Request $request)
    {
        $karyawan = Karyawan::with(['divisi', 'jabatan', 'status', 'instansi'])->findOrFail($id);

        $filterTahun  = $request->get('tahun', date('Y'));
        $currentYear  = intval(date('Y'));
        $currentMonth = intval(date('m'));

        // Query all attendance records for this employee in the selected year
        $absensisRaw = Absensi::where('ID_karyawan', $karyawan->ID_karyawan)
            ->whereYear('date', $filterTahun)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        // Yearly Statistics Summary
        $statHadir        = 0;
        $statTelat        = 0;
        $statSetengahHari = 0;
        $statIzinSakit    = 0;
        $statAlpha        = 0;

        foreach ($absensisRaw as $rec) {
            $st = $rec->status_absensi;
            if ($st === 'Hadir') $statHadir++;
            elseif ($st === 'Telat') $statTelat++;
            elseif ($st === 'Setengah Hari') $statSetengahHari++;
            elseif (in_array($st, ['Izin', 'Sakit'])) $statIzinSakit++;
            elseif ($st === 'Alpha') $statAlpha++;
        }

        // Calculate Effective Working Days & Attendance Percentage
        $effectiveDaysCount = 0;
        $startDate = Carbon::createFromDate($filterTahun, 1, 1);
        $endDate   = intval($filterTahun) === $currentYear ? Carbon::today() : Carbon::createFromDate($filterTahun, 12, 31);

        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            if (!$cursor->isSunday()) {
                $effectiveDaysCount++;
            }
            $cursor->addDay();
        }

        $totalHadirEkuivalen = $statHadir + $statTelat + ($statSetengahHari * 0.5);
        $persentaseKehadiran = $effectiveDaysCount > 0 ? round(($totalHadirEkuivalen / $effectiveDaysCount) * 100, 1) : 0;

        // Indonesian Month & Day Names
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $namaHari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];

        // 1. Monthly Summary Table (12 Months)
        $monthlySummary = [];
        for ($m = 1; $m <= 12; $m++) {
            $isFuture = intval($filterTahun) > $currentYear || (intval($filterTahun) === $currentYear && $m > $currentMonth);

            if ($isFuture) {
                $monthlySummary[$m] = [
                    'nama_bulan'    => $namaBulan[$m],
                    'count_hadir'   => '-',
                    'count_telat'   => '-',
                    'count_setengah'=> '-',
                    'count_izin'    => '-',
                    'count_alpha'   => '-',
                    'total_log'     => '-',
                    'is_future'     => true,
                ];
            } else {
                $mRecords = $absensisRaw->filter(function($item) use ($m, $filterTahun) {
                    $dt = Carbon::parse($item->date);
                    return intval($dt->year) === intval($filterTahun) && intval($dt->month) === $m;
                });

                $monthlySummary[$m] = [
                    'nama_bulan'    => $namaBulan[$m],
                    'count_hadir'   => $mRecords->where('status_absensi', 'Hadir')->count(),
                    'count_telat'   => $mRecords->where('status_absensi', 'Telat')->count(),
                    'count_setengah'=> $mRecords->where('status_absensi', 'Setengah Hari')->count(),
                    'count_izin'    => $mRecords->whereIn('status_absensi', ['Izin', 'Sakit'])->count(),
                    'count_alpha'   => $mRecords->where('status_absensi', 'Alpha')->count(),
                    'total_log'     => $mRecords->count(),
                    'is_future'     => false,
                ];
            }
        }

        // 2. Heatmap Calendar (12 Months x Days)
        $heatmapMonths = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthObj = Carbon::createFromDate($filterTahun, $m, 1);
            $daysInM  = $monthObj->daysInMonth;
            $daysList = [];

            for ($d = 1; $d <= $daysInM; $d++) {
                $dateObj = Carbon::createFromDate($filterTahun, $m, $d);
                $dateStr = $dateObj->format('Y-m-d');
                $isSunday = $dateObj->isSunday();
                $isFuture = $dateObj->isFuture();

                if (isset($absensisRaw[$dateStr])) {
                    $rec = $absensisRaw[$dateStr];
                    $st  = $rec->status_absensi;
                } else {
                    $st = $isSunday ? 'Libur' : ($isFuture ? 'Masa Depan' : '-');
                }

                $hariName = $namaHari[$dateObj->format('l')] ?? $dateObj->format('l');
                $tglFormatted = "{$hariName}, {$d} " . $namaBulan[$m] . " {$filterTahun}";

                $daysList[] = [
                    'day'       => $d,
                    'date_str'  => $dateStr,
                    'status'    => $st,
                    'tooltip'   => "{$tglFormatted} — Status: " . ($st === '-' ? 'Tidak Ada Data' : $st),
                    'is_sunday' => $isSunday,
                    'is_future' => $isFuture,
                ];
            }

            $heatmapMonths[$m] = [
                'nama_bulan' => $namaBulan[$m],
                'days'       => $daysList,
            ];
        }

        $tahunList = range(date('Y'), date('Y') - 5);

        return view('layouts.absensi.tahunan', compact(
            'karyawan', 'filterTahun', 'tahunList',
            'statHadir', 'statTelat', 'statSetengahHari', 'statIzinSakit', 'statAlpha',
            'persentaseKehadiran', 'effectiveDaysCount',
            'monthlySummary', 'heatmapMonths'
        ));
    }
}
