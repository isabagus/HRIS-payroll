<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $jabatans = Jabatan::when($search, function ($query, $search) {
                $query->where('nama_jabatan', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('layouts.jabatan.index', compact('jabatans', 'search'));
    }

    public function create()
    {
        return view('layouts.jabatan.create_edit', ['jabatan' => null]);
    }

    public function store(Request $request)
    {
        // Support dynamic multi-row input
        if ($request->has('items') && is_array($request->items)) {
            $request->validate([
                'items.*.nama_jabatan' => 'required|string|max:100',
            ], [
                'items.*.nama_jabatan.required' => 'Nama jabatan wajib diisi pada setiap baris.',
            ]);

            $inserted = 0;
            foreach ($request->items as $item) {
                $nama = trim($item['nama_jabatan'] ?? '');
                if (!empty($nama)) {
                    Jabatan::firstOrCreate(['nama_jabatan' => $nama]);
                    $inserted++;
                }
            }

            return redirect()->route('jabatan.index')
                ->with('success', "{$inserted} Jabatan berhasil ditambahkan.");
        }

        // Single item fallback
        $request->validate([
            'nama_jabatan' => 'required|string|max:100|unique:jabatans,nama_jabatan',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.unique'   => 'Nama jabatan sudah terdaftar.',
            'nama_jabatan.max'      => 'Nama jabatan maksimal 100 karakter.',
        ]);

        Jabatan::create($request->only('nama_jabatan'));

        return redirect()->route('jabatan.index')
            ->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Jabatan $jabatan)
    {
        return view('layouts.jabatan.create_edit', compact('jabatan'));
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:100|unique:jabatans,nama_jabatan,' . $jabatan->ID_jabatan . ',ID_jabatan',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.unique'   => 'Nama jabatan sudah terdaftar.',
            'nama_jabatan.max'      => 'Nama jabatan maksimal 100 karakter.',
        ]);

        $jabatan->update($request->only('nama_jabatan'));

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
