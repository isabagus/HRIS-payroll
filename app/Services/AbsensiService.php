<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\PengaturanTarif;
use App\Models\Shift;
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
    public static function hitungJamKerja(
        $karyawan, 
        ?string $jamMasukAktual, 
        ?string $jamPulangAktual, 
        ?string $manualStatus = null,
        ?string $jadwalJamMasuk = null
    ): array
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

        // Fetch settings parameters from DB (Toleransi Keterlambatan)
        $paramsRaw = PengaturanTarif::all()->keyBy('nama_parameter');

        $toleransiUmum       = intval($paramsRaw['toleransi_umum_menit']->nilai ?? 5);
        $toleransiManager    = intval($paramsRaw['toleransi_manager_menit']->nilai ?? 60);
        $toleransiMgrAktif   = ($paramsRaw['toleransi_manager_aktif']->nilai ?? '0') === '1';

        // 1. Determine Karyawan, Role, Divisi, and Applicable Tolerance
        if (!($karyawan instanceof Karyawan)) {
            $karyawan = Karyawan::with(['jabatan', 'divisi'])->find($karyawan);
        } else {
            if (!$karyawan->relationLoaded('jabatan')) {
                $karyawan->load('jabatan');
            }
            if (!$karyawan->relationLoaded('divisi')) {
                $karyawan->load('divisi');
            }
        }

        $jabatanNama  = strtolower($karyawan->jabatan->nama_jabatan ?? '');
        $isManager    = (stripos($jabatanNama, 'manager') !== false);

        // Penentuan multi-shift bergilir murni dari konfigurasi Jabatan karyawan (atau fallback ke Divisi)
        $bisaDuaShift = false;
        if ($karyawan->jabatan && method_exists($karyawan->jabatan, 'isShift')) {
            $bisaDuaShift = $karyawan->jabatan->isShift();
        } elseif ($karyawan->divisi && method_exists($karyawan->divisi, 'isShift')) {
            $bisaDuaShift = $karyawan->divisi->isShift();
        }

        $toleransiBerlaku = ($toleransiMgrAktif && $isManager) ? $toleransiManager : $toleransiUmum;

        // 2. SMART SHIFT MATCHING:
        // Prioritas 1: Jika file import Fingerspot mencantumkan jadwal resmi (jam_kerja_masuk)
        // Prioritas 2: Smart Matching berdasarkan jam kedatangan aktual (jika tanpa jadwal resmi)
        $shiftDefault = Shift::where('is_default', true)->first() ?? Shift::where('nama_shift', 'like', '%pagi%')->first();
        $shiftSiang   = Shift::where('nama_shift', 'like', '%siang%')->first();

        $shiftAktif = $shiftDefault;
        $jadwalNormalized = self::normalizeTime($jadwalJamMasuk);

        if ($bisaDuaShift) {
            if ($jadwalNormalized && $shiftSiang) {
                // Ikuti jadwal resmi mesin Fingerspot (misal 08:00 vs 14:00)
                if ($jadwalNormalized >= '12:00') {
                    $shiftAktif = $shiftSiang; // Shift 2 (Siang)
                } else {
                    $shiftAktif = $shiftDefault; // Shift 1 (Pagi)
                }
            } elseif ($shiftSiang && $jamMasukAktual >= '12:00') {
                // Fallback smart matching jam aktual jika mesin tidak mencantumkan jam jadwal
                $shiftAktif = $shiftSiang;
            }
        }

        $jamKerjaMulaiStr    = $shiftAktif ? substr($shiftAktif->jam_masuk, 0, 5) : ($paramsRaw['jam_kerja_mulai']->nilai ?? '08:00');
        $jamKerjaSelesaiStr  = $shiftAktif ? substr($shiftAktif->jam_pulang, 0, 5) : ($paramsRaw['jam_kerja_selesai']->nilai ?? '17:00');
        $istirahatMulaiStr   = $shiftAktif && $shiftAktif->istirahat_mulai ? substr($shiftAktif->istirahat_mulai, 0, 5) : ($paramsRaw['istirahat_mulai']->nilai ?? '12:00');
        $istirahatSelesaiStr = $shiftAktif && $shiftAktif->istirahat_selesai ? substr($shiftAktif->istirahat_selesai, 0, 5) : ($paramsRaw['istirahat_selesai']->nilai ?? '13:00');

        // Parse Carbon objects on dummy base date for calculation
        $baseDate          = '2026-01-01 ';
        $jamKerjaMulai     = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($jamKerjaMulaiStr, 0, 5));
        $jamKerjaSelesai   = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($jamKerjaSelesaiStr, 0, 5));
        $istirahatMulai    = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($istirahatMulaiStr, 0, 5));
        $istirahatSelesai  = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($istirahatSelesaiStr, 0, 5));

        $jamMasukCarbon    = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($jamMasukAktual, 0, 5));
        $jamPulangCarbon   = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($jamPulangAktual, 0, 5));

        // Penanganan jika shift atau jam pulang melewati tengah malam (cross-day)
        if ($jamKerjaSelesai->lt($jamKerjaMulai)) {
            $jamKerjaSelesai->addDay();
        }
        if ($jamPulangCarbon->lt($jamMasukCarbon)) {
            $jamPulangCarbon->addDay();
        }

        // 3. Determine JAM MASUK EFEKTIF
        $terlambatMenit = 0;
        $isTelat        = false;

        if ($jamMasukCarbon->lte($jamKerjaMulai)) {
            // Datang tepat waktu atau lebih awal dari jam mulai shift
            $jamMasukEfektif = $jamKerjaMulai->copy();
        } else {
            // Datang setelah jam mulai shift
            $diffInMinutes = $jamKerjaMulai->diffInMinutes($jamMasukCarbon);
            if ($diffInMinutes <= $toleransiBerlaku) {
                // Dalam batas toleransi -> jam masuk efektif diset ke jam mulai shift, TIDAK TELAT
                $jamMasukEfektif = $jamKerjaMulai->copy();
            } else {
                // Melebihi toleransi -> jam masuk efektif sesuai jam aktual, STATUS TELAT
                $jamMasukEfektif = $jamMasukCarbon->copy();
                $terlambatMenit  = $diffInMinutes;
                $isTelat         = true;
            }
        }

        // 4. Determine JAM PULANG EFEKTIF (SISTEM KOMPENSASI TUKAR JAM KERJA)
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

        // 5. Gross Duration in minutes
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
                'id_shift'           => $shiftAktif?->id_shift,
                'nama_shift'         => $shiftAktif?->nama_shift ?? 'Shift Standar',
            ];
        }

        // 6. Subtract Break Overlap
        $overlapStart = $jamMasukEfektif->gt($istirahatMulai) ? $jamMasukEfektif->copy() : $istirahatMulai->copy();
        $overlapEnd   = $jamPulangEfektif->lt($istirahatSelesai) ? $jamPulangEfektif->copy() : $istirahatSelesai->copy();

        $durasiOverlap = 0;
        if ($overlapStart->lt($overlapEnd)) {
            $durasiOverlap = $overlapStart->diffInMinutes($overlapEnd);
        }

        $durasiBersih = max(0, $durasiKotor - $durasiOverlap);

        // 7. Convert clean duration to HOURS (decimal, max 8.0)
        $totalJamKerja = round($durasiBersih / 60, 2);
        if ($totalJamKerja > 8.0) {
            $totalJamKerja = 8.0;
        }

        // 8. Determine STATUS ABSENSI
        $statusAbsensi = $isTelat ? 'Telat' : 'Hadir';

        return [
            'total_jam_kerja'       => $totalJamKerja,
            'durasi_menit'          => $durasiBersih,
            'status_absensi'        => $statusAbsensi,
            'jam_masuk_efektif'     => $jamMasukEfektif->format('H:i'),
            'jam_pulang_efektif'    => $jamPulangEfektif->format('H:i'),
            'jam_kerja_mulai'       => $jamKerjaMulai->format('H:i'),
            'jam_kerja_selesai'     => $jamKerjaSelesai->format('H:i'),
            'batas_pulang_pokok'    => $batasPulangMaksimal->format('H:i'),
            'jam_mulai_lembur_sah'  => $batasPulangMaksimal->format('H:i'),
            'terlambat_menit'       => $terlambatMenit,
            'is_telat'              => $isTelat,
            'toleransi_berlaku'     => $toleransiBerlaku,
            'id_shift'              => $shiftAktif?->id_shift,
            'nama_shift'            => $shiftAktif?->nama_shift ?? 'Shift Standar',
        ];
    }

    /**
     * Dapatkan informasi shift kerja, absensi, dan jam sah mulai lembur untuk tanggal tertentu.
     * Mengimplementasikan proteksi ANTI-DOUBLE DIPPING agar jam tebusan keterlambatan
     * tidak dibayar ganda sebagai uang lembur.
     */
    public static function getInfoLemburHari($karyawan, $tanggal): array
    {
        if (is_numeric($karyawan)) {
            $karyawan = \App\Models\Karyawan::with(['jabatan', 'divisi'])->find($karyawan);
        }

        if (!$karyawan) {
            return [
                'has_absensi'          => false,
                'nama_shift'           => 'Shift Standar',
                'jam_selesai_shift'    => '17:00',
                'jam_mulai_lembur_sah' => '17:00',
                'terlambat_menit'      => 0,
                'is_telat'             => false,
                'catatan_proteksi'     => null,
            ];
        }

        $absensi = \App\Models\Absensi::where('ID_karyawan', $karyawan->ID_karyawan)
            ->where('date', $tanggal)
            ->first();

        if ($absensi && (!empty($absensi->jam_masuk) || !empty($absensi->jam_pulang))) {
            $calc = self::hitungJamKerja($karyawan, $absensi->jam_masuk, $absensi->jam_pulang, $absensi->status_absensi, $absensi->jam_kerja_masuk);

            $catatan = null;
            if ($calc['is_telat'] && $calc['terlambat_menit'] > 0) {
                $catatan = "Karyawan terlambat {$calc['terlambat_menit']} menit (masuk {$absensi->jam_masuk}). Waktu s/d jam {$calc['jam_mulai_lembur_sah']} merupakan waktu tebusan jam kerja pokok (Gaji Pokok utuh). Lembur baru sah dihitung setelah jam {$calc['jam_mulai_lembur_sah']} (Proteksi Anti-Double Dipping).";
            }

            return [
                'has_absensi'          => true,
                'jam_masuk_aktual'     => $absensi->jam_masuk,
                'jam_pulang_aktual'    => $absensi->jam_pulang,
                'nama_shift'           => $calc['nama_shift'],
                'jam_selesai_shift'    => $calc['jam_kerja_selesai'],
                'jam_mulai_lembur_sah' => $calc['jam_mulai_lembur_sah'],
                'terlambat_menit'      => $calc['terlambat_menit'],
                'is_telat'             => $calc['is_telat'],
                'catatan_proteksi'     => $catatan,
            ];
        }

        // Jika belum ada record absensi pada tanggal tersebut, tentukan berdasarkan shift default jabatan
        $bisaShift = $karyawan->jabatan && method_exists($karyawan->jabatan, 'isShift') && $karyawan->jabatan->isShift();
        $shiftPagi = \App\Models\Shift::where('is_default', true)->first() 
            ?? \App\Models\Shift::where('nama_shift', 'like', '%pagi%')->first();

        $jamSelesai = $shiftPagi ? substr($shiftPagi->jam_pulang, 0, 5) : '17:00';

        return [
            'has_absensi'          => false,
            'jam_masuk_aktual'     => null,
            'jam_pulang_aktual'    => null,
            'nama_shift'           => $shiftPagi ? $shiftPagi->nama_shift : 'Shift 1 (Pagi)',
            'jam_selesai_shift'    => $jamSelesai,
            'jam_mulai_lembur_sah' => $jamSelesai,
            'terlambat_menit'      => 0,
            'is_telat'             => false,
            'catatan_proteksi'     => null,
            'is_bisa_shift'        => $bisaShift,
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
