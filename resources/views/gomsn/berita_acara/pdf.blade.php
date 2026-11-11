<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara Penyelesaian Gangguan — {{ $ticket->no_tiket }}</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; }
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 40px;
            background: #f8fafc;
        }
        .paper {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 48px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .header {
            border-bottom: 2px solid #1e40af;
            padding-bottom: 16px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .title {
            font-size: 18pt;
            font-weight: 800;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 10pt;
            color: #64748b;
            margin-top: 4px;
        }
        .meta-box {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            font-size: 9.5pt;
        }
        .section-title {
            font-size: 11pt;
            font-weight: 700;
            color: #1e40af;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 16px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            font-weight: 600;
        }
        .signatures {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            text-align: center;
            font-size: 9.5pt;
        }
        .sig-box {
            padding: 10px;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .hash-footer {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            font-size: 8pt;
            color: #64748b;
            font-family: monospace;
            word-break: break-all;
        }
        .btn-print {
            background: #1e40af;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    <div class="no-print" style="max-width: 800px; margin: 0 auto 20px auto; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('tickets.show', $ticket->id) }}" style="text-decoration: none; color: #1e40af; font-size: 10pt; font-weight: 600;">
            &larr; Kembali ke Detail Tiket
        </a>
        <button onclick="window.print()" class="btn-print">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="paper">
        <div class="header">
            <div>
                <h1 class="title">Berita Acara Penyelesaian Gangguan</h1>
                <div class="subtitle">PT MEDIA SOLUSI NETWORK &bull; DIVISI OPERASIONAL JARINGAN</div>
            </div>
            <div style="text-align: right; font-size: 9pt;">
                <strong>No. Tiket:</strong> {{ $ticket->no_tiket }}<br>
                <strong>Status:</strong> {{ $ticket->status }}
            </div>
        </div>

        <div class="meta-box">
            <div>
                <strong>Customer / Segmen:</strong> {{ $ticket->customer_name }} ({{ $ticket->customer_id_ims ?? '-' }})<br>
                <strong>Tipe Gangguan:</strong> {{ $ticket->tipe_tiket === 'BDG' ? 'Backbone Degradasi / Putus' : 'Broadband Last-Mile' }}<br>
                <strong>Layanan Terkait:</strong> {{ $ticket->service_type ?? 'FTTH / Metro-E' }}
            </div>
            <div>
                <strong>Waktu Open:</strong> {{ $ticket->tanggal_open?->format('d/m/Y H:i') }} WIB<br>
                <strong>Waktu Selesai (Closed):</strong> {{ $ticket->tanggal_close?->format('d/m/Y H:i') }} WIB<br>
                <strong>MTTR Bersih:</strong> {{ $ticket->net_mttr_minutes }} Menit (SLA: <strong>{{ $ticket->sla_status }}</strong>)
            </div>
        </div>

        <div class="section-title">1. Uraian &amp; Analisis Akar Masalah (Root Cause)</div>
        <p style="font-size: 9.5pt; margin-bottom: 16px;">
            {{ $ticket->root_cause ?? $ticket->keluhan }}
        </p>

        <div class="section-title">2. Material &amp; Sparepart yang Digunakan</div>
        <table>
            <thead>
                <tr>
                    <th>Nama Material</th>
                    <th style="width: 80px; text-align: center;">Qty</th>
                    <th style="width: 80px;">Satuan</th>
                    <th>Nomor Seri / Batch</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ticket->materials as $m)
                <tr>
                    <td>{{ $m->material->nama_material }}</td>
                    <td style="text-align: center;">{{ $m->quantity }}</td>
                    <td>{{ $m->material->satuan }}</td>
                    <td>{{ $m->serial_number ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #94a3b8;">Tidak ada material tercatat yang digunakan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if ($ticket->repairPoints->isNotEmpty())
        <div class="section-title">3. Lokasi &amp; Titik Perbaikan (Repair Points)</div>
        <table>
            <thead>
                <tr>
                    <th>Nama Titik</th>
                    <th>Koordinat (Lat, Long)</th>
                    <th>Catatan Perbaikan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ticket->repairPoints as $rp)
                <tr>
                    <td>{{ $rp->point_name }}</td>
                    <td>{{ $rp->latitude }}, {{ $rp->longitude }}</td>
                    <td>{{ $rp->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="signatures">
            <div class="sig-box">
                <div>
                    <strong>Teknisi Pelaksana (Lead):</strong><br>
                    <span style="font-size: 8.5pt; color: #64748b;">PT Media Solusi Network</span>
                </div>
                <div style="font-weight: 700; color: #0f172a; margin-top: 50px;">
                    {{ $lead?->name ?? ($ticket->closingAwalUser?->name ?? 'Teknisi Lapangan') }}<br>
                    <span style="font-size: 8pt; font-weight: normal; color: #64748b;">ID: {{ $lead?->employee_code ?? '-' }}</span>
                </div>
            </div>

            <div class="sig-box">
                <div>
                    <strong>Diverifikasi &amp; Disetujui Oleh:</strong><br>
                    <span style="font-size: 8.5pt; color: #64748b;">Helpdesk NOC / Manager</span>
                </div>
                <div style="font-weight: 700; color: #0f172a; margin-top: 50px;">
                    {{ $ticket->verifierUser?->name ?? 'Helpdesk NOC' }}<br>
                    <span style="font-size: 8pt; font-weight: normal; color: #64748b;">ID: {{ $ticket->verifierUser?->employee_code ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="hash-footer">
            Dokumen ini di-generate secara elektronik oleh Portal Karyawan Terpadu PT MSN pada {{ $generated_at }}.<br>
            SHA-256 Verification Digest: {{ $document_hash }}
        </div>
    </div>
</body>
</html>

