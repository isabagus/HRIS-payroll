<?php

namespace App\Http\Controllers;

use App\Models\PengaturanTarif;
use Illuminate\Http\Request;

class PengaturanJamKerjaController extends Controller
{
    private function ensureSuperAdmin()
    {
        $u = auth()->user();
        if (!$u || (!$u->hasHakAkses('pengaturan_jam_kerja', 'read') && strtolower($u->role?->nama_role ?? '') !== 'super admin')) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman Pengaturan Jam Kerja.');
        }
    }

    public function index()
    {
        $this->ensureSuperAdmin();

        $paramsRaw = PengaturanTarif::all()->keyBy('nama_parameter');

        $settings = [
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
            'toleransi_umum_menit'    => 'required|numeric|min:0',
            'toleransi_manager_menit' => 'required|numeric|min:0',
        ], [
            'toleransi_umum_menit.required'    => 'Toleransi umum wajib diisi.',
            'toleransi_manager_menit.required' => 'Toleransi manager wajib diisi.',
        ]);

        $data = [
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
            ->with('success', 'Pengaturan Toleransi Keterlambatan berhasil diperbarui!');
    }
}
