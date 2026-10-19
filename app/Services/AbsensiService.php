<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\PengaturanTarif;
use Carbon\Carbon;

class AbsensiService
{
    /**
     * Hitung jam kerja efektif 1 hari untuk karyawan berdasarkan jam masuk dan jam pulang aktual.
     *
     * @param Karyawan|int $karyawan Model Karyawan atau ID Karyawan
     * @param string|null $jamMasukAktual String format "HH:MM" atau "HH:MM:SS"
     * @param string|null $jamPulangAktual String format "HH:MM" atau "HH:MM:SS"
     * @param string|null $manualStatus Status manual opsional (Izin, Sakit, Libur)
     * @return array
     */
    public static function hitungJamKerja($karyawan, ?string $jamMasukAktual, ?string $jamPulangAktual, ?string $manualStatus = null): array
    {
        // 0. Manual Status Check (Izin, Sakit, Libur)
        if ($manualStatus && in_array($manualStatus, ['Izin', 'Sakit', 'Libur'])) {
            return [
                'total_jam_kerja'    => 0.0,
                'durasi_menit'       => 0,
                'status_absensi'     => $manualStatus,
                'jam_masuk_efektif'  => null,
                'jam_pulang_efektif' => null,
                'terlambat_menit'    => 0,
                'is_telat'           => false,
                'toleransi_berlaku'  => 0,
            ];
        }

        // Clean & normalize time strings
        $jamMasukAktual  = self::normalizeTime($jamMasukAktual);
        $jamPulangAktual = self::normalizeTime($jamPulangAktual);

        if (empty($jamMasukAktual) || empty($jamPulangAktual)) {
            return [
                'total_jam_kerja'    => 0.0,
                'durasi_menit'       => 0,
                'status_absensi'     => 'Alpa',
                'jam_masuk_efektif'  => null,
                'jam_pulang_efektif' => null,
                'terlambat_menit'    => 0,
                'is_telat'           => false,
                'toleransi_berlaku'  => 0,
            ];
        }

        // Fetch settings parameters from DB
        $paramsRaw = PengaturanTarif::all()->keyBy('nama_parameter');

        $jamKerjaMulaiStr    = $paramsRaw['jam_kerja_mulai']->nilai ?? '08:00';
        $jamKerjaSelesaiStr  = $paramsRaw['jam_kerja_selesai']->nilai ?? '17:00';
        $istirahatMulaiStr   = $paramsRaw['istirahat_mulai']->nilai ?? '12:00';
        $istirahatSelesaiStr = $paramsRaw['istirahat_selesai']->nilai ?? '13:00';

        $toleransiUmum       = intval($paramsRaw['toleransi_umum_menit']->nilai ?? 5);
        $toleransiManager    = intval($paramsRaw['toleransi_manager_menit']->nilai ?? 60);
        $toleransiMgrAktif   = ($paramsRaw['toleransi_manager_aktif']->nilai ?? '0') === '1';

        // 1. Determine applicable tolerance
        if (!($karyawan instanceof Karyawan)) {
            $karyawan = Karyawan::with('jabatan')->find($karyawan);
        } else {
            if (!$karyawan->relationLoaded('jabatan')) {
                $karyawan->load('jabatan');
            }
        }

        $jabatanNama = $karyawan->jabatan->nama_jabatan ?? '';
        $isManager   = (stripos($jabatanNama, 'manager') !== false);

        $toleransiBerlaku = ($toleransiMgrAktif && $isManager) ? $toleransiManager : $toleransiUmum;

        // Parse Carbon objects on dummy base date for calculation
        $baseDate          = '2026-01-01 ';
        $jamKerjaMulai     = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($jamKerjaMulaiStr, 0, 5));
        $jamKerjaSelesai   = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($jamKerjaSelesaiStr, 0, 5));
        $istirahatMulai    = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($istirahatMulaiStr, 0, 5));
        $istirahatSelesai  = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($istirahatSelesaiStr, 0, 5));

        $jamMasukCarbon    = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($jamMasukAktual, 0, 5));
        $jamPulangCarbon   = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($jamPulangAktual, 0, 5));

        // 2. Determine JAM MASUK EFEKTIF
        $terlambatMenit = 0;
        $isTelat        = false;

        if ($jamMasukCarbon->lte($jamKerjaMulai)) {
            // Arrived earlier than or exactly at 08:00
            $jamMasukEfektif = $jamKerjaMulai->copy();
        } else {
            // Arrived after 08:00
            $diffInMinutes = $jamKerjaMulai->diffInMinutes($jamMasukCarbon);
            if ($diffInMinutes <= $toleransiBerlaku) {
                // Within tolerance -> effective clock in set to 08:00, NO DEDUCTION
                $jamMasukEfektif = $jamKerjaMulai->copy();
            } else {
                // Exceeded tolerance -> effective clock in is actual clock in time
                $jamMasukEfektif = $jamMasukCarbon->copy();
                $terlambatMenit  = $diffInMinutes;
                $isTelat         = true;
            }
        }

        // 3. Determine JAM PULANG EFEKTIF (SISTEM KOMPENSASI TUKAR JAM KERJA)
        // Jika karyawan datang terlambat, batas jam pulang reguler diperpanjang sebesar
        // menit keterlambatan agar karyawan dapat melunasi kuota 8 jam kerja regulernya.
        $batasPulangMaksimal = $jamKerjaSelesai->copy();
        if ($isTelat && $terlambatMenit > 0) {
            $batasPulangMaksimal->addMinutes($terlambatMenit);
        }

        // Jam pulang efektif di-cap pada batas maksimal kompensasi (tidak melebihi jam pelunasan)
        $jamPulangEfektif = $jamPulangCarbon->gt($batasPulangMaksimal) 
            ? $batasPulangMaksimal->copy() 
            : $jamPulangCarbon->copy();

        // 4. Gross Duration in minutes
        $durasiKotor = $jamMasukEfektif->lt($jamPulangEfektif) ? $jamMasukEfektif->diffInMinutes($jamPulangEfektif) : 0;

        if ($durasiKotor <= 0) {
            return [
                'total_jam_kerja'    => 0.0,
                'durasi_menit'       => 0,
                'status_absensi'     => 'Alpa',
                'jam_masuk_efektif'  => $jamMasukEfektif->format('H:i'),
                'jam_pulang_efektif' => $jamPulangEfektif->format('H:i'),
                'terlambat_menit'    => $terlambatMenit,
                'is_telat'           => $isTelat,
                'toleransi_berlaku'  => $toleransiBerlaku,
            ];
        }

        // 5. Subtract Break Overlap (12:00 - 13:00)
        $overlapStart = $jamMasukEfektif->gt($istirahatMulai) ? $jamMasukEfektif->copy() : $istirahatMulai->copy();
        $overlapEnd   = $jamPulangEfektif->lt($istirahatSelesai) ? $jamPulangEfektif->copy() : $istirahatSelesai->copy();

        $durasiOverlap = 0;
        if ($overlapStart->lt($overlapEnd)) {
            $durasiOverlap = $overlapStart->diffInMinutes($overlapEnd);
        }

        $durasiBersih = max(0, $durasiKotor - $durasiOverlap);

        // 6. Convert clean duration to HOURS (decimal, max 8.0)
        $totalJamKerja = round($durasiBersih / 60, 2);
        if ($totalJamKerja > 8.0) {
            $totalJamKerja = 8.0;
        }

        // 7. Determine STATUS ABSENSI
        $statusAbsensi = $isTelat ? 'Telat' : 'Hadir';

        return [
            'total_jam_kerja'    => $totalJamKerja,
            'durasi_menit'       => $durasiBersih,
            'status_absensi'     => $statusAbsensi,
            'jam_masuk_efektif'  => $jamMasukEfektif->format('H:i'),
            'jam_pulang_efektif' => $jamPulangEfektif->format('H:i'),
            'terlambat_menit'    => $terlambatMenit,
            'is_telat'           => $isTelat,
            'toleransi_berlaku'  => $toleransiBerlaku,
        ];
    }

    private static function normalizeTime(?string $timeStr): ?string
    {
        if (empty($timeStr) || $timeStr === '-' || $timeStr === '00:00' || $timeStr === '00:00:00') {
            return null;
        }

        $t = trim($timeStr);
        if (strlen($t) === 5) {
            return $t;
        }
        if (strlen($t) >= 8) {
            return substr($t, 0, 5);
        }

        return $t;
    }
}
