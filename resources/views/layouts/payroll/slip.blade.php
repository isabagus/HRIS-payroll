<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payroll->karyawan->nama_karyawan ?? 'Karyawan' }} ({{ $payroll->date ? $payroll->date->format('F Y') : '' }})</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #166534;
            --danger: #dc2626;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-sub: #64748b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            color: var(--text-main);
            padding: 30px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .slip-container {
            width: 100%;
            max-width: 750px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 36px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        .slip-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border);
            margin-bottom: 24px;
        }
        .company-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .company-logo {
            width: 44px;
            height: 44px;
            background: var(--primary);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
        }
        .company-info h2 { font-size: 1.25rem; font-weight: 800; color: #0f172a; }
        .company-info p { font-size: 0.825rem; color: var(--text-sub); }

        .slip-title { text-align: right; }
        .slip-title h1 { font-size: 1.2rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; }
        .slip-title p { font-size: 0.85rem; font-weight: 600; color: var(--text-sub); }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .info-item { display: flex; flex-direction: column; gap: 2px; }
        .info-label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-sub); font-weight: 700; letter-spacing: 0.5px; }
        .info-value { font-size: 0.925rem; font-weight: 600; color: var(--text-main); }

        .breakdown-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }
        .breakdown-box {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
        }
        .breakdown-header {
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-bottom: 1.5px dashed var(--border);
            display: flex;
            justify-content: space-between;
        }
        .header-income { color: var(--success); }
        .header-deduction { color: var(--danger); }

        .breakdown-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            padding: 6px 0;
            color: var(--text-main);
        }
        .breakdown-row .val { font-weight: 600; }

        .breakdown-total {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1.5px solid var(--border);
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .thp-box {
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 14px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        .thp-label { font-size: 1rem; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; }
        .thp-label p { font-size: 0.775rem; font-weight: 500; color: #15803d; text-transform: none; }
        .thp-value { font-size: 1.6rem; font-weight: 800; color: #166534; }

        .signatures {
            display: flex;
            justify-content: space-between;
            padding-top: 20px;
        }
        .sig-col { text-align: center; width: 200px; }
        .sig-col p { font-size: 0.8rem; color: var(--text-sub); margin-bottom: 60px; }
        .sig-col strong { font-size: 0.875rem; font-weight: 700; color: var(--text-main); border-top: 1px solid var(--border); padding-top: 4px; display: block; }

        .no-print-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }
        .btn-print {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 22px; background: var(--primary); color: white;
            border: none; border-radius: 10px; font-weight: 600;
            cursor: pointer; font-size: 0.9rem; transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(79,70,229,0.25);
        }
        .btn-print:hover { background: #4338ca; transform: translateY(-1px); }

        @media print {
            body { background: white; padding: 0; }
            .slip-container { border: none; box-shadow: none; padding: 0; width: 100%; max-width: 100%; }
            .no-print-bar { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
            </svg>
            Cetak / Download PDF
        </button>
    </div>

    <div class="slip-container">

        {{-- Header --}}
        <div class="slip-header">
            <div class="company-brand">
                <div class="company-logo">HP</div>
                <div class="company-info">
                    <h2>{{ ($payroll->karyawan->id_instansi && $payroll->karyawan->instansi) ? $payroll->karyawan->instansi->nama_instansi : 'Nama Instansi' }}</h2>
                    <p>Sistem Penggajian & Managemen Kepegawaian</p>
                </div>
            </div>
            <div class="slip-title">
                <h1>SLIP GAJI</h1>
                <p>PERIODE: {{ $payroll->date ? strtoupper($payroll->date->translatedFormat('F Y')) : strtoupper(date('F Y')) }}</p>
            </div>
        </div>

        {{-- Employee Info Grid --}}
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Nama Karyawan</span>
                <span class="info-value">{{ $payroll->karyawan->nama_karyawan ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">NIK / Kode Karyawan</span>
                <span class="info-value">{{ $payroll->karyawan->Kode_karyawan }} / {{ $payroll->karyawan->nik ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Divisi / Jabatan</span>
                <span class="info-value">{{ $payroll->karyawan->divisi->nama_divisi ?? '-' }} — {{ $payroll->karyawan->jabatan->nama_jabatan ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Rekap Kehadiran</span>
                <span class="info-value">{{ $payroll->total_masuk }} Hari Hadir • {{ $payroll->total_izin }} Izin • {{ $payroll->total_alpha }} Alpha</span>
            </div>
        </div>

        @php
            $tarifPerJam = floatval($payroll->getAttributes()['gaji_pokok'] ?? 0);
            $totalJamKerja = $payroll->total_jam_kerja_efektif;
            $totalGajiHarian = $payroll->gaji_pokok_kalkulasi;
            $totalIncomeSum  = $totalGajiHarian + $payroll->total_lemburan;
        @endphp

        {{-- Breakdown Grid --}}
        <div class="breakdown-grid">
            {{-- Income Box --}}
            <div class="breakdown-box">
                <div class="breakdown-header header-income">
                    <span>PENERIMAAN (INCOME)</span>
                    <span>NOMINAL</span>
                </div>
                <div class="breakdown-row">
                    <span>Gaji Pokok ({{ number_format($totalJamKerja, $totalJamKerja == floor($totalJamKerja) ? 0 : 2, ',', '.') }} Jam × Rp {{ number_format($tarifPerJam, 0, ',', '.') }}/jam)</span>
                    <span class="val">Rp {{ number_format($totalGajiHarian, 0, ',', '.') }}</span>
                </div>

                @if($payroll->total_lemburan > 0)
                <div class="breakdown-row">
                    <span>Uang Lembur ({{ count($lemburDetail) }} Transaksi)</span>
                    <span class="val">Rp {{ number_format($payroll->total_lemburan, 0, ',', '.') }}</span>
                </div>
                @endif

                @foreach($customComponents as $cDet)
                    @if(!str_contains(strtolower($cDet->komponenGaji->nama_gaji ?? ''), 'pokok') && $cDet->nominal_gaji > 0)
                        @php $totalIncomeSum += $cDet->nominal_gaji; @endphp
                        <div class="breakdown-row">
                            <span>{{ $cDet->komponenGaji->nama_gaji }}</span>
                            <span class="val">Rp {{ number_format($cDet->nominal_gaji, 0, ',', '.') }}</span>
                        </div>
                    @endif
                @endforeach

                <div class="breakdown-total header-income">
                    <span>TOTAL PENERIMAAN</span>
                    <span>Rp {{ number_format($totalIncomeSum, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Deductions Box --}}
            <div class="breakdown-box">
                <div class="breakdown-header header-deduction">
                    <span>POTONGAN (DEDUCTIONS)</span>
                    <span>NOMINAL</span>
                </div>
                <div class="breakdown-row">
                    <span>Potongan Kasbon ({{ count($kasbonDetail) }} Transaksi)</span>
                    <span class="val">Rp {{ number_format($payroll->total_kasbon, 0, ',', '.') }}</span>
                </div>
                <div class="breakdown-total header-deduction">
                    <span>TOTAL POTONGAN</span>
                    <span>Rp {{ number_format($payroll->total_kasbon, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- THP Highlight Box --}}
        <div class="thp-box">
            <div class="thp-label">
                TAKE HOME PAY (GAJI BERSIH)
                <p>Total penerimaan dikurangi seluruh potongan</p>
            </div>
            <div class="thp-value">
                Rp {{ number_format($payroll->gaji_bersih, 0, ',', '.') }}
            </div>
        </div>

        {{-- Signatures --}}
        <div class="signatures">
            <div class="sig-col">
                <p>Penerima Gaji,</p>
                <strong>{{ $payroll->karyawan->nama_karyawan ?? 'Karyawan' }}</strong>
            </div>
            <div class="sig-col">
                <p>Manajer HRD / Finance,</p>
                <strong>( Administrator )</strong>
            </div>
        </div>

    </div>

</body>
</html>
