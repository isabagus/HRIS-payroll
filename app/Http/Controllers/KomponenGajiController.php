<?php

namespace App\Http\Controllers;

use App\Models\KomponenGaji;
use Illuminate\Http\Request;

class KomponenGajiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $komponens = KomponenGaji::when($search, function ($query, $search) {
                $query->where('nama_gaji', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('layouts.gaji.index', compact('komponens', 'search'));
    }

    public function create()
    {
        return view('layouts.gaji.create_edit', ['komponen' => null]);
    }

    public function store(Request $request)
    {
        // Support dynamic multi-row input
        if ($request->has('items') && is_array($request->items)) {
            $request->validate([
                'items.*.nama_gaji' => 'required|string|max:100',
            ], [
                'items.*.nama_gaji.required' => 'Nama komponen gaji wajib diisi pada setiap baris.',
            ]);

            $inserted = 0;
            foreach ($request->items as $item) {
                $nama = trim($item['nama_gaji'] ?? '');
                if (!empty($nama)) {
                    KomponenGaji::firstOrCreate(['nama_gaji' => $nama]);
                    $inserted++;
                }
            }

            return redirect()->route('gaji.index')
                ->with('success', "{$inserted} Komponen gaji berhasil ditambahkan.");
        }

        // Single item fallback
        $request->validate([
            'nama_gaji' => 'required|string|max:100|unique:komponen_gajis,nama_gaji',
        ], [
            'nama_gaji.required' => 'Nama komponen gaji wajib diisi.',
            'nama_gaji.unique'   => 'Nama komponen gaji sudah terdaftar.',
            'nama_gaji.max'      => 'Nama komponen gaji maksimal 100 karakter.',
        ]);

        KomponenGaji::create($request->only('nama_gaji'));

        return redirect()->route('gaji.index')
            ->with('success', 'Komponen gaji berhasil ditambahkan.');
    }

    public function edit(KomponenGaji $gaji)
    {
        return view('layouts.gaji.create_edit', ['komponen' => $gaji]);
    }

    public function update(Request $request, KomponenGaji $gaji)
    {
        $request->validate([
            'nama_gaji' => 'required|string|max:100|unique:komponen_gajis,nama_gaji,' . $gaji->ID_komponen_gaji . ',ID_komponen_gaji',
        ], [
            'nama_gaji.required' => 'Nama komponen gaji wajib diisi.',
            'nama_gaji.unique'   => 'Nama komponen gaji sudah terdaftar.',
            'nama_gaji.max'      => 'Nama komponen gaji maksimal 100 karakter.',
        ]);

        $gaji->update($request->only('nama_gaji'));

        return redirect()->route('gaji.index')
            ->with('success', 'Komponen gaji berhasil diperbarui.');
    }

    public function destroy(KomponenGaji $gaji)
    {
        try {
            // Delete linked detail records first so user can freely delete unwanted components
            \App\Models\DetailGajiKaryawan::where('ID_komponen_gaji', $gaji->ID_komponen_gaji)->delete();

            $gaji->delete();
            return redirect()->route('gaji.index')
                ->with('success', 'Komponen gaji berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('gaji.index')
                ->with('error', 'Gagal menghapus komponen gaji: ' . $e->getMessage());
        }
    }
}
