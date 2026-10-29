<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $payroll->karyawan->nama_karyawan ?? 'Karyawan' }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Courier', 'Helvetica', 'Arial', monospace;
            font-size: 11px;
            color: #000;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* HEADER */
        .header-container {
            width: 100%;
            margin-bottom: 8px;
        }
        .logo-cell {
            width: 70px;
            vertical-align: middle;
        }
        .logo-img {
            max-width: 60px;
            max-height: 50px;
        }
        .company-cell {
            vertical-align: middle;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .slip-title {
            font-size: 11px;
            font-weight: normal;
            color: #333;
            margin-top: 2px;
        }
        .header-line-thick {
            border-bottom: 2.5px solid #000;
            margin-bottom: 12px;
        }

        /* EMPLOYEE INFO */
        .info-row {
            width: 100%;
            margin-bottom: 6px;
        }
        .info-left {
            float: left;
            width: 55%;
            font-weight: bold;
            font-size: 12px;
        }
        .info-right {
            float: right;
            width: 40%;
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }
        .clear { clear: both; }
        .info-line-thin {
            border-bottom: 1px solid #000;
            margin-bottom: 14px;
        }

        /* SALARY & COMPONENT ITEMS */
        .section-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .section-table td {
            padding: 3px 0;
            font-size: 11px;
            vertical-align: top;
        }
        .col-label { width: 140px; }
        .col-colon { width: 15px; text-align: center; }
        .col-calc { width: 220px; }
        .col-eq { width: 25px; text-align: center; }
        .col-val { text-align: right; font-weight: bold; }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 10px;
            margin-bottom: 4px;
            font-size: 11.5px;
        }

        .indent-item {
            padding-left: 15px !important;
        }

        /* TOTAL SECTION */
        .line-divider {
            border-top: 1px solid #000;
            margin: 6px 0;
        }
        .line-double {
            border-top: 2px solid #000;
            margin: 8px 0 4px;
        }

        .subtotal-text {
            font-weight: bold;
            font-size: 12px;
            text-decoration: underline;
        }

        /* KASBON & BPJS */
        .box-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 12px;
            margin-bottom: 4px;
            font-size: 11.5px;
        }

        /* HIGHLIGHT TOTAL BOX */
        .thp-box {
            background-color: #e2e8f0;
            border: 1.5px solid #000;
            padding: 10px 14px;
            margin-top: 18px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
            text-align: left;
        }
        .thp-amount {
            float: right;
            font-size: 15px;
        }

        /* FOOTER SIGNATURES */
        .sig-container {
            width: 100%;
            margin-top: 30px;
        }
        .sig-box {
            width: 40%;
            float: left;
            text-align: center;
        }
        .sig-box-right {
            width: 40%;
            float: right;
            text-align: center;
        }
        .sig-space {
            height: 50px;
        }
        .sig-name {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 4px;
            display: inline-block;
            min-width: 150px;
        }
    </style>
