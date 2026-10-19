@extends('base')

@section('page-title', 'Setup Gaji Karyawan - ' . $karyawan->nama_karyawan)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gaji-karyawan.index') }}">Gaji Karyawan</a></li>
    <li class="breadcrumb-item active">Setup &amp; Riwayat Gaji</li>
@endsection

@section('content')
<div class="master-page max-w-5xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="page-header-left flex items-center gap-3">
            <div class="page-header-icon flex-shrink-0" style="width: 44px; height: 44px; background: #e0e7ff; color: #4f46e5; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 24px; height: 24px; flex-shrink: 0;">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title text-xl sm:text-2xl font-bold text-slate-800">Setup &amp; Histori Gaji Karyawan</h1>
                <p class="page-subtitle text-xs sm:text-sm text-slate-500">Kelola komponen tarif gaji pokok &amp; pantau riwayat perubahan rate</p>
            </div>
        </div>
        <a href="{{ route('gaji-karyawan.index') }}" class="btn-back-action inline-flex items-center gap-2 self-start sm:self-auto">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; flex-shrink: 0;">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- 1. TOP CARD: Employee Profile Read-Only Info --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex-shrink-0 flex items-center justify-center font-bold text-lg text-white shadow-sm {{ $karyawan->jenis_kelamin === 'P' ? 'bg-pink-500' : 'bg-indigo-600' }}">
                    {{ strtoupper(substr($karyawan->nama_karyawan, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">{{ $karyawan->nama_karyawan }}</h2>
                    <p class="text-xs text-slate-400 font-medium">
                        Kode: <span class="text-slate-600 font-semibold">{{ $karyawan->Kode_karyawan }}</span> 
                        | NIK: <span class="text-slate-600 font-semibold">{{ $karyawan->nik ?? '-' }}</span>
                    </p>
                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $karyawan->divisi->nama_divisi ?? 'Divisi -' }}
                        </span>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $karyawan->jabatan->nama_jabatan ?? 'Jabatan -' }}
                        </span>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100">
                            @if($karyawan->id_instansi && $karyawan->instansi)
                                {{ $karyawan->instansi->nama_instansi }}
                            @else
                                <span style="color:#94a3b8; font-style:italic;">Nama Instansi</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <div class="w-full sm:w-auto bg-slate-50 border border-slate-100 rounded-xl p-3 sm:text-right">
                <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400 block">Status Rate Gaji Saat Ini</span>
                @if($activeGaji)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 mt-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Aktif (sejak {{ $activeGaji->date ? $activeGaji->date->format('d/m/Y') : '-' }})
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200 mt-1">
                        Belum Diatur
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. MIDDLE: Setup Form Komponen Gaji --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex-shrink-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; flex-shrink: 0;">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-800">Form Atur Rate Komponen Gaji</h3>
                <p class="text-xs text-slate-500">Setiap penyimpanan akan membuat versi histori gaji baru dan mengarsipkan rate lama</p>
            </div>
        </div>

        <form method="POST" action="{{ route('gaji-karyawan.storeSetup', $karyawan->ID_karyawan) }}" class="space-y-6">
            @csrf

            {{-- Keterangan Perubahan --}}
            <div class="form-group mb-4">
                <label class="form-label text-xs font-bold text-slate-700 block mb-1">
                    Keterangan Perubahan Gaji <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="keterangan" class="form-input w-full text-sm rounded-xl"
                       value="{{ old('keterangan', $activeGaji ? 'Penyesuaian Rate Gaji / Kenaikan Gaji' : 'Setup Gaji Awal Karyawan') }}"
                       placeholder="Misal: Kenaikan Gaji Evaluasi 6 Bulan, Penyesuaian UMR 2026, Setup Awal..." required>
                <p class="text-[11px] text-slate-400 mt-1">Catatan ini akan tersimpan permanen dalam riwayat perkembangan gaji karyawan.</p>
            </div>

            {{-- Gaji Pokok Harian --}}
            @php
                $gajiPokokVal = floatval($componentValues[1] ?? 0);
                if ($gajiPokokVal <= 0) {
                    foreach ($componentValues as $cId => $cNom) {
                        $cModel = $komponens->firstWhere('ID_komponen_gaji', $cId);
                        if ($cModel && str_contains(strtolower($cModel->nama_gaji), 'pokok')) {
                            $gajiPokokVal = floatval($cNom);
                            break;
                        }
                    }
                }
            @endphp
            {{-- 1. GAJI POKOK --}}
            <div class="p-4 rounded-xl bg-indigo-50/70 border border-indigo-100 mb-6">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="form-label text-xs font-bold text-indigo-950 flex items-center gap-1.5 mb-0">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        Gaji Pokok / Jam (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] font-medium text-indigo-700 bg-indigo-100/70 px-2 py-0.5 rounded-md">Basis Jam Kerja</span>
                </div>
                <input type="text" inputmode="numeric" name="gaji_pokok" class="form-input w-full text-base font-bold text-indigo-950 rounded-xl bg-white currency-input"
                       value="{{ old('gaji_pokok', $gajiPokokVal ? number_format($gajiPokokVal, 0, ',', '.') : '') }}" placeholder="7.500" required>
            </div>

            @php
                $pendapatanKomps = $komponens->filter(function($k) {
                    return ($k->jenis ?? 'pendapatan') === 'pendapatan' && !str_contains(strtolower($k->nama_gaji), 'pokok');
                });
                $potonganKomps = $komponens->filter(function($k) {
                    return ($k->jenis ?? 'pendapatan') === 'potongan';
                });
            @endphp

            {{-- 2. KOMPONEN PENDAPATAN & TUNJANGAN --}}
            <div class="mb-6 p-4 sm:p-5 rounded-2xl bg-emerald-50/40 border border-emerald-100/80">
                <div class="flex items-center justify-between mb-3.5 pb-2.5 border-b border-emerald-100">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                            +
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-950">Komponen Pendapatan &amp; Tunjangan</h4>
                            <p class="text-[11px] text-emerald-700">Tunjangan dan insentif yang menambah penghasilan karyawan</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                        {{ $pendapatanKomps->count() }} Komponen
                    </span>
                </div>

                @if($pendapatanKomps->isEmpty())
                    <p class="text-xs text-slate-400 italic py-2">Belum ada komponen tunjangan tambahan selain gaji pokok.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($pendapatanKomps as $komp)
                            @php
                                $n = strtolower($komp->nama_gaji);
                                $unit = '';
                                if (str_contains($n, 'makan')) $unit = '(per hari hadir)';
                                elseif (str_contains($n, 'mingguan') || str_contains($n, 'kerajinan')) $unit = '(per minggu penuh)';
                                else $unit = '(per bulan)';
                                $val = floatval($componentValues[$komp->ID_komponen_gaji] ?? 0);
                            @endphp
                            <div class="form-group mb-0 bg-white p-3 rounded-xl border border-emerald-100/70 shadow-xs">
                                <label class="form-label text-xs font-semibold text-slate-800 block mb-1">
                                    {{ $komp->nama_gaji }} <span class="text-emerald-700 font-normal text-[11px]">{{ $unit }}</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs text-slate-400 font-semibold">Rp</span>
                                    <input type="text" inputmode="numeric" name="komponen[{{ $komp->ID_komponen_gaji }}]"
                                           class="form-input w-full text-sm rounded-lg pl-9 font-semibold text-slate-800 currency-input"
                                           value="{{ old('komponen.'.$komp->ID_komponen_gaji, $val > 0 ? number_format($val, 0, ',', '.') : '') }}" placeholder="0">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 3. KOMPONEN POTONGAN RUTIN KARYAWAN --}}
            <div class="mb-6 p-4 sm:p-5 rounded-2xl bg-rose-50/40 border border-rose-100/80">
                <div class="flex items-center justify-between mb-3.5 pb-2.5 border-b border-rose-100">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-rose-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                            &minus;
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-rose-950">Komponen Potongan Rutin Karyawan</h4>
                            <p class="text-[11px] text-rose-700">Potongan tetap per periode (BPJS Karyawan, Iuran, Koperasi, dll)</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-200">
                        {{ $potonganKomps->count() }} Komponen
                    </span>
                </div>

                @if($potonganKomps->isEmpty())
                    <p class="text-xs text-slate-400 italic py-2">Belum ada komponen potongan rutin terdaftar.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($potonganKomps as $komp)
                            @php
                                $val = floatval($componentValues[$komp->ID_komponen_gaji] ?? 0);
                            @endphp
                            <div class="form-group mb-0 bg-white p-3 rounded-xl border border-rose-100/70 shadow-xs">
                                <label class="form-label text-xs font-semibold text-slate-800 block mb-1">
                                    {{ $komp->nama_gaji }} <span class="text-rose-600 font-normal text-[11px]">(memotong gaji)</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs text-rose-400 font-semibold">Rp</span>
                                    <input type="text" inputmode="numeric" name="komponen[{{ $komp->ID_komponen_gaji }}]"
                                           class="form-input w-full text-sm rounded-lg pl-9 font-semibold text-rose-700 currency-input"
                                           value="{{ old('komponen.'.$komp->ID_komponen_gaji, $val > 0 ? number_format($val, 0, ',', '.') : '') }}" placeholder="0">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('gaji-karyawan.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px; flex-shrink: 0;">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Rate Gaji Baru
                </button>
            </div>
        </form>
    </div>

    {{-- 3. BOTTOM: Timeline Riwayat Gaji --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
            <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex-shrink-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; flex-shrink: 0;">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-800">Timeline Riwayat Histori Gaji</h3>
                <p class="text-xs text-slate-500">Rekapitulasi sejarah perubahan dan kenaikan gaji karyawan dari waktu ke waktu</p>
            </div>
        </div>

        @if($histories->count() > 0)
        <div class="relative border-l-2 border-slate-200 ml-3 sm:ml-4 space-y-6 py-2">
            @foreach($histories as $h)
            @php
                $isActive = $h->status === 'Aktif';
                $badgeBg  = $isActive ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-600 border-slate-200';
                $dotBg    = $isActive ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-slate-400';
                $totalEst = $h->details->sum('nominal_gaji');
            @endphp
            <div class="relative pl-6 sm:pl-8">
                {{-- Dot indicator --}}
                <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full {{ $dotBg }}"></div>

                <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-sm space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500">
                                {{ $h->date ? $h->date->format('d F Y') : '-' }}
                            </span>
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full border {{ $badgeBg }}">
                                {{ $h->status }}
                            </span>
                        </div>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-lg self-start sm:self-auto">
                            Estimasi Total: Rp {{ number_format($totalEst, 0, ',', '.') }}
                        </span>
                    </div>

                    <h4 class="text-sm font-bold text-slate-800">{{ $h->keterangan ?: 'Perubahan Gaji' }}</h4>

                    <div class="border-t border-slate-200/80 pt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($h->details as $det)
                        <div class="flex items-center justify-between text-xs bg-white px-3 py-1.5 rounded-lg border border-slate-100">
                            <span class="text-slate-600">{{ $det->komponenGaji->nama_gaji ?? 'Komponen' }}</span>
                            <span class="font-semibold text-slate-800">Rp {{ number_format($det->nominal_gaji, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-slate-400 text-sm">
            Belum ada riwayat gaji tersimpan untuk karyawan ini.
        </div>
        @endif
    </div>

</div>
@endsection
