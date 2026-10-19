<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    protected $table = 'shifts';
    protected $primaryKey = 'id_shift';

    protected $fillable = [
        'nama_shift',
        'jam_masuk',
        'jam_pulang',
        'istirahat_mulai',
        'istirahat_selesai',
        'is_default',
        'deskripsi',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Format jam masuk (HH:mm)
     */
    public function getJamMasukFormatAttribute(): string
    {
        return $this->jam_masuk ? substr($this->jam_masuk, 0, 5) : '-';
    }

    /**
     * Format jam pulang (HH:mm)
     */
    public function getJamPulangFormatAttribute(): string
    {
        return $this->jam_pulang ? substr($this->jam_pulang, 0, 5) : '-';
    }

    /**
     * Format rentang istirahat (HH:mm - HH:mm)
     */
    public function getIstirahatFormatAttribute(): string
    {
        if (!$this->istirahat_mulai || !$this->istirahat_selesai) return '-';
        return substr($this->istirahat_mulai, 0, 5) . ' - ' . substr($this->istirahat_selesai, 0, 5);
    }

    /**
     * Hitung durasi kerja bersih (Jam) setelah dikurangi waktu istirahat
     */
    public function getDurasiJamKerjaAttribute(): float
    {
        if (!$this->jam_masuk || !$this->jam_pulang) return 0.0;

        try {
            $baseDate = '2026-01-01 ';
            $in  = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($this->jam_masuk, 0, 5));
            $out = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($this->jam_pulang, 0, 5));

            if ($out->lt($in)) {
                $out->addDay();
            }

            $durasiKotor = $in->diffInMinutes($out);

            // Hitung durasi istirahat
            $durasiIstirahat = 0;
            if ($this->istirahat_mulai && $this->istirahat_selesai) {
                $bIn  = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($this->istirahat_mulai, 0, 5));
                $bOut = Carbon::createFromFormat('Y-m-d H:i', $baseDate . substr($this->istirahat_selesai, 0, 5));
                if ($bOut->lt($bIn)) $bOut->addDay();
                $durasiIstirahat = $bIn->diffInMinutes($bOut);
            }

            $durasiBersih = max(0, $durasiKotor - $durasiIstirahat);
            return round($durasiBersih / 60, 2);
        } catch (\Exception $e) {
            return 8.0;
        }
    }

    /**
     * Ambil shift default
     */
    public static function getDefault(): ?self
    {
        return self::where('is_default', true)->first() ?? self::first();
    }
}