</head>
<body>

    {{-- 1. HEADER --}}
    <table class="header-container">
        <tr>
            @php
                if (!$logoBase64 && $payroll->karyawan->id_instansi && file_exists(public_path('images/logo.png'))) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo.png')));
                }
            @endphp
            @if($logoBase64)
            <td class="logo-cell">
                <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
            </td>
            @endif
            <td class="company-cell">
                <div class="company-name">
                    @if($payroll->karyawan->id_instansi && $payroll->karyawan->instansi)
                        {{ strtoupper($payroll->karyawan->instansi->nama_instansi) }}
                    @else
                        NAMA INSTANSI
                    @endif
                </div>
                <div class="slip-title">SLIP GAJI KARYAWAN</div>
            </td>
        </tr>
    </table>
    <div class="header-line-thick"></div>

    {{-- 2. INFORMASI KARYAWAN --}}
    <div class="info-row">
        <div class="info-left">Nama : {{ $payroll->karyawan->nama_karyawan ?? '-' }}</div>
        <div class="info-right">Tanggal : {{ $tglCetak }}</div>
        <div class="clear"></div>
    </div>
    <div class="info-line-thin"></div>

    {{-- 3. RINCIAN GAJI POKOK & KOMPONEN --}}
    <table class="section-table">
        <tr>
            <td class="col-label">Gaji Pokok</td>
            <td class="col-colon">:</td>
            <td class="col-calc">{{ number_format($totalJamKerja, $totalJamKerja == floor($totalJamKerja) ? 0 : 2, ',', '.') }} jam x Rp. {{ number_format($gajiPokokPerJam, 0, ',', '.') }}/jam</td>
            <td class="col-eq">=</td>
            <td class="col-val">Rp. {{ number_format($hasilHarian, 0, ',', '.') }}</td>
        </tr>
        <!-- <tr>
            <td class="col-label" style="color:#555; font-size:10px;"></td>
            <td class="col-colon"></td>
            <td class="col-calc" style="color:#555; font-size:10px; font-style:italic;">Tarif: Rp. {{ number_format($gajiPokokPerJam, 0, ',', '.') }}/jam</td>
            <td colspan="2"></td>
        </tr> -->
        <tr>
            <td class="col-label">Uang Makan</td>
            <td class="col-colon">:</td>
            <td class="col-calc">{{ (float)$totalMasuk }} x Rp. {{ number_format($uangMakanPerHari, 0, ',', '.') }}</td>
            <td class="col-eq">=</td>
            <td class="col-val">Rp. {{ number_format($hasilUangMakan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="col-label">Mingguan</td>
            <td class="col-colon">:</td>
            <td class="col-calc">{{ $jumlahMingguPenuh }} x Rp. {{ number_format($nominalMingguan, 0, ',', '.') }}</td>
            <td class="col-eq">=</td>
            <td class="col-val">Rp. {{ number_format($hasilMingguan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="col-label">Bulanan</td>
            <td class="col-colon">:</td>
            <td class="col-calc">1 x Rp. {{ number_format($nominalBulanan, 0, ',', '.') }}</td>
            <td class="col-eq">=</td>
            <td class="col-val">Rp. {{ number_format($hasilBulanan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="col-label">Kerajinan</td>
            <td class="col-colon">:</td>
            <td class="col-calc">
                @if($jumlahMingguRajin > 0 && $nominalKerajinan > 0)
                    {{ $jumlahMingguRajin }} x Rp. {{ number_format($nominalKerajinan, 0, ',', '.') }}
                @else
                    -
                @endif
            </td>
            <td class="col-eq">=</td>
            <td class="col-val">
                @if($jumlahMingguRajin > 0 && $nominalKerajinan > 0)
                    Rp. {{ number_format($hasilKerajinan, 0, ',', '.') }}
                @else
                    -
                @endif
            </td>
        </tr>

        @if($payroll->karyawan->jabatan && $hasilTunjanganJabatan > 0)
        <tr>
            <td class="col-label">Jabatan</td>
            <td class="col-colon">:</td>
            <td class="col-calc" colspan="3"><strong>{{ $payroll->karyawan->jabatan->nama_jabatan }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">Tunjangan Jabatan</td>
            <td class="col-colon">:</td>
            <td class="col-calc">Rp. {{ number_format($nominalTunjanganJabatan, 0, ',', '.') }}</td>
            <td class="col-eq">=</td>
            <td class="col-val">Rp. {{ number_format($hasilTunjanganJabatan, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    {{-- 4. LEMBUR --}}
    @if($kejadianHarian > 0 || $kejadianLibur > 0)
    <div class="section-title">Lembur :</div>
    <table class="section-table">
        {{-- ──── Lembur Harian ──── --}}
        @if($kejadianHarian > 0)
        <tr>
            <td class="col-label indent-item" colspan="5"><strong>Lembur Harian ({{ $kejadianHarian }} Hari) :</strong></td>
        </tr>
        <tr>
            <td class="col-label indent-item">- per jam</td>
            <td class="col-colon">:</td>
            <td class="col-calc">{{ number_format($totalJamHarian, $totalJamHarian == floor($totalJamHarian) ? 0 : 1, ',', '.') }} jam x Rp. {{ number_format($tarifJamHarian, 0, ',', '.') }}</td>
            <td class="col-eq">=</td>
            <td class="col-val">Rp. {{ number_format($hasilJamHarian, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="col-label indent-item">- Uang Makan</td>
            <td class="col-colon">:</td>
            <td class="col-calc">{{ $kejadianHarian }} x Rp. {{ number_format($tarifMakanLemburHarian, 0, ',', '.') }}</td>
            <td class="col-eq">=</td>
            <td class="col-val">Rp. {{ number_format($hasilMakanHarian, 0, ',', '.') }}</td>
        </tr>
        @endif

        {{-- ──── Lembur Hari Libur ──── --}}
        @if($kejadianLibur > 0)
        @if($kejadianHarian > 0)
        <tr><td colspan="5" style="padding:2px 0;"></td></tr>
        @endif
        <tr>
            <td class="col-label indent-item" colspan="5"><strong>Lembur Hari Libur ({{ $kejadianLibur }} Hari) :</strong></td>
        </tr>
        <tr>
            <td class="col-label indent-item">- Gaji Pokok (2x)</td>
            <td class="col-colon">:</td>
            <td class="col-calc">{{ $kejadianLibur }} x Rp. {{ number_format($hasilLiburPerKejadian, 0, ',', '.') }}</td>
            <td class="col-eq">=</td>
            <td class="col-val">Rp. {{ number_format($hasilLiburTotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="col-label indent-item">- Uang Makan (2x)</td>
            <td class="col-colon">:</td>
            <td class="col-calc">{{ $kejadianLibur }} x Rp. {{ number_format($tarifMakanLiburPerKejadian, 0, ',', '.') }}</td>
            <td class="col-eq">=</td>
            <td class="col-val">Rp. {{ number_format($hasilMakanLiburTotal, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>
    @endif

    <div class="line-divider"></div>
    <table class="section-table">
        <tr>
            <td class="subtotal-text" style="width:70%;">Total :</td>
            <td class="col-val subtotal-text">Rp. {{ number_format($subtotalGajiSampaiLembur, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- 5. POTONGAN BPJS --}}
    <div class="box-title">Potongan BPJS</div>
    <table class="section-table">
        <tr>
            <td style="width:220px;">- Dibayar oleh kantor</td>
            <td class="col-colon">:</td>
            <td class="col-val">Rp. {{ number_format($bpjsKantor, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="width:220px;">- Dibayar oleh Karyawan</td>
            <td class="col-colon">:</td>
            <td class="col-val">Rp. {{ number_format($bpjsKaryawan, 0, ',', '.') }}</td>
        </tr>
    </table>
    <div class="line-divider"></div>
    <table class="section-table">
        <tr>
            <td style="width:220px; font-weight:bold;">Total Iuran BPJS :</td>
            <td class="col-colon">:</td>
            <td class="col-val" style="font-weight:bold;">Rp. {{ number_format($totalBpjs, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- 6. KASBON --}}
    <div class="box-title">Kasbon</div>
    <table class="section-table">
        <tr>
            <td style="width:220px;">- Total Kasbon Sisa</td>
            <td class="col-colon">:</td>
            <td class="col-val">Rp. {{ number_format($totalKasbonSisa, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="width:220px;">- Angsuran ke- {{ $angsuranKe }}</td>
            <td class="col-colon">:</td>
            <td class="col-val">Rp. {{ number_format($subtotalKasbonBulanIni, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- 6b. POTONGAN MANUAL / SANKSI / LAIN-LAIN --}}
    @php
        $manualDeductions = $payroll->details
            ->where('jenis', 'manual')
            ->filter(fn($d) => floatval($d->subtotal) < 0);
    @endphp
    @if($manualDeductions->count() > 0)
    <div class="box-title">Potongan Lain-lain</div>
    <table class="section-table">
        @foreach($manualDeductions as $md)
        <tr>
            <td style="width:220px;">- {{ $md->keterangan ?: 'Potongan Manual / Sanksi' }}</td>
            <td class="col-colon">:</td>
            <td class="col-val">Rp. {{ number_format(abs(floatval($md->subtotal)), 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    {{-- 7. TOTAL AKHIR (TOTAL YANG DITERIMA) --}}
    <div class="thp-box">
        Total yang Diterima
        <span class="thp-amount">Rp. {{ number_format($payroll->gaji_bersih ?: $payroll->total_gaji, 0, ',', '.') }}</span>
        <div class="clear"></div>
    </div>

    {{-- FOOTER SIGNATURES --}}
    <div class="sig-container">
        <div class="sig-box">
            <div>Karyawan,</div>
            <div class="sig-space"></div>
            <div class="sig-name">{{ $payroll->karyawan->nama_karyawan ?? 'Karyawan' }}</div>
        </div>
        <div class="sig-box-right">
            <div>Admin / HRD,</div>
            <div class="sig-space"></div>
            <div class="sig-name">( Administrator )</div>
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>
