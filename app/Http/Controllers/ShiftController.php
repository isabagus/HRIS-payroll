<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $shifts = Shift::when($search, function ($query, $search) {
                $query->where('nama_shift', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('jam_masuk', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('layouts.shift.index', compact('shifts', 'search'));
    }

    public function create()
    {
        return view('layouts.shift.create_edit', ['shift' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_shift'        => 'required|string|max:100|unique:shifts,nama_shift',
            'jam_masuk'         => 'required|date_format:H:i',
            'jam_pulang'        => 'required|date_format:H:i',
            'istirahat_mulai'   => 'nullable|date_format:H:i',
            'istirahat_selesai' => 'nullable|date_format:H:i',
            'deskripsi'         => 'nullable|string|max:255',
        ], [
            'nama_shift.required'        => 'Nama shift wajib diisi.',
            'nama_shift.unique'          => 'Nama shift sudah terdaftar.',
            'jam_masuk.required'         => 'Jam masuk wajib diisi.',
            'jam_pulang.required'        => 'Jam pulang wajib diisi.',
            'jam_masuk.date_format'      => 'Format jam masuk harus HH:MM (contoh: 08:00).',
            'jam_pulang.date_format'     => 'Format jam pulang harus HH:MM (contoh: 17:00).',
        ]);

        $isDefault = $request->boolean('is_default');

        // Jika diset default, nonaktifkan default pada shift lain
        if ($isDefault) {
            Shift::where('is_default', true)->update(['is_default' => false]);
        }

        // Jika belum ada shift sama sekali, otomatis jadikan default
        if (Shift::count() === 0) {
            $isDefault = true;
        }

        Shift::create([
            'nama_shift'        => $request->nama_shift,
            'jam_masuk'         => $request->jam_masuk . ':00',
            'jam_pulang'        => $request->jam_pulang . ':00',
            'istirahat_mulai'   => $request->istirahat_mulai ? $request->istirahat_mulai . ':00' : '12:00:00',
            'istirahat_selesai' => $request->istirahat_selesai ? $request->istirahat_selesai . ':00' : '13:00:00',
            'is_default'        => $isDefault,
            'deskripsi'         => $request->deskripsi,
        ]);

        return redirect()->route('shift.index')
            ->with('success', 'Master Shift Kerja berhasil ditambahkan.');
    }

    public function edit(Shift $shift)
    {
        return view('layouts.shift.create_edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'nama_shift'        => 'required|string|max:100|unique:shifts,nama_shift,' . $shift->id_shift . ',id_shift',
            'jam_masuk'         => 'required|date_format:H:i',
            'jam_pulang'        => 'required|date_format:H:i',
            'istirahat_mulai'   => 'nullable|date_format:H:i',
            'istirahat_selesai' => 'nullable|date_format:H:i',
            'deskripsi'         => 'nullable|string|max:255',
        ], [
            'nama_shift.required'        => 'Nama shift wajib diisi.',
            'nama_shift.unique'          => 'Nama shift sudah digunakan.',
            'jam_masuk.required'         => 'Jam masuk wajib diisi.',
            'jam_pulang.required'        => 'Jam pulang wajib diisi.',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault && !$shift->is_default) {
            Shift::where('is_default', true)->update(['is_default' => false]);
        } elseif (!$isDefault && $shift->is_default) {
            // Cegah hilangnya shift default sama sekali
            if (Shift::where('is_default', true)->count() <= 1) {
                $isDefault = true; // Pertahankan tetap default jika ini satu-satunya
            }
        }

        $shift->update([
            'nama_shift'        => $request->nama_shift,
            'jam_masuk'         => substr($request->jam_masuk, 0, 5) . ':00',
            'jam_pulang'        => substr($request->jam_pulang, 0, 5) . ':00',
            'istirahat_mulai'   => $request->istirahat_mulai ? substr($request->istirahat_mulai, 0, 5) . ':00' : '12:00:00',
            'istirahat_selesai' => $request->istirahat_selesai ? substr($request->istirahat_selesai, 0, 5) . ':00' : '13:00:00',
            'is_default'        => $isDefault,
            'deskripsi'         => $request->deskripsi,
        ]);

        return redirect()->route('shift.index')
            ->with('success', 'Master Shift Kerja berhasil diperbarui.');
    }

    public function setDefault(Shift $shift)
    {
        Shift::where('is_default', true)->update(['is_default' => false]);
        $shift->update(['is_default' => true]);

        return redirect()->route('shift.index')
            ->with('success', "Shift '{$shift->nama_shift}' berhasil ditetapkan sebagai Shift Utama (Default).");
    }

    public function destroy(Shift $shift)
    {
        if ($shift->is_default) {
            return redirect()->route('shift.index')
                ->with('error', 'Shift Utama (Default) tidak dapat dihapus. Silakan tetapkan shift lain sebagai default terlebih dahulu.');
        }

        $shift->delete();

        return redirect()->route('shift.index')
            ->with('success', 'Master Shift Kerja berhasil dihapus.');
    }
}
