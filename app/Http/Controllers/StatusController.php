<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $statuses = Status::when($search, function ($query, $search) {
                $query->where('nama_status', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('layouts.status.index', compact('statuses', 'search'));
    }

    public function create()
    {
        return view('layouts.status.create_edit', ['status' => null]);
    }

    public function store(Request $request)
    {
        // Support dynamic multi-row input
        if ($request->has('items') && is_array($request->items)) {
            $request->validate([
                'items.*.nama_status' => 'required|string|max:100',
            ], [
                'items.*.nama_status.required' => 'Nama status wajib diisi pada setiap baris.',
            ]);

            $inserted = 0;
            foreach ($request->items as $item) {
                $nama = trim($item['nama_status'] ?? '');
                if (!empty($nama)) {
                    Status::firstOrCreate(['nama_status' => $nama]);
                    $inserted++;
                }
            }

            return redirect()->route('status.index')
                ->with('success', "{$inserted} Status karyawan berhasil ditambahkan.");
        }

        // Single item fallback
        $request->validate([
            'nama_status' => 'required|string|max:100|unique:statuses,nama_status',
        ], [
            'nama_status.required' => 'Nama status wajib diisi.',
            'nama_status.unique'   => 'Nama status sudah terdaftar.',
            'nama_status.max'      => 'Nama status maksimal 100 karakter.',
        ]);

        Status::create($request->only('nama_status'));

        return redirect()->route('status.index')
            ->with('success', 'Status karyawan berhasil ditambahkan.');
    }

    public function edit(Status $status)
    {
        return view('layouts.status.create_edit', compact('status'));
    }

    public function update(Request $request, Status $status)
    {
        $request->validate([
            'nama_status' => 'required|string|max:100|unique:statuses,nama_status,' . $status->ID_status . ',ID_status',
        ], [
            'nama_status.required' => 'Nama status wajib diisi.',
            'nama_status.unique'   => 'Nama status sudah terdaftar.',
            'nama_status.max'      => 'Nama status maksimal 100 karakter.',
        ]);

        $status->update($request->only('nama_status'));

        return redirect()->route('status.index')
            ->with('success', 'Status karyawan berhasil diperbarui.');
    }

    public function destroy(Status $status)
    {
        try {
            $status->delete();
            return redirect()->route('status.index')
                ->with('success', 'Status karyawan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('status.index')
                ->with('error', 'Status tidak dapat dihapus karena masih digunakan oleh data karyawan.');
        }
    }
}
