@extends('base')

@section('page-title', 'Riwayat Absensi Tahunan - ' . $karyawan->nama_karyawan)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('absensi.index') }}">Rekap Absensi</a></li>
    <li class="breadcrumb-item"><a href="{{ route('absensi.show', ['absensi' => $karyawan->ID_karyawan, 'tahun' => $filterTahun]) }}">Rincian Bulanan</a></li>
    <li class="breadcrumb-item active">Riwayat Tahunan</li>
@endsection

@section('content')
<div class="master-page space-y-5 sm:space-y-6">

    {{-- Page Header --}}
    <div class="page-header flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="page-header-left flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 border border-sky-100">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 sm:w-6 sm:h-6">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg sm:text-2xl font-bold text-slate-800 leading-tight">Riwayat Absensi Tahunan</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Rekapitulasi performa & kontribusi harian sepanjang tahun — <strong class="text-slate-700">{{ $karyawan->nama_karyawan }}</strong></p>
            </div>
        </div>

        <div class="page-header-actions flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-between md:justify-end border-t md:border-t-0 pt-3 md:pt-0 border-slate-100">
            {{-- Filter Year Form --}}
            <form method="GET" action="{{ route('absensi.tahunan', $karyawan->ID_karyawan) }}" class="flex items-center gap-2">
                <label for="select-tahun" class="text-xs font-semibold text-slate-600">Tahun:</label>
                <select name="tahun" id="select-tahun" class="filter-select text-xs font-bold py-2 px-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white focus:bg-white transition cursor-pointer shadow-2xs" onchange="this.form.submit()">
                    @foreach($tahunList as $y)
                        <option value="{{ $y }}" {{ (string)$filterTahun === (string)$y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('absensi.show', ['absensi' => $karyawan->ID_karyawan, 'tahun' => $filterTahun]) }}" class="btn-secondary-action inline-flex items-center gap-1.5 text-xs py-2 px-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-slate-500">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Rincian Bulanan
            </a>
        </div>
    </div>

    {{-- Karyawan Profile Summary Banner --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 text-center sm:text-left">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center font-bold text-xl text-white shadow-sm shrink-0 {{ $karyawan->jenis_kelamin === 'P' ? 'bg-pink-500' : 'bg-indigo-600' }}">
                {{ strtoupper(substr($karyawan->nama_karyawan, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-800 truncate">{{ $karyawan->nama_karyawan }}</h2>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            Kode: <strong class="text-slate-700 font-mono">{{ $karyawan->Kode_karyawan }}</strong>
                            @if($karyawan->nik) &bull; NIK: <strong class="text-slate-700 font-mono">{{ $karyawan->nik }}</strong> @endif
                            @if($karyawan->instansi) &bull; Instansi: <strong class="text-slate-700">{{ $karyawan->instansi->nama_instansi }}</strong> @endif
                        </p>
                    </div>
                    <div class="flex items-center justify-center sm:justify-start gap-2 shrink-0">
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200">{{ $karyawan->divisi->nama_divisi ?? 'Tanpa Divisi' }}</span>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $karyawan->jabatan->nama_jabatan ?? 'Staff' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 6 Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
        {{-- Total Hadir --}}
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 border-l-4 border-l-emerald-500 shadow-2xs flex items-center gap-3">
            <div class="p-2 sm:p-2.5 rounded-xl bg-emerald-50 text-emerald-600 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 sm:w-5 sm:h-5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-base sm:text-lg font-bold text-slate-800 block truncate">{{ number_format($statHadir) }}</span>
                <span class="text-[11px] sm:text-xs text-slate-500 block truncate">Total Hadir</span>
            </div>
        </div>

        {{-- Total Telat --}}
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 border-l-4 border-l-amber-500 shadow-2xs flex items-center gap-3">
            <div class="p-2 sm:p-2.5 rounded-xl bg-amber-50 text-amber-600 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 sm:w-5 sm:h-5">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-base sm:text-lg font-bold text-slate-800 block truncate">{{ number_format($statTelat) }}</span>
                <span class="text-[11px] sm:text-xs text-slate-500 block truncate">Total Telat</span>
            </div>
        </div>

        {{-- Total Setengah Hari --}}
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 border-l-4 border-l-orange-500 shadow-2xs flex items-center gap-3">
            <div class="p-2 sm:p-2.5 rounded-xl bg-orange-50 text-orange-600 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 sm:w-5 sm:h-5">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-base sm:text-lg font-bold text-slate-800 block truncate">{{ number_format($statSetengahHari) }}</span>
                <span class="text-[11px] sm:text-xs text-slate-500 block truncate">1/2 Hari (0.5 H)</span>
            </div>
        </div>

        {{-- Total Izin / Sakit --}}
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 border-l-4 border-l-blue-500 shadow-2xs flex items-center gap-3">
            <div class="p-2 sm:p-2.5 rounded-xl bg-blue-50 text-blue-600 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 sm:w-5 sm:h-5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-base sm:text-lg font-bold text-slate-800 block truncate">{{ number_format($statIzinSakit) }}</span>
                <span class="text-[11px] sm:text-xs text-slate-500 block truncate">Izin / Sakit</span>
            </div>
        </div>

        {{-- Total Alpha --}}
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 border-l-4 border-l-red-500 shadow-2xs flex items-center gap-3">
            <div class="p-2 sm:p-2.5 rounded-xl bg-red-50 text-red-600 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 sm:w-5 sm:h-5">
                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-base sm:text-lg font-bold text-slate-800 block truncate">{{ number_format($statAlpha) }}</span>
                <span class="text-[11px] sm:text-xs text-slate-500 block truncate">Total Alpha</span>
            </div>
        </div>

        {{-- Persentase Kehadiran --}}
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 border-l-4 border-l-indigo-600 shadow-2xs flex items-center gap-3">
            <div class="p-2 sm:p-2.5 rounded-xl bg-indigo-50 text-indigo-600 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 sm:w-5 sm:h-5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-base sm:text-lg font-bold text-indigo-600 block truncate">{{ $persentaseKehadiran }}%</span>
                <span class="text-[11px] sm:text-xs text-slate-500 block truncate">Kehadiran ({{ $effectiveDaysCount }} H)</span>
            </div>
        </div>
    </div>

    {{-- SECTION 1: HEATMAP KALENDER KEHADIRAN TAHUNAN --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Heatmap Kalender Kehadiran {{ $filterTahun }}</h3>
                    <p class="text-xs text-slate-500">Visualisasi kontribusi kehadiran harian sepanjang 12 bulan (hover/arahkan kursor pada kotak tanggal)</p>
                </div>
            </div>

            {{-- Legend Box --}}
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs bg-slate-50 px-3 py-2 rounded-xl border border-slate-200/80 w-full lg:w-auto">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-emerald-500 inline-block"></span>
                    <span class="text-slate-600 text-[11px] font-medium">Hadir</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-amber-400 inline-block"></span>
                    <span class="text-slate-600 text-[11px] font-medium">Telat</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-orange-500 inline-block"></span>
                    <span class="text-slate-600 text-[11px] font-medium">1/2 Hari</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-blue-500 inline-block"></span>
                    <span class="text-slate-600 text-[11px] font-medium">Izin/Sakit</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-red-500 inline-block"></span>
                    <span class="text-slate-600 text-[11px] font-medium">Alpha</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-slate-200 inline-block"></span>
                    <span class="text-slate-600 text-[11px] font-medium">Libur / Off</span>
                </div>
            </div>
        </div>

        {{-- Responsive Grid 12 Months Heatmap --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-4 pt-1">
            @foreach($heatmapMonths as $mIdx => $mInfo)
            <div class="bg-slate-50/70 rounded-xl p-3 sm:p-3.5 border border-slate-200/70 flex flex-col justify-between hover:border-slate-300 transition">
                <div class="flex items-center justify-between mb-2 pb-1.5 border-b border-slate-200/60">
                    <span class="text-xs font-bold text-slate-700">{{ $mInfo['nama_bulan'] }}</span>
                    <a href="{{ route('absensi.show', ['absensi' => $karyawan->ID_karyawan, 'bulan' => str_pad($mIdx, 2, '0', STR_PAD_LEFT), 'tahun' => $filterTahun]) }}" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold underline">
                        Detail
                    </a>
                </div>

                {{-- Day Squares Grid --}}
                <div class="grid grid-cols-7 gap-1 text-center">
                    @foreach(['M', 'S', 'S', 'R', 'K', 'J', 'S'] as $hIndex => $hLabel)
                        <span class="text-[9px] font-bold text-slate-400 mb-0.5">{{ $hLabel }}</span>
                    @endforeach

                    @php
                        // First day offset (0 = Sunday, 1 = Monday ...)
                        $firstDayOfWeek = \Carbon\Carbon::createFromDate($filterTahun, $mIdx, 1)->dayOfWeek;
                    @endphp

                    @for($i = 0; $i < $firstDayOfWeek; $i++)
                        <span class="w-full aspect-square rounded"></span>
                    @endfor

                    @foreach($mInfo['days'] as $dayItem)
                        @php
                            $bgClass = match($dayItem['status']) {
                                'Hadir'         => 'bg-emerald-500 text-white',
                                'Telat'         => 'bg-amber-400 text-slate-900',
                                'Setengah Hari' => 'bg-orange-500 text-white',
                                'Izin', 'Sakit' => 'bg-blue-500 text-white',
                                'Alpha'         => 'bg-red-500 text-white',
                                'Libur'         => 'bg-slate-200 text-slate-400 opacity-60',
                                'Masa Depan'    => 'bg-slate-100 text-slate-300 opacity-40',
                                default         => 'bg-slate-200 text-slate-500',
                            };
                        @endphp
                        <div class="w-full aspect-square min-h-[22px] max-h-[28px] text-[10px] font-semibold rounded-md flex items-center justify-center transition-all duration-150 hover:scale-125 hover:z-20 shadow-2xs cursor-pointer select-none {{ $bgClass }}"
                             title="{{ $dayItem['tooltip'] }}">
                            {{ $dayItem['day'] }}
                        </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- SECTION 2: TABEL REKAPITULASI 12 BULAN --}}
    <div class="table-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="table-card-header p-4 sm:p-5 bg-slate-50/50 border-b border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div>
                <h2 class="table-card-title text-base font-bold text-slate-800">Rekapitulasi Kehadiran Bulanan (Tahun {{ $filterTahun }})</h2>
                <p class="text-xs text-slate-500 mt-0.5">Rincian jumlah statistik per bulan dari Januari hingga Desember</p>
            </div>
        </div>

        <x-responsive-table id="rekap-tahunan-table">
            <table class="data-table w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <th width="50" class="text-center p-3">NO</th>
                        <th class="p-3">BULAN</th>
                        <th class="p-3 text-center">HADIR</th>
                        <th class="p-3 text-center">TELAT</th>
                        <th class="p-3 text-center">1/2 HARI</th>
                        <th class="p-3 text-center">IZIN / SAKIT</th>
                        <th class="p-3 text-center">ALPHA</th>
                        <th class="p-3 text-center">TOTAL LOG</th>
                        <th width="120" class="text-center p-3">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($monthlySummary as $mNum => $mSummary)
                    @php
                        $isCurrentMonth = intval(date('Y')) === intval($filterTahun) && intval(date('m')) === $mNum;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition {{ $isCurrentMonth ? 'bg-indigo-50/40 border-l-4 border-l-indigo-600' : '' }} {{ $mSummary['is_future'] ? 'opacity-50 bg-slate-50/30' : '' }}">
                        <td class="text-center text-slate-400 font-mono p-3">{{ $mNum }}</td>
                        <td class="font-bold text-slate-700 p-3">
                            {{ $mSummary['nama_bulan'] }}
                            @if($isCurrentMonth)
                                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded-full ml-1.5">Bulan Ini</span>
                            @elseif($mSummary['is_future'])
                                <span class="text-[10px] text-slate-400 font-normal italic ml-1">(Belum Terjadi)</span>
                            @endif
                        </td>
                        <td class="text-center p-3">
                            @if($mSummary['count_hadir'] === '-')
                                <span class="text-slate-400">-</span>
                            @else
                                <span class="badge-absensi badge-hadir font-bold">{{ $mSummary['count_hadir'] }}</span>
                            @endif
                        </td>
                        <td class="text-center p-3">
                            @if($mSummary['count_telat'] === '-')
                                <span class="text-slate-400">-</span>
                            @else
                                <span class="badge-absensi badge-kode font-semibold" style="background:#fef3c7; color:#b45309; border-color:#fde68a;">{{ $mSummary['count_telat'] }}</span>
                            @endif
                        </td>
                        <td class="text-center p-3">
                            @if($mSummary['count_setengah'] === '-')
                                <span class="text-slate-400">-</span>
                            @else
                                <span class="badge-absensi badge-absensi-setengah font-semibold">{{ $mSummary['count_setengah'] }}</span>
                            @endif
                        </td>
                        <td class="text-center p-3">
                            @if($mSummary['count_izin'] === '-')
                                <span class="text-slate-400">-</span>
                            @else
                                <span class="badge-absensi badge-izin font-semibold">{{ $mSummary['count_izin'] }}</span>
                            @endif
                        </td>
                        <td class="text-center p-3">
                            @if($mSummary['count_alpha'] === '-')
                                <span class="text-slate-400">-</span>
                            @else
                                <span class="badge-absensi badge-alpha font-semibold">{{ $mSummary['count_alpha'] }}</span>
                            @endif
                        </td>
                        <td class="text-center font-bold text-slate-700 p-3">
                            {{ $mSummary['total_log'] }}
                        </td>
                        <td class="text-center p-3">
                            @if(!$mSummary['is_future'])
                            <a href="{{ route('absensi.show', ['absensi' => $karyawan->ID_karyawan, 'bulan' => str_pad($mNum, 2, '0', STR_PAD_LEFT), 'tahun' => $filterTahun]) }}" class="btn-action btn-action-edit inline-flex items-center gap-1 text-[11px]" title="Lihat Rincian Harian Bulan {{ $mSummary['nama_bulan'] }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Rincian
                            </a>
                            @else
                            <span class="text-slate-300 text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </x-responsive-table>
    </div>

</div>
@endsection
