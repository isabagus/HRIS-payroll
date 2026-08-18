<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Status;
use App\Models\KomponenGaji;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $bulanIni = $today->month;
        $tahunIni = $today->year;

        // ── STAT CARDS ──────────────────────────────────────────
        $totalKaryawan = DB::table('karyawans')->count();

        $totalGajiBulanIni = DB::table('payrolls')
            ->whereMonth('date', $bulanIni)
            ->whereYear('date', $tahunIni)
            ->sum('total_gaji');

        // Absensi hari ini
        $hadir = DB::table('absensis')
            ->whereDate('date', $today)
            ->where('status_absensi', 'Hadir')
            ->count();

        $absen = DB::table('absensis')
            ->whereDate('date', $today)
            ->where('status_absensi', 'Alpha')
            ->count();

        $izinSakit = DB::table('absensis')
            ->whereDate('date', $today)
            ->whereIn('status_absensi', ['Izin', 'Sakit'])
            ->count();

        $terlambat = DB::table('absensis')
            ->whereDate('date', $today)
            ->where('status_absensi', 'Hadir')
            ->whereTime('jam_masuk', '>', '08:00:00')
            ->count();

        // Total kasbon aktif (Pending + Disetujui)
        $totalKasbon = DB::table('kasbons')
            ->whereIn('status', ['Pending', 'Disetujui'])
            ->sum('total_kasbon');

        $jumlahKasbonAktif = DB::table('kasbons')
            ->whereIn('status', ['Pending', 'Disetujui'])
            ->count();

        // ── MASTER DATA COUNTS ───────────────────────────────────
        $totalDivisi    = DB::table('divisis')->count();
        $totalJabatan   = DB::table('jabatans')->count();
        $totalStatus    = DB::table('statuses')->count();
        $totalKomponen  = DB::table('komponen_gajis')->count();

        // ── PAYROLL STATUS BULAN INI ─────────────────────────────
        $sudahPayroll = DB::table('payrolls')
            ->whereMonth('date', $bulanIni)
            ->whereYear('date', $tahunIni)
            ->distinct('ID_karyawan')
            ->count('ID_karyawan');

        $belumPayroll = $totalKaryawan - $sudahPayroll;

        // ── RIWAYAT PAYROLL TERBARU ──────────────────────────────
        $riwayatPayroll = DB::table('payrolls as p')
            ->join('karyawans as k', 'p.ID_karyawan', '=', 'k.ID_karyawan')
            ->join('divisis as d', 'k.id_divisi', '=', 'd.ID_divisi')
            ->select(
                'k.nama_karyawan',
                'k.Kode_karyawan',
                'd.nama_divisi',
                'p.date',
                'p.total_gaji',
                'p.created_at'
            )
            ->orderByDesc('p.created_at')
            ->limit(5)
            ->get();

        // ── KARYAWAN TERBARU ─────────────────────────────────────
        $karyawanTerbaru = DB::table('karyawans as k')
            ->join('divisis as d', 'k.id_divisi', '=', 'd.ID_divisi')
            ->join('jabatans as j', 'k.id_jabatan', '=', 'j.ID_jabatan')
            ->select('k.nama_karyawan', 'k.Kode_karyawan', 'd.nama_divisi', 'j.nama_jabatan', 'k.created_at')
            ->orderByDesc('k.created_at')
            ->limit(5)
            ->get();

        return view('layouts.dashboard.index', compact(
            'totalKaryawan',
            'totalGajiBulanIni',
            'hadir', 'absen', 'izinSakit', 'terlambat',
            'totalKasbon', 'jumlahKasbonAktif',
            'totalDivisi', 'totalJabatan', 'totalStatus', 'totalKomponen',
            'sudahPayroll', 'belumPayroll',
            'riwayatPayroll',
            'karyawanTerbaru',
            'bulanIni', 'tahunIni', 'today'
        ));
    }
}
