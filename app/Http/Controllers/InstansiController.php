<?php

namespace App\Http\Controllers;

use App\Models\Instansi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstansiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $instansis = Instansi::when($search, function ($query, $search) {
                $query->where('nama_instansi', 'like', "%{$search}%");
            })
            ->orderBy('ID_instansi', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('layouts.instansi.index', compact('instansis', 'search'));
    }

    public function create()
    {
        return view('layouts.instansi.create_edit', ['instansi' => null]);
    }

    public function store(Request $request)
    {
        // Support dynamic multi-row input
        if ($request->has('items') && is_array($request->items)) {
            $request->validate([
                'items.*.nama_instansi' => 'required|string|max:150',
                'items.*.logo'          => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            ], [
                'items.*.nama_instansi.required' => 'Nama brand / instansi wajib diisi pada setiap baris.',
                'items.*.logo.image'             => 'File logo harus berupa gambar valid.',
                'items.*.logo.max'               => 'Ukuran logo maksimal 2MB.',
            ]);

            $inserted = 0;
            foreach ($request->items as $idx => $item) {
                $nama = trim($item['nama_instansi'] ?? '');
                if (!empty($nama)) {
                    $slug = Str::slug($nama);
                    $logo = null;
                    if ($request->hasFile("items.{$idx}.logo")) {
                        $file = $request->file("items.{$idx}.logo");
                        $logo = 'logo_' . $slug . '_' . time() . '_' . $idx . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('images'), $logo);
                    } elseif (file_exists(public_path("images/logo_{$slug}.png"))) {
                        $logo = "logo_{$slug}.png";
                    }
                    Instansi::create([
                        'nama_instansi' => $nama,
                        'logo'          => $logo,
                    ]);
                    $inserted++;
                }
            }

            return redirect()->route('instansi.index')
                ->with('success', "{$inserted} Brand / Instansi berhasil ditambahkan.");
        }

        // Single item fallback
        $request->validate([
            'nama_instansi' => 'required|string|max:150|unique:instansis,nama_instansi',
            'logo'          => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ], [
            'nama_instansi.required' => 'Nama instansi wajib diisi.',
            'nama_instansi.unique'   => 'Nama instansi sudah terdaftar.',
            'nama_instansi.max'      => 'Nama instansi maksimal 150 karakter.',
            'logo.image'             => 'File logo harus berupa gambar.',
            'logo.max'               => 'Ukuran logo maksimal 2MB.',
        ]);

        $logoName = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $logoName = 'logo_' . Str::slug($request->nama_instansi) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $logoName);
        }

        Instansi::create([
            'nama_instansi' => $request->nama_instansi,
            'logo'          => $logoName,
        ]);

        return redirect()->route('instansi.index')
            ->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function edit(Instansi $instansi)
    {
        return view('layouts.instansi.create_edit', compact('instansi'));
    }

    public function update(Request $request, Instansi $instansi)
    {
        $request->validate([
            'nama_instansi' => 'required|string|max:150|unique:instansis,nama_instansi,' . $instansi->ID_instansi . ',ID_instansi',
            'logo'          => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ], [
            'nama_instansi.required' => 'Nama instansi wajib diisi.',
            'nama_instansi.unique'   => 'Nama instansi sudah terdaftar.',
            'nama_instansi.max'      => 'Nama instansi maksimal 150 karakter.',
            'logo.image'             => 'File logo harus berupa gambar.',
            'logo.max'               => 'Ukuran logo maksimal 2MB.',
        ]);

        $logoName = $instansi->logo;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $logoName = 'logo_' . Str::slug($request->nama_instansi) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $logoName);
        }

        $instansi->update([
            'nama_instansi' => $request->nama_instansi,
            'logo'          => $logoName,
        ]);

        return redirect()->route('instansi.index')
            ->with('success', 'Instansi berhasil diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        try {
            $instansi->delete();
            return redirect()->route('instansi.index')
                ->with('success', 'Instansi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('instansi.index')
                ->with('error', 'Instansi tidak dapat dihapus karena masih digunakan oleh data karyawan.');
        }
    }
}
