<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\GajiKaryawan;
use App\Models\DetailGajiKaryawan;
use App\Models\KomponenGaji;
use Illuminate\Http\Request;

class GajiKaryawanController extends Controller
{
    public function index(Request $request)
    {
        $search       = $request->get('search');
        $filterDivisi = $request->get('divisi');

        // Dynamically fetch and order all active components from database
        $komponens = KomponenGaji::all()->sortBy(function ($k) {
            $name = strtolower($k->nama_gaji);
            if (str_contains($name, 'pokok')) return 1;
            if (str_contains($name, 'makan')) return 2;
            if (str_contains($name, 'mingguan')) return 3;
            if (str_contains($name, 'bulanan')) return 4;
            if (str_contains($name, 'kerajinan')) return 5;
            if (str_contains($name, 'jabatan')) return 6;
            return 10;
        })->values();

        $karyawans = Karyawan::with(['divisi', 'jabatan', 'instansi'])
            ->when($search, function ($q) use ($search) {
                $q->where('nama_karyawan', 'like', "%{$search}%")
                  ->orWhere('Kode_karyawan', 'like', "%{$search}%");
            })
            ->orderBy('Kode_karyawan', 'asc')
            ->paginate(10)
            ->withQueryString();

        foreach ($karyawans as $k) {
            $gajiHeader = GajiKaryawan::where('ID_karyawan', $k->ID_karyawan)
                ->where('status', 'Aktif')
                ->latest()
                ->first();

            $componentValues = [];
            if ($gajiHeader) {
                $details = DetailGajiKaryawan::where('ID_gaji', $gajiHeader->ID_gaji)->get();
                foreach ($details as $det) {
                    $componentValues[$det->ID_komponen_gaji] = floatval($det->nominal_gaji);
                }
            }

            $k->gajiHeader = $gajiHeader;
            $k->componentValues = $componentValues;
        }

        $divisis = \App\Models\Divisi::all();

        return view('layouts.gaji_karyawan.index', compact('karyawans', 'komponens', 'divisis', 'search', 'filterDivisi'));
    }

    public function setup($id)
    {
        $karyawan = Karyawan::withTrashed()->with(['divisi', 'jabatan', 'instansi'])->findOrFail($id);

        $komponens = KomponenGaji::all()->sortBy(function ($k) {
            $name = strtolower($k->nama_gaji);
            if (str_contains($name, 'pokok')) return 1;
            if (str_contains($name, 'makan')) return 2;
            if (str_contains($name, 'mingguan')) return 3;
            if (str_contains($name, 'bulanan')) return 4;
            if (str_contains($name, 'kerajinan')) return 5;
            if (str_contains($name, 'jabatan')) return 6;
            return 10;
        })->values();

        $activeGaji = GajiKaryawan::where('ID_karyawan', $karyawan->ID_karyawan)
            ->where('status', 'Aktif')
            ->latest()
            ->first();

        $componentValues = [];
        if ($activeGaji) {
            $details = DetailGajiKaryawan::where('ID_gaji', $activeGaji->ID_gaji)->get();
            foreach ($details as $det) {
                $componentValues[$det->ID_komponen_gaji] = floatval($det->nominal_gaji);
            }
        }

        $histories = GajiKaryawan::where('ID_karyawan', $karyawan->ID_karyawan)
            ->with(['details.komponenGaji'])
            ->orderBy('ID_gaji', 'desc')
            ->get();

        return view('layouts.gaji_karyawan.setup', compact('karyawan', 'komponens', 'activeGaji', 'componentValues', 'histories'));
    }

