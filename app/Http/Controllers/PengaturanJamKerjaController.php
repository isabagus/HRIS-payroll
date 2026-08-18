<?php

namespace App\Http\Controllers;

use App\Models\PengaturanTarif;
use Illuminate\Http\Request;

class PengaturanJamKerjaController extends Controller
{
    private function ensureSuperAdmin()
    {
        $u = auth()->user();
        if (!$u || strtolower($u->role?->nama_role ?? '') !== 'super admin') {
            abort(403, 'Halaman ini khusus dapat diakses oleh Super Admin.');
        }
    }

    public function index()
    {
        $this->ensureSuperAdmin();

        $paramsRaw = PengaturanTarif::all()->keyBy('nama_parameter');

        $settings = [
            'jam_kerja_mulai'         => $paramsRaw['jam_kerja_mulai']->nilai ?? '08:00',
            'jam_kerja_selesai'       => $paramsRaw['jam_kerja_selesai']->nilai ?? '17:00',
            'istirahat_mulai'         => $paramsRaw['istirahat_mulai']->nilai ?? '12:00',
            'istirahat_selesai'       => $paramsRaw['istirahat_selesai']->nilai ?? '13:00',
            'toleransi_umum_menit'    => $paramsRaw['toleransi_umum_menit']->nilai ?? '5',
            'toleransi_manager_menit' => $paramsRaw['toleransi_manager_menit']->nilai ?? '60',
            'toleransi_manager_aktif' => $paramsRaw['toleransi_manager_aktif']->nilai ?? '0',
        ];

        return view('layouts.pengaturan_jam_kerja.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->ensureSuperAdmin();

        $request->validate([
            'jam_kerja_mulai'         => 'required|string',
            'jam_kerja_selesai'       => 'required|string',
            'istirahat_mulai'         => 'required|string',
            'istirahat_selesai'       => 'required|string',
            'toleransi_umum_menit'    => 'required|numeric|min:0',
            'toleransi_manager_menit' => 'required|numeric|min:0',
        ]);

        $data = [
            'jam_kerja_mulai'         => $request->input('jam_kerja_mulai'),
            'jam_kerja_selesai'       => $request->input('jam_kerja_selesai'),
            'istirahat_mulai'         => $request->input('istirahat_mulai'),
            'istirahat_selesai'       => $request->input('istirahat_selesai'),
            'toleransi_umum_menit'    => (string) $request->input('toleransi_umum_menit'),
            'toleransi_manager_menit' => (string) $request->input('toleransi_manager_menit'),
            'toleransi_manager_aktif' => $request->has('toleransi_manager_aktif') ? '1' : '0',
        ];

        foreach ($data as $key => $val) {
            PengaturanTarif::updateOrCreate(
                ['nama_parameter' => $key],
                ['nilai' => $val]
            );
        }

        return redirect()->route('pengaturan-jam-kerja.index')
            ->with('success', 'Pengaturan jam kerja dan toleransi berhasil diperbarui!');
    }
}
