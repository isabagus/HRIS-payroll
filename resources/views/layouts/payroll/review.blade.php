@extends('base')

@section('page-title', 'Review & Konfirmasi Payroll Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('payroll.index') }}">Payroll Karyawan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Review & Konfirmasi</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon" style="background:#eff6ff; color:#2563eb;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Review Kalkulasi Payroll Karyawan</h1>
                <p class="page-subtitle">Periode: <strong>{{ $namaBulanSelected }} {{ $tahun }}</strong> — Periksa dan sesuaikan potongan sebelum disimpan ke database</p>
            </div>
        </div>
        <a href="{{ route('payroll.index') }}" class="btn-back-action">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    {{-- NOTIFIKASI KARYAWAN DI-SKIP (SEBELUMNYA SUDAH DIPROSES) --}}
    @if(count($skippedEmployees) > 0)
    <div class="alert alert-warning" style="background:#fefce8; border:1px solid #fef08a; color:#854d0e; padding:14px 18px; border-radius:12px; margin-bottom:20px; display:flex; align-items:flex-start; gap:12px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:2px;">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <div>
            <strong style="font-size:14px;">Notifikasi Skip Karyawan (Data Sudah Pernah Diproses):</strong>
            <p style="margin:4px 0 6px 0; font-size:12px;">Sebanyak <strong>{{ count($skippedEmployees) }} karyawan</strong> di-skip dari kalkulasi ini untuk mencegah duplikasi gaji pada periode {{ $namaBulanSelected }} {{ $tahun }}:</p>
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
                @foreach($skippedEmployees as $sk)
                    <span class="badge-kode" style="background:#fef08a; color:#713f12; font-size:11px;">{{ $sk->nama_karyawan }} ({{ $sk->Kode_karyawan }})</span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- NOTIFIKASI KARYAWAN BELUM MEMILIKI SETUP GAJI --}}
    @if(count($noRateEmployees) > 0)
    <div class="alert alert-danger" style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:14px 18px; border-radius:12px; margin-bottom:20px; display:flex; align-items:flex-start; gap:12px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:2px;">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div>
            <strong style="font-size:14px;">Karyawan Belum Di-Setup Gaji (Di-skip):</strong>
            <p style="margin:4px 0 6px 0; font-size:12px;">Sebanyak <strong>{{ count($noRateEmployees) }} karyawan</strong> belum dapat dihitung gajinya karena belum memiliki komponen gaji aktif di menu Pengaturan Gaji Karyawan:</p>
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
                @foreach($noRateEmployees as $nr)
                    <span class="badge-kode" style="background:#fee2e2; color:#991b1b; font-size:11px;">{{ $nr->nama_karyawan }} ({{ $nr->Kode_karyawan }})</span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(empty($reviewItems))
    <div class="table-card" style="text-align:center; padding:40px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--text-muted); margin-bottom:12px;">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <h3>Tidak Ada Data Karyawan Baru yang Perlu Diproses</h3>
        <p class="text-muted">Seluruh karyawan aktif untuk periode {{ $namaBulanSelected }} {{ $tahun }} sudah pernah diproses atau belum di-setup komponen gajinya.</p>
        <a href="{{ route('payroll.index') }}" class="btn-primary-action" style="margin-top:15px; display:inline-flex;">Kembali ke Halaman Payroll</a>
    </div>
    @else

    {{-- TABEL REVIEW DAFTAR KARYAWAN --}}
    <form action="{{ route('payroll.store') }}" method="POST" id="form-payroll-save">
        @csrf
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div class="table-card">
            <div class="table-card-header" style="background:#f8fafc; border-bottom:1px solid var(--border);">
                <div>
                    <h2 class="table-card-title" style="font-size:1.1rem; color:#1e293b;">Review Rincian Penggajian ({{ count($reviewItems) }} Karyawan Siap Diproses)</h2>
                    <p class="text-xs text-muted" style="margin-top:2px;">Anda dapat menyesuaikan **Potongan Kasbon**, **Potongan Lainnya**, dan **BPJS (Kantor & Karyawan)** secara manual secara instan.</p>
                </div>
            </div>

            <x-responsive-table id="payroll-review">
                <table class="data-table" id="tabel-review-payroll">
                    <thead>
                        <tr>
                            <th width="40" class="text-center">NO</th>
                            <th width="200">KARYAWAN</th>
                            <th width="100" class="text-center">ABSENSI</th>
                            <th>GAJI POKOK</th>
                            <th>UANG MAKAN</th>
                            <th>MINGGUAN</th>
                            <th>KERAJINAN</th>
                            <th>BULANAN</th>
                            <th>TUNJANGAN JABATAN</th>
                            <th>TOTAL LEMBURAN</th>
                            <th width="140">POTONGAN KASBON</th>
                            <th width="140">POTONGAN LAINNYA</th>
                            <th width="130">BPJS KARYAWAN</th>
                            <th width="130">BPJS KANTOR</th>
                            <th width="160" class="text-center" style="background:#f0fdf4; color:#166534;">TOTAL THP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviewItems as $idx => $item)
                        @php $k = $item['karyawan']; @endphp
                        <tr id="review-row-{{ $idx }}">
                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <div class="data-name-cell">
                                    <div class="data-avatar {{ $k->jenis_kelamin === 'P' ? 'data-avatar-pink' : 'data-avatar-blue' }}">
                                        {{ strtoupper(substr($k->nama_karyawan, 0, 2)) }}
                                    </div>
                                    <div class="data-name-info">
                                        <span class="data-name">{{ $k->nama_karyawan }}</span>
                                        <span class="data-sub">{{ $k->Kode_karyawan }} • {{ $k->divisi->nama_divisi ?? '-' }}</span>
                                    </div>
                                </div>

                                {{-- Hidden fields for form submission --}}
                                <input type="hidden" name="items[{{ $idx }}][ID_karyawan]" value="{{ $item['ID_karyawan'] }}">
                                <input type="hidden" name="items[{{ $idx }}][ID_gaji]" value="{{ $item['ID_gaji'] }}">
                                <input type="hidden" name="items[{{ $idx }}][gaji_pokok_rate]" value="{{ $item['gaji_pokok_rate'] }}">
                                <input type="hidden" name="items[{{ $idx }}][total_masuk]" value="{{ $item['total_masuk'] }}">
                                <input type="hidden" name="items[{{ $idx }}][total_jam_kerja]" value="{{ $item['total_jam_kerja'] }}">
                                <input type="hidden" name="items[{{ $idx }}][total_izin]" value="{{ $item['total_izin'] }}">
                                <input type="hidden" name="items[{{ $idx }}][total_alpha]" value="{{ $item['total_alpha'] }}">
                                <input type="hidden" name="items[{{ $idx }}][total_gaji_pokok_harian]" value="{{ $item['total_gaji_pokok_harian'] }}">
                                <input type="hidden" name="items[{{ $idx }}][total_lemburan]" value="{{ $item['total_lemburan'] }}">
                                <input type="hidden" name="items[{{ $idx }}][total_pendapatan]" value="{{ $item['total_pendapatan'] }}">
                            </td>
                            <td class="text-center">
                                <div style="display:flex; flex-direction:column; gap:2px; font-size:11px;">
                                    <span class="badge-absensi badge-hadir" style="font-weight:700;">{{ (float)$item['total_masuk'] }} Hari ({{ number_format($item['total_jam_kerja'], $item['total_jam_kerja'] == floor($item['total_jam_kerja']) ? 0 : 2, ',', '.') }} Jam)</span>
                                    @if(($item['count_hadir'] ?? 0) > 0) <span class="text-xs text-muted">Hadir: {{ $item['count_hadir'] }}</span> @endif
                                    @if(($item['count_telat'] ?? 0) > 0) <span class="badge-absensi badge-kode" style="background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;">Telat: {{ $item['count_telat'] }}</span> @endif
                                    @if(($item['count_setengah_hari'] ?? 0) > 0) <span class="badge-absensi badge-absensi-setengah">1/2 Hari: {{ $item['count_setengah_hari'] }}</span> @endif
                                    @if($item['total_izin'] > 0) <span class="badge-absensi badge-izin">{{ $item['total_izin'] }} Izin</span> @endif
                                    @if($item['total_alpha'] > 0) <span class="badge-absensi badge-alpha">{{ $item['total_alpha'] }} Alpha</span> @endif
                                </div>
                            </td>
                            <td>
                                <span class="font-semibold">Rp {{ number_format($item['total_gaji_pokok_harian'], 0, ',', '.') }}</span>
                                <div class="text-xs text-muted">{{ number_format($item['total_jam_kerja'], $item['total_jam_kerja'] == floor($item['total_jam_kerja']) ? 0 : 2, ',', '.') }} jam @ Rp {{ number_format($item['gaji_pokok_rate'], 0, ',', '.') }}/jam</div>
                            </td>
                            <td>
                                <span class="font-semibold">Rp {{ number_format($item['total_uang_makan'], 0, ',', '.') }}</span>
                            </td>
                            <td>
                                @if($item['total_mingguan'] > 0)
                                    <span class="font-semibold text-success">+ Rp {{ number_format($item['total_mingguan'], 0, ',', '.') }}</span>
                                @else <span class="text-muted">-</span> @endif
                            </td>
                            <td>
                                @if($item['total_kerajinan'] > 0)
                                    <span class="font-semibold text-success">+ Rp {{ number_format($item['total_kerajinan'], 0, ',', '.') }}</span>
                                @else <span class="text-muted">-</span> @endif
                            </td>
                            <td>
                                @if($item['total_bulanan'] > 0)
                                    <span class="font-semibold text-success">+ Rp {{ number_format($item['total_bulanan'], 0, ',', '.') }}</span>
                                @else <span class="text-muted">-</span> @endif
                            </td>
                            <td>
                                @if($item['total_tunjangan_jabatan'] > 0)
                                    <span class="font-semibold text-success">+ Rp {{ number_format($item['total_tunjangan_jabatan'], 0, ',', '.') }}</span>
                                @else <span class="text-muted">-</span> @endif
                            </td>
                            <td>
                                @if($item['total_lemburan'] > 0)
                                    <span class="font-semibold text-success">+ Rp {{ number_format($item['total_lemburan'], 0, ',', '.') }}</span>
                                @else <span class="text-muted">Rp 0</span> @endif
                            </td>

                            {{-- EDITABLE MANUALLY POTONGAN KASBON INPUT --}}
                            <td>
                                <input type="number" min="0" max="{{ $item['sisa_kasbon_aktif'] }}"
                                       name="items[{{ $idx }}][potongan_kasbon]"
                                       id="kasbon-input-{{ $idx }}"
                                       class="form-input text-right input-kasbon-manual"
                                       value="{{ $item['potongan_kasbon'] }}"
                                       data-idx="{{ $idx }}"
                                       data-sisa="{{ $item['sisa_kasbon_aktif'] }}"
                                       data-pendapatan="{{ $item['total_pendapatan'] }}"
                                       oninput="recalculateReviewRow({{ $idx }})"
                                       placeholder="0"
                                       style="padding:6px 10px; font-weight:600; font-size:12px; color:var(--danger);">
                                <div class="text-xs text-muted" style="margin-top:2px; font-size:10px;">
                                    Sisa Kasbon: <strong style="color:var(--danger);">Rp {{ number_format($item['sisa_kasbon_aktif'], 0, ',', '.') }}</strong>
                                </div>
                                <div id="kasbon-error-{{ $idx }}" class="font-semibold text-danger" style="display:none; margin-top:3px; font-size:10px; line-height:1.2;"></div>
                            </td>

                            {{-- EDITABLE MANUALLY POTONGAN LAINNYA / SANKSI INPUT --}}
                            <td>
                                <input type="number" min="0" step="1000"
                                       name="items[{{ $idx }}][potongan_lain]"
                                       id="lain-input-{{ $idx }}"
                                       class="form-input text-right"
                                       value="{{ $item['potongan_lain'] }}"
                                       data-idx="{{ $idx }}"
                                       oninput="recalculateReviewRow({{ $idx }})"
                                       placeholder="Misal telat"
                                       style="padding:6px 10px; font-weight:600; font-size:12px; color:var(--danger);">
                                <div class="text-xs text-muted" style="margin-top:2px; font-size:9px;">
                                    Terlambat / Sanksi
                                </div>
                            </td>

                            {{-- EDITABLE MANUALLY BPJS KARYAWAN INPUT --}}
                            <td>
                                <input type="number" min="0" step="1000"
                                       name="items[{{ $idx }}][potongan_bpjs]"
                                       id="bpjs-input-{{ $idx }}"
                                       class="form-input text-right"
                                       value="{{ $item['potongan_bpjs'] }}"
                                       data-idx="{{ $idx }}"
                                       oninput="recalculateReviewRow({{ $idx }})"
                                       placeholder="40000"
                                       style="padding:6px 10px; font-weight:600; font-size:12px; color:var(--danger);">
                                <div class="text-xs text-muted" style="margin-top:2px; font-size:9px;">
                                    Potong THP
                                </div>
                            </td>

                            {{-- EDITABLE MANUALLY BPJS KANTOR / PERUSAHAAN INPUT --}}
                            <td>
                                <input type="number" min="0" step="1000"
                                       name="items[{{ $idx }}][bpjs_perusahaan]"
                                       id="bpjs-kantor-input-{{ $idx }}"
                                       class="form-input text-right"
                                       value="{{ $item['bpjs_perusahaan'] ?? 40000 }}"
                                       placeholder="40000"
                                       style="padding:6px 10px; font-weight:600; font-size:12px; color:#1e293b;">
                                <div class="text-xs text-muted" style="margin-top:2px; font-size:9px;">
                                    Beban Perusahaan
                                </div>
                            </td>

                            {{-- TOTAL THP REAL-TIME --}}
                            <td class="text-right" style="background:#f0fdf4;">
                                <span class="font-bold text-success" id="review-thp-span-{{ $idx }}" style="font-size:1rem;">
                                    Rp {{ number_format($item['gaji_bersih'], 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-responsive-table>

            <div class="form-actions" style="padding:20px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <a href="{{ route('payroll.index') }}" class="btn-form-cancel">Batal</a>
                <button type="button" class="btn-primary-action" id="btn-submit-confirm" onclick="confirmPayrollSave()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Konfirmasi & Simpan Semua Payroll
                </button>
            </div>
        </div>
    </form>
    @endif

</div>
@endsection

@push('scripts')
<script>
function recalculateReviewRow(idx) {
    const kasbonInput = document.getElementById('kasbon-input-' + idx);
    const kasbonError = document.getElementById('kasbon-error-' + idx);
    const lainInput   = document.getElementById('lain-input-' + idx);
    const bpjsInput   = document.getElementById('bpjs-input-' + idx);
    const thpSpan     = document.getElementById('review-thp-span-' + idx);

    if (!kasbonInput || !thpSpan) return;

    const pendapatan   = parseFloat(kasbonInput.getAttribute('data-pendapatan')) || 0;
    const sisaKasbon   = parseFloat(kasbonInput.getAttribute('data-sisa')) || 0;
    const bpjsKaryawan = parseFloat(bpjsInput ? bpjsInput.value : 40000) || 0;
    const kasbon       = parseFloat(kasbonInput.value) || 0;
    const lain         = parseFloat(lainInput ? lainInput.value : 0) || 0;

    // Real-time validation for Kasbon input
    if (kasbon > sisaKasbon) {
        kasbonInput.style.borderColor = '#ef4444';
        kasbonInput.style.backgroundColor = '#fef2f2';
        if (kasbonError) {
            kasbonError.textContent = 'Nominal melebihi sisa kasbon tersisa (Rp ' + Math.round(sisaKasbon).toLocaleString('id-ID') + ')';
            kasbonError.style.display = 'block';
        }
    } else {
        kasbonInput.style.borderColor = '';
        kasbonInput.style.backgroundColor = '';
        if (kasbonError) {
            kasbonError.style.display = 'none';
        }
    }

    let thp = pendapatan - (kasbon + bpjsKaryawan + lain);
    if (thp < 0) thp = 0;

    thpSpan.textContent = 'Rp ' + Math.round(thp).toLocaleString('id-ID');
}

function confirmPayrollSave() {
    // Check if any row has invalid kasbon input exceeding remaining kasbon balance
    const kasbonInputs = document.querySelectorAll('.input-kasbon-manual');
    let hasValidationError = false;
    let errorMessage = '';

    kasbonInputs.forEach(input => {
        const val = parseFloat(input.value) || 0;
        const sisa = parseFloat(input.getAttribute('data-sisa')) || 0;
        if (val > sisa) {
            hasValidationError = true;
            errorMessage = `Nominal potongan kasbon (Rp ${val.toLocaleString('id-ID')}) melebihi sisa kasbon yang tersisa (Rp ${sisa.toLocaleString('id-ID')}). Silakan perbaiki terlebih dahulu.`;
        }
    });

    if (hasValidationError) {
        alert(errorMessage);
        return;
    }

    confirmAction({
        title: 'Konfirmasi Simpan Payroll',
        text: 'Apakah Anda yakin ingin memproses & menyimpan seluruh data payroll karyawan periode {{ $namaBulanSelected }} {{ $tahun }}?',
        icon: 'question',
        confirmButtonText: 'Ya, Simpan Payroll',
        confirmButtonColor: '#10b981'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-payroll-save').submit();
        }
    });
}
</script>
@endpush
