<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Gangguan Pelanggan - {{ $tiket->no_tiket }}</title>
    <style>
        @page {
            margin-top: 68px;
            margin-bottom: 56px;
            margin-left: 24px;
            margin-right: 24px;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 8.5pt;
            line-height: 1.4;
            position: relative;
        }

        /* ── KOP SURAT RESMI PT. MEDIA SOLUSI NETWORK ── */
        .msn-letterhead-header {
            position: fixed;
            top: -68px;
            left: -24px;
            right: -24px;
            height: 60px;
            z-index: 1000;
        }

        /* ── WATERMARK RESMI MSN TENGAH HALAMAN ── */
        .msn-watermark {
            position: fixed;
            top: 25%;
            left: 15%;
            width: 70%;
            height: 50%;
            text-align: center;
            z-index: -1000;
            opacity: 0.045;
        }

        /* ── FOOTER RESMI PT. MEDIA SOLUSI NETWORK ── */
        .msn-letterhead-footer {
            position: fixed;
            bottom: -56px;
            left: -24px;
            right: -24px;
            height: 48px;
            z-index: 1000;
        }

        /* ── DOKUMEN HEADER & BADGE ── */
        .doc-header-table {
            width: 100%;
            border-bottom: 2px solid #0c2033;
            padding-bottom: 5px;
            margin-bottom: 7px;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: 800;
            color: #0c2033;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .doc-sub {
            font-size: 7.2pt;
            font-weight: 700;
            color: #0284c7;
            letter-spacing: 0.4px;
            margin-top: 1px;
            text-transform: uppercase;
        }
        .doc-no-badge {
            display: inline-block;
            font-size: 8.5pt;
            font-family: monospace;
            color: #0369a1;
            font-weight: 700;
            text-align: right;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .section-title {
            font-size: 8.5pt;
            font-weight: 700;
            color: #ffffff;
            background-color: #0c2033;
            padding: 3.5px 8px;
            margin-top: 8px;
            margin-bottom: 4px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .info-grid td {
            padding: 2.5px 5px;
            font-size: 8pt;
            vertical-align: top;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-label {
            font-weight: 700;
            color: #475569;
            width: 28%;
        }
        .info-colon { width: 2%; text-align: center; color: #94a3b8; }
        .info-val { width: 70%; color: #0f172a; }

        .status-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #00bcd4;
            padding: 5px 8px;
            margin-top: 2px;
            margin-bottom: 5px;
            border-radius: 0 4px 4px 0;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7pt;
            font-weight: 700;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-danger  { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-primary { background-color: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
        .badge-gray    { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        .doc-gallery-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 5px;
        }
        .doc-gallery-table td {
            width: 33.33%;
            padding: 3px;
            vertical-align: top;
            text-align: center;
        }
        .doc-img-card {
            border: 1px solid #cbd5e1;
            padding: 3px;
            background: #f8fafc;
            border-radius: 3px;
        }
        .doc-img {
            max-width: 100%;
            max-height: 85px;
            height: auto;
            border-radius: 2px;
            display: block;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
        }
        .doc-caption {
            font-size: 6.8pt;
            color: #334155;
            margin-top: 2px;
            line-height: 1.2;
        }

        /* ── DOCKING SIGNATURE ── */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 4px 14px;
        }
        .signature-role {
            color: #64748b;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .signature-company {
            font-weight: 800;
            font-size: 8.8pt;
            color: #0c2033;
            margin-top: 1px;
            letter-spacing: 0.2px;
        }
        .sign-space {
            height: 48px;
        }
        .signature-name {
            font-weight: 800;
            font-size: 8.5pt;
            color: #0f172a;
            border-bottom: 1px solid #94a3b8;
            display: inline-block;
            min-width: 180px;
            padding-bottom: 2px;
        }
        .signature-sub {
            font-size: 7.2pt;
            color: #64748b;
            margin-top: 3px;
        }
    </style>
</head>
<body>

    <!-- ── 1. HEADER KOP SURAT RESMI PT MEDIA SOLUSI NETWORK ── -->
    <div class="msn-letterhead-header">
        <!-- Top Accent Strip -->
        <table style="width: 100%; border-collapse: collapse; height: 3px; font-size: 1px; line-height: 1px;">
            <tr>
                <td style="background: #00d2ff; width: 35%; height: 3px;"></td>
                <td style="background: #0284c7; width: 25%; height: 3px;"></td>
                <td style="background: #0c2033; width: 40%; height: 3px;"></td>
            </tr>
        </table>
        <!-- Main Kop Surat Table -->
        <table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #00bcd4; background: #ffffff;">
            <tr>
                <!-- Sisi Kiri: Logo Resmi PT Media Solusi Network -->
                <td style="width: 50%; vertical-align: middle; text-align: left; padding-left: 24px; padding-top: 4px; padding-bottom: 5px;">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" alt="MSN Logo" style="height: 42px; width: auto; display: block;">
                    @else
                        <div style="font-size: 12pt; font-weight: 800; color: #0c2033;">
                            <span style="color: #00bcd4;">PT. MEDIA</span> SOLUSI NETWORK
                        </div>
                    @endif
                </td>
                <!-- Sisi Kanan: Corporate NOC Badge & Tagline -->
                <td style="width: 50%; vertical-align: middle; text-align: right; padding-right: 24px; padding-top: 4px; padding-bottom: 5px;">
                    <div style="font-size: 8.5pt; font-weight: 800; color: #0c2033; letter-spacing: 0.5px; text-transform: uppercase;">
                        NETWORK OPERATION CENTER
                    </div>
                    <div style="font-size: 6.8pt; font-weight: 700; color: #0284c7; letter-spacing: 0.6px; margin-top: 1.5px; text-transform: uppercase;">
                        24/7 SUPPORT &bull; FIBER OPTIC INFRASTRUCTURE
                    </div>
                    <div style="font-size: 6pt; color: #64748b; margin-top: 2px; letter-spacing: 0.3px;">
                        OFFICIAL INCIDENT REPORT &bull; PT MEDIA SOLUSI NETWORK
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ── 2. WATERMARK TENGAH HALAMAN ── -->
    <div class="msn-watermark">
        <svg width="340" height="340" viewBox="0 0 100 100">
            <line x1="25" y1="30" x2="75" y2="30" stroke="#0284c7" stroke-width="7"/>
            <line x1="25" y1="30" x2="50" y2="75" stroke="#0284c7" stroke-width="7"/>
            <line x1="75" y1="30" x2="50" y2="75" stroke="#0284c7" stroke-width="7"/>
            <circle cx="25" cy="30" r="16" fill="#0284c7"/>
            <circle cx="75" cy="30" r="16" fill="#0284c7"/>
            <circle cx="50" cy="75" r="18" fill="#0284c7"/>
        </svg>
    </div>

    <!-- ── 3. FOOTER BAR RESMI PT. MEDIA SOLUSI NETWORK ── -->
    <div class="msn-letterhead-footer">
        <table style="width: 100%; border-collapse: collapse; background: #0c2033; border-top: 2.5px solid #00d2ff; color: #ffffff; padding: 4px 12px; font-size: 6.2pt;">
            <tr>
                <td style="width: 32%; vertical-align: middle; padding: 2px 6px; border-right: 1px solid rgba(255,255,255,0.12);">
                    <div style="color: #00d2ff; font-weight: 800; font-size: 6.8pt; margin-bottom: 1px; letter-spacing: 0.3px;">OFFICE</div>
                    <div style="color: #cbd5e1; line-height: 1.25;">
                        Jl. Raya Leuwigajah No. 223 Kel. Utama, Kec. Cimahi Selatan, Kota Cimahi, 40533
                    </div>
                </td>
                <td style="width: 33%; vertical-align: middle; padding: 2px 6px; border-right: 1px solid rgba(255,255,255,0.12);">
                    <div style="color: #00d2ff; font-weight: 800; font-size: 6.8pt; margin-bottom: 1px; letter-spacing: 0.3px;">OPERATIONAL</div>
                    <div style="color: #cbd5e1; line-height: 1.25;">
                        Jl. Reog No 18, Kel. Turangga, Kec. Lengkong, Kota Bandung, 40264
                    </div>
                </td>
                <td style="width: 18%; vertical-align: middle; padding: 2px 6px; text-align: center; border-right: 1px solid rgba(255,255,255,0.12);">
                    <div style="color: #ffffff; font-weight: 700; font-size: 6.8pt; line-height: 1.3;">
                        0896-9662-9955<br>
                        (022)-7303384
                    </div>
                </td>
                <td style="width: 17%; vertical-align: middle; padding: 2px 6px; text-align: right;">
                    <div style="color: #00d2ff; font-weight: 700; font-size: 6.8pt;">ptmsn.co.id</div>
                    <div style="color: #cbd5e1; font-size: 6.2pt; margin-top: 1px;">info@ptmsn.co.id</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ── 4. KONTEN DOKUMEN ── -->
    <table class="doc-header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="doc-title">BERITA ACARA PENANGANAN GANGGUAN</div>
                <div class="doc-sub">INCIDENT REPORT &bull; CUSTOMER COPY</div>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="doc-no-badge">{{ $tiket->no_tiket }}</div>
            </td>
        </tr>
    </table>

    <div class="status-box">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 70%;">
                    <div style="font-size: 7.5pt; color: #64748b; font-weight: bold; text-transform: uppercase;">Status Penyelesaian Insiden:</div>
                    <div style="font-size: 10.5pt; font-weight: bold; color: #0d9488; margin-top: 1px;">
                        @if($tiket->status === 'CLOSE')
                            ✓ SELESAI &bull; LAYANAN NORMAL KEMBALI
                        @else
                            SELESAI DITANGANI (TAHAP CLOSING)
                        @endif
                    </div>
                </td>
                <td style="width: 30%; text-align: right;">
                    <div style="font-size: 7.5pt; color: #64748b; font-weight: bold;">Kepatuhan SLA:</div>
                    <div style="margin-top: 1px;">
                        @if($tiket->sla_status === 'TEPAT')
                            <span class="badge badge-success">MEMENUHI SLA</span>
                        @elseif($tiket->sla_status === 'LEBIH')
                            <span class="badge badge-danger">MELEBIHI SLA</span>
                        @else
                            <span class="badge badge-primary">SESUAI TARGET</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ── 1. INFORMASI LAYANAN & GANGGUAN ── -->
    <div class="section-title">1. Informasi Gangguan &amp; Layanan</div>
    <table class="info-grid">
        <tr>
            <td class="info-label">Nomor Tiket / Referensi</td>
            <td class="info-colon">:</td>
            <td class="info-val"><strong style="font-family: monospace; font-size: 8.5pt;">{{ $tiket->no_tiket }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">Pelanggan / Segmen Layanan</td>
            <td class="info-colon">:</td>
            <td class="info-val">
                <strong>{{ $tiket->customer_name ?: ($tiket->backbone_segment ?: 'Layanan Fiber Optik PT MSN') }}</strong>
                @if($tiket->customer_id)
                    <span style="color: #64748b;">(ID: {{ $tiket->customer_id }})</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Status Dampak Layanan</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $tiket->status_link_impact ?: 'DEGRADED / DOWN' }}</td>
        </tr>
        <tr>
            <td class="info-label">Waktu Mulai Gangguan (Open)</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $tiket->tanggal_open ? $tiket->tanggal_open->translatedFormat('l, d F Y - H:i') . ' WIB' : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Waktu Pemulihan / Selesai (Close)</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $tiket->tanggal_close ? $tiket->tanggal_close->translatedFormat('l, d F Y - H:i') . ' WIB' : ($tiket->updated_at->translatedFormat('l, d F Y - H:i') . ' WIB') }}</td>
        </tr>
        <tr>
            <td class="info-label">Total Durasi Gangguan (Downtime)</td>
            <td class="info-colon">:</td>
            <td class="info-val">
                <strong>{{ $tiket->formatted_mttr }}</strong>
                @if($tiket->net_mttr_minutes)
                    <span style="color: #64748b;">({{ $tiket->net_mttr_minutes }} Menit)</span>
                @endif
            </td>
        </tr>
        @if($tiket->repairPoints && $tiket->repairPoints->count() > 0)
        <tr>
            <td class="info-label">Lokasi / Titik Penanganan</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $tiket->repairPoints->pluck('nama_titik')->join(', ') }}</td>
        </tr>
        @endif
    </table>

    <!-- ── 2. AKAR MASALAH & TINDAKAN PERBAIKAN ── -->
    <div class="section-title">2. Temuan Masalah &amp; Tindakan Perbaikan (Root Cause &amp; Action)</div>
    <table class="info-grid">
        <tr>
            <td class="info-label">Deskripsi / Indikasi Awal</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $tiket->keluhan ?: ($tiket->problem_temuan ?: 'Laporan gangguan transmisi fiber optik pada segmen terkait.') }}</td>
        </tr>
        <tr>
            <td class="info-label">Akar Masalah (Root Cause)</td>
            <td class="info-colon">:</td>
            <td class="info-val" style="color: #b91c1c; font-weight: bold;">
                {{ $tiket->problem_temuan ?: 'Terjadi kendala fisik pada kabel fiber optik di lapangan.' }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Tindakan Perbaikan (Action Taken)</td>
            <td class="info-colon">:</td>
            <td class="info-val" style="white-space: pre-line;">{{ $tiket->action_taken ?: 'Tim teknis PT Media Solusi Network telah melakukan penelusuran lapangan, perbaikan/penyambungan kabel di titik gangguan, pengelasan (splicing) serta pengukuran kembali redaman optik hingga normal.' }}</td>
        </tr>
        @if($tiket->catatan_tambahan)
        <tr>
            <td class="info-label">Catatan Pemeliharaan</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $tiket->catatan_tambahan }}</td>
        </tr>
        @endif
        <tr>
            <td class="info-label">Kondisi Akhir Link</td>
            <td class="info-colon">:</td>
            <td class="info-val" style="color: #15803d; font-weight: bold;">
                ✓ Redaman optik normal ({{ $tiket->redaman_sesudah ? $tiket->redaman_sesudah . ' dBm' : 'Standar Normal' }}), link transmisi aktif dan beroperasi normal (Link UP).
            </td>
        </tr>
    </table>

    <!-- ── 3. DOKUMENTASI FOTO PERBAIKAN LAPANGAN ── -->
    @if($photoDocs && $photoDocs->count() > 0)
    <div class="section-title">3. Dokumentasi Foto Lapangan (Field Evidence)</div>
    @php
        $selectedDocs = $photoDocs->take(3);
    @endphp
    <table class="doc-gallery-table">
        <tr>
            @foreach($selectedDocs as $doc)
            <td>
                <div class="doc-img-card">
                    @if(!empty($doc['base64']))
                        <img src="{{ $doc['base64'] }}" alt="Dokumentasi" class="doc-img">
                    @else
                        <div style="height: 75px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 7.5pt; color: #94a3b8;">
                            Foto Dokumentasi Terlampir
                        </div>
                    @endif
                    <div class="doc-caption">
                        <strong>{{ $doc['category'] ?? 'Dokumentasi' }}</strong><br>
                        {{ $doc['message'] ?: 'Dokumentasi perbaikan lapangan' }}
                    </div>
                </div>
            </td>
            @endforeach
            @for($i = count($selectedDocs); $i < 3; $i++)
            <td style="width: 33.33%;"></td>
            @endfor
        </tr>
    </table>
    @endif

    <!-- ── TANDA TANGAN PENGESAHAN ── -->
    <table class="signature-table">
        <tr>
            <td class="signature-box">
                <div class="signature-role">Pihak Penyedia Jasa</div>
                <div class="signature-company">PT MEDIA SOLUSI NETWORK</div>
                <div class="sign-space"></div>
                <div class="signature-name">( {{ $tiket->assignedLead?->name ?? ($tiket->closer?->name ?? 'Tim Operasional Jaringan') }} )</div>
                <div class="signature-sub">Field Operations &amp; NOC Staff</div>
            </td>
            <td class="signature-box">
                <div class="signature-role">Pihak Pelanggan / Mitra</div>
                <div class="signature-company">{{ $tiket->customer_name ?: 'CUSTOMER / MITRA' }}</div>
                <div class="sign-space"></div>
                <div class="signature-name">( .................................................... )</div>
                <div class="signature-sub">Perwakilan / Penanggung Jawab Pelanggan</div>
            </td>
        </tr>
    </table>

</body>
</html>
