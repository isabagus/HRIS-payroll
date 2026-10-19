<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Divisi;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $jabatans = Jabatan::with('divisi')
            ->when($search, function ($query, $search) {
                $query->where('nama_jabatan', 'like', "%{$search}%")
                    ->orWhereHas('divisi', function ($q) use ($search) {
                        $q->where('nama_divisi', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('layouts.jabatan.index', compact('jabatans', 'search'));
    }

    public function create()
    {
        $divisis = Divisi::orderBy('nama_divisi')->get();
        return view('layouts.jabatan.create_edit', [
            'jabatan' => null,
            'divisis' => $divisis
        ]);
    }

    public function store(Request $request)
    {
        // Support dynamic multi-row input
        if ($request->has('items') && is_array($request->items)) {
            $request->validate([
                'items.*.nama_jabatan' => 'required|string|max:100',
                'items.*.ID_divisi'    => 'nullable|exists:divisis,ID_divisi',
                'items.*.tipe_jadwal'  => 'required|in:staff,shift',
            ], [
                'items.*.nama_jabatan.required' => 'Nama jabatan wajib diisi pada setiap baris.',
            ]);

            $inserted = 0;
            foreach ($request->items as $item) {
                $nama = trim($item['nama_jabatan'] ?? '');
                if (!empty($nama)) {
                    Jabatan::create([
                        'nama_jabatan' => $nama,
                        'ID_divisi'    => !empty($item['ID_divisi']) ? $item['ID_divisi'] : null,
                        'tipe_jadwal'  => $item['tipe_jadwal'] ?? 'staff',
                    ]);
                    $inserted++;
                }
            }

            return redirect()->route('jabatan.index')
                ->with('success', "{$inserted} Jabatan berhasil ditambahkan.");
        }

        // Single item fallback
        $request->validate([
            'nama_jabatan' => 'required|string|max:100|unique:jabatans,nama_jabatan',
            'ID_divisi'    => 'nullable|exists:divisis,ID_divisi',
            'tipe_jadwal'  => 'required|in:staff,shift',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.unique'   => 'Nama jabatan sudah terdaftar.',
            'nama_jabatan.max'      => 'Nama jabatan maksimal 100 karakter.',
        ]);

        Jabatan::create($request->only('nama_jabatan', 'ID_divisi', 'tipe_jadwal'));

        return redirect()->route('jabatan.index')
            ->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Jabatan $jabatan)
    {
        $divisis = Divisi::orderBy('nama_divisi')->get();
        return view('layouts.jabatan.create_edit', compact('jabatan', 'divisis'));
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:100|unique:jabatans,nama_jabatan,' . $jabatan->ID_jabatan . ',ID_jabatan',
            'ID_divisi'    => 'nullable|exists:divisis,ID_divisi',
            'tipe_jadwal'  => 'required|in:staff,shift',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.unique'   => 'Nama jabatan sudah terdaftar.',
            'nama_jabatan.max'      => 'Nama jabatan maksimal 100 karakter.',
        ]);

        $jabatan->update($request->only('nama_jabatan', 'ID_divisi', 'tipe_jadwal'));

        return redirect()->route('jabatan.index')
            ->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Jabatan $jabatan)
    {
        try {
            $jabatan->delete();
            return redirect()->route('jabatan.index')
                ->with('success', 'Jabatan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('jabatan.index')
                ->with('error', 'Jabatan tidak dapat dihapus karena masih digunakan oleh data karyawan.');
        }
    }
}
