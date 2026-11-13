<x-layouts.app title="Dashboard Operasional &amp; Statistik">
    <!-- Hero Welcome Banner (self-contained CSS so it never depends on compiled Tailwind) -->
    @php
        $nameParts = preg_split('/\s+/', trim($user->name), 2);
        $firstName = $nameParts[0] ?? $user->name;
        $restName = $nameParts[1] ?? '';
        $clockInTime = $metrics['attendance_today']['clock_in_time'] ?? null;
        $scopeType = $stats['scope']['type'] ?? 'self';
    @endphp
    <style>
        .hh{position:relative;overflow:hidden;border-radius:24px;margin-bottom:20px;border:1px solid rgba(45,212,191,.22);box-shadow:0 16px 40px -10px rgba(1,20,23,.6),inset 0 1px 0 rgba(94,234,212,.25);background:radial-gradient(100% 140% at 15% 20%,#093d40 0%,#05282b 55%,#021619 100%);color:#fff;user-select:none}
        .hh-bg-svg{position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:1}
        .hh-glow1{position:absolute;left:-70px;top:-70px;width:300px;height:300px;border-radius:50%;background:rgba(20,184,166,.18);filter:blur(65px);pointer-events:none;z-index:1}
        .hh-glow2{position:absolute;right:80px;bottom:-30px;width:320px;height:320px;border-radius:50%;background:rgba(45,212,191,.15);filter:blur(70px);pointer-events:none;z-index:1}
        .hh-inner{position:relative;z-index:2;display:flex;align-items:flex-end;justify-content:space-between;gap:28px;padding:0 40px;min-height:290px}
        .hh-left{min-width:0;flex:1;padding:22px 0;align-self:center}
        .hh-date{display:inline-flex;align-items:center;gap:7px;padding:5px 14px;border-radius:999px;background:rgba(4,26,29,.55);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border:1px solid rgba(45,212,191,.35);font-size:12px;font-weight:600;color:#99f6e4;box-shadow:inset 0 1px 0 rgba(255,255,255,.08)}
        .hh-hello-wrap{display:flex;align-items:center;gap:10px;margin:12px 0 4px}
        .hh-hello{margin:0;font-size:11.5px;font-weight:800;letter-spacing:.26em;text-transform:uppercase;color:#fff}
        .hh-hello-line{width:32px;height:2.5px;border-radius:2px;background:#2dd4bf;box-shadow:0 0 10px rgba(45,212,191,.75)}
        .hh-name{margin:0;font-weight:900;text-transform:uppercase;line-height:1.02;letter-spacing:-.02em;font-size:clamp(32px,3.8vw,48px)}
        .hh-name .a{display:inline;color:#fff}
        .hh-name .b{display:inline;margin-left:6px;color:#2dd4bf;background:linear-gradient(90deg,#5eead4 0%,#2dd4bf 55%,#14b8a6 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;-webkit-box-decoration-break:clone;box-decoration-break:clone;padding-bottom:2px}
        .hh-sub{margin:8px 0 0;font-size:12.5px;font-style:italic;line-height:1.45;color:rgba(204,251,241,.82);max-width:460px}
        .hh-pills{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:14px}
        .hh-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;font-size:11.5px;font-weight:600;background:rgba(2,19,22,.65);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);color:#e2e8f0;border:1px solid rgba(45,212,191,.25)}
        .hh-pill.ok{background:rgba(2,24,27,.75);color:#fff;border-color:rgba(45,212,191,.45);font-weight:700}
        .hh-dot{width:6.5px;height:6.5px;border-radius:50%;background:#10b981;box-shadow:0 0 10px #10b981}
        .hh-cta{display:inline-flex;align-items:center;gap:8px;margin-top:16px;padding:9.5px 20px;border-radius:999px;font-size:12px;font-weight:700;color:#fff;text-decoration:none;background:linear-gradient(135deg,#14b8a6 0%,#0d9488 100%);border:1px solid rgba(94,234,212,.35);box-shadow:0 8px 20px -4px rgba(13,148,136,.6),inset 0 1px 0 rgba(255,255,255,.3);transition:transform .15s ease,filter .15s ease}
        .hh-cta:hover{filter:brightness(1.1);transform:translateY(-1px)}
        .hh-cta svg{transition:transform .15s ease}
        .hh-cta:hover svg{transform:translateX(2px)}
        .hh-mobile-dots{display:none}
        .hh-right{position:relative;flex:none;width:350px;height:290px;margin-right:8px;align-self:flex-end}
        .hh-circle-layer{position:absolute;right:0;bottom:-18px;width:300px;height:300px;border-radius:50%;background:linear-gradient(135deg,#0d9488 0%,#14b8a6 50%,#2dd4bf 100%);opacity:.92;box-shadow:0 0 40px rgba(20,184,166,.35);z-index:1}
        .hh-circle-ring{position:absolute;right:-18px;bottom:-36px;width:336px;height:336px;border-radius:50%;border:1.5px solid rgba(94,234,212,.35);pointer-events:none;z-index:1}
        .hh-arc-overlay{position:absolute;right:-42px;bottom:-60px;width:384px;height:384px;border-radius:50%;border:1px solid rgba(94,234,212,.18);pointer-events:none;z-index:1}
        .hh-flare{position:absolute;left:32px;top:100px;width:5.5px;height:5.5px;border-radius:50%;background:#fff;box-shadow:0 0 14px 5px rgba(94,234,212,.9),0 0 26px 10px rgba(20,184,166,.6);pointer-events:none;z-index:2}
        .hh-photo{position:absolute;left:0;right:0;bottom:-46px;height:310px;z-index:2;display:flex;align-items:flex-end;justify-content:center;pointer-events:none}
        .hh-photo img{width:100%;height:100%;object-fit:contain;object-position:center bottom;display:block;transform:scale(1.02);transform-origin:50% 100%;filter:drop-shadow(0 12px 24px rgba(0,0,0,.5))}
        .hh-initials{position:absolute;right:0;bottom:-18px;width:300px;height:300px;border-radius:50%;display:none;align-items:center;justify-content:center;font-size:64px;font-weight:900;color:rgba(255,255,255,.95);z-index:2}
        .hh-initials.show{display:flex}
        .hh-check{position:absolute;right:30px;bottom:16px;z-index:4;display:flex;align-items:center;gap:12px;padding:9px 16px;border-radius:18px;background:rgba(4,24,28,.85);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid rgba(94,234,212,.35);box-shadow:0 12px 28px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.1)}
        .hh-check .ic{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(16,185,129,.15)}
        .hh-check .ic i{width:10px;height:10px;border-radius:50%;background:#10b981;display:block;box-shadow:0 0 10px #10b981}
        .hh-check.no .ic{background:rgba(245,158,11,.15)}
        .hh-check.no .ic i{background:#eab308;box-shadow:0 0 12px 2px #eab308}
        .hh-check .chk-info{flex:1;min-width:0}
        .hh-check b{display:block;font-size:11.5px;font-weight:700;color:#fff;letter-spacing:.01em}
        .hh-check small{display:block;font-size:10px;color:rgba(153,246,228,.75);margin-top:1px}
        .hh-check-arrow{display:none}
        @media (min-width:768px) and (max-width:1100px){
            .hh-inner{padding:0 24px;min-height:270px;gap:18px}
            .hh-right{width:300px;height:270px;margin-right:0}
            .hh-circle-layer{width:260px;height:260px;bottom:-14px}
            .hh-circle-ring{width:290px;height:290px;bottom:-28px}
            .hh-arc-overlay{width:330px;height:330px;bottom:-44px}
            .hh-photo{bottom:-40px;height:290px}
            .hh-photo img{transform:scale(1.02)}
            .hh-check{right:16px;bottom:14px}
        }
        @media (max-width:767px){
            .hh{border-radius:28px;margin-bottom:20px;box-shadow:0 18px 45px -8px rgba(1,20,23,.65);min-height:auto}
            .hh-inner{position:relative;display:flex;flex-direction:column;align-items:stretch;justify-content:flex-start;padding:16px 16px 16px;gap:0;min-height:auto}
            .hh-left{position:relative;z-index:3;padding:0;align-self:stretch;text-align:left;max-width:56%}
            .hh-top-bar{position:relative;display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%;z-index:4;margin-bottom:12px}
            .hh-date{font-size:10.5px;padding:4.5px 12px;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;flex-shrink:0}
            .hh-date svg{width:13px;height:13px;flex-shrink:0}
            .hh-mobile-dots{display:flex;align-items:center;justify-content:center;padding-top:4px;padding-right:2px}
            .hh-mobile-dots .dots-grid{display:grid;grid-template-columns:repeat(2,4.5px);gap:5.5px}
            .hh-mobile-dots span{width:4.5px;height:4.5px;border-radius:50%;background:#5eead4;opacity:.9;box-shadow:0 0 6px rgba(94,234,212,.7)}
            .hh-hello-wrap{margin:6px 0 4px;gap:8px}
            .hh-hello{font-size:11px;letter-spacing:.22em;color:#fff}
            .hh-hello-line{width:28px;height:2.5px}
            .hh-name{font-size:clamp(25px,7.5vw,34px);line-height:1.05}
            .hh-sub{margin:8px 0 0;font-size:11.5px;line-height:1.42;max-width:100%}
            .hh-pills{display:flex;flex-direction:column;align-items:flex-start;gap:6px;margin-top:14px}
            .hh-pill{font-size:11px;padding:5px 12px;border-radius:999px;width:auto}
            .hh-cta{display:inline-flex;width:auto;align-self:flex-start;margin-top:14px;padding:9px 18px;font-size:11.5px;border-radius:999px}
            .hh-cta svg:last-child{margin-left:6px}
            
            /* Right Arch & Employee cutout on Mobile right side */
            .hh-right{position:absolute;right:0;top:0;bottom:70px;width:60%;height:auto;margin:0;align-self:auto;display:flex;align-items:flex-end;justify-content:flex-end;pointer-events:none;z-index:2;overflow:hidden}
            .hh-circle-layer{position:absolute;right:-24px;top:75px;bottom:0;left:auto;transform:none;width:190px;height:auto;border-radius:999px 999px 0 0;background:linear-gradient(180deg,#2dd4bf 0%,#14b8a6 50%,#0d9488 100%);box-shadow:0 -10px 35px rgba(20,184,166,.3)}
            .hh-circle-ring{position:absolute;right:-38px;top:58px;bottom:0;left:auto;transform:none;width:220px;height:auto;border-radius:999px 999px 0 0;border:1.5px solid rgba(94,234,212,.35)}
            .hh-arc-overlay{position:absolute;right:-54px;top:42px;bottom:0;left:auto;transform:none;width:250px;height:auto;border-radius:999px 999px 0 0;border:1px solid rgba(94,234,212,.18)}
            .hh-flare{left:auto;right:170px;top:150px;width:6px;height:6px}
            .hh-photo{position:absolute;right:-26px;left:auto;bottom:0;width:215px;height:100%;max-height:330px;display:flex;align-items:flex-end;justify-content:center}
            .hh-photo img{width:100%;height:100%;object-fit:contain;object-position:center bottom;transform:scale(1.0);transform-origin:50% 100%}
            .hh-initials{position:absolute;right:-24px;top:75px;bottom:0;left:auto;transform:none;width:190px;height:auto;border-radius:999px 999px 0 0;font-size:62px}
            
            /* Check-in floating pill card pinned closely under the CTA button */
            .hh-check{position:relative;left:auto;right:auto;bottom:auto;transform:none;margin:16px 0 0;width:100%;box-sizing:border-box;padding:12px 18px;border-radius:22px;background:rgba(4,24,28,.88);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid rgba(94,234,212,.35);box-shadow:0 14px 34px rgba(0,0,0,.6);z-index:4}
            .hh-check .ic{width:34px;height:34px}
            .hh-check .ic i{width:14px;height:14px}
            .hh-check b{font-size:12.5px}
            .hh-check small{font-size:11px}
            .hh-check-arrow{display:block;margin-left:auto;color:#99f6e4}
        }
        /* Slide-in Entrance Animations on Dashboard Load (Zero Scrollbar / Zero Layout Shift) */
        @keyframes hubSlideLeft{0%{opacity:0;transform:translate3d(-24px,0,0)}100%{opacity:1;transform:translate3d(0,0,0)}}
        @keyframes hubArchUp{0%{opacity:0;transform:translate3d(0,36px,0) scale(.96)}100%{opacity:1;transform:translate3d(0,0,0) scale(1)}}
        @keyframes hubPhotoUp{0%{opacity:0;transform:translate3d(0,40px,0)}100%{opacity:1;transform:translate3d(0,0,0)}}
        @keyframes hubBadgePop{0%{opacity:0;transform:translate3d(-16px,10px,0) scale(.94)}100%{opacity:1;transform:translate3d(0,0,0) scale(1)}}
        @keyframes hubCardSlide{0%{opacity:0;transform:translate3d(-16px,0,0)}100%{opacity:1;transform:translate3d(0,0,0)}}
        .hh-date{animation:hubSlideLeft .48s cubic-bezier(.22,1,.36,1) .04s both;will-change:transform,opacity}
        .hh-hello{animation:hubSlideLeft .48s cubic-bezier(.22,1,.36,1) .09s both;will-change:transform,opacity}
        .hh-name{animation:hubSlideLeft .52s cubic-bezier(.22,1,.36,1) .14s both;will-change:transform,opacity}
        .hh-sub{animation:hubSlideLeft .48s cubic-bezier(.22,1,.36,1) .19s both;will-change:transform,opacity}
        .hh-pills{animation:hubSlideLeft .48s cubic-bezier(.22,1,.36,1) .24s both;will-change:transform,opacity}
        .hh-cta{animation:hubSlideLeft .48s cubic-bezier(.22,1,.36,1) .29s both;will-change:transform,opacity}
        .hh-circle-layer{animation:hubArchUp .55s cubic-bezier(.22,1,.36,1) .08s both;transform-origin:50% 100%;will-change:transform,opacity}
        .hh-circle-ring,.hh-arc-overlay{animation:hubArchUp .58s cubic-bezier(.22,1,.36,1) .10s both;transform-origin:50% 100%;will-change:transform,opacity}
        .hh-photo{animation:hubPhotoUp .62s cubic-bezier(.16,1,.3,1) .18s both;will-change:transform,opacity}
        .hh-check{animation:hubBadgePop .52s cubic-bezier(.22,1,.36,1) .32s both;will-change:transform,opacity}
        .hub-anim-section{overflow-x:clip}
        .hub-anim-d2{animation:hubCardSlide .5s cubic-bezier(.22,1,.36,1) .22s both}
        .hub-stagger > *{animation:hubCardSlide .48s cubic-bezier(.22,1,.36,1) both}
        .hub-stagger > *:nth-child(1){animation-delay:.12s}
        .hub-stagger > *:nth-child(2){animation-delay:.18s}
        .hub-stagger > *:nth-child(3){animation-delay:.24s}
        .hub-stagger > *:nth-child(4){animation-delay:.30s}
        .hub-stagger > *:nth-child(5){animation-delay:.36s}
        @media (prefers-reduced-motion:reduce){
            .hh-date,.hh-hello,.hh-name,.hh-sub,.hh-pills,.hh-cta,.hh-circle-layer,.hh-circle-ring,.hh-arc-overlay,.hh-photo,.hh-check,.hub-anim-d2,.hub-stagger > *{animation:none!important}
        }
        
        /* Modern Clean KPI Card Styling matching exact reference */
        .kpi-card{background:#ffffff;border:1px solid #f1f5f9;border-radius:24px;box-shadow:0 10px 25px -4px rgba(15,23,42,.04),0 2px 6px -1px rgba(15,23,42,.02);padding:20px;display:flex;flex-direction:column;justify-content:space-between;box-sizing:border-box}
        .kpi-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
        .kpi-hdr-left{display:flex;align-items:center;gap:10px}
        .kpi-hdr-icon{width:36px;height:36px;border-radius:12px;background:#e6fcf5;color:#0ca678;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .kpi-hdr-title{font-size:13.5px;font-weight:700;color:#0f172a;line-height:1.2;margin:0}
        .kpi-hdr-sub{font-size:11px;color:#64748b;margin-top:2px;display:block}
        .kpi-badge-aktif{display:inline-flex;align-items:center;gap:5px;padding:3.5px 10px;border-radius:999px;font-size:11px;font-weight:700;background:#ecfdf5;color:#059669;border:1px solid #d1fae5}
        .kpi-badge-aktif span{width:6.5px;height:6.5px;border-radius:50%;background:#10b981}
        .kpi-dropdown-btn{display:inline-flex;align-items:center;gap:5px;padding:3.5px 10px;border-radius:10px;font-size:11px;font-weight:600;background:#f8fafc;color:#334155;border:1px solid #e2e8f0}
        
        .kpi-box{border-radius:14px;border:1px solid;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:10px 4px 8px;text-align:center;box-sizing:border-box;min-height:82px}
        .kpi-box-num{font-size:18px;font-weight:800;color:#0f172a;line-height:1}
        .kpi-box-label{font-size:10.5px;font-weight:600;margin-top:3px;margin-bottom:5px;line-height:1.2}
        .kpi-box-icon{width:22px;height:22px;border-radius:6px;display:flex;align-items:center;justify-content:center}
        
        .kpi-box-mint{background:#ecfdf5!important;border-color:#a7f3d0!important}
        .kpi-box-mint .kpi-box-icon{background:#d1fae5;color:#059669}
        .kpi-box-mint .kpi-box-label{color:#065f46}
        
        .kpi-box-amber{background:#fffbeb!important;border-color:#fde68a!important}
        .kpi-box-amber .kpi-box-icon{background:#fef3c7;color:#d97706}
        .kpi-box-amber .kpi-box-label{color:#92400e}
        
        .kpi-box-blue{background:#eff6ff!important;border-color:#bfdbfe!important}
        .kpi-box-blue .kpi-box-icon{background:#dbeafe;color:#2563eb}
        .kpi-box-blue .kpi-box-label{color:#1e40af}
        
        .kpi-box-rose{background:#fff1f2!important;border-color:#fecdd3!important}
        .kpi-box-rose .kpi-box-icon{background:#ffe4e6;color:#e11d48}
        .kpi-box-rose .kpi-box-label{color:#9f1239}

        .kpi-box-slate{background:#f8fafc!important;border-color:#e2e8f0!important}
        .kpi-box-slate .kpi-box-icon{background:#e2e8f0;color:#475569}
        .kpi-box-slate .kpi-box-label{color:#334155}

        .kpi-box-purple{background:#faf5ff!important;border-color:#e9d5ff!important}
        .kpi-box-purple .kpi-box-icon{background:#f3e8ff;color:#9333ea}
        .kpi-box-purple .kpi-box-label{color:#6b21a8}

        .kpi-detail-row{display:flex;align-items:center;justify-content:space-between;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:6px}
        .kpi-detail-row svg{width:15px;height:15px;color:#94a3b8;flex-shrink:0}
        .kpi-detail-row strong{color:#0f172a;font-weight:700}

        .kpi-card-footer{padding-top:12px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;font-size:11px}
        .kpi-btn-cta{display:flex;align-items:center;justify-content:center;gap:6px;width:100%;padding:8px 14px;border-radius:14px;background:#e6fcf5;color:#0ca678;border:1px solid #c3fae8;font-size:11.5px;font-weight:700;transition:all .15s ease;text-decoration:none}
        .kpi-btn-cta:hover{background:#c3fae8}
    </style>
    <div class="hh">
        <!-- SVG Futuristic Background Geometry (Diagonal Bars, Concentric Orbit Rings, Grid Dots) -->
        <svg class="hh-bg-svg" viewBox="0 0 1100 290" fill="none" preserveAspectRatio="none">
            <defs>
                <linearGradient id="hhDiag1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.35"/>
                    <stop offset="60%" stop-color="#14b8a6" stop-opacity="0.12"/>
                    <stop offset="100%" stop-color="#04272a" stop-opacity="0"/>
                </linearGradient>
                <linearGradient id="hhDiag2" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#5eead4" stop-opacity="0.22"/>
                    <stop offset="100%" stop-color="#0d9488" stop-opacity="0"/>
                </linearGradient>
                <linearGradient id="hhArcGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#5eead4" stop-opacity="0.45"/>
                    <stop offset="50%" stop-color="#14b8a6" stop-opacity="0.2"/>
                    <stop offset="100%" stop-color="#042f2e" stop-opacity="0.05"/>
                </linearGradient>
                <!-- Dot Pattern -->
                <pattern id="hhDotGrid" x="0" y="0" width="18" height="18" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.5" fill="#5eead4" fill-opacity="0.28"/>
                </pattern>
            </defs>
            <!-- Left Top Diagonal Futuristic Glass Bars -->
            <path d="M-80 -40 L90 -40 L-20 200 L-190 200 Z" fill="url(#hhDiag1)"/>
            <path d="M-20 -40 L45 -40 L-85 220 L-150 220 Z" fill="url(#hhDiag2)"/>
            <path d="M50 -40 L85 -40 L-40 200 L-75 200 Z" fill="url(#hhDiag2)" opacity="0.6"/>

            <!-- Right Top Accent Wave Ribbon -->
            <path d="M800 0 C870 100, 940 150, 1100 200 L1100 0 Z" fill="url(#hhDiag1)" opacity="0.45"/>

            <!-- Large Concentric Orbit Rims Centered at Employee Portrait Circle (center around 920, 175) -->
            <circle cx="920" cy="175" r="260" stroke="url(#hhArcGrad)" stroke-width="1.2" opacity="0.6"/>
            <circle cx="920" cy="175" r="335" stroke="url(#hhArcGrad)" stroke-width="1" stroke-dasharray="8 6" opacity="0.4"/>
            <circle cx="920" cy="175" r="415" stroke="url(#hhArcGrad)" stroke-width="0.8" opacity="0.3"/>

            <!-- Tech Dot Matrix Grids (Top Right & Bottom Left) -->
            <rect x="990" y="20" width="80" height="48" fill="url(#hhDotGrid)"/>
            <rect x="24" y="228" width="80" height="48" fill="url(#hhDotGrid)"/>
        </svg>

        <div class="hh-glow1"></div>
        <div class="hh-glow2"></div>
        <div class="hh-inner">
            <div class="hh-left">
                <div class="hh-top-bar">
                    <div class="hh-date">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span id="hub-live-clock">{{ now('Asia/Jakarta')->translatedFormat('l, d F Y · H:i:s') }} WIB</span>
                    </div>
                    <div class="hh-mobile-dots">
                        <div class="dots-grid">
                            <span></span><span></span>
                            <span></span><span></span>
                        </div>
                    </div>
                </div>
                <div class="hh-hello-wrap">
                    <p class="hh-hello">SELAMAT DATANG</p>
                    <span class="hh-hello-line"></span>
                </div>
                <h1 class="hh-name">
                    <span class="a">{{ $firstName }}</span>
                    @if ($restName !== '')
                        <span class="b">{{ $restName }}</span>
                    @endif
                </h1>
                <p class="hh-sub">{{ $stats['scope']['subtitle'] }}</p>
                <div class="hh-pills">
                    <span class="hh-pill ok"><span class="hh-dot" style="{{ $user->is_active ? '' : 'background:#fb7185;box-shadow:0 0 10px #fb7185' }}"></span>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    <span class="hh-pill">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        NIK: {{ $user->employee_code ?? ($user->nik ?? 'MSN') }}
                    </span>
                    @if ($user->division)
                        <span class="hh-pill">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $user->division }}
                        </span>
                    @endif
                </div>
                <a href="{{ route('portal.profile') }}" class="hh-cta">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>
                        @if ($scopeType === 'company')
                            {{ $stats['scope']['badge'] ?? 'Seluruh Perusahaan' }}
                        @elseif ($scopeType === 'division')
                            {{ $stats['scope']['badge'] }}
                        @else
                            Statistik Pribadi
                        @endif
                    </span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="hh-right">
                <div class="hh-circle-ring"></div>
                <div class="hh-arc-overlay"></div>
                <div class="hh-circle-layer"></div>
                <div class="hh-flare"></div>
                <div class="hh-photo">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                             onerror="this.style.display='none'; this.nextElementSibling.classList.add('show');">
                        <div class="hh-initials">{{ $user->initials }}</div>
                    @else
                        <div class="hh-initials show">{{ $user->initials }}</div>
                    @endif
                </div>
            </div>

            <!-- Check-in Floating Card (Rendered inside inner container so it flows cleanly on mobile & desktop) -->
            <div class="hh-check {{ $clockInTime ? '' : 'no' }}">
                <span class="ic"><i></i></span>
                <div class="chk-info">
                    <b>{{ $clockInTime ? 'Hadir · ' . $clockInTime : 'Belum Check-in' }}</b>
                    <small>{{ $clockInTime ? 'Check-in hari ini' : 'Belum ada rekam hari ini' }}</small>
                </div>
                <svg class="hh-check-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>
    </div>
    <script>
        (function () {
            var el = document.getElementById('hub-live-clock');
            if (!el) return;
            var days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            function tick() {
                var parts = new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jakarta', hour12: false, year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', weekday: 'short' }).formatToParts(new Date());
                var m = {}; parts.forEach(function (p) { m[p.type] = p.value; });
                var dow = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].indexOf(m.weekday);
                el.textContent = days[dow] + ', ' + String(m.day).padStart(2, '0') + ' ' + months[parseInt(m.month, 10) - 1] + ' ' + m.year + ' · ' + m.hour + ':' + m.minute + ':' + m.second + ' WIB';
            }
            tick(); setInterval(tick, 1000);
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) {
                    var nodes = document.querySelectorAll('.hh, .hh-date, .hh-hello, .hh-name, .hh-sub, .hh-pills, .hh-cta, .hh-ring, .hh-arch, .hh-photo, .hh-check, .hub-anim-section, .hub-stagger > *');
                    nodes.forEach(function (n) {
                        n.style.animation = 'none';
                        void n.offsetHeight;
                        n.style.animation = '';
                    });
                }
            });
        })();
    </script>

    <!-- Section 1: KPI Summary Cards Matching Reference -->
    <section class="mb-8 hub-anim-section hub-anim-d1" aria-labelledby="heading-stats-kpi">
        <h2 id="heading-stats-kpi" class="sr-only">Ringkasan Statistik Utama</h2>
        
        @if ($stats['scope']['type'] === 'personal')
        {{-- TAMPILAN KHUSUS KARYAWAN: PENGAJUAN, LEMBUR, ABSEN --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 hub-stagger">
            
            <!-- Card 1: Pengajuan (Total, Disetujui, Ditolak, Menunggu) -->
            <div class="kpi-card">
                <div>
                    <!-- Header -->
                    <div class="kpi-hdr">
                        <div class="kpi-hdr-left">
                            <div class="kpi-hdr-icon" style="background:#ecfeff; color:#0891b2;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="kpi-hdr-title">Pengajuan Saya</h3>
                                <span class="kpi-hdr-sub">Periode {{ now('Asia/Jakarta')->translatedFormat('F Y') }}</span>
                            </div>
                        </div>
                        <span class="kpi-dropdown-btn">
                            {{ now('Asia/Jakarta')->translatedFormat('M Y') }}
                        </span>
                    </div>

                    <!-- 4 Status Blocks: Total, Disetujui, Ditolak, Menunggu -->
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <!-- Total -->
                        <div class="kpi-box kpi-box-blue">
                            <span class="kpi-box-num">{{ $stats['pengajuan']['total'] }}</span>
                            <span class="kpi-box-label">Total</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                        </div>

                        <!-- Disetujui -->
                        <div class="kpi-box kpi-box-mint">
                            <span class="kpi-box-num text-emerald-700">{{ $stats['pengajuan']['approved'] }}</span>
                            <span class="kpi-box-label text-emerald-800">Disetujui</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>

                        <!-- Ditolak -->
                        <div class="kpi-box kpi-box-rose">
                            <span class="kpi-box-num text-rose-700">{{ $stats['pengajuan']['rejected'] }}</span>
                            <span class="kpi-box-label text-rose-800">Ditolak</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        </div>

                        <!-- Menunggu -->
                        <div class="kpi-box kpi-box-amber">
                            <span class="kpi-box-num text-amber-700">{{ $stats['pengajuan']['pending'] }}</span>
                            <span class="kpi-box-label text-amber-800">Menunggu</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Details Row -->
                    <div class="space-y-1.5 pt-1 mb-3">
                        <div class="kpi-detail-row">
                            <span class="inline-flex items-center gap-2">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Nominal Disetujui
                            </span>
                            <strong class="text-emerald-700">Rp {{ number_format($stats['pengajuan']['nominal_disetujui'], 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="kpi-card-footer">
                    <span class="text-slate-500 inline-flex items-center gap-1.5">
                        <svg width="14" height="14" class="text-cyan-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Dana &amp; Pengadaan Barang
                    </span>
                    <a href="{{ route('gomsn.pengajuan.index') }}" class="font-bold text-cyan-700 hover:text-cyan-800 inline-flex items-center gap-1">
                        Lihat Semua &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 2: Lembur (Total, Disetujui, Ditolak, Menunggu) -->
            <div class="kpi-card">
                <div>
                    <!-- Header -->
                    <div class="kpi-hdr">
                        <div class="kpi-hdr-left">
                            <div class="kpi-hdr-icon" style="background:#eef2ff; color:#4f46e5;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="kpi-hdr-title">Lembur Saya</h3>
                                <span class="kpi-hdr-sub">Periode {{ now('Asia/Jakarta')->translatedFormat('F Y') }}</span>
                            </div>
                        </div>
                        <span class="kpi-dropdown-btn">
                            {{ $stats['lembur']['total_hours'] }} Jam
                        </span>
                    </div>

                    <!-- 4 Status Blocks: Total, Disetujui, Ditolak, Menunggu -->
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <!-- Total -->
                        <div class="kpi-box kpi-box-blue">
                            <span class="kpi-box-num">{{ $stats['lembur']['total'] }}</span>
                            <span class="kpi-box-label">Total</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                        </div>

                        <!-- Disetujui -->
                        <div class="kpi-box kpi-box-mint">
                            <span class="kpi-box-num text-emerald-700">{{ $stats['lembur']['approved'] }}</span>
                            <span class="kpi-box-label text-emerald-800">Disetujui</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>

                        <!-- Ditolak -->
                        <div class="kpi-box kpi-box-rose">
                            <span class="kpi-box-num text-rose-700">{{ $stats['lembur']['rejected'] }}</span>
                            <span class="kpi-box-label text-rose-800">Ditolak</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        </div>

                        <!-- Menunggu -->
                        <div class="kpi-box kpi-box-amber">
                            <span class="kpi-box-num text-amber-700">{{ $stats['lembur']['pending'] }}</span>
                            <span class="kpi-box-label text-amber-800">Menunggu</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Details Row -->
                    <div class="space-y-1.5 pt-1 mb-3">
                        <div class="kpi-detail-row">
                            <span class="inline-flex items-center gap-2">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Durasi Terverifikasi
                            </span>
                            <strong class="text-indigo-700">{{ $stats['lembur']['total_hours'] }} Jam Kerja</strong>
                        </div>
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="kpi-card-footer">
                    <span class="text-slate-500 inline-flex items-center gap-1.5">
                        <svg width="14" height="14" class="text-indigo-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Surat Perintah Lembur
                    </span>
                    <a href="{{ route('gomsn.overtime.index') }}" class="font-bold text-indigo-700 hover:text-indigo-800 inline-flex items-center gap-1">
                        Lihat Lembur &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 3: Absen (Total Hadir, Tepat Waktu, Tidak Hadir, Total Potongan) -->
            <div class="kpi-card">
                <div>
                    <!-- Header -->
                    <div class="kpi-hdr">
                        <div class="kpi-hdr-left">
                            <div class="kpi-hdr-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="kpi-hdr-title">Absensi Saya</h3>
                                <span class="kpi-hdr-sub">Periode {{ now('Asia/Jakarta')->translatedFormat('F Y') }}</span>
                            </div>
                        </div>
                        <span class="kpi-badge-aktif">
                            <span></span>
                            Aktif
                        </span>
                    </div>

                    <!-- 4 Status Blocks: Total Hadir, Tepat Waktu, Tidak Hadir, Total Potongan -->
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <!-- Total Hadir -->
                        <div class="kpi-box kpi-box-mint">
                            <span class="kpi-box-num">{{ $stats['absensi']['month_present_days'] }}</span>
                            <span class="kpi-box-label">Total Hadir</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                        </div>

                        <!-- Tepat Waktu -->
                        <div class="kpi-box kpi-box-blue">
                            <span class="kpi-box-num text-blue-700">{{ $stats['absensi']['month_ontime_days'] ?? $stats['absensi']['today_present'] }}</span>
                            <span class="kpi-box-label text-blue-800">Tepat Waktu</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>

                        <!-- Tidak Hadir / Alpa -->
                        <div class="kpi-box kpi-box-rose">
                            <span class="kpi-box-num text-rose-700">{{ $stats['absensi']['month_absent_days'] ?? 0 }}</span>
                            <span class="kpi-box-label text-rose-800">Tidak Hadir</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        </div>

                        <!-- Total Potongan -->
                        <div class="kpi-box kpi-box-amber">
                            @php
                                $potonganVal = $stats['absensi']['month_total_deduction'] ?? 0;
                            @endphp
                            <span class="kpi-box-num text-[13px] font-bold text-amber-800 truncate" title="Rp {{ number_format($potonganVal, 0, ',', '.') }}">
                                {{ $potonganVal > 0 ? (strlen((string) (int) $potonganVal) > 6 ? round($potonganVal/1000) . 'k' : number_format($potonganVal, 0, ',', '.')) : '0' }}
                            </span>
                            <span class="kpi-box-label text-amber-900">Total Potong</span>
                            <div class="kpi-box-icon">
                                <svg width="14" height="14" class="fill-amber-600 text-amber-600" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Details Row -->
                    <div class="space-y-1.5 pt-1 mb-3">
                        <div class="kpi-detail-row">
                            <span class="inline-flex items-center gap-2">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Akumulasi Potongan
                            </span>
                            <strong class="{{ ($stats['absensi']['month_total_deduction'] ?? 0) > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                Rp {{ number_format($stats['absensi']['month_total_deduction'] ?? 0, 0, ',', '.') }}
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="kpi-card-footer">
                    <span class="text-slate-500 inline-flex items-center gap-1.5">
                        <svg width="14" height="14" class="text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Denda &amp; Koreksi Absen
                    </span>
                    <a href="{{ route('attendance.deductions') }}" class="font-bold text-teal-700 hover:text-teal-800 inline-flex items-center gap-1">
                        Detail Potongan &rarr;
                    </a>
                </div>
            </div>

        </div>
        @else
        {{-- TAMPILAN MANAJEMEN / SUPERVISOR / COMPANY --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 hub-stagger">
            
            <!-- Card 1: Absensi Hari Ini -->
            <div class="kpi-card">
                <div>
                    <!-- Header -->
                    <div class="kpi-hdr">
                        <div class="kpi-hdr-left">
                            <div class="kpi-hdr-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="kpi-hdr-title">Absensi Hari Ini</h3>
                                <span class="kpi-hdr-sub">{{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
                            </div>
                        </div>
                        <span class="kpi-badge-aktif">
                            <span></span>
                            Aktif
                        </span>
                    </div>

                    <!-- 4 Status Blocks -->
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <!-- Hadir -->
                        <div class="kpi-box kpi-box-mint">
                            <span class="kpi-box-num">{{ $stats['absensi']['today_present'] }}</span>
                            <span class="kpi-box-label">Hadir</span>
                            <div class="kpi-box-icon">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                        </div>

                        <!-- Terlambat -->
                        <div class="kpi-box kpi-box-amber">
                            <span class="kpi-box-num">{{ $stats['absensi']['today_late'] }}</span>
                            <span class="kpi-box-label">Terlambat</span>
                            <div class="kpi-box-icon">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>

                        <!-- Izin -->
                        <div class="kpi-box kpi-box-blue">
                            <span class="kpi-box-num">{{ $stats['absensi']['today_leave'] }}</span>
                            <span class="kpi-box-label">Izin</span>
                            <div class="kpi-box-icon">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </div>

                        <!-- Sakit -->
                        <div class="kpi-box kpi-box-rose">
                            <span class="kpi-box-num">{{ $stats['absensi']['today_absent'] }}</span>
                            <span class="kpi-box-label">Sakit</span>
                            <div class="kpi-box-icon">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Details Row -->
                    <div class="space-y-1.5 pt-1 mb-3">
                        <div class="kpi-detail-row">
                            <span class="inline-flex items-center gap-2">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Total Hari Kerja
                            </span>
                            <strong>{{ $metrics['monthly_attendance']['present_days'] ?? $stats['absensi']['month_present_days'] }} Hari</strong>
                        </div>
                        <div class="kpi-detail-row">
                            <span class="inline-flex items-center gap-2">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Total Jam Kerja
                            </span>
                            <strong>{{ $metrics['monthly_attendance']['total_working_hours'] ?? ($stats['absensi']['month_present_days'] * 8) }} Jam</strong>
                        </div>
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="kpi-card-footer">
                    <span class="text-slate-500 inline-flex items-center gap-1.5">
                        <svg width="14" height="14" class="text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Data absensi diperbarui real-time
                    </span>
                    <a href="{{ route('attendance.index') }}" class="font-bold text-teal-700 hover:text-teal-800 inline-flex items-center gap-1">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Refresh
                    </a>
                </div>
            </div>

            <!-- Card 2: Ringkasan Gaji & Tunjangan -->
            <div class="kpi-card">
                <div>
                    <!-- Header -->
                    <div class="kpi-hdr">
                        <div class="kpi-hdr-left">
                            <div class="kpi-hdr-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <h3 class="kpi-hdr-title">Ringkasan Gaji &amp; Tunjangan</h3>
                                <span class="kpi-hdr-sub">Periode {{ now('Asia/Jakarta')->translatedFormat('F Y') }}</span>
                            </div>
                        </div>
                        <span class="kpi-dropdown-btn">
                            {{ now('Asia/Jakarta')->translatedFormat('M Y') }}
                            <svg width="12" height="12" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </div>

                    <!-- Gaji Pokok Headline -->
                    <div class="mb-4">
                        <span class="text-xs text-slate-500 font-semibold block">Gaji Pokok</span>
                        <div class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">
                            Rp 7.500.000
                        </div>
                    </div>

                    <!-- 3 Pill Blocks: Tunjangan, Insentif, Potongan -->
                    <div class="grid grid-cols-3 gap-2.5 mb-4">
                        <!-- Tunjangan -->
                        <div class="kpi-box kpi-box-slate">
                            <div class="kpi-box-icon mb-1">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <span class="kpi-box-label">Tunjangan</span>
                            <span class="text-xs font-bold text-slate-900 leading-tight">Rp 1.500.000</span>
                        </div>

                        <!-- Insentif -->
                        <div class="kpi-box kpi-box-mint">
                            <div class="kpi-box-icon mb-1">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                            </div>
                            <span class="kpi-box-label">Insentif</span>
                            <span class="text-xs font-bold text-emerald-700 leading-tight">Rp 500.000</span>
                        </div>

                        <!-- Potongan -->
                        <div class="kpi-box kpi-box-rose">
                            <div class="kpi-box-icon mb-1">
                                <svg width="14" height="14" class="fill-rose-600 text-rose-600" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <span class="kpi-box-label">Potongan</span>
                            <span class="text-xs font-bold text-rose-700 leading-tight">Rp 250.000</span>
                        </div>
                    </div>

                    <!-- Total Penghasilan -->
                    <div class="flex items-center justify-between pt-1 mb-3">
                        <span class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                            <svg width="16" height="16" class="text-teal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Total Penghasilan
                        </span>
                        <strong class="text-emerald-700 font-extrabold text-base">Rp 8.750.000</strong>
                    </div>
                </div>

                <!-- Footer Button CTA -->
                <div class="pt-2">
                    <a href="{{ route('attendance.deductions') }}" class="kpi-btn-cta">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Lihat Detail Gaji</span>
                        <svg width="14" height="14" class="ml-auto" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Card 3: Cuti & Perizinan -->
            <div class="kpi-card">
                <div>
                    <!-- Header -->
                    <div class="kpi-hdr">
                        <div class="kpi-hdr-left">
                            <div class="kpi-hdr-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            </div>
                            <div>
                                <h3 class="kpi-hdr-title">Cuti &amp; Perizinan</h3>
                                <span class="kpi-hdr-sub">Ringkasan Pengajuan Anda</span>
                            </div>
                        </div>
                        <span class="kpi-dropdown-btn">
                            Bulan Ini
                            <svg width="12" height="12" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </div>

                    <!-- Total Pengajuan Headline -->
                    <div class="mb-4">
                        <span class="text-xs text-slate-500 font-semibold inline-flex items-center gap-1.5">
                            <svg width="14" height="14" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Total Pengajuan
                        </span>
                        <div class="text-2xl font-black text-slate-900 tracking-tight mt-0.5 flex items-baseline gap-1.5">
                            <span>{{ $metrics['leave_breakdown']['total'] ?? 0 }}</span>
                            <span class="text-sm font-semibold text-slate-600">Pengajuan</span>
                        </div>
                    </div>

                    <!-- 3 Pill Blocks: Cuti, Izin, Work From Home -->
                    <div class="grid grid-cols-3 gap-2.5 mb-4">
                        <!-- Cuti -->
                        <div class="kpi-box kpi-box-mint">
                            <div class="kpi-box-icon mb-1">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            </div>
                            <span class="kpi-box-label">Cuti</span>
                            <span class="text-[11px] font-bold text-slate-600 leading-tight">{{ $metrics['leave_breakdown']['cuti'] ?? 0 }} Pengajuan</span>
                        </div>

                        <!-- Izin -->
                        <div class="kpi-box kpi-box-blue">
                            <div class="kpi-box-icon mb-1">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <span class="kpi-box-label">Izin</span>
                            <span class="text-[11px] font-bold text-slate-600 leading-tight">{{ $metrics['leave_breakdown']['izin'] ?? 0 }} Pengajuan</span>
                        </div>

                        <!-- Work From Home -->
                        <div class="kpi-box kpi-box-purple">
                            <div class="kpi-box-icon mb-1">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            </div>
                            <span class="kpi-box-label">Work From Home</span>
                            <span class="text-[11px] font-bold text-slate-600 leading-tight">{{ $metrics['leave_breakdown']['wfh'] ?? 0 }} Pengajuan</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="kpi-card-footer">
                    <a href="{{ route('attendance.leaves') }}" class="w-full flex items-center justify-between font-bold text-slate-700 hover:text-teal-700 transition-colors">
                        <span class="inline-flex items-center gap-2">
                            <svg width="16" height="16" class="text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Lihat Semua Pengajuan
                        </span>
                        <svg width="16" height="16" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

        </div>
        @endif
    </section>

    <!-- Section 2: Visualisasi Grafik Informatif (Interactive Charts) -->
    <section class="mb-8 hub-anim-section hub-anim-d2" aria-labelledby="heading-charts">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 id="heading-charts" class="font-heading text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="p-1 rounded-lg bg-teal-100 text-teal-800 text-xs">📊</span>
                    Grafik &amp; Analitik Operasional
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Data tren kehadiran, komposisi pengajuan, dan jam lembur 7 hari terakhir
                </p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-white border border-slate-200 px-2.5 py-1 rounded-lg shadow-2xs">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Data
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">
            <!-- Chart 1: Tren Kehadiran 7 Hari Terakhir (Col 8) -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 mb-3 border-b border-slate-100 gap-2">
                        <div>
                            <h3 class="font-heading font-bold text-sm text-slate-800">
                                Tren Kehadiran Harian (7 Hari Terakhir)
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Distribusi status tepat waktu, keterlambatan, izin, dan alpa
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-[11px] font-medium text-slate-600 flex-wrap">
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-teal-600 inline-block"></span> Tepat</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-amber-500 inline-block"></span> Telat</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-indigo-500 inline-block"></span> Izin</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-rose-500 inline-block"></span> Alpa</span>
                        </div>
                    </div>

                    <div class="relative w-full h-[250px] sm:h-[280px]">
                        <canvas id="attendanceTrendChart"></canvas>
                    </div>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                    <span>*Data dihitung berdasarkan jadwal kerja harian (Shift &amp; Presensi)</span>
                    <a href="{{ route('attendance.leaderboard') }}" class="font-semibold text-teal-700 hover:underline">
                        Lihat Leaderboard &rarr;
                    </a>
                </div>
            </div>

            <!-- Chart 2: Komposisi Pengajuan (Col 4) -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="pb-3 mb-3 border-b border-slate-100">
                        <h3 class="font-heading font-bold text-sm text-slate-800">
                            Komposisi Status Pengajuan
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Rasio persetujuan kasbon &amp; barang bulan ini
                        </p>
                    </div>

                    <div class="relative w-full h-[200px] flex items-center justify-center my-2">
                        <canvas id="pengajuanDonutChart"></canvas>
                    </div>

                    <!-- Status Breakdown List -->
                    <div class="space-y-2 mt-4 text-xs">
                        <div class="flex items-center justify-between p-2 rounded-xl bg-teal-50/60 border border-teal-100">
                            <span class="flex items-center gap-2 font-medium text-teal-900">
                                <span class="h-2 w-2 rounded-full bg-teal-600"></span> Disetujui / Cair
                            </span>
                            <strong class="font-bold text-teal-800">{{ $stats['pengajuan']['approved'] }} Item</strong>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-amber-50/60 border border-amber-100">
                            <span class="flex items-center gap-2 font-medium text-amber-900">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span> Menunggu Approval
                            </span>
                            <strong class="font-bold text-amber-800">{{ $stats['pengajuan']['pending'] }} Item</strong>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-rose-50/60 border border-rose-100">
                            <span class="flex items-center gap-2 font-medium text-rose-900">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span> Ditolak
                            </span>
                            <strong class="font-bold text-rose-800">{{ $stats['pengajuan']['rejected'] }} Item</strong>
                        </div>
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 text-center text-xs text-slate-400">
                    Total {{ $stats['pengajuan']['total'] }} Pengajuan di periode ini
                </div>
            </div>
        </div>

        <!-- Chart 3: Tren Jam Lembur Mingguan (Full Width Col 12) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 mb-3 border-b border-slate-100 gap-2">
                <div>
                    <h3 class="font-heading font-bold text-sm text-slate-800">
                        Tren Jam Lembur Harian (7 Hari Terakhir)
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Akumulasi total jam lembur yang telah disetujui &amp; diverifikasi per hari
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Total 7 Hari: {{ array_sum($stats['charts']['lembur']['hours']) }} Jam
                    </span>
                </div>
            </div>

            <div class="relative w-full h-[220px] sm:h-[240px]">
                <canvas id="overtimeTrendChart"></canvas>
            </div>
        </div>
    </section>

    <!-- Section 3: Aktivitas Terkini (Pengajuan & Lembur Realtime) -->
    <section class="mb-8 hub-anim-section hub-anim-d3" aria-labelledby="heading-recent-activities">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 hub-stagger">
            <!-- Table 1: Pengajuan Terbaru -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="h-8 w-8 rounded-xl bg-cyan-50 text-cyan-700 flex items-center justify-center font-bold text-sm">
                                💳
                            </span>
                            <div>
                                <h3 class="font-heading font-bold text-sm text-slate-800">Pengajuan Terbaru</h3>
                                <span class="text-[11px] text-slate-400">Dana &amp; Pengadaan Barang</span>
                            </div>
                        </div>
                        <a href="{{ route('gomsn.pengajuan.index') }}" class="text-xs font-semibold text-cyan-700 hover:underline">
                            Semua &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                    <th class="pb-2.5">Item &amp; Pemohon</th>
                                    <th class="pb-2.5 px-2">Nominal</th>
                                    <th class="pb-2.5 px-2">Status</th>
                                    <th class="pb-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($stats['recent']['pengajuan'] as $p)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-2.5">
                                            <div class="font-semibold text-slate-800 line-clamp-1">{{ $p->judul }}</div>
                                            <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                                <span class="font-mono text-cyan-700">{{ $p->nomor_pengajuan }}</span>
                                                <span>·</span>
                                                <span>{{ $p->user->name ?? 'User' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-2 font-mono font-semibold text-slate-700">
                                            Rp {{ number_format($p->nominal_total, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-2">
                                            @if (in_array($p->status, ['disetujui', 'ditransfer', 'terealisasi']))
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    {{ strtoupper(str_replace('_', ' ', $p->status)) }}
                                                </span>
                                            @elseif (str_starts_with($p->status, 'menunggu'))
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    MENUNGGU
                                                </span>
                                            @elseif ($p->status === 'ditolak')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    DITOLAK
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    {{ strtoupper($p->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-right">
                                            <a href="{{ route('gomsn.pengajuan.show', $p->id) }}" class="font-semibold text-cyan-700 hover:text-cyan-800">
                                                Detail &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-400 text-xs">
                                            Belum ada pengajuan terkini di filter ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Table 2: Lembur Terbaru -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="h-8 w-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                ⏱️
                            </span>
                            <div>
                                <h3 class="font-heading font-bold text-sm text-slate-800">Lembur Terbaru</h3>
                                <span class="text-[11px] text-slate-400">Pekerjaan Tambahan &amp; Darurat</span>
                            </div>
                        </div>
                        <a href="{{ route('gomsn.overtime.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">
                            Semua &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                    <th class="pb-2.5">Karyawan &amp; Alasan</th>
                                    <th class="pb-2.5 px-2">Tanggal &amp; Durasi</th>
                                    <th class="pb-2.5 px-2">Status</th>
                                    <th class="pb-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($stats['recent']['lembur'] as $l)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-2.5">
                                            <div class="font-semibold text-slate-800">{{ $l->user->name ?? 'User' }}</div>
                                            <div class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                                                {{ $l->alasan ?: 'Lembur operasional' }}
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-2">
                                            <div class="font-semibold text-slate-700">
                                                {{ round($l->durasi_menit / 60, 1) }} Jam
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ \Carbon\Carbon::parse($l->tanggal)->translatedFormat('d M Y') }}
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-2">
                                            @if ($l->status === 'approved')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    DISETUJUI
                                                </span>
                                            @elseif ($l->status === 'paid')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    DIBAYAR
                                                </span>
                                            @elseif ($l->status === 'pending')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    MENUNGGU
                                                </span>
                                            @elseif ($l->status === 'rejected')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    DITOLAK
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    {{ strtoupper($l->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-right">
                                            <a href="{{ route('gomsn.overtime.index') }}" class="font-semibold text-indigo-700 hover:text-indigo-800">
                                                Detail &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-400 text-xs">
                                            Belum ada data lembur terkini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 4: Quick Access Modul Utama -->
    <section class="mb-8 hub-anim-section hub-anim-d4" aria-labelledby="heading-quick-access">
        <div class="flex items-center justify-between mb-3.5">
            <h2 id="heading-quick-access" class="font-heading text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="p-1 rounded-lg bg-teal-100 text-teal-800 text-xs">⚡</span>
                Pintasan Cepat Modul
            </h2>
            <span class="text-xs text-slate-400 font-medium">Akses langsung fitur operasional harian</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 hub-stagger">
            <!-- Card 1: Presensi Biometrik -->
            <a href="{{ route('attendance.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-teal-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 bg-teal-50 px-2 py-0.5 rounded-md border border-teal-100">
                            PRESENSI HRIS
                        </span>
                        <span class="text-emerald-600 font-bold text-xs">✓ Online</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900 mt-2 group-hover:text-teal-700 transition-colors">
                        Presensi &amp; Face Biometrik
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Clock-in, clock-out dengan validasi GPS &amp; AI deteksi wajah.
                    </p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-teal-700 font-semibold">
                    <span>Buka Absen</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 2: Pengajuan Kasbon & Barang -->
            <a href="{{ route('gomsn.pengajuan.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-cyan-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded-md border border-cyan-100">
                            FINANCE &amp; LOGISTIK
                        </span>
                        <span class="text-amber-600 font-bold text-xs">{{ $stats['pengajuan']['pending'] }} Pending</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900 mt-2 group-hover:text-cyan-700 transition-colors">
                        Pengajuan Dana &amp; Barang
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Buat permohonan kasbon, reimburs, atau pengadaan alat kerja.
                    </p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-cyan-700 font-semibold">
                    <span>Ajukan Sekarang</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 3: Lembur (Overtime) -->
            <a href="{{ route('gomsn.overtime.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-indigo-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                            OPERASIONAL &amp; SPK
                        </span>
                        <span class="text-indigo-600 font-bold text-xs">{{ $stats['lembur']['total_hours'] }} Jam</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900 mt-2 group-hover:text-indigo-700 transition-colors">
                        Pengajuan Lembur
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Klaim jam lembur darurat lapangan, hari kerja atau hari libur.
                    </p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-indigo-700 font-semibold">
                    <span>Kelola Lembur</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 4: Jaringan OLT, PON & ODP -->
            <a href="{{ route('gomsn.network.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-teal-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 bg-teal-50 px-2 py-0.5 rounded-md border border-teal-100">
                            INFRASTRUKTUR NOC
                        </span>
                        <span class="text-emerald-600 font-bold text-xs">Aktif</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900 mt-2 group-hover:text-teal-700 transition-colors">
                        OLT, PON &amp; ODP
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Peta topologi fiber, distribusi port splitter ODP &amp; nomor internet pelanggan.
                    </p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-teal-700 font-semibold">
                    <span>Buka Jaringan</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 5: Leaderboard Disiplin -->
            <a href="{{ route('attendance.leaderboard') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-emerald-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                            RANKING DISIPLIN
                        </span>
                        <span class="text-amber-500 font-bold text-xs">🏆 Top 10</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900 mt-2 group-hover:text-emerald-700 transition-colors">
                        Leaderboard Kehadiran
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Pantau peringkat kehadiran terbaik per divisi dan perusahaan.
                    </p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-emerald-700 font-semibold">
                    <span>Lihat Ranking</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
        </div>
    </section>

    <!-- Chart.js Initialization Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not yet loaded');
                return;
            }

            const chartFontFamily = 'Plus Jakarta Sans, Inter, sans-serif';

            // 1. Attendance Trend Chart (Stacked / Grouped Bar)
            const attCanvas = document.getElementById('attendanceTrendChart');
            if (attCanvas) {
                const attData = @json($stats['charts']['attendance']);
                new Chart(attCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: attData.labels,
                        datasets: [
                            {
                                label: 'Tepat Waktu',
                                data: attData.present,
                                backgroundColor: '#0D9488',
                                borderRadius: 6,
                                maxBarThickness: 28,
                            },
                            {
                                label: 'Terlambat',
                                data: attData.late,
                                backgroundColor: '#F59E0B',
                                borderRadius: 6,
                                maxBarThickness: 28,
                            },
                            {
                                label: 'Izin / Cuti',
                                data: attData.leave,
                                backgroundColor: '#6366F1',
                                borderRadius: 6,
                                maxBarThickness: 28,
                            },
                            {
                                label: 'Alpa / Belum',
                                data: attData.absent,
                                backgroundColor: '#EF4444',
                                borderRadius: 6,
                                maxBarThickness: 28,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                padding: 10,
                                cornerRadius: 8,
                                bodyFont: { family: chartFontFamily, size: 12 },
                                titleFont: { family: chartFontFamily, size: 12, weight: 'bold' },
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: { display: false },
                                ticks: { font: { family: chartFontFamily, size: 11 }, color: '#64748B' }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: { color: '#F1F5F9' },
                                ticks: {
                                    stepSize: 1,
                                    font: { family: chartFontFamily, size: 11 },
                                    color: '#64748B'
                                }
                            }
                        }
                    }
                });
            }

            // 2. Pengajuan Donut Chart
            const pengajuanCanvas = document.getElementById('pengajuanDonutChart');
            if (pengajuanCanvas) {
                const pengData = @json($stats['charts']['pengajuan']);
                const totalItems = pengData.data.reduce((a, b) => a + b, 0);

                new Chart(pengajuanCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: pengData.labels,
                        datasets: [{
                            data: totalItems > 0 ? pengData.data : [1],
                            backgroundColor: totalItems > 0 ? pengData.colors : ['#E2E8F0'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: totalItems > 0,
                                padding: 10,
                                cornerRadius: 8,
                                bodyFont: { family: chartFontFamily, size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        const val = context.raw || 0;
                                        const pct = totalItems > 0 ? Math.round((val / totalItems) * 100) : 0;
                                        return ` ${context.label}: ${val} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Overtime Trend Chart
            const lemburCanvas = document.getElementById('overtimeTrendChart');
            if (lemburCanvas) {
                const lemburData = @json($stats['charts']['lembur']);
                const ctx = lemburCanvas.getContext('2d');
                
                // Gradient fill
                const gradient = ctx.createLinearGradient(0, 0, 0, 200);
                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: lemburData.labels,
                        datasets: [
                            {
                                label: 'Jam Lembur Disetujui',
                                data: lemburData.hours,
                                borderColor: '#6366F1',
                                borderWidth: 2.5,
                                backgroundColor: gradient,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#6366F1',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                padding: 10,
                                cornerRadius: 8,
                                bodyFont: { family: chartFontFamily, size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        return ` Durasi: ${context.raw} Jam`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: chartFontFamily, size: 11 }, color: '#64748B' }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#F1F5F9' },
                                ticks: {
                                    font: { family: chartFontFamily, size: 11 },
                                    color: '#64748B',
                                    callback: function(value) {
                                        return value + 'j';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-layouts.app>
