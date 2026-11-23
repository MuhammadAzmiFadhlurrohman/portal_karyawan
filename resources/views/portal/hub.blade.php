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
        /* =========================================================
           Executive Sculpted-Light Hero Banner (PT MSN Ocean Teal)
           ========================================================= */
        .hh{
            --mx: 75%;
            --my: 40%;
            position:relative;
            overflow:hidden;
            border-radius:26px;
            margin-bottom:20px;
            border:1px solid rgba(94,234,212,.22);
            box-shadow:0 22px 50px -14px rgba(1,18,22,.62),0 4px 16px -4px rgba(2,24,28,.35),inset 0 1px 0 rgba(153,246,228,.24);
            background:
                radial-gradient(115% 150% at 12% 10%, #0a4247 0%, #05282c 48%, #021417 100%);
            color:#fff;
            user-select:none;
            isolation:isolate;
            animation:hhStageIn .75s cubic-bezier(.16,1,.3,1) both;
        }
        /* Subtle film grain to remove gradient banding */
        .hh::after{
            content:"";
            position:absolute;
            inset:0;
            pointer-events:none;
            z-index:1;
            opacity:.045;
            mix-blend-mode:overlay;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }
        /* Top specular light rim */
        .hh-rim{
            position:absolute;
            top:0;
            left:32px;
            right:32px;
            height:1.5px;
            background:linear-gradient(90deg,transparent 0%,rgba(94,234,212,.5) 30%,rgba(255,255,255,.55) 50%,rgba(45,212,191,.4) 70%,transparent 100%);
            pointer-events:none;
            z-index:3;
            opacity:.85;
        }
        /* Interactive cursor spotlight sheen (desktop only) */
        .hh-spotlight{
            position:absolute;
            inset:0;
            pointer-events:none;
            z-index:1;
            background:radial-gradient(520px circle at var(--mx) var(--my), rgba(94,234,212,.11), transparent 62%);
            opacity:0;
            transition:opacity .45s cubic-bezier(.16,1,.3,1);
        }
        .hh:hover .hh-spotlight{opacity:1}
        /* Architectural faint hairline grid */
        .hh-grid-bg{
            position:absolute;
            inset:0;
            pointer-events:none;
            z-index:1;
            background-image:
                linear-gradient(rgba(153,246,228,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(153,246,228,.045) 1px, transparent 1px);
            background-size:44px 44px;
            -webkit-mask-image:radial-gradient(ellipse 75% 85% at 25% 40%, #000 10%, transparent 78%);
            mask-image:radial-gradient(ellipse 75% 85% at 25% 40%, #000 10%, transparent 78%);
        }
        .hh-bg-svg{position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:1}
        /* Sculpted ambient aurora glows with gentle organic drift */
        .hh-glow1{
            position:absolute;left:-80px;top:-90px;width:360px;height:320px;border-radius:50%;
            background:radial-gradient(circle, rgba(20,184,166,.26) 0%, rgba(13,148,136,.08) 55%, transparent 75%);
            filter:blur(58px);pointer-events:none;z-index:1;
            animation:hhDriftA 18s ease-in-out infinite alternate;
        }
        .hh-glow2{
            position:absolute;right:55px;bottom:-55px;width:380px;height:360px;border-radius:50%;
            background:radial-gradient(circle, rgba(45,212,191,.22) 0%, rgba(13,148,136,.06) 58%, transparent 78%);
            filter:blur(66px);pointer-events:none;z-index:1;
            animation:hhDriftB 22s ease-in-out infinite alternate;
        }
        .hh-inner{position:relative;z-index:2;display:flex;align-items:flex-end;justify-content:space-between;gap:28px;padding:0 42px;min-height:296px}
        .hh-left{min-width:0;flex:1;padding:26px 0;align-self:center}
        .hh-date{
            display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:999px;
            background:rgba(3,22,26,.58);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
            border:1px solid rgba(94,234,212,.26);
            font-size:11.5px;font-weight:600;color:#ccfbf1;
            font-variant-numeric:tabular-nums;
            box-shadow:0 4px 14px rgba(0,0,0,.22),inset 0 1px 0 rgba(255,255,255,.1);
            transition:border-color .22s ease,background-color .22s ease;
        }
        .hh-date:hover{border-color:rgba(94,234,212,.45);background:rgba(4,28,33,.72)}
        .hh-date-dot{width:6px;height:6px;border-radius:50%;background:#2dd4bf;box-shadow:0 0 8px #2dd4bf;flex-shrink:0;animation:hhBreathe 2.6s ease-in-out infinite}
        .hh-hello-wrap{display:flex;align-items:center;gap:12px;margin:15px 0 6px}
        .hh-hello{margin:0;font-size:11px;font-weight:700;letter-spacing:.28em;text-transform:uppercase;color:rgba(204,251,241,.82)}
        .hh-hello-line{
            width:38px;height:2px;border-radius:999px;
            background:linear-gradient(90deg,#5eead4 0%,rgba(45,212,191,.15) 100%);
            box-shadow:0 0 10px rgba(94,234,212,.6);
            transform-origin:left center;
            animation:hhLineDraw .85s cubic-bezier(.16,1,.3,1) .18s both;
        }
        .hh-name{
            margin:0;font-family:var(--font-display);font-weight:800;text-transform:uppercase;
            line-height:1.02;letter-spacing:-.03em;font-size:clamp(32px,3.8vw,48px);
        }
        .hh-name .a{display:inline;color:#ffffff;text-shadow:0 2px 18px rgba(0,0,0,.28)}
        .hh-name .b{
            display:inline;margin-left:7px;color:#5eead4;
            background:linear-gradient(110deg,#ccfbf1 0%,#5eead4 35%,#2dd4bf 65%,#99f6e4 100%);
            background-size:220% 100%;
            -webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;
            -webkit-box-decoration-break:clone;box-decoration-break:clone;
            padding-bottom:2px;
            animation:hhShimmerText 9s ease-in-out infinite;
        }
        .hh-sub{
            margin:9px 0 0;font-size:13px;font-style:normal;font-weight:400;line-height:1.5;
            color:rgba(204,251,241,.76);max-width:460px;letter-spacing:.005em;
        }
        .hh-pills{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:16px}
        .hh-pill{
            display:inline-flex;align-items:center;gap:6.5px;padding:5.5px 12.5px;border-radius:999px;
            font-size:11.5px;font-weight:600;
            background:rgba(3,20,24,.62);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
            color:rgba(226,232,240,.92);border:1px solid rgba(153,246,228,.16);
            box-shadow:inset 0 1px 0 rgba(255,255,255,.06);
            transition:border-color .2s ease,background-color .2s ease,color .2s ease;
        }
        .hh-pill:hover{border-color:rgba(94,234,212,.42);background:rgba(6,34,39,.78);color:#fff}
        .hh-pill svg{color:#5eead4;opacity:.85}
        .hh-pill.ok{background:rgba(4,28,31,.76);color:#fff;border-color:rgba(52,211,153,.38);font-weight:700}
        .hh-dot{width:6.5px;height:6.5px;border-radius:50%;background:#10b981;box-shadow:0 0 10px #10b981;animation:hhBreathe 2.4s ease-in-out infinite}
        /* Glossy CTA Button — steady click target (no position shift on hover), light-based feedback */
        .hh-cta{
            position:relative;overflow:hidden;
            display:inline-flex;align-items:center;gap:9px;white-space:nowrap;margin-top:18px;
            padding:10px 22px;border-radius:999px;font-size:12.5px;font-weight:700;color:#fff;text-decoration:none;
            background:linear-gradient(135deg,#18c4c4 0%,#0d9488 60%,#0f766e 100%);
            border:1px solid rgba(153,246,228,.45);
            box-shadow:0 10px 24px -6px rgba(13,148,136,.62),inset 0 1px 0 rgba(255,255,255,.42),inset 0 -1px 0 rgba(2,22,25,.28);
            transition:filter .18s cubic-bezier(.2,0,0,1),box-shadow .22s cubic-bezier(.2,0,0,1),border-color .18s ease;
        }
        .hh-cta::before{
            content:"";position:absolute;top:0;left:-120%;width:70%;height:100%;
            background:linear-gradient(110deg,transparent 0%,rgba(255,255,255,.32) 50%,transparent 100%);
            transform:skewX(-18deg);
            transition:left .65s cubic-bezier(.16,1,.3,1);
            pointer-events:none;
        }
        .hh-cta:hover{
            filter:brightness(1.08);
            border-color:rgba(204,251,241,.72);
            box-shadow:0 12px 30px -4px rgba(24,196,196,.72),0 0 18px -2px rgba(94,234,212,.38),inset 0 1px 0 rgba(255,255,255,.55);
        }
        .hh-cta:hover::before{left:145%}
        .hh-cta:focus-visible{outline:2px solid #5eead4;outline-offset:3px}
        .hh-mobile-dots{display:none}
        .hh-right{position:relative;flex:none;width:356px;height:296px;margin-right:0;align-self:flex-end}
        /* Sculpted Luminous Portrait Halo */
        .hh-circle-layer{
            position:absolute;right:0;bottom:-20px;width:304px;height:304px;border-radius:50%;
            background:radial-gradient(circle at 32% 24%, #2dd4bf 0%, #14b8a6 38%, #0d7a72 72%, #053e3d 100%);
            opacity:.95;
            box-shadow:
                0 0 55px -6px rgba(20,184,166,.48),
                inset 0 2px 22px rgba(255,255,255,.34),
                inset 0 -22px 38px rgba(2,20,23,.52);
            border:1px solid rgba(153,246,228,.35);
            z-index:1;
        }
        .hh-circle-ring{
            position:absolute;right:-19px;bottom:-39px;width:342px;height:342px;border-radius:50%;
            border:1px solid rgba(94,234,212,.3);
            pointer-events:none;z-index:1;
        }
        .hh-arc-overlay{
            position:absolute;right:-42px;bottom:-62px;width:388px;height:388px;border-radius:50%;
            border:1px dashed rgba(94,234,212,.22);
            pointer-events:none;z-index:1;
            animation:hhOrbitSpin 72s linear infinite;
        }
        .hh-flare{
            position:absolute;left:34px;top:98px;width:6px;height:6px;border-radius:50%;
            background:#fff;
            box-shadow:0 0 14px 4px rgba(153,246,228,.9),0 0 28px 10px rgba(20,184,166,.55);
            pointer-events:none;z-index:2;
            animation:hhFlarePulse 3.6s ease-in-out infinite;
        }
        .hh-photo{position:absolute;left:24px;right:-24px;bottom:-46px;height:316px;z-index:2;display:flex;align-items:flex-end;justify-content:center;pointer-events:none}
        .hh-photo img{
            width:100%;height:100%;object-fit:contain;object-position:center bottom;display:block;
            transform:scale(1.02);transform-origin:50% 100%;
            filter:drop-shadow(0 16px 26px rgba(1,16,19,.62));
        }
        .hh-initials{position:absolute;right:0;bottom:-20px;width:304px;height:304px;border-radius:50%;display:none;align-items:center;justify-content:center;font-size:64px;font-weight:900;color:rgba(255,255,255,.95);z-index:2}
        .hh-initials.show{display:flex}
        /* Floating Glass Check-in Status Card */
        .hh-check{
            position:absolute;right:30px;bottom:18px;z-index:4;
            display:flex;align-items:center;gap:12px;padding:10px 16px;border-radius:18px;
            background:rgba(3,20,24,.78);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);
            border:1px solid rgba(94,234,212,.32);
            box-shadow:0 16px 34px rgba(0,0,0,.48),inset 0 1px 0 rgba(255,255,255,.14);
            transition:border-color .25s ease,background-color .25s ease,box-shadow .25s ease;
        }
        .hh-check:hover{
            border-color:rgba(94,234,212,.55);
            background:rgba(4,26,31,.88);
            box-shadow:0 18px 38px rgba(0,0,0,.55),0 0 22px -6px rgba(45,212,191,.32),inset 0 1px 0 rgba(255,255,255,.2);
        }
        .hh-check .ic{position:relative;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(16,185,129,.16);flex-shrink:0}
        .hh-check .ic::before{content:"";position:absolute;inset:2px;border-radius:50%;border:1px solid rgba(52,211,153,.45);animation:hhPulseRing 2.6s cubic-bezier(.16,1,.3,1) infinite}
        .hh-check .ic i{width:9.5px;height:9.5px;border-radius:50%;background:#10b981;display:block;box-shadow:0 0 10px #10b981}
        .hh-check.no .ic{background:rgba(245,158,11,.16)}
        .hh-check.no .ic::before{border-color:rgba(251,191,36,.45)}
        .hh-check.no .ic i{background:#f59e0b;box-shadow:0 0 12px 2px rgba(245,158,11,.8)}
        .hh-check .chk-info{flex:1;min-width:0}
        .hh-check b{display:block;font-size:12px;font-weight:700;color:#fff;letter-spacing:.01em;font-variant-numeric:tabular-nums}
        .hh-check small{display:block;font-size:10.5px;color:rgba(153,246,228,.78);margin-top:1px}
        .hh-check-arrow{display:none}
        @media (min-width:768px) and (max-width:1100px){
            .hh-inner{padding:0 26px;min-height:274px;gap:18px}
            .hh-right{width:304px;height:274px;margin-right:0}
            .hh-circle-layer{width:264px;height:264px;bottom:-14px}
            .hh-circle-ring{width:294px;height:294px;bottom:-28px}
            .hh-arc-overlay{width:334px;height:334px;bottom:-46px}
            .hh-photo{left:20px;right:-20px;bottom:-40px;height:292px}
            .hh-photo img{transform:scale(1.02)}
            .hh-check{right:16px;bottom:14px}
        }
        @media (max-width:767px){
            .hh{border-radius:26px;margin-bottom:20px;box-shadow:0 18px 45px -8px rgba(1,20,23,.65);min-height:auto}
            .hh-spotlight{display:none}
            .hh-inner{position:relative;display:flex;flex-direction:column;align-items:stretch;justify-content:flex-start;padding:16px 16px 16px;gap:0;min-height:auto}
            .hh-left{position:relative;z-index:3;padding:0;align-self:stretch;text-align:left;max-width:58%}
            .hh-top-bar{position:relative;display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%;z-index:4;margin-bottom:12px}
            .hh-date{font-size:10.5px;padding:4.5px 11px;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;flex-shrink:0}
            .hh-date svg{width:13px;height:13px;flex-shrink:0}
            .hh-mobile-dots{display:flex;align-items:center;justify-content:center;padding-top:4px;padding-right:2px}
            .hh-mobile-dots .dots-grid{display:grid;grid-template-columns:repeat(2,4.5px);gap:5.5px}
            .hh-mobile-dots span{width:4.5px;height:4.5px;border-radius:50%;background:#5eead4;opacity:.9;box-shadow:0 0 6px rgba(94,234,212,.7)}
            .hh-hello-wrap{margin:6px 0 4px;gap:8px}
            .hh-hello{font-size:10.5px;letter-spacing:.22em;color:rgba(204,251,241,.85)}
            .hh-hello-line{width:28px;height:2px}
            .hh-name{font-size:clamp(24px,7.2vw,34px);line-height:1.05}
            .hh-sub{margin:8px 0 0;font-size:11.5px;line-height:1.45;max-width:100%}
            .hh-pills{display:flex;flex-direction:column;align-items:flex-start;gap:6px;margin-top:14px}
            .hh-pill{font-size:11px;padding:5px 11.5px;border-radius:999px;width:auto}
            .hh-cta{display:inline-flex;width:auto;align-self:flex-start;margin-top:14px;padding:9px 18px;font-size:11.5px;border-radius:999px}
            .hh-cta svg:last-child{margin-left:4px}

            /* Right Arch & Employee cutout on Mobile right side */
            .hh-right{position:absolute;right:0;top:0;bottom:70px;width:60%;height:auto;margin:0;align-self:auto;display:flex;align-items:flex-end;justify-content:flex-end;pointer-events:none;z-index:2;overflow:hidden}
            .hh-circle-layer{position:absolute;right:-24px;top:75px;bottom:0;left:auto;transform:none;width:190px;height:auto;border-radius:999px 999px 0 0;background:linear-gradient(180deg,#2dd4bf 0%,#14b8a6 48%,#095e5a 100%);box-shadow:0 -10px 35px rgba(20,184,166,.32),inset 0 2px 16px rgba(255,255,255,.3)}
            .hh-circle-ring{position:absolute;right:-38px;top:58px;bottom:0;left:auto;transform:none;width:220px;height:auto;border-radius:999px 999px 0 0;border:1px solid rgba(94,234,212,.32)}
            .hh-arc-overlay{position:absolute;right:-54px;top:42px;bottom:0;left:auto;transform:none;width:250px;height:auto;border-radius:999px 999px 0 0;border:1px dashed rgba(94,234,212,.2);animation:none}
            .hh-flare{left:auto;right:170px;top:150px;width:5.5px;height:5.5px}
            .hh-photo{position:absolute;right:-26px;left:auto;bottom:0;width:215px;height:100%;max-height:330px;display:flex;align-items:flex-end;justify-content:center}
            .hh-photo img{width:100%;height:100%;object-fit:contain;object-position:center bottom;transform:scale(1.0);transform-origin:50% 100%}
            .hh-initials{position:absolute;right:-24px;top:75px;bottom:0;left:auto;transform:none;width:190px;height:auto;border-radius:999px 999px 0 0;font-size:62px}

            /* Check-in floating pill card pinned closely under the CTA button */
            .hh-check{position:relative;left:auto;right:auto;bottom:auto;transform:none;margin:16px 0 0;width:100%;box-sizing:border-box;padding:12px 18px;border-radius:20px;background:rgba(3,20,24,.84);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(94,234,212,.32);box-shadow:0 14px 34px rgba(0,0,0,.55);z-index:4}
            .hh-check .ic{width:34px;height:34px}
            .hh-check .ic i{width:12px;height:12px}
            .hh-check b{font-size:12.5px}
            .hh-check small{font-size:11px}
            .hh-check-arrow{display:block;margin-left:auto;color:#99f6e4}
        }
        /* Choreographed Weighty Entrance & Subtle Ambient Animations (Expo-Out, Zero Bounce) */
        @keyframes hhStageIn{0%{opacity:0;transform:translate3d(0,12px,0) scale(.992)}100%{opacity:1;transform:translate3d(0,0,0) scale(1)}}
        @keyframes hhRevealUp{0%{opacity:0;transform:translate3d(0,18px,0)}100%{opacity:1;transform:translate3d(0,0,0)}}
        @keyframes hhLineDraw{0%{opacity:0;transform:scaleX(0)}100%{opacity:1;transform:scaleX(1)}}
        @keyframes hhHaloIn{0%{opacity:0;transform:translate3d(0,26px,0) scale(.94)}100%{opacity:1;transform:translate3d(0,0,0) scale(1)}}
        @keyframes hhPhotoRise{0%{opacity:0;transform:translate3d(0,32px,0) scale(.98)}100%{opacity:1;transform:translate3d(0,0,0) scale(1)}}
        @keyframes hhBadgeFloatIn{0%{opacity:0;transform:translate3d(0,16px,0) scale(.96)}100%{opacity:1;transform:translate3d(0,0,0) scale(1)}}
        @keyframes hhDriftA{0%{transform:translate3d(0,0,0) scale(1)}100%{transform:translate3d(32px,20px,0) scale(1.08)}}
        @keyframes hhDriftB{0%{transform:translate3d(0,0,0) scale(1)}100%{transform:translate3d(-28px,-18px,0) scale(1.06)}}
        @keyframes hhOrbitSpin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
        @keyframes hhFlarePulse{0%,100%{opacity:.72;transform:scale(1)}50%{opacity:1;transform:scale(1.22)}}
        @keyframes hhBreathe{0%,100%{opacity:.75;transform:scale(1)}50%{opacity:1;transform:scale(1.18)}}
        @keyframes hhPulseRing{0%{transform:scale(.85);opacity:.85}70%,100%{transform:scale(1.42);opacity:0}}
        @keyframes hhShimmerText{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}
        @keyframes hubCardSlide{0%{opacity:0;transform:translate3d(0,18px,0)}100%{opacity:1;transform:translate3d(0,0,0)}}

        .hh-date{animation:hhRevealUp .72s cubic-bezier(.16,1,.3,1) .06s both;will-change:transform,opacity}
        .hh-hello{animation:hhRevealUp .72s cubic-bezier(.16,1,.3,1) .13s both;will-change:transform,opacity}
        .hh-name{animation:hhRevealUp .86s cubic-bezier(.16,1,.3,1) .20s both;will-change:transform,opacity}
        .hh-sub{animation:hhRevealUp .78s cubic-bezier(.16,1,.3,1) .28s both;will-change:transform,opacity}
        .hh-pills .hh-pill{animation:hhRevealUp .72s cubic-bezier(.16,1,.3,1) both;will-change:transform,opacity}
        .hh-pills .hh-pill:nth-child(1){animation-delay:.34s}
        .hh-pills .hh-pill:nth-child(2){animation-delay:.40s}
        .hh-pills .hh-pill:nth-child(3){animation-delay:.46s}
        .hh-cta{animation:hhRevealUp .78s cubic-bezier(.16,1,.3,1) .50s both;will-change:transform,opacity}
        .hh-circle-layer{animation:hhHaloIn .92s cubic-bezier(.16,1,.3,1) .14s both;transform-origin:50% 100%;will-change:transform,opacity}
        .hh-circle-ring{animation:hhHaloIn .98s cubic-bezier(.16,1,.3,1) .18s both;transform-origin:50% 100%;will-change:transform,opacity}
        .hh-photo{animation:hhPhotoRise 1.02s cubic-bezier(.16,1,.3,1) .24s both;will-change:transform,opacity}
        .hh-check{animation:hhBadgeFloatIn .85s cubic-bezier(.16,1,.3,1) .44s both;will-change:transform,opacity}
        .hub-anim-section{overflow-x:clip}
        .hub-anim-d2{animation:hubCardSlide .72s cubic-bezier(.16,1,.3,1) .26s both}
        .hub-stagger > *{animation:hubCardSlide .72s cubic-bezier(.16,1,.3,1) both}
        .hub-stagger > *:nth-child(1){animation-delay:.16s}
        .hub-stagger > *:nth-child(2){animation-delay:.22s}
        .hub-stagger > *:nth-child(3){animation-delay:.28s}
        .hub-stagger > *:nth-child(4){animation-delay:.34s}
        .hub-stagger > *:nth-child(5){animation-delay:.40s}
        @media (prefers-reduced-motion:reduce){
            .hh,.hh-date,.hh-date-dot,.hh-hello,.hh-hello-line,.hh-name,.hh-name .b,.hh-sub,.hh-pills .hh-pill,.hh-dot,.hh-cta,.hh-glow1,.hh-glow2,.hh-circle-layer,.hh-circle-ring,.hh-arc-overlay,.hh-flare,.hh-photo,.hh-check,.hh-check .ic::before,.hub-anim-d2,.hub-stagger > *{animation:none!important;transition:none!important}
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

        /* Donut Chart & Stacked Progress Bar (Refined Executive Polish) */
        .kpi-donut-wrap{display:flex;align-items:center;gap:16px;padding:6px 0 14px}
        .kpi-donut-ring{position:relative;width:98px;height:98px;border-radius:9999px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 14px -4px rgba(15,23,42,.10);transition:transform .2s ease,box-shadow .2s ease}
        .kpi-donut-ring:hover{transform:scale(1.03);box-shadow:0 8px 20px -4px rgba(15,23,42,.14)}
        .kpi-donut-center{width:68px;height:68px;border-radius:9999px;background:#ffffff;display:flex;flex-direction:column;align-items:center;justify-content:center;box-shadow:inset 0 1px 4px rgba(15,23,42,.06)}
        .kpi-donut-num{font-size:22px;font-weight:800;color:#0f172a;line-height:1;letter-spacing:-0.02em}
        .kpi-donut-lbl{font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;margin-top:3px}

        :root{--donut-empty:#cbd5e1;--donut-zero:#e2e8f0}
        html.dark{--donut-empty:#334155;--donut-zero:#1e293b}
        /* Dark Mode Support for Dashboard KPI Cards, Donut Charts & Calendar */
        html.dark .kpi-card{background:#131c2e!important;border-color:rgba(51,65,85,.85)!important;box-shadow:0 12px 28px -6px rgba(0,0,0,.45),0 2px 6px -1px rgba(0,0,0,.3)!important}
        html.dark .kpi-hdr-title{color:#f8fafc!important}
        html.dark .kpi-hdr-sub{color:#94a3b8!important}
        html.dark .kpi-hdr-icon{background:rgba(20,184,166,.16)!important;color:#2dd4bf!important;border:1px solid rgba(45,212,191,.28)!important}
        html.dark .kpi-badge-aktif{background:rgba(16,185,129,.16)!important;color:#34d399!important;border-color:rgba(16,185,129,.35)!important}
        html.dark .kpi-dropdown-btn{background:#1e293b!important;color:#e2e8f0!important;border-color:#334155!important}
        html.dark .kpi-donut-ring{box-shadow:0 4px 16px -2px rgba(0,0,0,.5)!important}
        html.dark .kpi-donut-center{background:#131c2e!important;box-shadow:inset 0 1px 5px rgba(0,0,0,.45)!important}
        html.dark .kpi-donut-num{color:#f8fafc!important}
        html.dark .kpi-donut-lbl{color:#94a3b8!important}
        html.dark .kpi-legend-item{background:#162235!important;border-color:rgba(51,65,85,.75)!important}
        html.dark .kpi-legend-item:hover{background:#1e293b!important}
        html.dark .kpi-legend-left{color:#cbd5e1!important}
        html.dark .kpi-legend-val-green{background:rgba(16,185,129,.18)!important;color:#6ee7b7!important}
        html.dark .kpi-legend-val-red{background:rgba(244,63,94,.18)!important;color:#fda4af!important}
        html.dark .kpi-legend-val-amber{background:rgba(245,158,11,.18)!important;color:#fcd34d!important}
        html.dark .kpi-legend-val-blue{background:rgba(59,130,246,.18)!important;color:#93c5fd!important}
        html.dark .kpi-detail-row{color:#94a3b8!important}
        html.dark .kpi-detail-row strong{color:#f1f5f9!important}
        html.dark .kpi-card-footer{border-top-color:rgba(51,65,85,.8)!important}
        html.dark .kpi-btn-cta{background:rgba(20,184,166,.16)!important;color:#5eead4!important;border-color:rgba(45,212,191,.32)!important}
        html.dark .kpi-btn-cta:hover{background:rgba(20,184,166,.26)!important}
        html.dark .kpi-abs-box-teal{background:rgba(20,184,166,.14)!important;border-color:rgba(45,212,191,.3)!important}
        html.dark .kpi-abs-box-amber{background:rgba(245,158,11,.14)!important;border-color:rgba(245,158,11,.3)!important}
        html.dark .kpi-abs-num{color:#f8fafc!important}
        html.dark .kpi-abs-unit{color:#5eead4!important}
        html.dark .kpi-abs-money{color:#fcd34d!important}
        html.dark .kpi-abs-icon-teal{background:rgba(20,184,166,.22)!important;color:#5eead4!important}
        html.dark .kpi-abs-icon-amber{background:rgba(245,158,11,.22)!important;color:#fcd34d!important}
        html.dark .kpi-pbar-track{background:#1e293b!important;border-color:#334155!important}
        html.dark .kpi-pbar-legend{color:#94a3b8!important}
        html.dark .kpi-box-num{color:#f8fafc!important}
        html.dark .kpi-box-mint{background:rgba(16,185,129,.14)!important;border-color:rgba(16,185,129,.35)!important}
        html.dark .kpi-box-mint .kpi-box-label{color:#6ee7b7!important}
        html.dark .kpi-box-amber{background:rgba(245,158,11,.14)!important;border-color:rgba(245,158,11,.35)!important}
        html.dark .kpi-box-amber .kpi-box-label{color:#fcd34d!important}
        html.dark .kpi-box-blue{background:rgba(59,130,246,.14)!important;border-color:rgba(59,130,246,.35)!important}
        html.dark .kpi-box-blue .kpi-box-label{color:#93c5fd!important}
        html.dark .kpi-box-rose{background:rgba(244,63,94,.14)!important;border-color:rgba(244,63,94,.35)!important}
        html.dark .kpi-box-rose .kpi-box-label{color:#fda4af!important}
        html.dark .kpi-box-slate{background:#162235!important;border-color:#334155!important}
        html.dark .kpi-box-slate .kpi-box-label{color:#cbd5e1!important}
        html.dark .kpi-box-purple{background:rgba(168,85,247,.14)!important;border-color:rgba(168,85,247,.35)!important}
        html.dark .kpi-box-purple .kpi-box-label{color:#d8b4fe!important}
        html.dark .cal-tile-present{background-color:rgba(16,185,129,.18)!important;color:#6ee7b7!important;border-color:rgba(16,185,129,.4)!important}
        html.dark .cal-tile-late{background-color:rgba(245,158,11,.18)!important;color:#fcd34d!important;border-color:rgba(245,158,11,.4)!important}
        html.dark .cal-tile-absent{background-color:rgba(244,63,94,.18)!important;color:#fda4af!important;border-color:rgba(244,63,94,.4)!important}
        html.dark .cal-tile-off{background-color:#1e293b!important;color:#64748b!important;border-color:#334155!important}
        html.dark .cal-tile-future{background-color:#0f172a!important;color:#64748b!important;border-color:#334155!important}

        .kpi-legend-list{flex:1;display:flex;flex-direction:column;gap:6px;min-width:0}
        .kpi-legend-item{display:flex;align-items:center;justify-content:space-between;padding:5px 10px;border-radius:10px;background:#f8fafc;border:1px solid #f1f5f9;font-size:11.5px;transition:background .15s ease}
        .kpi-legend-item:hover{background:#f1f5f9}
        .kpi-legend-left{display:flex;align-items:center;gap:8px;min-width:0;color:#475569;font-weight:600}
        .kpi-legend-dot{width:8px;height:8px;border-radius:9999px;flex-shrink:0}
        .kpi-legend-dot-green{background:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.16)}
        .kpi-legend-dot-red{background:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.15)}
        .kpi-legend-dot-amber{background:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.18)}
        .kpi-legend-dot-blue{background:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.18)}
        .kpi-legend-val{font-size:11.5px;font-weight:700;padding:1px 8px;border-radius:9999px;min-width:24px;text-align:center;flex-shrink:0;margin-left:6px}
        .kpi-legend-val-green{background:#ecfdf5;color:#047857}
        .kpi-legend-val-red{background:#fff1f2;color:#be123c}
        .kpi-legend-val-amber{background:#fffbeb;color:#b45309}
        .kpi-legend-val-blue{background:#eff6ff;color:#1d4ed8}

        .kpi-abs-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-bottom:14px}
        .kpi-abs-box{padding:10px 12px;border-radius:14px;display:flex;align-items:center;justify-content:space-between;gap:8px;min-width:0}
        .kpi-abs-box-teal{background:#f0fdfa;border:1px solid #ccfbf1}
        .kpi-abs-box-amber{background:#fffbeb;border:1px solid #fef3c7}
        .kpi-abs-lbl{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;display:block;line-height:1.2}
        .kpi-abs-val-row{display:flex;align-items:baseline;gap:4px;margin-top:4px}
        .kpi-abs-num{font-size:20px;font-weight:800;color:#0f172a;line-height:1;letter-spacing:-0.02em}
        .kpi-abs-unit{font-size:11px;font-weight:600;color:#0f766e}
        .kpi-abs-money{font-size:14px;font-weight:800;color:#92400e;line-height:1.1;margin-top:5px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .kpi-abs-icon{width:30px;height:30px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .kpi-abs-icon-teal{background:#ccfbf1;color:#0f766e}
        .kpi-abs-icon-amber{background:#fef3c7;color:#d97706}

        .kpi-pbar-track{height:10px;width:100%;background:#f1f5f9;border-radius:9999px;overflow:hidden;display:flex;gap:2px;padding:1px;border:1px solid #e2e8f0;margin-bottom:10px}
        .kpi-pbar-seg{height:100%;border-radius:9999px;transition:width .5s cubic-bezier(.22,1,.36,1)}
        .kpi-pbar-legend{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;font-size:11px;color:#64748b;margin-bottom:12px}
        .kpi-pbar-item{display:inline-flex;align-items:center;gap:5px;font-weight:500}
        .kpi-pbar-dot{width:7px;height:7px;border-radius:9999px;flex-shrink:0}

        /* Kalender Kehadiran Bulanan (Self-contained 7-column grid to ensure it never breaks) */
        .cal-grid-7 {
            display: grid !important;
            grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
            gap: 6px !important;
            width: 100% !important;
        }
        @media (min-width: 640px) {
            .cal-grid-7 {
                gap: 8px !important;
            }
        }
        .cal-day-header {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            text-align: center;
            padding: 2px 0;
        }
        .cal-tile {
            aspect-ratio: 1 / 1 !important;
            width: 100% !important;
            border-radius: 12px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            user-select: none !important;
            box-sizing: border-box !important;
            transition: transform .15s ease, box-shadow .15s ease !important;
            cursor: pointer !important;
        }
        @media (min-width: 640px) {
            .cal-tile {
                border-radius: 14px !important;
                font-size: 14px !important;
            }
        }
        .cal-tile:hover {
            transform: scale(1.06) !important;
            z-index: 2 !important;
        }
        .cal-tile-present {
            background-color: #ecfdf5 !important;
            color: #047857 !important;
            border: 1px solid #a7f3d0 !important;
        }
        .cal-tile-late {
            background-color: #fffbeb !important;
            color: #b45309 !important;
            border: 1px solid #fde68a !important;
        }
        .cal-tile-absent {
            background-color: #fef2f2 !important;
            color: #ef4444 !important;
            border: 1px solid #fecaca !important;
        }
        .cal-tile-off {
            background-color: #f1f5f9 !important;
            color: #94a3b8 !important;
            border: 1px solid #e2e8f0 !important;
        }
        .cal-tile-future {
            background-color: #ffffff !important;
            color: #94a3b8 !important;
            border: 1px dashed #cbd5e1 !important;
        }
        .cal-tile-today {
            box-shadow: 0 0 0 2.5px #3b82f6 !important;
            border-color: transparent !important;
        }
        .cal-dot-today {
            position: absolute !important;
            top: 3px !important;
            right: 3px !important;
            width: 5px !important;
            height: 5px !important;
            border-radius: 9999px !important;
            background-color: #2563eb !important;
        }
        @media (min-width: 640px) {
            .cal-dot-today {
                top: 4px !important;
                right: 4px !important;
                width: 6px !important;
                height: 6px !important;
            }
        }

        /* GoMSN Sculpted Ocean Teal PWA Install Banner */
        .pwa-banner {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            margin-bottom: 20px;
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border: 1px solid rgba(45, 212, 191, .28);
            background: linear-gradient(100deg, #022029 0%, #053540 35%, #094f5c 70%, #0d6270 100%);
            box-shadow: 0 12px 28px -8px rgba(2, 32, 41, .45), inset 0 1px 0 rgba(94, 234, 212, .24);
            color: #ffffff;
            user-select: none;
        }
        .pwa-banner.is-hidden {
            display: none !important;
        }
        @media (min-width: 768px) {
            .pwa-banner {
                display: none !important;
            }
        }
        .pwa-rim {
            position: absolute;
            top: 0;
            left: 24px;
            right: 24px;
            height: 1.5px;
            background: linear-gradient(90deg, transparent, rgba(94, 234, 212, .65), transparent);
            pointer-events: none;
        }
        .pwa-glow {
            position: absolute;
            right: -30px;
            top: -40px;
            width: 220px;
            height: 140px;
            border-radius: 50%;
            background: rgba(24, 196, 196, .16);
            filter: blur(42px);
            pointer-events: none;
        }
        .pwa-wave-svg {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            height: 100%;
            width: 55%;
            opacity: .36;
            pointer-events: none;
        }
        .pwa-left {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
            flex: 1;
        }
        .pwa-phone {
            width: 34px;
            height: 56px;
            border-radius: 10px;
            background: #071b21;
            border: 1.5px solid rgba(94, 234, 212, .4);
            padding: 3px;
            flex-shrink: 0;
            box-shadow: 0 6px 14px rgba(0, 0, 0, .38), 0 0 14px rgba(20, 184, 166, .22);
            display: flex;
            align-items: stretch;
            justify-content: stretch;
        }
        .pwa-phone-screen {
            flex: 1;
            border-radius: 6.5px;
            background: #ffffff;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 7px 3px 3px;
            overflow: hidden;
        }
        .pwa-phone-notch {
            position: absolute;
            top: 3px;
            left: 50%;
            transform: translateX(-50%);
            width: 11px;
            height: 2.5px;
            border-radius: 999px;
            background: #0f172a;
        }
        .pwa-phone-screen img {
            width: 100%;
            height: auto;
            max-height: 28px;
            object-fit: contain;
            display: block;
        }
        .pwa-text {
            min-width: 0;
        }
        .pwa-title {
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }
        .pwa-sub {
            font-size: 12px;
            color: rgba(204, 251, 241, .82);
            margin: 3px 0 0;
            line-height: 1.4;
        }
        .pwa-cta {
            position: relative;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, #18C4C4 0%, #0D9488 100%);
            border: 1px solid rgba(153, 246, 228, .42);
            box-shadow: 0 6px 18px -4px rgba(20, 184, 166, .58), inset 0 1px 0 rgba(255, 255, 255, .32);
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
            transition: transform .15s ease, filter .15s ease, box-shadow .15s ease;
        }
        .pwa-cta:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
            box-shadow: 0 8px 22px -4px rgba(24, 196, 196, .68), inset 0 1px 0 rgba(255, 255, 255, .4);
        }
        .pwa-cta:active {
            transform: scale(.98);
        }
        .pwa-cta:disabled {
            opacity: .75;
            cursor: wait;
            transform: none;
        }
        @media (max-width: 639px) {
            .pwa-banner {
                flex-direction: column;
                align-items: stretch;
                padding: 14px 16px;
                gap: 12px;
                border-radius: 20px;
            }
            .pwa-cta {
                width: 100%;
                min-height: 40px;
            }
        }
    </style>
    <div class="hh" id="hub-hero-banner">
        <!-- Top Specular Rim, Hairline Grid & Interactive Spotlight -->
        <div class="hh-rim" aria-hidden="true"></div>
        <div class="hh-grid-bg" aria-hidden="true"></div>
        <div class="hh-spotlight" aria-hidden="true"></div>

        <!-- SVG Futuristic Background Geometry (Diagonal Glass Beams, Concentric Orbit Rings, Grid Dots) -->
        <svg class="hh-bg-svg" viewBox="0 0 1100 296" fill="none" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <linearGradient id="hhDiag1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.28"/>
                    <stop offset="60%" stop-color="#14b8a6" stop-opacity="0.08"/>
                    <stop offset="100%" stop-color="#021417" stop-opacity="0"/>
                </linearGradient>
                <linearGradient id="hhDiag2" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#5eead4" stop-opacity="0.18"/>
                    <stop offset="100%" stop-color="#0d9488" stop-opacity="0"/>
                </linearGradient>
                <linearGradient id="hhArcGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#99f6e4" stop-opacity="0.42"/>
                    <stop offset="50%" stop-color="#14b8a6" stop-opacity="0.18"/>
                    <stop offset="100%" stop-color="#042f2e" stop-opacity="0.04"/>
                </linearGradient>
                <pattern id="hhDotGrid" x="0" y="0" width="18" height="18" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.4" fill="#5eead4" fill-opacity="0.26"/>
                </pattern>
            </defs>
            <!-- Left Top Diagonal Glass Beams -->
            <path d="M-80 -40 L90 -40 L-20 205 L-190 205 Z" fill="url(#hhDiag1)"/>
            <path d="M-20 -40 L45 -40 L-85 225 L-150 225 Z" fill="url(#hhDiag2)"/>
            <path d="M50 -40 L85 -40 L-40 205 L-75 205 Z" fill="url(#hhDiag2)" opacity="0.55"/>

            <!-- Right Top Accent Wave Ribbon -->
            <path d="M790 0 C865 100, 940 155, 1100 205 L1100 0 Z" fill="url(#hhDiag1)" opacity="0.42"/>

            <!-- Large Concentric Orbit Rims Centered at Employee Portrait Circle -->
            <circle cx="920" cy="178" r="262" stroke="url(#hhArcGrad)" stroke-width="1.1" opacity="0.58"/>
            <circle cx="920" cy="178" r="338" stroke="url(#hhArcGrad)" stroke-width="0.95" stroke-dasharray="8 6" opacity="0.38"/>
            <circle cx="920" cy="178" r="418" stroke="url(#hhArcGrad)" stroke-width="0.8" opacity="0.26"/>

            <!-- Tech Dot Matrix Grids (Top Right & Bottom Left) -->
            <rect x="990" y="20" width="80" height="48" fill="url(#hhDotGrid)"/>
            <rect x="24" y="232" width="80" height="48" fill="url(#hhDotGrid)"/>
        </svg>

        <div class="hh-glow1" aria-hidden="true"></div>
        <div class="hh-glow2" aria-hidden="true"></div>
        <div class="hh-inner">
            <div class="hh-left">
                <div class="hh-top-bar">
                    <div class="hh-date">
                        <span class="hh-date-dot" aria-hidden="true"></span>
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
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
                        ID: {{ $user->employee_code ?? ($user->nik ?? 'MSN') }}
                    </span>
                    @if ($user->division)
                        <span class="hh-pill">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $user->division }}
                        </span>
                    @endif
                </div>
                <a href="{{ route('portal.profile') }}" class="hh-cta">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Profil Saya</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
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
            if (el) {
                var days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                function tick() {
                    var parts = new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jakarta', hour12: false, year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', weekday: 'short' }).formatToParts(new Date());
                    var m = {}; parts.forEach(function (p) { m[p.type] = p.value; });
                    var dow = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].indexOf(m.weekday);
                    el.textContent = days[dow] + ', ' + String(m.day).padStart(2, '0') + ' ' + months[parseInt(m.month, 10) - 1] + ' ' + m.year + ' · ' + m.hour + ':' + m.minute + ':' + m.second + ' WIB';
                }
                tick(); setInterval(tick, 1000);
            }

            // Interactive subtle cursor spotlight on Hero (Desktop + no reduced-motion)
            var hero = document.getElementById('hub-hero-banner');
            var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var pointerFine = window.matchMedia && window.matchMedia('(pointer: fine)').matches;
            if (hero && !reduced && pointerFine) {
                var rafId = null;
                hero.addEventListener('pointermove', function (e) {
                    if (rafId) return;
                    rafId = window.requestAnimationFrame(function () {
                        var rect = hero.getBoundingClientRect();
                        var x = ((e.clientX - rect.left) / rect.width) * 100;
                        var y = ((e.clientY - rect.top) / rect.height) * 100;
                        hero.style.setProperty('--mx', x.toFixed(1) + '%');
                        hero.style.setProperty('--my', y.toFixed(1) + '%');
                        rafId = null;
                    });
                }, { passive: true });
            }

            window.addEventListener('pageshow', function (e) {
                if (e.persisted) {
                    var nodes = document.querySelectorAll('.hh, .hh-date, .hh-hello, .hh-hello-line, .hh-name, .hh-sub, .hh-pills .hh-pill, .hh-cta, .hh-circle-layer, .hh-circle-ring, .hh-photo, .hh-check, .hub-anim-section, .hub-stagger > *');
                    nodes.forEach(function (n) {
                        n.style.animation = 'none';
                        void n.offsetHeight;
                        n.style.animation = '';
                    });
                }
            });
        })();
    </script>

    <!-- PWA App Install Banner (GoMSN Ocean Teal Theme - Mobile Only) -->
    <section id="pwa-install-banner" class="pwa-banner md:hidden hub-anim-d2" aria-label="Pasang Aplikasi Mobile PT MSN">
        <div class="pwa-rim" aria-hidden="true"></div>
        <div class="pwa-glow" aria-hidden="true"></div>
        <svg class="pwa-wave-svg" viewBox="0 0 360 90" fill="none" preserveAspectRatio="none" aria-hidden="true">
            <path d="M 0 90 C 90 25 190 70 360 12 L 360 90 Z" fill="url(#pwaBannerWave)"/>
            <path d="M 45 90 C 135 20 225 58 360 22" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.4" fill="none"/>
            <defs>
                <linearGradient id="pwaBannerWave" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.08"/>
                    <stop offset="65%" stop-color="#2dd4bf" stop-opacity="0.22"/>
                    <stop offset="100%" stop-color="#5eead4" stop-opacity="0.32"/>
                </linearGradient>
            </defs>
        </svg>

        <div class="pwa-left">
            <div class="pwa-phone" aria-hidden="true">
                <div class="pwa-phone-screen">
                    <span class="pwa-phone-notch"></span>
                    <img src="{{ asset('assets/logo/logo-msn2.png') }}" alt="">
                </div>
            </div>
            <div class="pwa-text">
                <h2 class="pwa-title">Aplikasi Mobile PT MSN</h2>
                <p id="pwa-banner-sub" class="pwa-sub">Pasang aplikasi untuk akses cepat, praktis, dan notifikasi langsung di HP.</p>
            </div>
        </div>

        <button type="button" id="pwa-install-btn" class="pwa-cta" aria-label="Pasang Aplikasi">
            <svg id="pwa-btn-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
            </svg>
            <span id="pwa-btn-text">Pasang Aplikasi</span>
        </button>
    </section>
    <script>
        (function () {
            var banner = document.getElementById('pwa-install-banner');
            var btn = document.getElementById('pwa-install-btn');
            var btnText = document.getElementById('pwa-btn-text');
            var btnIcon = document.getElementById('pwa-btn-icon');
            var subText = document.getElementById('pwa-banner-sub');
            if (!banner || !btn) return;

            // In standalone PWA mode, hide banner because user is already using the installed mobile app
            var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            if (isStandalone) {
                banner.classList.add('is-hidden');
                return;
            }

            function checkInstalled() {
                var stored = false;
                try {
                    stored = localStorage.getItem('gomsn_pwa_installed') === '1';
                } catch (e) {}
                return Boolean(window.__pwaIsInstalled || stored);
            }

            function updateBannerUI() {
                if (checkInstalled()) {
                    banner.classList.remove('is-hidden');
                    if (btnText) btnText.textContent = 'Buka Aplikasi';
                    if (subText) subText.textContent = 'Aplikasi sudah terpasang di perangkat Anda. Buka aplikasi untuk akses instan.';
                    if (btnIcon) {
                        btnIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>';
                    }
                    btn.setAttribute('aria-label', 'Buka Aplikasi Mobile PT MSN');
                } else {
                    banner.classList.remove('is-hidden');
                    if (btnText) btnText.textContent = 'Pasang Aplikasi';
                    if (subText) subText.textContent = 'Pasang aplikasi untuk akses cepat, praktis, dan notifikasi langsung di HP.';
                    if (btnIcon) {
                        btnIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>';
                    }
                    btn.setAttribute('aria-label', 'Pasang Aplikasi Mobile PT MSN');
                }
            }

            updateBannerUI();

            window.addEventListener('pwa:installed', updateBannerUI);
            window.addEventListener('pwa:installed-status-changed', updateBannerUI);

            btn.addEventListener('click', async function () {
                if (checkInstalled()) {
                    if (typeof window.openPwaApp === 'function') {
                        window.openPwaApp();
                    } else {
                        window.location.href = '/?source=pwa_banner_open';
                    }
                    return;
                }

                btn.disabled = true;
                try {
                    if (typeof window.triggerPwaInstall === 'function') {
                        var outcome = await window.triggerPwaInstall();
                        if (outcome === 'accepted' || outcome === 'installed') {
                            updateBannerUI();
                        }
                    }
                } finally {
                    btn.disabled = false;
                }
            });
        })();
    </script>

    <!-- Section 1: KPI Summary Cards Matching Reference -->
    <section class="mb-8 hub-anim-section hub-anim-d1" aria-labelledby="heading-stats-kpi">
        <h2 id="heading-stats-kpi" class="sr-only">Ringkasan Statistik Utama</h2>

        @php
            $buildDonutStyle = function ($total, $approved, $rejected, $pending) {
                $total = (int) $total;
                $approved = (int) $approved;
                $rejected = (int) $rejected;
                $pending = (int) $pending;

                if ($total <= 0) {
                    return 'var(--donut-zero, #e2e8f0)';
                }

                $appDeg = ($approved / $total) * 360;
                $rejDeg = ($rejected / $total) * 360;
                $pendDeg = ($pending / $total) * 360;

                $cur = 0;
                $parts = [];
                if ($approved > 0) {
                    $next = round($cur + $appDeg, 1);
                    $parts[] = "#10b981 {$cur}deg {$next}deg";
                    $cur = $next;
                }
                if ($rejected > 0) {
                    $next = round($cur + $rejDeg, 1);
                    $parts[] = "#ef4444 {$cur}deg {$next}deg";
                    $cur = $next;
                }
                if ($pending > 0) {
                    $next = round($cur + $pendDeg, 1);
                    $parts[] = "#f59e0b {$cur}deg {$next}deg";
                    $cur = $next;
                }

                if ($cur < 360) {
                    $parts[] = "var(--donut-empty, #cbd5e1) {$cur}deg 360deg";
                }

                return count($parts) > 0 ? 'conic-gradient(' . implode(', ', $parts) . ')' : 'var(--donut-zero, #e2e8f0)';
            };

            $buildAttDonutStyle = function ($ontime, $late, $leave, $absent) {
                $total = (int)$ontime + (int)$late + (int)$leave + (int)$absent;
                if ($total <= 0) {
                    return 'var(--donut-zero, #e2e8f0)';
                }

                $ontimeDeg = ($ontime / $total) * 360;
                $lateDeg = ($late / $total) * 360;
                $leaveDeg = ($leave / $total) * 360;
                $absentDeg = ($absent / $total) * 360;

                $cur = 0;
                $parts = [];
                if ($ontime > 0) {
                    $next = round($cur + $ontimeDeg, 1);
                    $parts[] = "#10b981 {$cur}deg {$next}deg";
                    $cur = $next;
                }
                if ($late > 0) {
                    $next = round($cur + $lateDeg, 1);
                    $parts[] = "#f59e0b {$cur}deg {$next}deg";
                    $cur = $next;
                }
                if ($leave > 0) {
                    $next = round($cur + $leaveDeg, 1);
                    $parts[] = "#3b82f6 {$cur}deg {$next}deg";
                    $cur = $next;
                }
                if ($absent > 0) {
                    $next = round($cur + $absentDeg, 1);
                    $parts[] = "#ef4444 {$cur}deg {$next}deg";
                    $cur = $next;
                }

                if ($cur < 360) {
                    $parts[] = "var(--donut-empty, #cbd5e1) {$cur}deg 360deg";
                }

                return count($parts) > 0 ? 'conic-gradient(' . implode(', ', $parts) . ')' : 'var(--donut-zero, #e2e8f0)';
            };
        @endphp
        
        @if ($stats['scope']['type'] === 'personal')
        {{-- TAMPILAN KHUSUS KARYAWAN: PENGAJUAN, LEMBUR, ABSEN (DONUT CHARTS & STACKED PROGRESS BAR) --}}

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 hub-stagger">
            
            <!-- Card 1: Pengajuan Saya (Donut Chart + Vertical Legend) -->
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

                    @php
                        $pTotal = (int) ($stats['pengajuan']['total'] ?? 0);
                        $pApproved = (int) ($stats['pengajuan']['approved'] ?? 0);
                        $pRejected = (int) ($stats['pengajuan']['rejected'] ?? 0);
                        $pPending = (int) ($stats['pengajuan']['pending'] ?? 0);
                        $donutPengajuan = $buildDonutStyle($pTotal, $pApproved, $pRejected, $pPending);
                    @endphp

                    <!-- Donut Chart & Vertical Legend -->
                    <div class="kpi-donut-wrap">
                        <!-- Sisi Kiri: Donut Chart -->
                        <div class="kpi-donut-ring" style="background: {{ $donutPengajuan }};" title="Total Pengajuan: {{ $pTotal }}">
                            <div class="kpi-donut-center">
                                <span class="kpi-donut-num">{{ $pTotal }}</span>
                                <span class="kpi-donut-lbl">Total</span>
                            </div>
                        </div>

                        <!-- Sisi Kanan: Vertical Legend -->
                        <div class="kpi-legend-list">
                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-green"></span>
                                    <span class="truncate">Disetujui</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-green">{{ $pApproved }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-red"></span>
                                    <span class="truncate">Ditolak</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-red">{{ $pRejected }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-amber"></span>
                                    <span class="truncate">Menunggu</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-amber">{{ $pPending }}</span>
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

            <!-- Card 2: Lembur Saya (Donut Chart + Vertical Legend) -->
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

                    @php
                        $lTotal = (int) ($stats['lembur']['total'] ?? 0);
                        $lApproved = (int) ($stats['lembur']['approved'] ?? 0);
                        $lRejected = (int) ($stats['lembur']['rejected'] ?? 0);
                        $lPending = (int) ($stats['lembur']['pending'] ?? 0);
                        $donutLembur = $buildDonutStyle($lTotal, $lApproved, $lRejected, $lPending);
                    @endphp

                    <!-- Donut Chart & Vertical Legend -->
                    <div class="kpi-donut-wrap">
                        <!-- Sisi Kiri: Donut Chart -->
                        <div class="kpi-donut-ring" style="background: {{ $donutLembur }};" title="Total Lembur: {{ $lTotal }}">
                            <div class="kpi-donut-center">
                                <span class="kpi-donut-num">{{ $lTotal }}</span>
                                <span class="kpi-donut-lbl">Total</span>
                            </div>
                        </div>

                        <!-- Sisi Kanan: Vertical Legend -->
                        <div class="kpi-legend-list">
                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-green"></span>
                                    <span class="truncate">Disetujui</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-green">{{ $lApproved }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-red"></span>
                                    <span class="truncate">Ditolak</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-red">{{ $lRejected }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-amber"></span>
                                    <span class="truncate">Menunggu</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-amber">{{ $lPending }}</span>
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

            <!-- Card 3: Absensi Saya (Stacked Progress Bar) -->
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

                    @php
                        $hadir = (int) ($stats['absensi']['month_present_days'] ?? 0);
                        $tepatWaktu = (int) ($stats['absensi']['month_ontime_days'] ?? $stats['absensi']['today_present'] ?? 0);
                        $terlambat = max(0, $hadir - $tepatWaktu);
                        $tidakHadir = (int) ($stats['absensi']['month_absent_days'] ?? 0);
                        $potonganVal = $stats['absensi']['month_total_deduction'] ?? 0;

                        $totalDays = max(1, $hadir + $tidakHadir);
                        $tepatPct = round(($tepatWaktu / $totalDays) * 100, 1);
                        $terlambatPct = round(($terlambat / $totalDays) * 100, 1);
                        $tidakHadirPct = round(($tidakHadir / $totalDays) * 100, 1);
                    @endphp

                    <!-- Bagian Atas: Total Hadir & Potongan Menonjol -->
                    <div class="kpi-abs-grid">
                        <!-- Total Hadir Box -->
                        <div class="kpi-abs-box kpi-abs-box-teal">
                            <div class="min-w-0">
                                <span class="kpi-abs-lbl" style="color:#0f766e;">Total Hadir</span>
                                <div class="kpi-abs-val-row">
                                    <span class="kpi-abs-num">{{ $hadir }}</span>
                                    <span class="kpi-abs-unit">Hari</span>
                                </div>
                            </div>
                            <div class="kpi-abs-icon kpi-abs-icon-teal">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                        </div>

                        <!-- Total Potongan Box -->
                        <div class="kpi-abs-box kpi-abs-box-amber">
                            <div class="min-w-0">
                                <span class="kpi-abs-lbl" style="color:#b45309;">Potongan</span>
                                <span class="kpi-abs-money" title="Rp {{ number_format($potonganVal, 0, ',', '.') }}">
                                    Rp {{ $potonganVal > 0 ? (strlen((string) (int) $potonganVal) > 6 ? round($potonganVal/1000) . 'k' : number_format($potonganVal, 0, ',', '.')) : '0' }}
                                </span>
                            </div>
                            <div class="kpi-abs-icon kpi-abs-icon-amber">
                                <svg width="14" height="14" class="fill-amber-600 text-amber-600" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Tengah: Stacked Progress Bar -->
                    <div class="kpi-pbar-track"
                         title="Tepat Waktu: {{ $tepatWaktu }} hari, Terlambat: {{ $terlambat }} hari, Tidak Hadir: {{ $tidakHadir }} hari">
                        @if ($hadir + $tidakHadir === 0)
                            <div class="kpi-pbar-seg" style="width:100%; background:#e2e8f0;"></div>
                        @else
                            @if ($tepatPct > 0)
                                <div class="kpi-pbar-seg" style="width: {{ $tepatPct }}%; background: linear-gradient(90deg, #10b981, #059669);"></div>
                            @endif
                            @if ($terlambatPct > 0)
                                <div class="kpi-pbar-seg" style="width: {{ $terlambatPct }}%; background: linear-gradient(90deg, #fbbf24, #f59e0b);"></div>
                            @endif
                            @if ($tidakHadirPct > 0)
                                <div class="kpi-pbar-seg" style="width: {{ $tidakHadirPct }}%; background: linear-gradient(90deg, #fb7185, #e11d48);"></div>
                            @endif
                        @endif
                    </div>

                    <!-- Bawah Bar: Label Keterangan -->
                    <div class="kpi-pbar-legend">
                        <span class="kpi-pbar-item">
                            <span class="kpi-pbar-dot" style="background:#10b981;"></span>
                            <span>Tepat Waktu: <strong style="color:#047857; font-weight:700;">{{ $tepatWaktu }}</strong></span>
                        </span>
                        @if ($terlambat > 0)
                        <span class="kpi-pbar-item">
                            <span class="kpi-pbar-dot" style="background:#f59e0b;"></span>
                            <span>Terlambat: <strong style="color:#b45309; font-weight:700;">{{ $terlambat }}</strong></span>
                        </span>
                        @endif
                        <span class="kpi-pbar-item">
                            <span class="kpi-pbar-dot" style="background:{{ $tidakHadir > 0 ? '#e11d48' : '#cbd5e1' }};"></span>
                            <span>Tidak Hadir: <strong style="color:{{ $tidakHadir > 0 ? '#be123c' : '#64748b' }}; font-weight:700;">{{ $tidakHadir }}</strong></span>
                        </span>
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
        {{-- TAMPILAN MANAJEMEN / HRD / SUPERVISOR / COMPANY --}}
        @php
            $isCompany = ($stats['scope']['type'] === 'company');
            $scopeLabel = $isCompany ? 'Seluruh Karyawan' : 'Divisi';
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 hub-stagger">
            
            <!-- Card 1: Pengajuan Seluruh Karyawan (Donut Chart + Vertical Legend) -->
            <div class="kpi-card">
                <div>
                    <!-- Header -->
                    <div class="kpi-hdr">
                        <div class="kpi-hdr-left">
                            <div class="kpi-hdr-icon" style="background:#ecfeff; color:#0891b2;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="kpi-hdr-title">Pengajuan {{ $scopeLabel }}</h3>
                                <span class="kpi-hdr-sub">Periode {{ now('Asia/Jakarta')->translatedFormat('F Y') }}</span>
                            </div>
                        </div>
                        <span class="kpi-dropdown-btn">
                            {{ now('Asia/Jakarta')->translatedFormat('M Y') }}
                        </span>
                    </div>

                    @php
                        $pTotal = (int) ($stats['pengajuan']['total'] ?? 0);
                        $pApproved = (int) ($stats['pengajuan']['approved'] ?? 0);
                        $pRejected = (int) ($stats['pengajuan']['rejected'] ?? 0);
                        $pPending = (int) ($stats['pengajuan']['pending'] ?? 0);
                        $donutPengajuan = $buildDonutStyle($pTotal, $pApproved, $pRejected, $pPending);
                    @endphp

                    <!-- Donut Chart & Vertical Legend -->
                    <div class="kpi-donut-wrap">
                        <!-- Sisi Kiri: Donut Chart -->
                        <div class="kpi-donut-ring" style="background: {{ $donutPengajuan }};" title="Total Pengajuan: {{ $pTotal }}">
                            <div class="kpi-donut-center">
                                <span class="kpi-donut-num">{{ $pTotal }}</span>
                                <span class="kpi-donut-lbl">Total</span>
                            </div>
                        </div>

                        <!-- Sisi Kanan: Vertical Legend -->
                        <div class="kpi-legend-list">
                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-green"></span>
                                    <span class="truncate">Disetujui</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-green">{{ $pApproved }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-red"></span>
                                    <span class="truncate">Ditolak</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-red">{{ $pRejected }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-amber"></span>
                                    <span class="truncate">Menunggu</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-amber">{{ $pPending }}</span>
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
                        Kelola Pengajuan &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 2: Absensi Seluruh Karyawan (Donut Chart + Vertical Legend) -->
            <div class="kpi-card">
                <div>
                    <!-- Header -->
                    <div class="kpi-hdr">
                        <div class="kpi-hdr-left">
                            <div class="kpi-hdr-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="kpi-hdr-title">Absensi {{ $scopeLabel }}</h3>
                                <span class="kpi-hdr-sub">Periode {{ now('Asia/Jakarta')->translatedFormat('F Y') }}</span>
                            </div>
                        </div>
                        <span class="kpi-badge-aktif">
                            <span></span>
                            Aktif
                        </span>
                    </div>

                    @php
                        $attOntime = (int) ($stats['absensi']['month_ontime_days'] ?? 0);
                        $attLate = (int) ($stats['absensi']['month_late_days'] ?? 0);
                        $attLeave = (int) ($stats['absensi']['month_leave_days'] ?? 0);
                        $attAbsent = (int) ($stats['absensi']['month_absent_days'] ?? 0);
                        $attTotalCount = $attOntime + $attLate + $attLeave + $attAbsent;
                        $donutAbsensi = $buildAttDonutStyle($attOntime, $attLate, $attLeave, $attAbsent);
                    @endphp

                    <!-- Donut Chart & Vertical Legend -->
                    <div class="kpi-donut-wrap">
                        <!-- Sisi Kiri: Donut Chart -->
                        <div class="kpi-donut-ring" style="background: {{ $donutAbsensi }};" title="Tingkat Kehadiran: {{ $stats['absensi']['month_rate'] }}%">
                            <div class="kpi-donut-center">
                                <span class="kpi-donut-num text-lg font-black text-teal-700">{{ $stats['absensi']['month_rate'] }}%</span>
                                <span class="kpi-donut-lbl">Rate</span>
                            </div>
                        </div>

                        <!-- Sisi Kanan: Vertical Legend -->
                        <div class="kpi-legend-list">
                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-green"></span>
                                    <span class="truncate">Tepat</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-green">{{ $attOntime }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-amber"></span>
                                    <span class="truncate">Telat</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-amber">{{ $attLate }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-blue"></span>
                                    <span class="truncate">Izin</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-blue">{{ $attLeave }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-red"></span>
                                    <span class="truncate">Alpa</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-red">{{ $attAbsent }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Details Row -->
                    <div class="space-y-1.5 pt-1 mb-3">
                        <div class="kpi-detail-row">
                            <span class="inline-flex items-center gap-2">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Total Kehadiran
                            </span>
                            <strong class="text-teal-800">{{ $stats['absensi']['month_present_days'] }} Hari Kerja</strong>
                        </div>
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="kpi-card-footer">
                    <span class="text-slate-500 inline-flex items-center gap-1.5">
                        <svg width="14" height="14" class="text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Rekapitulasi Presensi
                    </span>
                    <a href="{{ route('attendance.index') }}" class="font-bold text-teal-700 hover:text-teal-800 inline-flex items-center gap-1">
                        Rekap Absen &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 3: Lembur Seluruh Karyawan (Donut Chart + Vertical Legend) -->
            <div class="kpi-card">
                <div>
                    <!-- Header -->
                    <div class="kpi-hdr">
                        <div class="kpi-hdr-left">
                            <div class="kpi-hdr-icon" style="background:#eef2ff; color:#4f46e5;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="kpi-hdr-title">Lembur {{ $scopeLabel }}</h3>
                                <span class="kpi-hdr-sub">Periode {{ now('Asia/Jakarta')->translatedFormat('F Y') }}</span>
                            </div>
                        </div>
                        <span class="kpi-dropdown-btn">
                            {{ $stats['lembur']['total_hours'] }} Jam
                        </span>
                    </div>

                    @php
                        $lTotal = (int) ($stats['lembur']['total'] ?? 0);
                        $lApproved = (int) ($stats['lembur']['approved'] ?? 0);
                        $lRejected = (int) ($stats['lembur']['rejected'] ?? 0);
                        $lPending = (int) ($stats['lembur']['pending'] ?? 0);
                        $donutLembur = $buildDonutStyle($lTotal, $lApproved, $lRejected, $lPending);
                    @endphp

                    <!-- Donut Chart & Vertical Legend -->
                    <div class="kpi-donut-wrap">
                        <!-- Sisi Kiri: Donut Chart -->
                        <div class="kpi-donut-ring" style="background: {{ $donutLembur }};" title="Total Lembur: {{ $lTotal }}">
                            <div class="kpi-donut-center">
                                <span class="kpi-donut-num">{{ $lTotal }}</span>
                                <span class="kpi-donut-lbl">Total</span>
                            </div>
                        </div>

                        <!-- Sisi Kanan: Vertical Legend -->
                        <div class="kpi-legend-list">
                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-green"></span>
                                    <span class="truncate">Disetujui</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-green">{{ $lApproved }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-red"></span>
                                    <span class="truncate">Ditolak</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-red">{{ $lRejected }}</span>
                            </div>

                            <div class="kpi-legend-item">
                                <span class="kpi-legend-left">
                                    <span class="kpi-legend-dot kpi-legend-dot-amber"></span>
                                    <span class="truncate">Menunggu</span>
                                </span>
                                <span class="kpi-legend-val kpi-legend-val-amber">{{ $lPending }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Details Row -->
                    <div class="space-y-1.5 pt-1 mb-3">
                        <div class="kpi-detail-row">
                            <span class="inline-flex items-center gap-2">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Total Jam Terverifikasi
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
                        Kelola Lembur &rarr;
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
                    Data tren dan kalender kehadiran harian
                </p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-white border border-slate-200 px-2.5 py-1 rounded-lg shadow-2xs">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Data
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- Chart 1: Tren Kehadiran 7 Hari Terakhir (Col 7) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
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

            <!-- Kalender Kehadiran Bulan Ini (Matching User Reference) -->
            @if (isset($monthlyCalendar))
            <div class="lg:col-span-5 order-first lg:order-last bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <!-- Header: Icon, Month & Year, Present Counter Badge -->
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-2xs border border-blue-100/80 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-heading font-extrabold text-slate-800 text-sm sm:text-base leading-tight">
                                    {{ $monthlyCalendar['month_name'] }}
                                </h3>
                            </div>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/70 shadow-2xs">
                                {{ $monthlyCalendar['present_count'] }} hari hadir
                            </span>
                        </div>
                    </div>

                    <!-- Day Names Row (SEN, SEL, RAB, KAM, JUM, SAB, MIN) -->
                    <div class="cal-grid-7 mb-2">
                        <span class="cal-day-header">SEN</span>
                        <span class="cal-day-header">SEL</span>
                        <span class="cal-day-header">RAB</span>
                        <span class="cal-day-header">KAM</span>
                        <span class="cal-day-header">JUM</span>
                        <span class="cal-day-header">SAB</span>
                        <span class="cal-day-header">MIN</span>
                    </div>

                    <!-- Day Tiles Grid (7 Columns) -->
                    <div class="cal-grid-7">
                        <!-- Empty offset slots before day 1 -->
                        @for ($i = 0; $i < $monthlyCalendar['empty_slots']; $i++)
                            <div class="cal-tile border-transparent pointer-events-none" style="visibility:hidden;"></div>
                        @endfor

                        <!-- Month Days -->
                        @foreach ($monthlyCalendar['days'] as $dayItem)
                            @php
                                $status = $dayItem['status'];
                                $isToday = $dayItem['is_today'];
                                
                                // Color state rules:
                                // Hijau = hadir, Kuning = terlambat, Merah = alpa, Abu = libur, Putih = belum terlewat
                                if ($status === 'present') {
                                    $tileClass = 'cal-tile-present';
                                } elseif ($status === 'late') {
                                    $tileClass = 'cal-tile-late';
                                } elseif ($status === 'absent') {
                                    $tileClass = 'cal-tile-absent';
                                } elseif ($status === 'off') {
                                    $tileClass = 'cal-tile-off';
                                } else { // future / belum terlewat
                                    $tileClass = 'cal-tile-future';
                                }

                                $todayClass = $isToday ? 'cal-tile-today' : '';
                            @endphp
                            <div class="cal-tile {{ $tileClass }} {{ $todayClass }}"
                                 title="{{ $dayItem['date_formatted'] }}: {{ $dayItem['label'] }}{{ $isToday ? ' (Hari Ini)' : '' }}">
                                <span>{{ $dayItem['day'] }}</span>
                                @if ($isToday)
                                    <span class="cal-dot-today"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Legend at Bottom -->
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-center gap-3 sm:gap-4 flex-wrap text-[10.5px] sm:text-xs text-slate-500 font-medium">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 border border-emerald-500 shrink-0"></span>
                        Hadir
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 border border-amber-500 shrink-0"></span>
                        Terlambat
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-400 border border-rose-500 shrink-0"></span>
                        Alpa
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-200 border border-slate-300 shrink-0"></span>
                        Libur
                    </span>
                </div>
            </div>
            @endif
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
                                            @if (in_array($p->status, ['disetujui', 'ditransfer', 'terealisasi', 'dicairkan', 'menunggu_bukti', 'selesai']))
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
                const isDarkNow = document.documentElement.classList.contains('dark');
                const attChart = new Chart(attCanvas.getContext('2d'), {
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
                                ticks: { font: { family: chartFontFamily, size: 11 }, color: isDarkNow ? '#94A3B8' : '#64748B' }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: { color: isDarkNow ? 'rgba(51, 65, 85, 0.5)' : '#F1F5F9' },
                                ticks: {
                                    stepSize: 1,
                                    font: { family: chartFontFamily, size: 11 },
                                    color: isDarkNow ? '#94A3B8' : '#64748B'
                                }
                            }
                        }
                    }
                });

                window.addEventListener('gomsn-theme-changed', function (e) {
                    const dark = e.detail && e.detail.dark;
                    attChart.options.scales.x.ticks.color = dark ? '#94A3B8' : '#64748B';
                    attChart.options.scales.y.ticks.color = dark ? '#94A3B8' : '#64748B';
                    attChart.options.scales.y.grid.color = dark ? 'rgba(51, 65, 85, 0.5)' : '#F1F5F9';
                    attChart.update('none');
                });
            }
        });
    </script>
</x-layouts.app>
