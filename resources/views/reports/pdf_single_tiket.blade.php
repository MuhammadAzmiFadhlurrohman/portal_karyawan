<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Gangguan - {{ $tiket->no_tiket }}</title>
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
            margin-top: 7px;
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

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            font-size: 7.8pt;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            text-align: left;
        }

        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            font-size: 6.8pt;
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
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 4px 8px;
        }
        .signature-role {
            color: #64748b;
            font-size: 7.2pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .signature-company {
            font-weight: 800;
            font-size: 8pt;
            color: #0c2033;
            margin-top: 1px;
            letter-spacing: 0.2px;
        }
        .sign-space {
            height: 44px;
        }
        .signature-name {
            font-weight: 800;
            font-size: 8pt;
            color: #0f172a;
            border-bottom: 1px solid #94a3b8;
            display: inline-block;
            min-width: 140px;
            padding-bottom: 2px;
        }
        .signature-sub {
            font-size: 6.8pt;
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
                <div class="doc-title">BERITA ACARA GANGGUAN</div>
                <div class="doc-sub">
                    @if($tiket->kategori_tiket === 'BROADBAND')
                        BROADBAND LAST-MILE &bull; NETWORK OPERATION CENTER
                    @else
                        FIBER OPTIC BACKBONE &bull; NETWORK OPERATION CENTER
                    @endif
                </div>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="doc-no-badge">{{ $tiket->no_tiket }}</div>
            </td>
        </tr>
    </table>

    <!-- ── 1. INFORMASI UMUM & STATUS SLA ── -->
    <div class="section-title">1. Informasi Gangguan &amp; Kepatuhan SLA</div>
    <table class="info-grid">
        <tr>
            <td class="info-label">Status Link Impact</td>
            <td class="info-colon">:</td>
            <td class="info-val"><strong>{{ $tiket->status_link_impact ?: 'DEGRADED / DOWN' }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">{{ $tiket->kategori_tiket === 'BROADBAND' ? 'Customer / Pelanggan' : 'Segment Backbone' }}</td>
            <td class="info-colon">:</td>
            <td class="info-val">
                <strong>{{ $tiket->backbone_segment ?: ($tiket->customer_name ? $tiket->customer_name . ' (' . ($tiket->customer_id ?? '-') . ')' : '-') }}</strong>
            </td>
        </tr>
        <tr>
            <td class="info-label">Deskripsi Insiden</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $tiket->problem_temuan ?: ($tiket->keluhan ?: '-') }}</td>
        </tr>
        <tr>
            <td class="info-label">Waktu Open Tiket</td>
            <td class="info-colon">:</td>
            <td class="info-val">
                {{ $tiket->tanggal_open ? $tiket->tanggal_open->translatedFormat('d F Y H:i') . ' WIB' : '-' }} (Oleh: {{ $tiket->creator?->name ?? 'Helpdesk / NOC' }})
            </td>
        </tr>
        <tr>
            <td class="info-label">Waktu Closing Tiket</td>
            <td class="info-colon">:</td>
            <td class="info-val">
                {{ $tiket->tanggal_close ? $tiket->tanggal_close->translatedFormat('d F Y H:i') . ' WIB' : 'Belum Ditutup' }}
                @if($tiket->closer)
                    (Oleh: {{ $tiket->closer->name }})
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Durasi MTTR</td>
            <td class="info-colon">:</td>
            <td class="info-val">
                <strong>{{ $tiket->formatted_mttr }}</strong> ({{ $tiket->net_mttr_minutes ?? 0 }} Menit)
            </td>
        </tr>
        <tr>
            <td class="info-label">Target SLA &amp; Status</td>
            <td class="info-colon">:</td>
            <td class="info-val">
                Target: {{ $tiket->formatted_sla_target }} |
                Status:
                @if($tiket->sla_status === 'TEPAT')
                    <span class="badge badge-success">TEPAT SLA</span>
                @elseif($tiket->sla_status === 'LEBIH')
                    <span class="badge badge-danger">MELEBIHI SLA</span>
                @else
                    <span class="badge badge-gray">PROSES / N/A</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- ── 2. RESUME PEKERJAAN ── -->
    <div class="section-title">2. Resume Akhir Pekerjaan &amp; Tindakan Perbaikan</div>
    <table class="info-grid">
        <tr>
            <td class="info-label">Tim OM Pelaksana</td>
            <td class="info-colon">:</td>
            <td class="info-val">
                @if(!empty($tiket->team_members) && is_array($tiket->team_members))
                    {{ implode(', ', $tiket->team_members) }}
                @elseif($tiket->assignees && $tiket->assignees->count() > 0)
                    {{ $tiket->assignees->pluck('name')->join(', ') }}
                @else
                    {{ $tiket->assignedLead?->name ?? '-' }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Problem / Temuan Masalah</td>
            <td class="info-colon">:</td>
            <td class="info-val" style="color: #b91c1c; font-weight: bold;">
                {{ $tiket->problem_temuan ?: '-' }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Action / Tindakan Perbaikan</td>
            <td class="info-colon">:</td>
            <td class="info-val" style="white-space: pre-line;">
                {{ $tiket->action_taken ?: '-' }}
            </td>
        </tr>
        @if($tiket->redaman_sebelum !== null || $tiket->redaman_sesudah !== null)
        <tr>
            <td class="info-label">Hasil Redaman Optik</td>
            <td class="info-colon">:</td>
            <td class="info-val">
                Sebelum: <strong>{{ $tiket->redaman_sebelum !== null ? $tiket->redaman_sebelum . ' dBm' : '-' }}</strong> &bull;
                Sesudah: <strong>{{ $tiket->redaman_sesudah !== null ? $tiket->redaman_sesudah . ' dBm' : '-' }}</strong> &bull;
                Link: <span class="badge {{ $tiket->has_link_up ? 'badge-success' : 'badge-gray' }}">{{ $tiket->has_link_up ? 'UP / NORMAL' : 'PENDING' }}</span>
            </td>
        </tr>
        @endif
        @if($tiket->catatan_tambahan)
        <tr>
            <td class="info-label">Catatan Tambahan</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $tiket->catatan_tambahan }}</td>
        </tr>
        @endif
    </table>

    <!-- ── 3. MATERIAL & TITIK TAGGING ── -->
    <div class="section-title">3. Penggunaan Material &amp; Titik Perbaikan</div>
    <table style="width: 100%;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 5px;">
                <strong style="font-size: 8pt; color: #475569;">Material yang Digunakan:</strong>
                <table class="data-table" style="margin-top: 3px;">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th style="text-align: right;">Jumlah</th>
                            <th>Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiket->materials as $m)
                        <tr>
                            <td>{{ $m->material->nama_material ?? ($m->nama_material ?? 'Material') }}</td>
                            <td style="text-align: right; font-family: monospace;">{{ $m->quantity ?? $m->jumlah }}</td>
                            <td>{{ $m->material->satuan ?? ($m->satuan ?? 'pcs') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8;">Tidak ada material khusus</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 5px;">
                <strong style="font-size: 8pt; color: #475569;">Titik Tagging / Joint Closure:</strong>
                <table class="data-table" style="margin-top: 3px;">
                    <thead>
                        <tr>
                            <th>Titik</th>
                            <th>Koordinat GPS</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiket->repairPoints as $tp)
                        <tr>
                            <td><strong>{{ $tp->nama_titik ?: $tp->point_name }}</strong></td>
                            <td style="font-family: monospace; font-size: 7.5pt;">{{ round($tp->latitude, 5) }}, {{ round($tp->longitude, 5) }}</td>
                            <td>{{ $tp->jenis_kerusakan ?: ($tp->keterangan ?: '-') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8;">Tidak ada titik tagging</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- ── 4. PENCATATAN KABEL & JOINT CLOSURE (JC) ── -->
    @if($tiket->jointClosures->count() > 0)
    <div class="section-title">4. Pencatatan Kabel &amp; Sambungan Joint Closure (JC)</div>
    @foreach($tiket->jointClosures as $jc)
    <table class="data-table" style="margin-bottom: 6px;">
        <thead>
            <tr style="background-color: #0f172a; color: #ffffff;">
                <th colspan="4" style="background-color: #1e293b; color: #ffffff; padding: 4px 6px;">
                    <span style="font-size: 8.5pt; font-weight: bold;">{{ $jc->nama_closure }}</span>
                    &nbsp;
                    @if($jc->is_new_cut || $jc->is_aset_baru)
                        <span class="badge badge-success">ASET BARU (NEW CUT)</span>
                    @else
                        <span class="badge badge-gray">EKSISTING</span>
                    @endif
                    &nbsp;&bull;&nbsp;
                    <span style="font-size: 7.5pt; font-weight: normal; color: #94a3b8;">
                        Tipe: {{ $jc->tipe_closure ?: 'DOME' }} | Penempatan: {{ $jc->penempatan_fisik ?: 'POLE' }}
                        @if($jc->repairPoint)
                            | GPS: {{ round($jc->repairPoint->latitude, 5) }}, {{ round($jc->repairPoint->longitude, 5) }}
                        @endif
                    </span>
                </th>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td colspan="4" style="font-size: 7.8pt; padding: 4px 6px; color: #334155;">
                    <strong>Spesifikasi Kapasitas:</strong> {{ $jc->kapasitas_core }} Core
                </td>
            </tr>
            <tr>
                <th style="width: 28%;">Kabel Asal (Tube - Core)</th>
                <th style="width: 28%;">Kabel Jumper (Tube - Core)</th>
                <th style="width: 20%;">Status &amp; Loss</th>
                <th style="width: 24%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jc->cores as $core)
            <tr>
                <td style="font-family: monospace; font-size: 7.8pt;">{{ $core->tube_asal }} &bull; Core {{ $core->core_asal }}</td>
                <td style="font-family: monospace; font-size: 7.8pt;">
                    @if($core->tube_tujuan || $core->core_tujuan)
                        {{ $core->tube_tujuan ?: '-' }} &bull; Core {{ $core->core_tujuan ?: '-' }}
                    @else
                        <span style="color: #94a3b8; font-style: italic;">Dikosongkan (Spare)</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ ($core->status_core === 'TERHUBUNG' || $core->status === 'TERHUBUNG') ? 'badge-success' : 'badge-danger' }}">
                        {{ $core->status_core ?: ($core->status ?: 'TERHUBUNG') }}
                    </span>
                    @if($core->loss_db !== null)
                        <small style="font-family: monospace;">({{ number_format($core->loss_db, 2) }} dB)</small>
                    @endif
                </td>
                <td style="font-size: 7.2pt; color: #475569;">{{ $core->notes ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #94a3b8; font-size: 7.5pt;">Belum ada baris mapping sambungan core tercatat.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endforeach
    @endif

    <!-- ── 5. MANUVER CORE OPTIK ── -->
    @if($tiket->coreManeuvers->count() > 0)
    <div class="section-title">5. Alokasi &amp; Manuver Core Optik</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Titik / Lokasi Manuver</th>
                <th style="width: 25%;">Core Asal ➔ Tujuan</th>
                <th style="width: 50%;">Keterangan &amp; Alasan Manuver</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tiket->coreManeuvers as $mc)
            <tr>
                <td><strong>{{ $mc->segmen }}</strong></td>
                <td style="font-family: monospace; font-size: 7.8pt;">
                    Core {{ $mc->core_asal }} ➔ Core {{ $mc->core_tujuan }}
                </td>
                <td style="font-size: 7.5pt; color: #334155;">
                    {{ $mc->keterangan ?: '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- ── 6. TIMELINE KRONOLOGIS LAPANGAN ── -->
    <div class="section-title">6. Timeline Kronologis Koordinasi Lapangan</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Waktu (WIB)</th>
                <th style="width: 22%;">PIC / Teknis</th>
                <th style="width: 58%;">Informasi &amp; Catatan Lapangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($chronologies as $krono)
            <tr>
                <td style="font-family: monospace; font-size: 7.5pt;">
                    {{ $krono->created_at ? $krono->created_at->format('d/m/Y H:i') : '-' }}
                </td>
                <td>
                    <strong>{{ $krono->user?->name ?? 'User' }}</strong>
                    <br><small style="color: #64748b;">({{ $krono->category ?: 'Teknis' }})</small>
                </td>
                <td style="font-size: 7.5pt;">
                    {{ preg_replace('/^>\s*/m', '', $krono->message) }}
                    @if($krono->latitude && $krono->longitude)
                        <br><small style="color: #0284c7; font-family: monospace;">[GPS: {{ round($krono->latitude, 5) }}, {{ round($krono->longitude, 5) }}]</small>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #94a3b8;">Belum ada update kronologis tercatat.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ── 7. RIWAYAT SERAH TERIMA / HANDOVER SHIFT ── -->
    @if($tiket->handovers->count() > 0)
    <div class="section-title">7. Riwayat Serah Terima / Oper Shift</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">Waktu Handover</th>
                <th style="width: 26%;">Perpindahan Shift</th>
                <th style="width: 26%;">Petugas (From ➔ To)</th>
                <th style="width: 30%;">Catatan Serah Terima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tiket->handovers as $h)
            <tr>
                <td style="font-family: monospace; font-size: 7.5pt;">
                    {{ $h->created_at ? $h->created_at->format('d/m/Y H:i') . ' WIB' : '-' }}
                </td>
                <td>
                    <strong>{{ $h->shift_from ?: 'Shift 1' }}</strong> ➔ <strong>{{ $h->shift_to ?: 'Shift 2' }}</strong>
                </td>
                <td>
                    {{ $h->handoverUser?->name ?? 'Petugas Shift' }} ➔ {{ $h->receiverUser?->name ?? 'PIC Shift Baru' }}
                </td>
                <td style="font-size: 7.5pt;">{{ $h->handover_notes ?: ($h->notes ?: '-') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- ── 8. DOKUMENTASI FOTO LAPANGAN (FIELD EVIDENCE) ── -->
    @if($photoDocs && $photoDocs->count() > 0)
    <div class="section-title">8. Dokumentasi Foto Lapangan (Field Evidence)</div>
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
                <div class="signature-role">Dibuat Oleh (Helpdesk / NOC)</div>
                <div class="signature-company">PT MEDIA SOLUSI NETWORK</div>
                <div class="sign-space"></div>
                <div class="signature-name">( {{ $tiket->creator?->name ?? 'Helpdesk NOC' }} )</div>
                <div class="signature-sub">NOC &amp; Ticketing Staff</div>
            </td>
            <td class="signature-box">
                <div class="signature-role">Tim Pelaksana Lapangan (OM)</div>
                <div class="signature-company">PT MEDIA SOLUSI NETWORK</div>
                <div class="sign-space"></div>
                <div class="signature-name">
                    (
                    @if($tiket->assignedLead)
                        {{ $tiket->assignedLead->name }}
                    @elseif(!empty($tiket->team_members) && is_array($tiket->team_members) && count($tiket->team_members) > 0)
                        {{ $tiket->team_members[0] }}
                    @else
                        {{ $tiket->closer?->name ?? 'Leader Teknis' }}
                    @endif
                    )
                </div>
                <div class="signature-sub">
                    @if($tiket->assignees && $tiket->assignees->count() > 0)
                        Leader &amp; Tim Teknisi ({{ $tiket->assignees->count() }} Orang)
                    @else
                        Teknisi Backbone &amp; Splicer
                    @endif
                </div>
            </td>
            <td class="signature-box">
                <div class="signature-role">Mengetahui (Manager Operasional)</div>
                <div class="signature-company">PT MEDIA SOLUSI NETWORK</div>
                <div class="sign-space"></div>
                <div class="signature-name">( Delli Digital )</div>
                <div class="signature-sub">Business Development / NOC</div>
            </td>
        </tr>
    </table>

</body>
</html>