    public function storeSetup(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    public function update(Request $request, $gaji_karyawan)
    {
        try {
            $karyawan = $gaji_karyawan instanceof Karyawan
                ? $gaji_karyawan
                : Karyawan::withTrashed()->findOrFail($gaji_karyawan);

            $existingActive = GajiKaryawan::where('ID_karyawan', $karyawan->ID_karyawan)
                ->where('status', 'Aktif')
                ->exists();

            $keteranganInput = trim($request->input('keterangan', ''));
            if ($existingActive && empty($keteranganInput)) {
                $keteranganInput = 'Penyesuaian Rate Gaji / Kenaikan Gaji';
            } elseif (empty($keteranganInput)) {
                $keteranganInput = 'Setup Gaji Awal Karyawan';
            }

            // 1. Deactivate old active salary headers for this employee
            GajiKaryawan::where('ID_karyawan', $karyawan->ID_karyawan)->update(['status' => 'Nonaktif']);

            // 2. Create new active salary header for this employee (IMMUTABLE LOGIC)
            $gajiHeader = GajiKaryawan::create([
                'ID_karyawan' => $karyawan->ID_karyawan,
                'date'        => date('Y-m-d'),
                'status'      => 'Aktif',
                'keterangan'  => $keteranganInput,
            ]);

            // 3. Fetch currently valid component IDs to prevent foreign key violation
            $validComponentIds = KomponenGaji::pluck('ID_komponen_gaji')->toArray();

            // 4. Save Gaji Pokok into detail_gaji_karyawans
            $gajiPokokInput = $request->input('gaji_pokok');
            $gajiPokokKomponen = KomponenGaji::where('nama_gaji', 'like', '%pokok%')->first();

            if ($gajiPokokInput !== null && $gajiPokokKomponen) {
                $cleanPokok = str_replace(['.', ','], '', (string)$gajiPokokInput);
                DetailGajiKaryawan::create([
                    'ID_gaji'          => $gajiHeader->ID_gaji,
                    'ID_komponen_gaji' => $gajiPokokKomponen->ID_komponen_gaji,
                    'nominal_gaji'     => max(0, floatval($cleanPokok)),
                ]);
            }

            // 5. Save all other component values to detail_gaji_karyawans
            $komponenInputs = $request->input('komponen', []);
            if (is_array($komponenInputs)) {
                foreach ($komponenInputs as $idKomponen => $nominal) {
                    if (!in_array($idKomponen, $validComponentIds)) continue; // Skip deleted components

                    // Skip duplicate if Gaji Pokok was saved above
                    if ($gajiPokokKomponen && $idKomponen == $gajiPokokKomponen->ID_komponen_gaji) {
                        continue;
                    }

                    $cleanNom = str_replace(['.', ','], '', (string)($nominal ?? 0));
                    $nom = max(0, floatval($cleanNom));
                    DetailGajiKaryawan::create([
                        'ID_gaji'          => $gajiHeader->ID_gaji,
                        'ID_komponen_gaji' => $idKomponen,
                        'nominal_gaji'     => $nom,
                    ]);
                }
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Gaji karyawan {$karyawan->nama_karyawan} berhasil disimpan!",
                ]);
            }

            return redirect()->route('gaji-karyawan.setup', $karyawan->ID_karyawan)
                ->with('success', "Data komponen gaji {$karyawan->nama_karyawan} berhasil disimpan.");

        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('gaji-karyawan.setup', $gaji_karyawan)
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function riwayat($id)
    {
        $karyawan = Karyawan::withTrashed()->findOrFail($id);

        $histories = GajiKaryawan::where('ID_karyawan', $karyawan->ID_karyawan)
            ->with(['details.komponenGaji'])
            ->orderBy('ID_gaji', 'desc')
            ->get()
            ->map(function ($gaji) {
                $totalEstimasi = $gaji->details->sum('nominal_gaji');
                return [
                    'ID_gaji'        => $gaji->ID_gaji,
                    'date_formatted' => $gaji->date ? $gaji->date->format('d F Y') : '-',
                    'status'         => $gaji->status,
                    'keterangan'     => $gaji->keterangan ?: '-',
                    'total_estimasi' => $totalEstimasi,
                    'details'        => $gaji->details->map(fn($d) => [
                        'nama_gaji' => $d->komponenGaji->nama_gaji ?? 'Komponen',
                        'nominal'   => floatval($d->nominal_gaji)
                    ])
                ];
            });

        return response()->json([
            'success'   => true,
            'karyawan'  => [
                'nama' => $karyawan->nama_karyawan,
                'kode' => $karyawan->Kode_karyawan,
            ],
            'histories' => $histories
        ]);
    }
}
