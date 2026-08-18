<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use Illuminate\Http\Request;

class DivisiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $divisis = Divisi::when($search, function ($query, $search) {
                $query->where('nama_divisi', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('layouts.divisi.index', compact('divisis', 'search'));
    }

    public function create()
    {
        return view('layouts.divisi.create_edit', ['divisi' => null]);
    }

    public function store(Request $request)
    {
        // Support dynamic multi-row input
        if ($request->has('items') && is_array($request->items)) {
            $request->validate([
                'items.*.nama_divisi' => 'required|string|max:100',
            ], [
                'items.*.nama_divisi.required' => 'Nama divisi wajib diisi pada setiap baris.',
            ]);

            $inserted = 0;
            foreach ($request->items as $item) {
                $nama = trim($item['nama_divisi'] ?? '');
                if (!empty($nama)) {
                    Divisi::firstOrCreate(['nama_divisi' => $nama]);
                    $inserted++;
                }
            }

            return redirect()->route('divisi.index')
                ->with('success', "{$inserted} Divisi berhasil ditambahkan.");
        }

        // Single item fallback
        $request->validate([
            'nama_divisi' => 'required|string|max:100|unique:divisis,nama_divisi',
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'nama_divisi.unique'   => 'Nama divisi sudah terdaftar.',
            'nama_divisi.max'      => 'Nama divisi maksimal 100 karakter.',
        ]);

        Divisi::create($request->only('nama_divisi'));

        return redirect()->route('divisi.index')
            ->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function edit(Divisi $divisi)
    {
        return view('layouts.divisi.create_edit', compact('divisi'));
    }

    public function update(Request $request, Divisi $divisi)
    {
        $request->validate([
            'nama_divisi' => 'required|string|max:100|unique:divisis,nama_divisi,' . $divisi->ID_divisi . ',ID_divisi',
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'nama_divisi.unique'   => 'Nama divisi sudah terdaftar.',
            'nama_divisi.max'      => 'Nama divisi maksimal 100 karakter.',
        ]);

        $divisi->update($request->only('nama_divisi'));

        return redirect()->route('divisi.index')
            ->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy(Divisi $divisi)
    {
        try {
            $divisi->delete();
            return redirect()->route('divisi.index')
                ->with('success', 'Divisi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('divisi.index')
                ->with('error', 'Divisi tidak dapat dihapus karena masih digunakan oleh data karyawan.');
        }
    }
}
