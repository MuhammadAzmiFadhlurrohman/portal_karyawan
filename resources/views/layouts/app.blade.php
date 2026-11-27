<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="GoMSN">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/logo/favicon-32x32.png') }}?v=4">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/logo/favicon-16x16.png') }}?v=4">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=4">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/logo/apple-touch-icon.png') }}?v=2026112308">
    <link rel="apple-touch-startup-image" href="{{ asset('assets/logo/apple-splash-screen.png') }}?v=2026112308">
    <link rel="manifest" href="/manifest.webmanifest?v=2026112308">
    <title>{{ $title ?? 'Portal Karyawan Terpadu' }} — PT MSN</title>
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('gomsn_theme');
                var isDark = savedTheme === 'dark';
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.style.colorScheme = 'dark';
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.style.colorScheme = 'light';
                }
                var savedAccent = localStorage.getItem('gomsn_accent_theme') || 'teal';
                document.documentElement.setAttribute('data-accent', savedAccent);
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof window.syncGomsnInlineAccent === 'function') {
                        window.syncGomsnInlineAccent(savedAccent);
                    }
                });
            } catch (e) {}
        })();

        window.setGomsnAccent = function(accent) {
            var valid = ['teal', 'blue', 'indigo', 'emerald', 'purple', 'rose', 'amber', 'pink'];
            var next = valid.indexOf(accent) !== -1 ? accent : 'teal';
            document.documentElement.setAttribute('data-accent', next);
            try {
                localStorage.setItem('gomsn_accent_theme', next);
            } catch (e) {}
            if (typeof window.syncGomsnInlineAccent === 'function') {
                window.syncGomsnInlineAccent(next);
            }
            window.dispatchEvent(new CustomEvent('gomsn-accent-changed', { detail: { accent: next } }));
        };

        window.syncGomsnInlineAccent = function(accent) {
            var current = accent || document.documentElement.getAttribute('data-accent') || 'teal';
            var headerHexes = ['#022836', '#053b47', '#0a525f', '#07383c', '#073337', '#0f4c5c', '#007f8c', '#052629', '#0b464b', '#094747', '#0d5f5f', '#093c40', '#06404d', '#0e6774', '#084c57', '#0c5a68', '#0a4247', '#05282c', '#072a2d', '#0e5458', '#17555b', 'rgb(2, 40, 54)', 'rgb(5, 59, 71)', 'rgb(10, 82, 95)', 'rgb(7, 56, 60)', 'rgb(7, 51, 55)', 'rgb(15, 76, 92)', 'rgb(0, 127, 140)', 'rgb(9, 71, 71)', 'rgb(13, 95, 95)', 'rgb(9, 60, 64)', 'rgb(7, 42, 45)', 'rgb(14, 84, 88)'];
            var buttonHexes = ['#18c4c4', '#0d9488', '#0f766e', '#155e63', '#115e59', '#029990', '#038078', '#015c57', 'rgb(24, 196, 196)', 'rgb(13, 148, 136)', 'rgb(15, 118, 110)', 'rgb(21, 94, 99)'];
            var els = document.querySelectorAll('[style*="background"]');
            for (var i = 0; i < els.length; i++) {
                var el = els[i];
                if (el.id === 'desktop-sidebar' || el.classList.contains('gomsn-home-orb') || el.closest('[data-accent-swatch="true"]') || el.hasAttribute('data-gomsn-no-accent')) continue;
                var orig = el.getAttribute('data-gomsn-orig-bg');
                if (orig === null) {
                    var bgOnly = (el.style.background || el.style.backgroundImage || el.style.backgroundColor || '').toLowerCase();
                    if (!bgOnly) continue;
                    var isHeader = headerHexes.some(function(h) { return bgOnly.indexOf(h) !== -1; });
                    var isBtn = !isHeader && buttonHexes.some(function(h) { return bgOnly.indexOf(h) !== -1; });
                    if (!isHeader && !isBtn) continue;
                    el.setAttribute('data-gomsn-orig-bg', el.style.background || el.style.backgroundImage || el.style.backgroundColor || '');
                    el.setAttribute('data-gomsn-bg-role', isHeader ? 'header' : 'active');
                }
                var role = el.getAttribute('data-gomsn-bg-role');
                if (current === 'teal') {
                    var saved = el.getAttribute('data-gomsn-orig-bg') || '';
                    el.style.setProperty('background', saved, 'important');
                } else if (role === 'header') {
                    el.style.setProperty('background', 'var(--gomsn-header-bg)', 'important');
                } else if (role === 'active') {
                    el.style.setProperty('background', 'var(--gomsn-active-bg)', 'important');
                }
            }
        };

        window.toggleGomsnTheme = function(event) {
            var html = document.documentElement;
            var willBeDark = !html.classList.contains('dark');

            var applySwitch = function() {
                if (willBeDark) {
                    html.classList.add('dark');
                    html.style.colorScheme = 'dark';
                } else {
                    html.classList.remove('dark');
                    html.style.colorScheme = 'light';
                }
                try {
                    localStorage.setItem('gomsn_theme', willBeDark ? 'dark' : 'light');
                } catch (e) {}
                window.dispatchEvent(new CustomEvent('gomsn-theme-changed', { detail: { dark: willBeDark } }));
            };

            var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (document.startViewTransition && !prefersReduced) {
                var x = window.innerWidth - 120;
                var y = 32;
                if (event && typeof event.clientX === 'number' && (event.clientX > 0 || event.clientY > 0)) {
                    x = event.clientX;
                    y = event.clientY;
                } else if (event && event.currentTarget && event.currentTarget.getBoundingClientRect) {
                    var rect = event.currentTarget.getBoundingClientRect();
                    x = rect.left + rect.width / 2;
                    y = rect.top + rect.height / 2;
                }
                var endRadius = Math.hypot(
                    Math.max(x, window.innerWidth - x),
                    Math.max(y, window.innerHeight - y)
                );

                html.classList.add('theme-vt-active');
                try {
                    var transition = document.startViewTransition(function() {
                        applySwitch();
                    });

                    transition.ready.then(function() {
                        document.documentElement.animate(
                            {
                                clipPath: [
                                    'circle(0px at ' + x + 'px ' + y + 'px)',
                                    'circle(' + endRadius + 'px at ' + x + 'px ' + y + 'px)'
                                ]
                            },
                            {
                                duration: 540,
                                easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                                pseudoElement: '::view-transition-new(root)'
                            }
                        );
                    }).catch(function() {});

                    transition.finished.finally(function() {
                        html.classList.remove('theme-vt-active');
                    });
                    return;
                } catch (err) {
                    html.classList.remove('theme-vt-active');
                }
            }

            html.classList.add('theme-smooth-transition');
            applySwitch();
            setTimeout(function() {
                html.classList.remove('theme-smooth-transition');
            }, 520);
        };
    </script>
    <!-- CRITICAL INSTANT THEME VARS (Render synchronously before any CSS/JS bundles) -->
    <style id="gomsn-theme-vars">
        @view-transition {
            navigation: auto;
        }
        #desktop-sidebar {
            view-transition-name: desktop-sidebar;
        }

        /* ==============================================================
           GLOBAL APP COLOR THEMES (KUSTOMISASI WARNA TEMA SELURUH HALAMAN)
           ============================================================== */
        :root,
        html[data-accent="teal"] {
            --gomsn-accent: #0d9488;
            --gomsn-accent-light: #5eead4;
            --gomsn-sidebar-bg: linear-gradient(180deg, #022836 0%, #053b47 22%, #094d5a 50%, #0c5a68 75%, #031e26 100%);
            --gomsn-header-bg: linear-gradient(100deg, #07383c 0%, #0c4d53 28%, #105c64 52%, #0a4449 82%, #063135 100%);
            --gomsn-active-bg: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);
            --gomsn-orb-bg: radial-gradient(circle at 50% 25%, #10b981 0%, #0d9488 45%, #065f46 100%);
            --gomsn-sidebar-wave-filter: none;
        }
        html[data-accent="blue"] {
            --gomsn-accent: #2563eb;
            --gomsn-accent-light: #93c5fd;
            --gomsn-sidebar-bg: linear-gradient(180deg, #0b1b3f 0%, #102a63 25%, #163b87 55%, #1d4ed8 85%, #0a1633 100%);
            --gomsn-header-bg: linear-gradient(100deg, #0f2557 0%, #173b85 35%, #1d4ed8 65%, #163578 100%);
            --gomsn-active-bg: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
            --gomsn-orb-bg: radial-gradient(circle at 50% 25%, #60a5fa 0%, #2563eb 48%, #1e3a8a 100%);
            --gomsn-sidebar-wave-filter: hue-rotate(42deg);
            --color-teal-50: var(--color-blue-50);
            --color-teal-100: var(--color-blue-100);
            --color-teal-200: var(--color-blue-200);
            --color-teal-300: var(--color-blue-300);
            --color-teal-400: var(--color-blue-400);
            --color-teal-500: var(--color-blue-500);
            --color-teal-600: var(--color-blue-600);
            --color-teal-700: var(--color-blue-700);
            --color-teal-800: var(--color-blue-800);
            --color-teal-900: var(--color-blue-900);
            --color-teal-950: var(--color-blue-950);
        }
        html[data-accent="indigo"] {
            --gomsn-accent: #4f46e5;
            --gomsn-accent-light: #a5b4fc;
            --gomsn-sidebar-bg: linear-gradient(180deg, #14123b 0%, #1f1b5c 25%, #2e2982 55%, #3730a3 85%, #100e2e 100%);
            --gomsn-header-bg: linear-gradient(100deg, #1b1852 0%, #2a247a 35%, #3730a3 65%, #201c5e 100%);
            --gomsn-active-bg: linear-gradient(90deg, #6366f1 0%, #4f46e5 100%);
            --gomsn-orb-bg: radial-gradient(circle at 50% 25%, #818cf8 0%, #4f46e5 48%, #312e81 100%);
            --gomsn-sidebar-wave-filter: hue-rotate(68deg);
            --color-teal-50: var(--color-indigo-50);
            --color-teal-100: var(--color-indigo-100);
            --color-teal-200: var(--color-indigo-200);
            --color-teal-300: var(--color-indigo-300);
            --color-teal-400: var(--color-indigo-400);
            --color-teal-500: var(--color-indigo-500);
            --color-teal-600: var(--color-indigo-600);
            --color-teal-700: var(--color-indigo-700);
            --color-teal-800: var(--color-indigo-800);
            --color-teal-900: var(--color-indigo-900);
            --color-teal-950: var(--color-indigo-950);
        }
        html[data-accent="emerald"] {
            --gomsn-accent: #059669;
            --gomsn-accent-light: #6ee7b7;
            --gomsn-sidebar-bg: linear-gradient(180deg, #03261b 0%, #064230 25%, #065f46 55%, #047857 85%, #021d15 100%);
            --gomsn-header-bg: linear-gradient(100deg, #053828 0%, #065f46 40%, #047857 70%, #053b2a 100%);
            --gomsn-active-bg: linear-gradient(90deg, #10b981 0%, #059669 100%);
            --gomsn-orb-bg: radial-gradient(circle at 50% 25%, #34d399 0%, #059669 48%, #064e3b 100%);
            --gomsn-sidebar-wave-filter: hue-rotate(-18deg);
            --color-teal-50: var(--color-emerald-50);
            --color-teal-100: var(--color-emerald-100);
            --color-teal-200: var(--color-emerald-200);
            --color-teal-300: var(--color-emerald-300);
            --color-teal-400: var(--color-emerald-400);
            --color-teal-500: var(--color-emerald-500);
            --color-teal-600: var(--color-emerald-600);
            --color-teal-700: var(--color-emerald-700);
            --color-teal-800: var(--color-emerald-800);
            --color-teal-900: var(--color-emerald-900);
            --color-teal-950: var(--color-emerald-950);
        }
        html[data-accent="purple"] {
            --gomsn-accent: #7c3aed;
            --gomsn-accent-light: #d8b4fe;
            --gomsn-sidebar-bg: linear-gradient(180deg, #1f0e3d 0%, #311761 25%, #4c1d95 55%, #5b21b6 85%, #170a2e 100%);
            --gomsn-header-bg: linear-gradient(100deg, #2b1254 0%, #4c1d95 40%, #6d28d9 70%, #341666 100%);
            --gomsn-active-bg: linear-gradient(90deg, #a855f7 0%, #7c3aed 100%);
            --gomsn-orb-bg: radial-gradient(circle at 50% 25%, #c084fc 0%, #7c3aed 48%, #4c1d95 100%);
            --gomsn-sidebar-wave-filter: hue-rotate(100deg);
            --color-teal-50: var(--color-purple-50);
            --color-teal-100: var(--color-purple-100);
            --color-teal-200: var(--color-purple-200);
            --color-teal-300: var(--color-purple-300);
            --color-teal-400: var(--color-purple-400);
            --color-teal-500: var(--color-purple-500);
            --color-teal-600: var(--color-purple-600);
            --color-teal-700: var(--color-purple-700);
            --color-teal-800: var(--color-purple-800);
            --color-teal-900: var(--color-purple-900);
            --color-teal-950: var(--color-purple-950);
        }
        html[data-accent="rose"] {
            --gomsn-accent: #e11d48;
            --gomsn-accent-light: #fda4af;
            --gomsn-sidebar-bg: linear-gradient(180deg, #360815 0%, #570e22 25%, #881337 55%, #9f1239 85%, #290510 100%);
            --gomsn-header-bg: linear-gradient(100deg, #4c0b1d 0%, #881337 40%, #be123c 70%, #570e22 100%);
            --gomsn-active-bg: linear-gradient(90deg, #f43f5e 0%, #e11d48 100%);
            --gomsn-orb-bg: radial-gradient(circle at 50% 25%, #fb7185 0%, #e11d48 48%, #881337 100%);
            --gomsn-sidebar-wave-filter: hue-rotate(165deg);
            --color-teal-50: var(--color-rose-50);
            --color-teal-100: var(--color-rose-100);
            --color-teal-200: var(--color-rose-200);
            --color-teal-300: var(--color-rose-300);
            --color-teal-400: var(--color-rose-400);
            --color-teal-500: var(--color-rose-500);
            --color-teal-600: var(--color-rose-600);
            --color-teal-700: var(--color-rose-700);
            --color-teal-800: var(--color-rose-800);
            --color-teal-900: var(--color-rose-900);
            --color-teal-950: var(--color-rose-950);
        }
        html[data-accent="amber"] {
            --gomsn-accent: #d97706;
            --gomsn-accent-light: #fcd34d;
            --gomsn-sidebar-bg: linear-gradient(180deg, #331804 0%, #542807 25%, #78350f 55%, #92400e 85%, #261203 100%);
            --gomsn-header-bg: linear-gradient(100deg, #472105 0%, #78350f 40%, #b45309 70%, #542807 100%);
            --gomsn-active-bg: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
            --gomsn-orb-bg: radial-gradient(circle at 50% 25%, #fbbf24 0%, #d97706 48%, #78350f 100%);
            --gomsn-sidebar-wave-filter: hue-rotate(-135deg);
            --color-teal-50: var(--color-amber-50);
            --color-teal-100: var(--color-amber-100);
            --color-teal-200: var(--color-amber-200);
            --color-teal-300: var(--color-amber-300);
            --color-teal-400: var(--color-amber-400);
            --color-teal-500: var(--color-amber-500);
            --color-teal-600: var(--color-amber-600);
            --color-teal-700: var(--color-amber-700);
            --color-teal-800: var(--color-amber-800);
            --color-teal-900: var(--color-amber-900);
            --color-teal-950: var(--color-amber-950);
        }
        html[data-accent="pink"] {
            --gomsn-accent: #ec4899;
            --gomsn-accent-light: #f9a8d4;
            --gomsn-sidebar-bg: linear-gradient(180deg, #3b0a24 0%, #61123d 25%, #831843 55%, #be185d 85%, #2b071a 100%);
            --gomsn-header-bg: linear-gradient(100deg, #500724 0%, #831843 40%, #db2777 75%, #61123d 100%);
            --gomsn-active-bg: linear-gradient(90deg, #f472b6 0%, #ec4899 100%);
            --gomsn-orb-bg: radial-gradient(circle at 50% 25%, #f9a8d4 0%, #ec4899 48%, #831843 100%);
            --gomsn-sidebar-wave-filter: hue-rotate(142deg);
            --color-teal-50: #fdf2f8;
            --color-teal-100: #fce7f3;
            --color-teal-200: #fbcfe8;
            --color-teal-300: #f9a8d4;
            --color-teal-400: #f472b6;
            --color-teal-500: #ec4899;
            --color-teal-600: #db2777;
            --color-teal-700: #be185d;
            --color-teal-800: #9d174d;
            --color-teal-900: #831843;
            --color-teal-950: #500724;
        }

        /* Komponen utama aplikasi: bind instan ke CSS Variables */
        #desktop-sidebar {
            background: var(--gomsn-sidebar-bg) !important;
        }
        html[data-accent]:not([data-accent="teal"]) #desktop-sidebar > div:first-child {
            filter: var(--gomsn-sidebar-wave-filter);
        }
        .gomsn-mobile-topbar,
        .gomsn-sheet-header,
        .gomsn-themed-banner {
            background: var(--gomsn-header-bg) !important;
        }
        .gomsn-home-orb {
            background: var(--gomsn-orb-bg) !important;
        }
        [data-sidebar-active="true"] {
            background: var(--gomsn-active-bg) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .text-\[\#0f766e\],
        html[data-accent]:not([data-accent="teal"]) .text-\[\#115e59\],
        html[data-accent]:not([data-accent="teal"]) .text-\[\#155E63\],
        html[data-accent]:not([data-accent="teal"]) .text-\[\#0D9488\],
        html[data-accent]:not([data-accent="teal"]) .text-\[\#0d9488\],
        html[data-accent]:not([data-accent="teal"]) .text-\[\#00897B\],
        html[data-accent]:not([data-accent="teal"]) .text-\[\#009688\] {
            color: var(--gomsn-accent) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .text-\[\#18C4C4\],
        html[data-accent]:not([data-accent="teal"]) .text-\[\#18c4c4\],
        html[data-accent]:not([data-accent="teal"]) .text-cyan-300,
        html[data-accent]:not([data-accent="teal"]) .text-cyan-200,
        html[data-accent]:not([data-accent="teal"]) .text-teal-200\/80,
        html[data-accent]:not([data-accent="teal"]) .text-teal-100\/85,
        html[data-accent]:not([data-accent="teal"]) .text-teal-100\/80 {
            color: var(--gomsn-accent-light) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .bg-cyan-500\/20,
        html[data-accent]:not([data-accent="teal"]) .bg-cyan-500\/25 {
            background-color: rgba(255, 255, 255, 0.14) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .border-cyan-400\/30,
        html[data-accent]:not([data-accent="teal"]) .border-cyan-400\/40 {
            border-color: rgba(255, 255, 255, 0.24) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .via-\[\#5eead4\]\/70,
        html[data-accent]:not([data-accent="teal"]) .via-\[\#2dd4bf\]\/35 {
            --tw-gradient-via: var(--gomsn-accent-light) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .border-\[\#0D9488\],
        html[data-accent]:not([data-accent="teal"]) .border-\[\#0d9488\],
        html[data-accent]:not([data-accent="teal"]) .focus\:border-\[\#0D9488\]:focus,
        html[data-accent]:not([data-accent="teal"]) .focus\:border-\[\#009688\]:focus {
            border-color: var(--gomsn-accent) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .ring-\[\#0D9488\]\/15 {
            --tw-ring-color: color-mix(in srgb, var(--gomsn-accent) 18%, transparent) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .bg-\[\#155E63\],
        html[data-accent]:not([data-accent="teal"]) .bg-\[\#0f766e\],
        html[data-accent]:not([data-accent="teal"]) .bg-\[\#0D9488\],
        html[data-accent]:not([data-accent="teal"]) .bg-\[\#0d9488\],
        html[data-accent]:not([data-accent="teal"]) .bg-\[\#009688\],
        html[data-accent]:not([data-accent="teal"]) .hover\:bg-\[\#00796B\]:hover,
        html[data-accent]:not([data-accent="teal"]) .hover\:bg-\[\#114b4f\]:hover {
            background-color: var(--gomsn-accent) !important;
        }
        html[data-accent]:not([data-accent="teal"]) #desktop-sidebar .bg-gradient-to-tr,
        html[data-accent]:not([data-accent="teal"]) .from-\[\#18C4C4\],
        html[data-accent]:not([data-accent="teal"]) .from-\[\#093c40\] {
            background: var(--gomsn-active-bg) !important;
        }

        /* Dashboard Hero Banner (.hh) mengikuti tema warna aplikasi */
        html[data-accent]:not([data-accent="teal"]) .hh {
            background: var(--gomsn-header-bg) !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .hh-circle-layer {
            background: var(--gomsn-orb-bg) !important;
            box-shadow: 0 0 48px -8px rgba(0, 0, 0, 0.45), inset 0 2px 22px rgba(255, 255, 255, 0.28) !important;
            border-color: rgba(255, 255, 255, 0.28) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .hh-cta {
            background: var(--gomsn-active-bg) !important;
            border-color: rgba(255, 255, 255, 0.35) !important;
            box-shadow: 0 10px 24px -6px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.35) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .hh-name .b {
            background: linear-gradient(110deg, #ffffff 0%, var(--gomsn-accent-light) 55%, #ffffff 100%) !important;
            -webkit-background-clip: text !important;
            background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }
        html[data-accent]:not([data-accent="teal"]) .hh-glow1,
        html[data-accent]:not([data-accent="teal"]) .hh-glow2,
        html[data-accent]:not([data-accent="teal"]) .hh-bg-svg,
        html[data-accent]:not([data-accent="teal"]) .hh-rim,
        html[data-accent]:not([data-accent="teal"]) .hh-circle-ring,
        html[data-accent]:not([data-accent="teal"]) .hh-arc-overlay {
            filter: var(--gomsn-sidebar-wave-filter) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .hh-hello-line,
        html[data-accent]:not([data-accent="teal"]) .hh-date-dot {
            background: var(--gomsn-accent-light) !important;
            box-shadow: 0 0 8px var(--gomsn-accent-light) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .hh-pill svg {
            color: var(--gomsn-accent-light) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .hh-date,
        html[data-accent]:not([data-accent="teal"]) .hh-pill,
        html[data-accent]:not([data-accent="teal"]) .hh-check {
            background: rgba(15, 23, 42, 0.55) !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
            color: #f8fafc !important;
        }

        /* Floating Chatbot Tab, Header, Bubble, Avatar & Buttons mengikuti tema warna aplikasi */
        html[data-accent]:not([data-accent="teal"]) .chatbot-docked-tab,
        html[data-accent]:not([data-accent="teal"]) .chatbot-header,
        html[data-accent]:not([data-accent="teal"]) .chatbot-header-bar {
            background: var(--gomsn-header-bg) !important;
            border-color: rgba(255, 255, 255, 0.22) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .chatbot-bubble-user {
            background: var(--gomsn-active-bg) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .chatbot-avatar.is-bot {
            background: var(--gomsn-active-bg) !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .chatbot-send-btn,
        html[data-accent]:not([data-accent="teal"]) button.chatbot-send-btn {
            background: var(--gomsn-active-bg) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .chatbot-action-btn {
            background: var(--color-teal-50) !important;
            border-color: var(--color-teal-200) !important;
            color: var(--color-teal-800) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .chatbot-action-btn:hover {
            background: var(--color-teal-100) !important;
            border-color: var(--color-teal-300) !important;
        }

        /* Hero Banner Mobile 4-Dot Accent */
        html[data-accent]:not([data-accent="teal"]) .hh-mobile-dots span {
            background: var(--gomsn-accent-light) !important;
            box-shadow: 0 0 8px var(--gomsn-accent-light) !important;
        }

        /* PWA Install Banner ("Aplikasi Mobile PT MSN") mengikuti tema warna aplikasi */
        html[data-accent]:not([data-accent="teal"]) .pwa-banner {
            background: var(--gomsn-header-bg) !important;
            border-color: rgba(255, 255, 255, 0.18) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .pwa-cta {
            background: var(--gomsn-active-bg) !important;
            border-color: rgba(255, 255, 255, 0.32) !important;
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.42) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .pwa-wave-svg,
        html[data-accent]:not([data-accent="teal"]) .pwa-rim,
        html[data-accent]:not([data-accent="teal"]) .pwa-glow {
            filter: var(--gomsn-sidebar-wave-filter) !important;
        }

        /* Mobile Topbar Pocket & Border menyatu rapi dengan tema */
        html[data-accent]:not([data-accent="teal"]) .gomsn-mobile-topbar {
            border-color: rgba(255, 255, 255, 0.22) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .gomsn-mobile-topbar-pocket #mobilePocketGrad stop {
            stop-color: #020617 !important;
        }
        html[data-accent]:not([data-accent="teal"]) .gomsn-mobile-topbar-pocket #mobileHighlightStroke stop {
            stop-color: var(--gomsn-accent-light) !important;
        }
        html[data-accent]:not([data-accent="teal"]) .gomsn-mobile-topbar-pocket svg path:first-child {
            opacity: 0.42 !important;
        }

        /* Mobile Bottom Navigation Bar mengikuti tema warna aplikasi */
        html[data-accent]:not([data-accent="teal"]) nav[aria-label="Navigasi Cepat Mobile"] {
            border-color: rgba(148, 163, 184, 0.35) !important;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.16) !important;
        }
        html[data-accent]:not([data-accent="teal"]) nav[aria-label="Navigasi Cepat Mobile"] .gomsn-nav-wave path {
            fill: var(--gomsn-accent) !important;
        }
        html[data-accent]:not([data-accent="teal"]) nav[aria-label="Navigasi Cepat Mobile"] .gomsn-nav-mound-stroke {
            stroke: var(--gomsn-accent-light) !important;
        }
        html[data-accent]:not([data-accent="teal"]) nav[aria-label="Navigasi Cepat Mobile"] .from-emerald-500 {
            background: var(--gomsn-active-bg) !important;
        }
        html.dark[data-accent]:not([data-accent="teal"]) nav[aria-label="Navigasi Cepat Mobile"] {
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.96) 0%, rgba(19, 28, 46, 0.98) 100%) !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
        }
        html.dark[data-accent]:not([data-accent="teal"]) nav[aria-label="Navigasi Cepat Mobile"] svg.w-\[22px\],
        html.dark[data-accent]:not([data-accent="teal"]) nav[aria-label="Navigasi Cepat Mobile"] svg.text-\[\#0f766e\],
        html.dark[data-accent]:not([data-accent="teal"]) nav[aria-label="Navigasi Cepat Mobile"] svg.text-\[\#115e59\],
        html.dark[data-accent]:not([data-accent="teal"]) nav[aria-label="Navigasi Cepat Mobile"] span.text-\[\#0f766e\] {
            color: var(--gomsn-accent-light) !important;
        }
        ::view-transition-old(desktop-sidebar),
        ::view-transition-new(desktop-sidebar) {
            animation: none;
            mix-blend-mode: normal;
        }
        html.theme-vt-active #desktop-sidebar {
            view-transition-name: none !important;
        }
        html.theme-vt-active::view-transition-old(root),
        html.theme-vt-active::view-transition-new(root) {
            animation: none !important;
            mix-blend-mode: normal !important;
        }
        html.theme-vt-active::view-transition-old(root) {
            z-index: 1;
        }
        html.theme-vt-active::view-transition-new(root) {
            z-index: 9999;
        }
        img.object-cover[src*="/avatar"] {
            object-position: center top !important;
        }
        #main-content, #main-content > * {
            overflow-anchor: none;
        }
        .overflow-x-hidden {
            overflow-x: clip !important;
            overflow-y: visible !important;
        }

        /* Modern Elegant Universal Scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.45) transparent;
        }
        html.dark * {
            scrollbar-color: rgba(148, 163, 184, 0.28) transparent;
        }
        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.45);
            border-radius: 9999px;
            border: 2px solid transparent;
            background-clip: padding-box;
            transition: background-color 0.2s ease;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #0d9488;
            background-clip: padding-box;
        }
        ::-webkit-scrollbar-corner {
            background: transparent;
        }
        ::-webkit-scrollbar-button {
            display: none;
            width: 0;
            height: 0;
        }
        html.dark ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.28);
            border: 2px solid transparent;
            background-clip: padding-box;
        }
        html.dark ::-webkit-scrollbar-thumb:hover {
            background: #14b8a6;
            background-clip: padding-box;
        }

        /* Custom Character Checkmark Icon in SweetAlert2 Success Toast */
        .swal2-popup.swal2-toast .swal2-icon.gomsn-toast-custom-icon {
            border: none !important;
            background: transparent !important;
            width: 46px !important;
            height: 46px !important;
            min-width: 46px !important;
            margin: 0 0.65rem 0 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
        .swal2-popup.swal2-toast .swal2-icon.gomsn-toast-custom-icon::before,
        .swal2-popup.swal2-toast .swal2-icon.gomsn-toast-custom-icon::after {
            display: none !important;
        }
        .swal2-popup.swal2-toast .swal2-icon.gomsn-toast-custom-icon .swal2-icon-content {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            height: 100% !important;
            font-size: inherit !important;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-[#F8FAFC] text-slate-900 font-sans antialiased"
      x-data="{ bottomSheetOpen: false, isDarkMode: document.documentElement.classList.contains('dark') }"
      @gomsn-theme-changed.window="isDarkMode = $event.detail.dark">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-lg focus:bg-[#155E63] focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white">
        Langsung ke konten utama
    </a>

    @guest
    <!-- Guest Layout (Login, OTP, Password Change) -->
    @if (request()->routeIs('login'))
        <main id="main-content" class="min-h-[100dvh] w-full overflow-x-hidden">
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    @else
    <div class="min-h-screen flex flex-col justify-between">
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 py-3 px-6">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="{{ route('login') }}" class="flex items-center gap-3">
                    <img src="{{ asset('assets/logo/logo-msn2.png') }}" 
                         alt="PT Media Solusi Network" 
                         class="h-10 w-auto object-contain">
                    <div>
                        <span class="font-heading font-bold text-base text-[#1F2937] tracking-tight block leading-none">
                            PT MEDIA SOLUSI NETWORK
                        </span>
                        <span class="text-xs text-slate-500 font-medium block mt-0.5">
                            Enterprise Portal · Keamanan UU PDP
                        </span>
                    </div>
                </a>
                <div class="flex items-center gap-2.5">
                    <button type="button"
                            @click="window.toggleGomsnTheme($event)"
                            :title="isDarkMode ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
                            class="theme-toggle-pill group relative inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200/80 bg-slate-50/90 hover:bg-white hover:border-teal-400 transition-all duration-300 cursor-pointer shadow-2xs">
                        <!-- Sun Icon -->
                        <svg class="theme-icon-sun w-4.5 h-4.5 text-amber-500 transition-all duration-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="4" fill="currentColor" fill-opacity="0.2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                        </svg>
                        <!-- Moon Icon -->
                        <svg class="theme-icon-moon w-4.5 h-4.5 text-cyan-300 transition-all duration-500 absolute" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.22" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                        </svg>
                    </button>
                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 border border-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-600 animate-pulse"></span>
                        <span>Server Prod Normal</span>
                    </div>
                </div>
            </div>

        </header>

        <main id="main-content" class="flex-1 py-8 px-4 sm:px-6">
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>

        <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-200/60">
            Hak Cipta &copy; {{ date('Y') }} PT Media Solusi Network · Seluruh Hak Dilindungi UU PDP No. 27/2022
        </footer>
    </div>
    @endif
    @endguest

    @auth
    <!-- Authenticated Enterprise Shell (Dual-Panel Layout) -->
    <div class="min-h-screen flex flex-row">
        <!-- Left Sidebar Navigation (Desktop Only: hidden on mobile, md:flex) -->
        <aside id="desktop-sidebar"
               class="hidden md:flex md:sticky top-0 inset-y-0 left-0 z-40 w-64 text-white flex-col justify-between h-screen shadow-2xl shrink-0 border-r border-teal-400/25 relative overflow-hidden select-none">
            
            <!-- Ambient Sculpted Waves & Clean Parallel Fiber Contours -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
                <!-- Ambient Radial Glow Spots -->
                <div class="absolute -top-20 -right-16 w-56 h-56 rounded-full bg-teal-400/12 blur-3xl pointer-events-none"></div>
                <div class="absolute top-1/2 -left-24 w-56 h-56 rounded-full bg-cyan-400/8 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-16 -right-12 w-64 h-64 rounded-full bg-teal-300/10 blur-3xl pointer-events-none"></div>

                <!-- Harmonious Parallel Contour Waves & Fiber Nodes -->
                <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 256 900" fill="none" preserveAspectRatio="none">
                    <defs>
                        <!-- Sculpted Translucent Wave Fills -->
                        <linearGradient id="sbWaveFill1" x1="100%" y1="0%" x2="20%" y2="100%">
                            <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.10"/>
                            <stop offset="55%" stop-color="#14b8a6" stop-opacity="0.05"/>
                            <stop offset="100%" stop-color="#0d9488" stop-opacity="0.0"/>
                        </linearGradient>
                        <linearGradient id="sbWaveFill2" x1="100%" y1="20%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.06"/>
                            <stop offset="60%" stop-color="#14b8a6" stop-opacity="0.04"/>
                            <stop offset="100%" stop-color="#042f2e" stop-opacity="0.0"/>
                        </linearGradient>
                        <linearGradient id="sbWaveFill3" x1="0%" y1="100%" x2="80%" y2="55%">
                            <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.08"/>
                            <stop offset="65%" stop-color="#14b8a6" stop-opacity="0.03"/>
                            <stop offset="100%" stop-color="#042f2e" stop-opacity="0.0"/>
                        </linearGradient>

                        <!-- Smooth Fade-in-Fade-out Parallel Stroke Gradients -->
                        <linearGradient id="sbContour1" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#5eead4" stop-opacity="0.0"/>
                            <stop offset="18%" stop-color="#5eead4" stop-opacity="0.34"/>
                            <stop offset="52%" stop-color="#2dd4bf" stop-opacity="0.42"/>
                            <stop offset="85%" stop-color="#38bdf8" stop-opacity="0.22"/>
                            <stop offset="100%" stop-color="#5eead4" stop-opacity="0.0"/>
                        </linearGradient>
                        <linearGradient id="sbContour2" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.0"/>
                            <stop offset="22%" stop-color="#5eead4" stop-opacity="0.22"/>
                            <stop offset="55%" stop-color="#38bdf8" stop-opacity="0.28"/>
                            <stop offset="88%" stop-color="#2dd4bf" stop-opacity="0.14"/>
                            <stop offset="100%" stop-color="#14b8a6" stop-opacity="0.0"/>
                        </linearGradient>
                        <linearGradient id="sbContour3" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#99f6e4" stop-opacity="0.0"/>
                            <stop offset="25%" stop-color="#99f6e4" stop-opacity="0.16"/>
                            <stop offset="60%" stop-color="#5eead4" stop-opacity="0.20"/>
                            <stop offset="92%" stop-color="#2dd4bf" stop-opacity="0.08"/>
                            <stop offset="100%" stop-color="#14b8a6" stop-opacity="0.0"/>
                        </linearGradient>

                        <!-- Radial Glow for Subtle Fiber Nodes -->
                        <radialGradient id="sbNodeGlow" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#99f6e4" stop-opacity="0.65"/>
                            <stop offset="50%" stop-color="#2dd4bf" stop-opacity="0.22"/>
                            <stop offset="100%" stop-color="#2dd4bf" stop-opacity="0.0"/>
                        </radialGradient>
                    </defs>

                    <!-- 3 Sculpted Translucent Wave Layers (Clean Logo Headroom: starts upper-right) -->
                    <path d="M 192 0 C 232 135, 118 285, 168 465 C 216 635, 130 770, 212 900 L 256 900 L 256 0 Z" fill="url(#sbWaveFill1)"/>
                    <path d="M 224 0 C 254 150, 152 300, 198 480 C 242 645, 168 780, 240 900 L 256 900 L 256 0 Z" fill="url(#sbWaveFill2)"/>
                    <path d="M 0 560 C 58 615, 42 735, 128 820 C 165 858, 198 882, 224 900 L 0 900 Z" fill="url(#sbWaveFill3)"/>

                    <!-- 5 Smooth Parallel Contour Curves (No crossing clutter, no dashed lines) -->
                    <path d="M 192 0 C 232 135, 118 285, 168 465 S 130 770, 212 900" stroke="url(#sbContour1)" stroke-width="1.4" stroke-linecap="round"/>
                    <path d="M 208 0 C 248 135, 134 285, 184 465 S 146 770, 228 900" stroke="url(#sbContour2)" stroke-width="1.15" stroke-linecap="round"/>
                    <path d="M 224 0 C 264 135, 150 285, 200 465 S 162 770, 244 900" stroke="url(#sbContour3)" stroke-width="1" stroke-linecap="round"/>
                    <path d="M 176 0 C 216 135, 102 285, 152 465 S 114 770, 196 900" stroke="url(#sbContour2)" stroke-width="1" stroke-linecap="round" opacity="0.8"/>
                    <path d="M 160 0 C 200 135, 86 285, 136 465 S 98 770, 180 900" stroke="url(#sbContour3)" stroke-width="0.85" stroke-linecap="round" opacity="0.65"/>

                    <!-- 3 Subtle Glowing Fiber Nodes along the Primary Contour Curve -->
                    <circle cx="177" cy="245" r="9" fill="url(#sbNodeGlow)"/>
                    <circle cx="177" cy="245" r="2.2" fill="#99f6e4" fill-opacity="0.85"/>

                    <circle cx="168" cy="465" r="10" fill="url(#sbNodeGlow)"/>
                    <circle cx="168" cy="465" r="2.4" fill="#5eead4" fill-opacity="0.9"/>

                    <circle cx="165" cy="705" r="8" fill="url(#sbNodeGlow)"/>
                    <circle cx="165" cy="705" r="2" fill="#7dd3fc" fill-opacity="0.8"/>
                </svg>

                <!-- Top & Edge Specular Gloss Highlights (matching Page Header top rim) -->
                <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
                <div class="absolute inset-y-0 right-0 w-[1px] bg-gradient-to-b from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>
            </div>

            <!-- Sidebar Top: Brand Logo (Full White & Centered) -->
            <div class="p-4 sm:p-5 pb-3.5 border-b border-cyan-400/20 relative z-10">
                <div class="flex items-center justify-center w-full">
                    <a href="{{ route('portal.hub') }}" class="flex items-center justify-center group py-0.5 mx-auto">
                        <img src="{{ asset('assets/logo/logo1.png') }}" 
                             alt="PT Media Solusi Network" 
                             style="filter: brightness(0) invert(1) !important;"
                             class="h-8 sm:h-8.5 w-auto max-w-[160px] object-contain group-hover:scale-105 transition-transform duration-200 mx-auto">
                    </a>
                </div>
            </div>


            <!-- Sidebar Scrollable Nav Menu -->
            <nav id="desktop-sidebar-nav"
                 @scroll.passive="sessionStorage.setItem('gomsn_sb_scroll', $el.scrollTop)"
                 @click="if ($event.target.closest('a')) sessionStorage.setItem('gomsn_sb_scroll', $el.scrollTop)"
                 class="flex-1 overflow-y-auto px-3 py-3.5 space-y-4 sidebar-scroll text-[11px] relative z-10">
                
                <!-- Portal Hub Utama -->
                <div>
                    <a href="{{ route('portal.hub') }}"
                       @if (request()->routeIs('portal.hub')) data-sidebar-active="true" style="background: var(--gomsn-active-bg) !important;" @endif
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all {{ request()->routeIs('portal.hub') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white font-medium hover:bg-white/15' }}">
                        <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                        @if (request()->routeIs('portal.hub'))
                        <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                        @endif
                    </a>
                </div>

                <!-- Bagian 1: Pengajuan & Lembur (Non-Dropdown / Selalu Terbuka) -->
                @php
                    $navUser = auth()->user();
                    $navIsHrdOnly = $navUser && $navUser->hasRole('hrd') && ! $navUser->hasRole(['super_admin', 'admin']);
                    $isHrdRole = $navIsHrdOnly;
                    $navCanApprovePengajuan = $navUser && ! $navIsHrdOnly && ($navUser->can('gomsn.pengajuan.approve_atasan') || $navUser->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'super_admin', 'admin']));
                    $navCanApproveKoreksi = $navUser && ! $navIsHrdOnly && ($navUser->can('attendance.correction.approve') || $navUser->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'super_admin', 'admin']));
                    $navIsAtasan = $navUser && ($navCanApprovePengajuan || $navUser->can('gomsn.lembur.approve'));
                    $navIsFinance = $navUser && ($navUser->hasRole(['finance', 'admin', 'super_admin']) || $navUser->can('gomsn.pengajuan.approve_finance'));
                    $navPendingAtasanCount = 0;
                    $navPendingLemburCount = 0;
                    $navPendingKoreksiCount = 0;
                    $navPendingLeaveHrdCount = 0;
                    $navLowStockCount = 0;
                    if ($navCanApprovePengajuan) {
                        $navQ = \App\Models\Pengajuan::where('status', 'menunggu_atasan')->where('user_id', '!=', $navUser->id);
                        if (! $navUser->hasRole(['super_admin', 'admin'])) {
                            $navQ->where('atasan_id', $navUser->id);
                        }
                        $navPendingAtasanCount = $navQ->count();
                    }
                    if ($navIsAtasan) {
                        $navLQ = \App\Models\Lembur::where('status', 'pending')->where('user_id', '!=', $navUser->id);
                        if (! $navUser->hasRole(['super_admin', 'admin', 'hrd'])) {
                            $navLQ->where(function ($q) use ($navUser) {
                                $q->where('atasan_id', $navUser->id);
                                if (! empty($navUser->division)) {
                                    $q->orWhereHas('user', function ($qu) use ($navUser) {
                                        $qu->whereRaw('LOWER(division) = ?', [strtolower($navUser->division)]);
                                    });
                                }
                            });
                        }
                        $navPendingLemburCount = $navLQ->count();
                    }
                    if ($navCanApproveKoreksi) {
                        $navCQ = \App\Models\AttendanceCorrection::whereIn('status', ['pending_supervisor', 'pending_hrd']);
                        if (! $navUser->hasRole(['super_admin', 'admin'])) {
                            $navCQ->where('user_id', '!=', $navUser->id);
                            if (! empty($navUser->division)) {
                                $navCQ->whereHas('user', function ($qu) use ($navUser) {
                                    $qu->whereRaw('LOWER(division) = ?', [strtolower($navUser->division)]);
                                });
                            }
                        }
                        $navPendingKoreksiCount = $navCQ->count();
                    }
                    if ($isHrdRole || ($navUser && $navUser->hasRole(['super_admin', 'admin']))) {
                        $navPendingLeaveHrdCount = \App\Models\Leave::whereIn('status', ['pending_hrd', 'pending_supervisor'])->count();
                    }
                    if ($navIsFinance) {
                        $navLowStockCount = 0;
                    }
                    $isPengajuanActive = request()->routeIs('gomsn.pengajuan.*') || request()->routeIs('attendance.corrections.*') || request()->routeIs('attendance.leaves.approvals*') || request()->routeIs('gomsn.overtime.*') || request()->routeIs('gomsn.inventory.*');
                    $totalPengajuanBadges = $navPendingAtasanCount + $navPendingLemburCount + $navPendingKoreksiCount + $navPendingLeaveHrdCount;

                    // Hitung jumlah tiket aktif yang ditujukan untuk teknisi / atasan teknik
                    $navAssignedTicketCount = 0;
                    if ($navUser) {
                        $navUserRoles = $navUser->roles->pluck('name')->toArray();
                        $isNavAtasanTeknik = ! empty(array_intersect(['super_admin', 'admin', 'atasan_teknik', 'atasan_umum', 'atasan', 'manager_teknisi'], $navUserRoles));
                        if ($isNavAtasanTeknik) {
                            $navAssignedTicketCount = \App\Models\Tiket::whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                                ->where(function ($q) use ($navUser) {
                                    $q->doesntHave('ticketAssignees')
                                      ->orWhereHas('ticketAssignees', fn ($sq) => $sq->where('user_id', $navUser->id));
                                })->count();
                        } else {
                            $navAssignedTicketCount = \App\Models\Tiket::whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                                ->whereHas('ticketAssignees', fn ($sq) => $sq->where('user_id', $navUser->id))
                                ->count();
                        }
                    }

                    // Data ringkas notifikasi & aksi pending untuk badge lonceng di header (Desktop & Mobile)
                    $navBellActionCount = ($totalPengajuanBadges ?? 0) + ($navAssignedTicketCount ?? 0);
                    $navBellNotifItems = [];
                    if ($navUser) {
                        try {
                            $navBellUid = (int) $navUser->id;
                            $navBellLogs = \App\Models\NotificationLog::query()
                                ->whereNotIn('event_type', ['shift_reminder_clock_in', 'shift_reminder_clock_out'])
                                ->where(function ($q) use ($navBellUid) {
                                    $q->where(function ($sub) use ($navBellUid) {
                                        $sub->where('user_id', $navBellUid)->where('event_type', '!=', 'broadcast');
                                    })->orWhere(function ($sub) use ($navBellUid) {
                                        $sub->where('event_type', 'broadcast')->where(function ($b) use ($navBellUid) {
                                            $b->whereJsonContains('payload->user_ids', $navBellUid)
                                              ->orWhere('payload->target_type', 'all');
                                        });
                                    });
                                })
                                ->orderByDesc('sent_at')
                                ->orderByDesc('id')
                                ->limit(30)
                                ->get(['id', 'payload', 'sent_at', 'created_at']);

                            foreach ($navBellLogs as $bl) {
                                $bp = is_array($bl->payload) ? $bl->payload : [];
                                $bt = mb_strtolower((string) ($bp['title'] ?? ''));
                                if (str_contains($bt, 'pengingat absen masuk') || str_contains($bt, 'pengingat absen pulang')) {
                                    continue;
                                }
                                $navBellNotifItems[] = [
                                    'id' => $bl->id,
                                    'ts' => ($bl->sent_at ?? $bl->created_at)?->timestamp ?? 0,
                                ];
                            }
                        } catch (\Throwable $e) {
                            $navBellNotifItems = [];
                        }
                    }
                    $navBellConfig = [
                        'userId' => $navUser?->id ?? 0,
                        'actionCount' => $navBellActionCount,
                        'items' => $navBellNotifItems,
                    ];
                @endphp
                <script>
                    window.__gomsnBellConfig = @json($navBellConfig);
                    function gomsnHeaderBell() {
                        const cfg = window.__gomsnBellConfig || {};
                        return {
                            badgeCount: cfg.actionCount || 0,
                            init() {
                                this.recalc();
                                window.addEventListener('gomsn-notif-updated', () => this.recalc());
                            },
                            recalc() {
                                this.badgeCount = cfg.actionCount || 0;
                            }
                        };
                    }
                </script>
                <div class="space-y-1">
                    <div class="pt-2.5 pb-1 px-3 flex items-center justify-between text-white/90 select-none">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-teal-200/90 font-mono">
                                PENGAJUAN &amp; LEMBUR
                            </span>
                            <div class="h-px flex-1 bg-white/15"></div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <!-- 1. Pengajuan Saya (Mandiri) -->
                        <a href="{{ route('gomsn.pengajuan.index') }}"
                           @if (request()->routeIs('gomsn.pengajuan.index') || request()->routeIs('gomsn.pengajuan.create') || (request()->routeIs('gomsn.pengajuan.show') && ! request()->routeIs('gomsn.pengajuan.approvals'))) data-sidebar-active="true" style="background: var(--gomsn-active-bg) !important;" @endif
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ (request()->routeIs('gomsn.pengajuan.index') || request()->routeIs('gomsn.pengajuan.create') || (request()->routeIs('gomsn.pengajuan.show') && ! request()->routeIs('gomsn.pengajuan.approvals'))) ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>{{ $navCanApprovePengajuan ? 'Pengajuan Saya' : 'Pengajuan' }}</span>
                            @if (request()->routeIs('gomsn.pengajuan.index') || request()->routeIs('gomsn.pengajuan.create'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>

                        <!-- 2. Pengajuan Karyawan (Khusus Atasan — Tidak Muncul di Role HRD) -->
                        @if ($navCanApprovePengajuan)
                        <a href="{{ route('gomsn.pengajuan.approvals') }}"
                           @if (request()->routeIs('gomsn.pengajuan.approvals')) data-sidebar-active="true" style="background: var(--gomsn-active-bg) !important;" @endif
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('gomsn.pengajuan.approvals') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('gomsn.pengajuan.approvals') ? 'text-white' : 'text-amber-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Pengajuan Karyawan</span>
                            </div>
                            @if ($navPendingAtasanCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-400 text-slate-900 shadow-xs">
                                {{ $navPendingAtasanCount }}
                            </span>
                            @endif
                        </a>
                        @endif

                        <!-- 3. Pengajuan Absen (Khusus Atasan & Admin: Koreksi Kehadiran Mandiri Karyawan) -->
                        @if ($navCanApproveKoreksi)
                        <a href="{{ route('attendance.corrections.approvals') }}"
                           @if (request()->routeIs('attendance.corrections.approvals')) data-sidebar-active="true" style="background: var(--gomsn-active-bg) !important;" @endif
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('attendance.corrections.approvals') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('attendance.corrections.approvals') ? 'text-white' : 'text-emerald-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <span>Pengajuan Absen</span>
                            </div>
                            @if ($navPendingKoreksiCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-400 text-slate-900 shadow-xs">
                                {{ $navPendingKoreksiCount }}
                            </span>
                            @endif
                        </a>
                        @endif

                        @php
                            $isOvertimeRoute = request()->routeIs('gomsn.overtime.*');
                            $isOvertimeSaya = $isOvertimeRoute && request('view_mode') === 'saya';
                            $isOvertimeKaryawan = $isOvertimeRoute && request('view_mode') !== 'saya';
                        @endphp

                        @if ($isHrdRole)
                        <!-- Khusus HRD: Tombol Lembur Saya (Pengajuan & Riwayat Milik Sendiri) -->
                        <a href="{{ route('gomsn.overtime.index', ['view_mode' => 'saya']) }}"
                           @if ($isOvertimeSaya) data-sidebar-active="true" style="background: var(--gomsn-active-bg) !important;" @endif
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ $isOvertimeSaya ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Lembur Saya</span>
                            </div>
                            @if ($isOvertimeSaya)
                            <span class="w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>
                        @elseif (! $navIsHrdOnly)
                        <a href="{{ route('gomsn.overtime.index') }}"
                           @if ($isOvertimeRoute) data-sidebar-active="true" style="background: var(--gomsn-active-bg) !important;" @endif
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ $isOvertimeRoute ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $navIsAtasan ? 'Pengajuan Lembur' : 'Lembur' }}</span>
                            </div>
                            @if ($navPendingLemburCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-400 text-slate-900 shadow-xs">
                                {{ $navPendingLemburCount }}
                            </span>
                            @elseif ($isOvertimeRoute)
                            <span class="w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>
                        @endif

                        <!-- 4. Stok Barang Kantor / Gudang -->
                        <a href="{{ route('gomsn.inventory.index') }}"
                           @if (request()->routeIs('gomsn.inventory.*')) data-sidebar-active="true" style="background: var(--gomsn-active-bg) !important;" @endif
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('gomsn.inventory.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Stok Barang</span>
                            </div>
                            @if (request()->routeIs('gomsn.inventory.*'))
                            <span class="w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>
                    </div>
                </div>

                <!-- Bagian 2 & 3: Absensi & Cuti / Kelola Karyawan / Pekerjaan & Tiketing (Urutan disesuaikan role NOC) -->
                @php
                    $sidebarUser = auth()->user();
                    $canViewReports = $sidebarUser && $sidebarUser->hasRole(['super_admin', 'admin']) && ! $sidebarUser->hasRole('finance') && strtolower($sidebarUser->division ?? '') !== 'finance';
                    $isHrdRole = $sidebarUser && $sidebarUser->hasRole('hrd') && ! $sidebarUser->hasRole(['super_admin', 'admin']);
                    $canViewEmployeeRecap = $sidebarUser && $sidebarUser->hasRole(['super_admin', 'admin', 'hrd']);

                    $isOvertimeRoute = request()->routeIs('gomsn.overtime.*');
                    $isOvertimeSaya = $isOvertimeRoute && request('view_mode') === 'saya';
                    $isOvertimeKaryawan = $isOvertimeRoute && request('view_mode') !== 'saya';

                    $isKelolaKaryawanActive = request()->routeIs('portal.employees.*', 'attendance.employee-recap*', 'attendance.leaves.approvals*', 'gomsn.car-usage.*')
                        || ($isOvertimeKaryawan && $isHrdRole)
                        || ($isHrdRole && request()->routeIs('attendance.leaderboard'));

                    $isAbsensiActive = (request()->routeIs('attendance.*') && ! request()->routeIs('attendance.corrections.approvals')) && ! $isKelolaKaryawanActive;
                    $isRiwayatActive = request()->routeIs('attendance.history', 'attendance.reports*');

                    $isTiketingActive = request()->routeIs('tickets.*') || request()->routeIs('gomsn.network.*') || request()->routeIs('coverage');
                    $isLainnyaActive = request()->routeIs('gomsn.analytics.*') || request()->routeIs('portal.sessions') || request()->routeIs('portal.notifications.*') || request()->routeIs('portal.settings') || request()->routeIs('portal.audit');

                    $isNocRole = $sidebarUser && ($sidebarUser->hasRole(['noc', 'atasan_noc', 'karyawan_noc']) || str_contains(strtolower($sidebarUser->division ?? ''), 'noc'));
                @endphp

                @if ($isNocRole)
                    <!-- Role NOC: Menu Pekerjaan & Tiketing berada di atas Absensi & Cuti -->
                    @include('layouts.partials.sidebar-tiketing')
                    @include('layouts.partials.sidebar-absensi')
                    @include('layouts.partials.sidebar-kelola-karyawan')
                @else
                    <!-- Default: Menu Absensi & Cuti, Kelola Karyawan, lalu Pekerjaan & Tiketing -->
                    @include('layouts.partials.sidebar-absensi')
                    @include('layouts.partials.sidebar-kelola-karyawan')
                    @include('layouts.partials.sidebar-tiketing')
                @endif

                @include('layouts.partials.sidebar-lainnya')
            </nav>

            <!-- Instant Synchronous Sidebar State & Scroll Restoration (Zero Jump / Zero Refresh) -->
            <script>
                (function () {
                    try {
                        var nav = document.getElementById('desktop-sidebar-nav');
                        if (!nav) return;

                        @if ($isAbsensiActive)
                            sessionStorage.setItem('gomsn_sb_absensi', '1');
                        @else
                            sessionStorage.removeItem('gomsn_sb_absensi');
                        @endif
                        @if ($isKelolaKaryawanActive)
                            sessionStorage.setItem('gomsn_sb_kelola_karyawan', '1');
                        @endif
                        @if ($isRiwayatActive)
                            sessionStorage.setItem('gomsn_sb_riwayat', '1');
                        @endif
                        @if ($isTiketingActive)
                            sessionStorage.setItem('gomsn_sb_tiketing', '1');
                        @else
                            sessionStorage.removeItem('gomsn_sb_tiketing');
                        @endif
                        @if ($isLainnyaActive)
                            sessionStorage.setItem('gomsn_sb_lainnya', '1');
                        @endif

                        var elAbsensi = document.getElementById('sb-menu-absensi');
                        if (elAbsensi) {
                            @if ($isAbsensiActive)
                                elAbsensi.style.display = '';
                            @else
                                elAbsensi.style.display = 'none';
                            @endif
                        }
                        var elKelola = document.getElementById('sb-menu-kelola-karyawan');
                        if (elKelola && sessionStorage.getItem('gomsn_sb_kelola_karyawan') === '1') {
                            elKelola.style.display = '';
                        }
                        var elRiwayat = document.getElementById('sb-menu-riwayat');
                        if (elRiwayat && sessionStorage.getItem('gomsn_sb_riwayat') === '1') {
                            elRiwayat.style.display = '';
                        }
                        var elTiketing = document.getElementById('sb-menu-tiketing');
                        if (elTiketing) {
                            @if ($isTiketingActive)
                                elTiketing.style.display = '';
                            @else
                                elTiketing.style.display = 'none';
                            @endif
                        }
                        var elLainnya = document.getElementById('sb-menu-lainnya');
                        if (elLainnya && sessionStorage.getItem('gomsn_sb_lainnya') === '1') {
                            elLainnya.style.display = '';
                        }

                        var savedScroll = sessionStorage.getItem('gomsn_sb_scroll');
                        if (savedScroll !== null) {
                            nav.scrollTop = parseInt(savedScroll, 10) || 0;
                        }
                    } catch (e) {}
                })();
            </script>

            <!-- Sidebar User Profile Footer (Blended Metallic Ocean Teal) -->
            <div class="p-3.5 border-t border-cyan-400/20 backdrop-blur-md relative z-10" style="background: linear-gradient(180deg, rgba(3, 30, 38, 0.90) 0%, rgba(2, 20, 26, 0.98) 100%) !important;">
                <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-[#5eead4]/40 to-transparent pointer-events-none"></div>
                <div class="flex items-center justify-between gap-2.5 relative z-10">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-[#18C4C4] to-[#0D9488] border border-white/20 flex items-center justify-center font-bold text-white text-xs shrink-0 shadow-sm">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <span class="text-[13px] font-bold text-white block truncate leading-tight">
                                {{ auth()->user()->name }}
                            </span>
                            <span class="text-[11px] text-teal-100/90 block truncate font-mono mt-0.5">
                                {{ auth()->user()->employee_code }} · {{ auth()->user()->getRoleNames()->first() ?? 'Staff' }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" title="Keluar dari Portal" class="p-2 rounded-xl text-white/90 hover:text-rose-300 hover:bg-white/10 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- Right Main Panel -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
            <!-- Mobile Fixed Header (Sculpted Metallic Teal Bar - Always Pinned on Scroll) -->
            <div class="md:hidden fixed top-0 inset-x-0 z-50 pt-2 px-3 pb-1.5 bg-slate-50/95 backdrop-blur-md select-none">
                <div class="gomsn-mobile-topbar relative h-[48px] w-full rounded-[22px] flex items-center justify-between pl-3.5 pr-0 border border-[#1cd8d2]/35 shadow-[0_4px_18px_rgba(4,40,44,0.38)]"
                     style="background: var(--gomsn-header-bg) !important;">
                    
                    <!-- Decorative Background & Highlights Layer (Clipped to Pill Corners) -->
                    <div class="absolute inset-0 rounded-[22px] overflow-hidden pointer-events-none">
                        <!-- Specular Highlights (Top & Bottom Gloss Rims) -->
                        <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/80 to-transparent"></div>
                        <div class="absolute inset-x-3 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/45 to-transparent"></div>
                        <div class="absolute inset-0 bg-gradient-to-b from-white/[0.10] via-transparent to-black/[0.20]"></div>
                    </div>

                    <!-- Left: White GoMSN Logo -->
                    <a href="{{ route('portal.hub') }}" class="relative z-10 flex items-center group py-0.5 shrink-0" title="GoMSN Portal">
                        <img src="{{ asset('assets/logo/logo1.png') }}" 
                             alt="GoMSN" 
                             style="filter: brightness(0) invert(1) !important;"
                             class="h-6 sm:h-7 w-auto max-w-[125px] object-contain drop-shadow-[0_1px_2px_rgba(0,0,0,0.6)]">
                    </a>

                    <!-- Right: Actions inside sculpted bezel (Pocket Crease always sits cleanly to the left of Dark Mode Orb) -->
                    <div class="relative z-10 h-full flex items-center gap-2 pl-11 pr-3.5">
                        <!-- Sculpted Aerodynamic Wave SVG on Right Pocket (Anchored to left edge of action group) -->
                        <div class="gomsn-mobile-topbar-pocket absolute inset-0 pointer-events-none overflow-hidden rounded-r-[22px] z-0">
                            <svg class="absolute left-0 top-0 h-full w-[500px] max-w-none" viewBox="0 0 500 48" fill="none" preserveAspectRatio="none">
                                <path d="M 38 0 C 28 14 14 28 0 38 L 0 48 L 500 48 L 500 0 Z" fill="url(#mobilePocketGrad)" opacity="0.88"/>
                                <path d="M 38 0 C 28 14 14 28 0 38 L 0 48" stroke="url(#mobileHighlightStroke)" stroke-width="2" stroke-linecap="round"/>
                                <defs>
                                    <linearGradient id="mobilePocketGrad" x1="0%" y1="0%" x2="45%" y2="100%">
                                        <stop offset="0%" stop-color="#042023"/>
                                        <stop offset="50%" stop-color="#072d31"/>
                                        <stop offset="100%" stop-color="#051f22"/>
                                    </linearGradient>
                                    <linearGradient id="mobileHighlightStroke" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#5eead4" stop-opacity="0.9"/>
                                        <stop offset="50%" stop-color="#2dd4bf" stop-opacity="1"/>
                                        <stop offset="100%" stop-color="#0d9488" stop-opacity="0.4"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>

                        <!-- Mobile Notification Bell Button (Mengarah ke Pusat Notifikasi + Badge Unread) -->
                        <a href="{{ route('portal.notifications.inbox') }}"
                           x-data="gomsnHeaderBell()"
                           title="Notifikasi"
                           aria-label="Notifikasi"
                           class="relative z-10 w-[33px] h-[33px] rounded-full bg-white/12 hover:bg-white/22 active:scale-90 border {{ request()->routeIs('portal.notifications.inbox') ? 'border-white/70 bg-white/25' : 'border-white/25' }} flex items-center justify-center text-white shadow-sm transition-all duration-200 cursor-pointer shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span x-show="badgeCount > 0" x-cloak
                                  class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full bg-rose-500 text-white text-[9px] font-black flex items-center justify-center ring-2 ring-slate-900/40 shadow-xs"
                                  x-text="badgeCount > 9 ? '9+' : badgeCount"></span>
                        </a>

                        <!-- Avatar with Solid White Ring + Name & Division + Chevron Down (Dropdown Menu) -->
                        <div class="relative z-10" x-data="{ mobileUserMenuOpen: false }" @click.outside="mobileUserMenuOpen = false">
                            <button @click="mobileUserMenuOpen = !mobileUserMenuOpen"
                                    type="button"
                                    aria-haspopup="true"
                                    :aria-expanded="mobileUserMenuOpen"
                                    class="flex items-center gap-2 cursor-pointer focus:outline-none select-none pl-1">
                                
                                <!-- User Name & Division Beside Profile (User Request 1) -->
                                <div class="text-right leading-tight max-w-[105px] sm:max-w-[150px] min-w-0">
                                    <span class="block text-xs font-bold text-white truncate drop-shadow-xs">{{ auth()->user()->name }}</span>
                                    <span class="block text-[10px] text-teal-200/90 font-medium truncate">{{ auth()->user()->division ?: (auth()->user()->employee_code ?: 'MSN') }}</span>
                                </div>

                                <!-- Avatar circle with solid 2px white border ring -->
                                <div class="relative w-[35px] h-[35px] shrink-0">
                                    @if (auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" 
                                             alt="" 
                                             onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                             class="w-[35px] h-[35px] rounded-full border-2 border-white object-cover shadow-md">
                                        <div style="display: none;" class="w-[35px] h-[35px] rounded-full border-2 border-white bg-gradient-to-tr from-[#18C4C4] to-[#155E63] text-white items-center justify-center font-bold text-xs shadow-md shrink-0">
                                            {{ auth()->user()->initials }}
                                        </div>
                                    @else
                                        <div class="w-[35px] h-[35px] rounded-full border-2 border-white bg-gradient-to-tr from-[#18C4C4] to-[#155E63] text-white flex items-center justify-center font-bold text-xs shadow-md shrink-0">
                                            {{ auth()->user()->initials }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Pure White Down Chevron -->
                                <svg class="w-3.5 h-3.5 text-white transition-transform duration-200 shrink-0"
                                     :class="mobileUserMenuOpen ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Mobile User Dropdown Menu -->
                            <div x-show="mobileUserMenuOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                 class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-1.5 z-50 divide-y divide-slate-100 select-auto">
                                
                                <!-- Header Ringkasan Akun -->
                                <div class="px-4 py-3 flex items-center gap-3">
                                    @if (auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" 
                                             alt="" 
                                             onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                             class="h-9 w-9 rounded-xl object-cover border border-slate-200 shadow-xs shrink-0">
                                        <div style="display: none;" class="h-9 w-9 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#155E63] text-white items-center justify-center font-bold text-xs shadow-xs shrink-0">
                                            {{ auth()->user()->initials }}
                                        </div>
                                    @else
                                        <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#155E63] text-white flex items-center justify-center font-bold text-xs shadow-xs shrink-0">
                                            {{ auth()->user()->initials }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                        <p class="text-[10px] text-teal-700 font-mono font-medium truncate">{{ auth()->user()->employee_code }}</p>
                                        <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->division ?: 'MSN Member' }}</p>
                                    </div>
                                </div>

                                <!-- Menu Tautan -->
                                <div class="py-1">
                                    <a href="{{ route('portal.profile') }}" 
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>Profile</span>
                                    </a>

                                    <a href="{{ route('portal.settings') }}" 
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>Pengaturan</span>
                                    </a>

                                    @if (auth()->user() && auth()->user()->hasRole(['super_admin', 'admin']))
                                    <a href="{{ route('portal.sessions') }}" 
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Perangkat &amp; Sesi Login</span>
                                    </a>
                                    @endif

                                    @if (auth()->user() && (auth()->user()->hasRole(['super_admin', 'admin', 'hrd']) || auth()->user()->can('attendance.employee.manage')))
                                    <a href="{{ route('portal.employees.index') }}" 
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span>Data Karyawan</span>
                                    </a>
                                    @endif

                                    @if (auth()->user() && (auth()->user()->hasRole(['super_admin', 'admin']) || (auth()->user()->can('attendance.employee.manage') && ! auth()->user()->hasRole('hrd'))))
                                    <a href="{{ route('portal.notifications.index') }}" 
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                        <span>Kirim Notifikasi</span>
                                    </a>

                                    <a href="{{ route('portal.notifications.history') }}" 
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Riwayat Notifikasi</span>
                                    </a>
                                    @endif
                                </div>

                                <!-- Logout Form & Button -->
                                <div class="py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer">
                                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            <span>Logout / Keluar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spacer for Fixed Mobile Header so content is never covered -->
            <div class="md:hidden h-[58px] shrink-0" aria-hidden="true"></div>

            <!-- Desktop Header (Pristine Clean White with Elegant Visual Highlights) -->
            <header class="hidden md:flex sticky top-0 z-30 bg-white/95 text-slate-900 backdrop-blur-xl border-b border-slate-200/90 h-16 items-center justify-between px-6 sm:px-8 gap-4 select-none relative transition-colors shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
                <!-- Left: Live Date & Enterprise Status Badge -->
                <div class="flex items-center gap-3 relative z-10 min-w-0">
                    <!-- Date Pill with Calendar Icon -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50/90 border border-slate-200/80 text-slate-700 text-xs font-semibold shadow-2xs">
                        <div class="w-5 h-5 rounded-lg bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="tracking-tight text-slate-800">
                            {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        </span>
                    </div>

                    <!-- Online Status Pill -->
                    <div class="hidden lg:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50/90 border border-emerald-200/70 text-emerald-700 text-[11px] font-semibold">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Portal Aktif</span>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3 shrink-0 relative z-10">
                    <!-- Shift Pill (Professional Badging) -->
                    <div class="inline-flex items-center gap-2 rounded-xl bg-slate-50/90 hover:bg-slate-100/90 px-3 py-1.5 text-xs font-semibold text-slate-700 border border-slate-200/80 shadow-2xs transition">
                        <div class="w-4.5 h-4.5 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-slate-800 font-bold">Shift Pagi</span>
                        <span class="text-slate-400 font-normal">·</span>
                        <span class="text-teal-700 font-mono text-[11px]">WIB</span>
                    </div>

                    <!-- Tombol Notifikasi Desktop (Di Samping Profile, Rapi, Sesuai Tema + Badge Unread) -->
                    <a href="{{ route('portal.notifications.inbox') }}"
                       x-data="gomsnHeaderBell()"
                       title="Pusat Notifikasi"
                       aria-label="Pusat Notifikasi"
                       class="relative inline-flex items-center justify-center w-9 h-9 rounded-xl border transition-all duration-200 shadow-2xs cursor-pointer {{ request()->routeIs('portal.notifications.inbox') ? 'bg-teal-50 border-teal-400 text-teal-700' : 'bg-slate-50/90 hover:bg-white border-slate-200/80 hover:border-teal-400 text-slate-600 hover:text-teal-600' }}">
                        <svg class="w-4.5 h-4.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="badgeCount > 0" x-cloak
                              class="absolute -top-1 -right-1 min-w-[17px] h-[17px] px-1 rounded-full bg-rose-500 text-white text-[9.5px] font-black flex items-center justify-center ring-2 ring-white shadow-xs"
                              x-text="badgeCount > 9 ? '9+' : badgeCount"></span>
                    </a>

                    <!-- Divider -->
                    <div class="h-6 w-px bg-slate-200"></div>

                    <!-- Profile Dropdown (Card Pill) -->
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button @click="userMenuOpen = !userMenuOpen"
                                @click.outside="userMenuOpen = false"
                                type="button"
                                class="flex items-center gap-2.5 pl-1.5 pr-3 py-1 rounded-2xl bg-slate-50/90 hover:bg-white border border-slate-200/80 hover:border-teal-300 hover:shadow-xs transition-all duration-200 cursor-pointer group focus:outline-none">
                            <!-- Avatar with online indicator ring -->
                            <div class="relative shrink-0">
                                @if (auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" 
                                         alt="" 
                                         onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                         class="w-8 h-8 rounded-xl object-cover border border-slate-200 shadow-2xs ring-2 ring-white">
                                    <div style="display: none;" class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white items-center justify-center font-bold text-xs shadow-2xs ring-2 ring-white shrink-0">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white flex items-center justify-center font-bold text-xs shadow-2xs ring-2 ring-white">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @endif
                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                            </div>

                            <!-- User Name & Division -->
                            <div class="text-left hidden sm:block min-w-0">
                                <span class="text-xs font-bold text-slate-800 block truncate leading-tight group-hover:text-teal-700 transition-colors max-w-[140px]">
                                    {{ auth()->user()->name }}
                                </span>
                                <span class="text-[10px] text-slate-400 block truncate leading-tight font-medium mt-0.5 max-w-[140px]">
                                    {{ auth()->user()->division ?: (auth()->user()->getRoleNames()->first() ? ucfirst(auth()->user()->getRoleNames()->first()) : 'MSN') }}
                                </span>
                            </div>

                            <!-- Dropdown Chevron Icon -->
                            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-teal-600 transition-transform duration-200 ml-0.5"
                                 :class="userMenuOpen ? 'rotate-180 text-teal-600' : ''"
                                 fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu Box -->
                        <div x-show="userMenuOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2.5 w-64 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-1.5 z-50 divide-y divide-slate-100">
                            
                            <!-- Header Ringkasan Akun -->
                            <div class="px-4 py-3 flex items-center gap-3">
                                @if (auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" 
                                         alt="" 
                                         onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                         class="h-10 w-10 rounded-xl object-cover border border-slate-200 shadow-xs shrink-0">
                                    <div style="display: none;" class="h-10 w-10 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white items-center justify-center font-bold text-xs shadow-xs shrink-0">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @else
                                    <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white flex items-center justify-center font-bold text-xs shadow-xs shrink-0">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-teal-700 font-mono font-medium truncate">{{ auth()->user()->employee_code }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->division ?: 'MSN Member' }}</p>
                                </div>
                            </div>

                            <!-- Menu Tautan -->
                            <div class="py-1">
                                <a href="{{ route('portal.profile') }}" 
                                   class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Profile</span>
                                </a>

                                <a href="{{ route('portal.settings') }}" 
                                   class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Pengaturan</span>
                                </a>

                                @if (auth()->user() && auth()->user()->hasRole(['super_admin', 'admin']))
                                <a href="{{ route('portal.sessions') }}" 
                                   class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Perangkat &amp; Sesi Login</span>
                                </a>
                                @endif
                            </div>

                            <!-- Logout Form & Button -->
                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer">
                                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>Logout / Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Toast / Status Banner -->
            @if (session('status'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 7000)"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform -translate-y-2 scale-95"
                 class="px-4 sm:px-6 lg:px-8 pt-4">
                <div role="alert" class="rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800/80 px-4 py-3 flex items-center justify-between gap-3 text-emerald-900 dark:text-emerald-200 shadow-xs">
                    <div class="flex items-center gap-2.5 text-sm font-medium min-w-0">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="truncate">{{ session('status') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 p-1 rounded-lg hover:bg-emerald-100/50 transition cursor-pointer shrink-0" title="Tutup Notifikasi">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            @endif

            @if (isset($errors) && $errors->any())
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                <div role="alert" class="rounded-xl bg-red-50 dark:bg-rose-950/40 border border-red-300 dark:border-rose-800/80 px-4 py-3 text-red-900 dark:text-rose-200 shadow-xs">
                    <div class="flex items-center gap-2 font-semibold text-sm mb-1">
                        <svg class="w-5 h-5 text-red-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Periksa kembali input Anda:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 text-red-800 dark:text-rose-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            @endif

            <!-- Main Page Slot (Compact Padding) -->
            <main id="main-content" class="flex-1 p-3.5 sm:p-5 lg:p-6 w-full pb-24 md:pb-6">
                @if (isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MOBILE BOTTOM NAVIGATION BAR (CENTER ELEVATED HOME WITH CURVED DOME) -->
    <!-- ============================================================== -->
    @php
        $navTabActive = 'home';
        if (request()->routeIs('attendance.*')) {
            $navTabActive = 'presensi';
        } elseif (request()->routeIs('gomsn.pengajuan.*')) {
            $navTabActive = 'pengajuan';
        } elseif (request()->routeIs('tickets.*')) {
            $navTabActive = 'tiket';
        } elseif (request()->routeIs('gomsn.overtime.*')) {
            $navTabActive = 'lembur';
        } elseif (request()->routeIs('portal.hub') || request()->routeIs('dashboard')) {
            $navTabActive = 'home';
        }

        $mobilePengajuanUrl = $navCanApprovePengajuan ? route('gomsn.pengajuan.approvals') : route('gomsn.pengajuan.index');
        $mobilePengajuanBadge = $navPendingAtasanCount ?? 0;
    @endphp

    <div class="md:hidden fixed bottom-3 inset-x-3 sm:inset-x-6 max-w-md mx-auto z-40 pointer-events-none pt-4"
         style="z-index: 40 !important;"
         x-data="{
             currentTab: '{{ $navTabActive }}',
             get isMenu() { return typeof bottomSheetOpen !== 'undefined' ? bottomSheetOpen : false; },
             isActive(tab) {
                 if (this.isMenu) return tab === 'menu';
                 return this.currentTab === tab;
             }
         }">
        <nav aria-label="Navigasi Cepat Mobile" 
             class="pointer-events-auto bg-gradient-to-r from-emerald-50/70 via-white to-teal-50/80 backdrop-blur-md rounded-[24px] shadow-[0_10px_30px_rgba(13,148,136,0.18)] border border-teal-200/90 h-[58px] px-2 flex items-center justify-around relative">
            
            <!-- Soft Ambient Wave Background on Right Side -->
            <div class="absolute right-0 top-0 bottom-0 w-2/5 rounded-r-[24px] bg-gradient-to-l from-teal-500/15 via-teal-300/10 to-transparent pointer-events-none"></div>
            <svg class="gomsn-nav-wave absolute right-0 bottom-0 h-full w-40 pointer-events-none opacity-35" viewBox="0 0 144 58" fill="none">
                <path d="M 0 58 C 45 22 85 50 144 14 L 144 58 Z" fill="#0d9488"/>
            </svg>

            <!-- 1. PRESENSI / ABSEN -->
            <a href="{{ route('attendance.index') }}" 
               @click="currentTab = 'presensi'"
               class="flex-1 h-full flex flex-col items-center justify-center relative group select-none cursor-pointer z-10 pt-1">
                <svg class="w-[22px] h-[22px] transition-colors" 
                     :class="isActive('presensi') ? 'text-[#0f766e]' : 'text-[#115e59] group-hover:text-[#042f2e]'"
                     fill="none" stroke="currentColor" :stroke-width="isActive('presensi') ? '2.2' : '1.9'" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="text-[11px] tracking-tight mt-0.5 leading-none transition-colors"
                      :class="isActive('presensi') ? 'font-black text-[#0f766e]' : 'font-semibold text-slate-700 group-hover:text-slate-900'">
                    Absen
                </span>
                <div class="h-1 flex items-center justify-center mt-1">
                    <span x-show="isActive('presensi')" class="w-5 h-[2.5px] bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full" style="display: none;"></span>
                </div>
            </a>

            <!-- 2. PENGAJUAN -->
            <a href="{{ $mobilePengajuanUrl }}" 
               @click="currentTab = 'pengajuan'"
               class="flex-1 h-full flex flex-col items-center justify-center relative group select-none cursor-pointer z-10 pt-1">
                <div class="relative">
                    <svg class="w-[22px] h-[22px] transition-colors" 
                         :class="isActive('pengajuan') ? 'text-[#0f766e]' : 'text-[#115e59] group-hover:text-[#042f2e]'"
                         fill="none" stroke="currentColor" :stroke-width="isActive('pengajuan') ? '2.2' : '1.9'" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    @if (!empty($mobilePengajuanBadge) && $mobilePengajuanBadge > 0)
                        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-white"></span>
                    @endif
                </div>
                <span class="text-[11px] tracking-tight mt-0.5 leading-none transition-colors"
                      :class="isActive('pengajuan') ? 'font-black text-[#0f766e]' : 'font-semibold text-slate-700 group-hover:text-slate-900'">
                    Pengajuan
                </span>
                <div class="h-1 flex items-center justify-center mt-1">
                    <span x-show="isActive('pengajuan')" class="w-5 h-[2.5px] bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full" style="display: none;"></span>
                </div>
            </a>

            <!-- 3. HOME (CENTER HERO - ORGANIC ARCH MOUND + 3D GLOSSY TEAL/EMERALD ORB) -->
            <a href="{{ route('portal.hub') }}" 
               @click="currentTab = 'home'"
               class="flex-1 h-full flex flex-col items-center justify-end relative group select-none cursor-pointer pb-1.5 z-20">
                
                <!-- Seamless Arch Mound Pedestal (Curves smoothly out of the navbar) -->
                <svg class="absolute -top-[19px] left-1/2 -translate-x-1/2 w-[82px] h-[21px] pointer-events-none drop-shadow-[0_-3px_3px_rgba(13,148,136,0.12)]" viewBox="0 0 82 21">
                    <!-- Mound Fill connecting to navbar body -->
                    <path class="mobile-nav-mound-fill" d="M 0,21 C 18,21 27,1 41,1 C 55,1 64,21 82,21 L 82,25 L 0,25 Z" fill="#ffffff"/>
                    <!-- Crisp Top Stroke connecting with navbar border -->
                    <path class="gomsn-nav-mound-stroke" d="M 0,21 C 18,21 27,1 41,1 C 55,1 64,21 82,21" fill="none" stroke="rgba(45, 212, 191, 0.9)" stroke-width="1.6"/>
                </svg>

                <!-- Elevated 3D Glossy Sphere Button with theme-aware gloss -->
                <div class="gomsn-home-orb absolute -top-4 w-[48px] h-[48px] rounded-full flex items-center justify-center text-white border-[2.5px] border-white shadow-[0_6px_18px_rgba(13,148,136,0.42)] transition-transform duration-200 transform group-hover:scale-105 active:scale-95 overflow-hidden"
                     style="background: var(--gomsn-orb-bg, radial-gradient(circle at 50% 25%, #10b981 0%, #0d9488 45%, #065f46 100%));">
                    
                    <!-- Specular Curved Gloss Arc (Bubble glass reflection on top) -->
                    <div class="absolute top-1 inset-x-2 h-[18px] rounded-full bg-gradient-to-b from-white/75 via-white/25 to-transparent pointer-events-none"></div>

                    <!-- Prominent House Icon (Peaked roof with rounded arch door) -->
                    <svg class="w-[25px] h-[25px] text-white relative z-10 drop-shadow-[0_1px_3px_rgba(0,0,0,0.3)]" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>

                <!-- Label & Indicator Line -->
                <span class="text-[11px] tracking-tight leading-none transition-colors"
                      :class="isActive('home') ? 'font-black text-[#0f766e]' : 'font-semibold text-slate-700 group-hover:text-[#0f766e]'">
                    Home
                </span>
                <div class="h-1 flex items-center justify-center mt-1">
                    <span x-show="isActive('home')" class="w-7 h-[2.8px] bg-gradient-to-r from-emerald-500 via-teal-600 to-emerald-500 rounded-full"></span>
                </div>
            </a>

            <!-- 4. TIKET GANGGUAN / LEMBUR (KHUSUS HRD: LEMBUR KARYAWAN DENGAN LABEL 'Lembur') -->
            @if ($isHrdRole)
            <a href="{{ route('gomsn.overtime.index') }}" 
               @click="currentTab = 'lembur'"
               class="flex-1 h-full flex flex-col items-center justify-center relative group select-none cursor-pointer z-10 pt-1">
                <div class="relative">
                    <svg class="w-[22px] h-[22px] transition-colors" 
                         :class="isActive('lembur') ? 'text-[#0f766e]' : 'text-[#115e59] group-hover:text-[#042f2e]'"
                         fill="none" stroke="currentColor" :stroke-width="isActive('lembur') ? '2.2' : '1.9'" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @if (!empty($navPendingLemburCount) && $navPendingLemburCount > 0)
                        <span class="absolute -top-1 -right-2 min-w-[16px] h-4 px-1 rounded-full bg-amber-500 text-slate-900 text-[9px] font-black flex items-center justify-center ring-2 ring-white shadow-xs animate-pulse">
                            {{ $navPendingLemburCount > 9 ? '9+' : $navPendingLemburCount }}
                        </span>
                    @endif
                </div>
                <span class="text-[11px] tracking-tight mt-0.5 leading-none transition-colors"
                      :class="isActive('lembur') ? 'font-black text-[#0f766e]' : 'font-semibold text-slate-700 group-hover:text-slate-900'">
                    Lembur
                </span>
                <div class="h-1 flex items-center justify-center mt-1">
                    <span x-show="isActive('lembur')" class="w-5 h-[2.5px] bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full" style="display: none;"></span>
                </div>
            </a>
            @else
            <a href="{{ route('tickets.index') }}" 
               @click="currentTab = 'tiket'"
               class="flex-1 h-full flex flex-col items-center justify-center relative group select-none cursor-pointer z-10 pt-1">
                <div class="relative">
                    <svg class="w-[22px] h-[22px] transition-colors" 
                         :class="isActive('tiket') ? 'text-[#0f766e]' : 'text-[#115e59] group-hover:text-[#042f2e]'"
                         fill="none" stroke="currentColor" :stroke-width="isActive('tiket') ? '2.2' : '1.9'" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    @if ($navAssignedTicketCount > 0)
                        <span class="absolute -top-1 -right-2 min-w-[16px] h-4 px-1 rounded-full bg-amber-500 text-slate-900 text-[9px] font-black flex items-center justify-center ring-2 ring-white shadow-xs animate-pulse">
                            {{ $navAssignedTicketCount > 9 ? '9+' : $navAssignedTicketCount }}
                        </span>
                    @endif
                </div>
                <span class="text-[11px] tracking-tight mt-0.5 leading-none transition-colors"
                      :class="isActive('tiket') ? 'font-black text-[#0f766e]' : 'font-semibold text-slate-700 group-hover:text-slate-900'">
                    Tiket
                </span>
                <div class="h-1 flex items-center justify-center mt-1">
                    <span x-show="isActive('tiket')" class="w-5 h-[2.5px] bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full" style="display: none;"></span>
                </div>
            </a>
            @endif

            <!-- 5. MENU (TOGGLE BOTTOM SHEET) -->
            <button @click="bottomSheetOpen = !bottomSheetOpen" 
                    type="button" 
                    class="flex-1 h-full flex flex-col items-center justify-center relative group cursor-pointer select-none z-10 pt-1">
                <svg class="w-[22px] h-[22px] transition-colors" 
                     :class="isActive('menu') ? 'text-[#0f766e]' : 'text-[#115e59] group-hover:text-[#042f2e]'"
                     fill="none" stroke="currentColor" :stroke-width="isActive('menu') ? '2.3' : '2'" viewBox="0 0 24 24">
                    <circle cx="7" cy="7" r="2.2"/>
                    <circle cx="17" cy="7" r="2.2"/>
                    <circle cx="7" cy="17" r="2.2"/>
                    <circle cx="17" cy="17" r="2.2"/>
                </svg>
                <span class="text-[11px] tracking-tight mt-0.5 leading-none transition-colors"
                      :class="isActive('menu') ? 'font-black text-[#0f766e]' : 'font-semibold text-slate-700 group-hover:text-slate-900'">
                    Menu
                </span>
                <div class="h-1 flex items-center justify-center mt-1">
                    <span x-show="isActive('menu')" class="w-5 h-[2.5px] bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full" style="display: none;"></span>
                </div>
            </button>
        </nav>
    </div>

    @include('layouts.partials.mobile-bottom-sheet')

    <!-- Floating Chatbot Asisten PT MSN (§5.8) - Hidden on ticket show page to avoid covering chat input -->
    @unless(request()->routeIs('tickets.show') || !empty($hideChatbot))
        <x-chatbot-widget />
    @endunless

    <!-- Onboarding Modal Izin Perangkat (Kamera, Lokasi, Notifikasi, Voice) -->
    <x-permission-prompt-modal />
    @endauth

    <!-- SweetAlert2 Toast Global Notifier (Ujung Kanan Atas) -->
    @php
        $toastAuthUser = auth()->user();
        $toastCeklisThemeUrls = [];
        foreach (['teal', 'blue', 'indigo', 'emerald', 'purple', 'rose', 'amber', 'pink'] as $tKey) {
            $toastCeklisThemeUrls[$tKey] = $toastAuthUser
                ? $toastAuthUser->getSuccessAvatarUrlForTheme($tKey)
                : (asset("assets/logo/ceklispria-{$tKey}.png") . '?v=1');
        }
        $toastDefaultCeklisUrl = $toastAuthUser
            ? asset('assets/logo/' . ($toastAuthUser->isPerempuan() ? 'ceklisperempuan.png' : 'ceklispria.png')) . '?v=1'
            : (asset('assets/logo/ceklispria.png') . '?v=1');
    @endphp
    <script>
        window.__gomsnSuccessToastMap = @json($toastCeklisThemeUrls);
        window.__gomsnSuccessToastDefault = @json($toastDefaultCeklisUrl);
        window.getGomsnSuccessToastIconHtml = function() {
            var accent = document.documentElement.getAttribute('data-accent') || 'teal';
            var map = window.__gomsnSuccessToastMap || {};
            var url = map[accent] || window.__gomsnSuccessToastDefault;
            return '<img src="' + url + '" alt="Berhasil" class="w-11 h-11 object-contain select-none drop-shadow-xs">';
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Override window.showToast to use character checkmark image for 'success'
            if (window.Toast) {
                window.showToast = function(icon, message, title = '') {
                    var isSuccess = icon === 'success';
                    var opts = {
                        icon: icon,
                        title: title ? '<strong>' + title + '</strong><br><span class="text-xs font-normal text-slate-600">' + message + '</span>' : message
                    };
                    if (isSuccess && typeof window.getGomsnSuccessToastIconHtml === 'function') {
                        opts.iconHtml = window.getGomsnSuccessToastIconHtml();
                        opts.customClass = {
                            popup: 'rounded-2xl shadow-2xl border border-slate-100 text-xs sm:text-sm font-sans select-none',
                            title: 'font-semibold text-slate-800 text-xs sm:text-sm',
                            icon: 'gomsn-toast-custom-icon border-0'
                        };
                    }
                    return window.Toast.fire(opts);
                };
            }

            function triggerToast(icon, message) {
                if (!message) return;
                const fire = () => {
                    const isSuccess = icon === 'success';
                    const customIconHtml = (isSuccess && typeof window.getGomsnSuccessToastIconHtml === 'function')
                        ? window.getGomsnSuccessToastIconHtml()
                        : undefined;
                    if (window.Toast) {
                        const opts = {
                            icon: icon,
                            title: message
                        };
                        if (isSuccess && customIconHtml) {
                            opts.iconHtml = customIconHtml;
                            opts.customClass = {
                                popup: 'rounded-2xl shadow-2xl border border-slate-100 text-xs sm:text-sm font-sans select-none',
                                title: 'font-semibold text-slate-800 text-xs sm:text-sm',
                                icon: 'gomsn-toast-custom-icon border-0'
                            };
                        }
                        window.Toast.fire(opts);
                    } else if (window.Swal) {
                        const opts = {
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 4500,
                            timerProgressBar: true,
                            padding: '0.75rem 1.25rem',
                            customClass: {
                                popup: 'rounded-2xl shadow-2xl border border-slate-100 text-xs sm:text-sm font-sans select-none',
                                title: 'font-semibold text-slate-800 text-xs sm:text-sm',
                                icon: isSuccess ? 'gomsn-toast-custom-icon border-0' : ''
                            },
                            icon: icon,
                            title: message
                        };
                        if (isSuccess && customIconHtml) {
                            opts.iconHtml = customIconHtml;
                        }
                        Swal.fire(opts);
                    }
                };

                if (window.Toast || window.Swal) {
                    fire();
                } else {
                    setTimeout(fire, 200);
                }
            }

            @if (session('success'))
                triggerToast('success', {!! json_encode(session('success')) !!});
            @elseif (session('status'))
                triggerToast('success', {!! json_encode(session('status')) !!});
            @elseif (session('message'))
                triggerToast('info', {!! json_encode(session('message')) !!});
            @elseif (session('warning'))
                triggerToast('warning', {!! json_encode(session('warning')) !!});
            @elseif (session('error'))
                triggerToast('error', {!! json_encode(session('error')) !!});
            @elseif (isset($errors) && $errors->any())
                triggerToast('error', {!! json_encode($errors->first()) !!});
            @endif

            // Global Elegant Confirmation Handler (Menggantikan confirm browser native yang kaku)
            window.confirmAction = function({
                title = 'Konfirmasi Tindakan',
                text = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
                icon = 'question',
                confirmButtonText = 'Ya, Lanjutkan',
                cancelButtonText = 'Batal',
                confirmButtonColor = '#155E63',
            }) {
                if (!window.Swal) {
                    return Promise.resolve({ isConfirmed: window.confirm(text) });
                }
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: cancelButtonText,
                    confirmButtonColor: confirmButtonColor,
                    cancelButtonColor: '#94a3b8',
                    reverseButtons: true,
                    backdrop: 'rgba(15, 23, 42, 0.65)',
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl border border-slate-200/90 font-sans p-6 text-center select-none',
                        title: 'font-extrabold text-base sm:text-lg text-slate-900 tracking-tight',
                        htmlContainer: 'text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed',
                        confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-md active:scale-95 transition cursor-pointer',
                        cancelButton: 'px-4 py-2.5 rounded-xl font-semibold text-xs text-slate-700 bg-slate-100 hover:bg-slate-200 active:scale-95 transition cursor-pointer'
                    }
                });
            };

            // Global Auto-interceptor for native confirm(...) in forms and buttons
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (!form || form.dataset.swalBypass) {
                    if (form) delete form.dataset.swalBypass;
                    return;
                }

                const onsubmitAttr = form.getAttribute('onsubmit');
                let confirmMsg = form.dataset.confirm;

                if (!confirmMsg && onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                    const match = onsubmitAttr.match(/confirm\(['"]([^'"]+)['"]\)/);
                    if (match && match[1]) {
                        confirmMsg = match[1];
                    }
                }

                if (confirmMsg) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    const isDanger = confirmMsg.toLowerCase().includes('hapus') || 
                                     confirmMsg.toLowerCase().includes('tolak') || 
                                     confirmMsg.toLowerCase().includes('batal');
                    const icon = isDanger ? 'warning' : 'question';
                    const color = isDanger ? '#e11d48' : '#155E63';
                    const btnText = isDanger ? 'Ya, Lanjutkan' : 'Ya, Setujui';

                    window.confirmAction({
                        title: 'Konfirmasi Tindakan',
                        text: confirmMsg,
                        icon: icon,
                        confirmButtonColor: color,
                        confirmButtonText: btnText
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.swalBypass = 'true';
                            if (onsubmitAttr) {
                                form.removeAttribute('onsubmit');
                                form.submit();
                                form.setAttribute('onsubmit', onsubmitAttr);
                            } else {
                                form.submit();
                            }
                        }
                    });
                }

                // Global Anti-Double-Submit & Button Loading Spinner for POST/PUT/DELETE forms
                const method = (form.getAttribute('method') || 'GET').toUpperCase();
                if (method === 'POST' && !e.defaultPrevented && !form.hasAttribute('data-no-loading')) {
                    if (form.dataset.gomsnSubmitting === 'true') {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        return;
                    }
                    form.dataset.gomsnSubmitting = 'true';

                    const submitBtn = e.submitter || form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.hasAttribute('data-custom-loading')) {
                        setTimeout(() => {
                            submitBtn.disabled = true;
                            submitBtn.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
                            if (!submitBtn.querySelector('.gomsn-btn-spinner')) {
                                const spinner = document.createElement('span');
                                spinner.className = 'gomsn-btn-spinner inline-flex items-center mr-1.5';
                                spinner.innerHTML = '<svg class="w-4 h-4 animate-spin text-current shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3.5"></circle><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                                const existingSvg = submitBtn.querySelector('svg');
                                if (existingSvg) {
                                    existingSvg.style.display = 'none';
                                }
                                submitBtn.insertBefore(spinner, submitBtn.firstChild);
                            }
                        }, 0);
                    }
                }
            }, true);

            window.addEventListener('pageshow', function() {
                document.querySelectorAll('form[data-gomsn-submitting]').forEach(function(f) {
                    delete f.dataset.gomsnSubmitting;
                });
            });

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('button[onclick*="confirm("], a[onclick*="confirm("], [data-confirm]');
                if (!btn || btn.dataset.swalBypass) return;

                let confirmMsg = btn.dataset.confirm;
                const onclickAttr = btn.getAttribute('onclick');

                if (!confirmMsg && onclickAttr && onclickAttr.includes('confirm(')) {
                    const match = onclickAttr.match(/confirm\(['"]([^'"]+)['"]\)/);
                    if (match && match[1]) {
                        confirmMsg = match[1];
                    }
                }

                if (confirmMsg) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    const isDanger = confirmMsg.toLowerCase().includes('hapus') || 
                                     confirmMsg.toLowerCase().includes('tolak') || 
                                     confirmMsg.toLowerCase().includes('batal');
                    const icon = isDanger ? 'warning' : 'question';
                    const color = isDanger ? '#e11d48' : '#155E63';
                    const btnText = isDanger ? 'Ya, Lanjutkan' : 'Ya, Setujui';

                    window.confirmAction({
                        title: 'Konfirmasi Tindakan',
                        text: confirmMsg,
                        icon: icon,
                        confirmButtonColor: color,
                        confirmButtonText: btnText
                    }).then((result) => {
                        if (result.isConfirmed) {
                            btn.dataset.swalBypass = 'true';
                            if (onclickAttr) {
                                btn.removeAttribute('onclick');
                                btn.click();
                                btn.setAttribute('onclick', onclickAttr);
                            } else if (btn.form && btn.type === 'submit') {
                                btn.form.submit();
                            } else {
                                btn.click();
                            }
                        }
                    });
                }
            }, true);
        });
    </script>

    @auth
    @php
        $sessionTimeoutMinutes = max(129600, (int) (\App\Services\SystemSettingService::get('security.session_idle_timeout_minutes', config('session.lifetime', 129600))));
        $sessionTimeoutSeconds = max(60, $sessionTimeoutMinutes * 60);
    @endphp
    <script>
        // Auto-redirect to login when session expires to prevent 419 / Page Expired
        (function() {
            const sessionLifetimeSeconds = {{ $sessionTimeoutSeconds }};
            const loginUrl = "{{ route('login') }}";
            let lastActivity = Date.now();

            function updateActivity() {
                lastActivity = Date.now();
            }

            // Track user interaction to refresh local activity timestamp
            ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function(evt) {
                window.addEventListener(evt, updateActivity, { passive: true });
            });

            function checkSessionTimeout() {
                const idleSeconds = Math.floor((Date.now() - lastActivity) / 1000);
                if (idleSeconds >= sessionLifetimeSeconds) {
                    window.location.replace(loginUrl);
                }
            }

            // Check periodically every 15 seconds
            setInterval(checkSessionTimeout, 15000);

            // Instant check when user switches back to this tab or wakes device
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    checkSessionTimeout();
                }
            });

            window.addEventListener('focus', checkSessionTimeout);

            // Intercept form submissions if idle timeout has already passed
            document.addEventListener('submit', function(e) {
                const idleSeconds = Math.floor((Date.now() - lastActivity) / 1000);
                if (idleSeconds >= sessionLifetimeSeconds) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    window.location.replace(loginUrl);
                }
            }, true);

            // Global fetch interceptor for 419 / 401
            if (window.fetch) {
                const nativeFetch = window.fetch;
                window.fetch = async function(...args) {
                    try {
                        const response = await nativeFetch.apply(this, args);
                        if (response && (response.status === 419 || response.status === 401)) {
                            window.location.replace(loginUrl);
                        }
                        return response;
                    } catch (err) {
                        throw err;
                    }
                };
            }

            // Global XMLHttpRequest interceptor for 419 / 401
            if (window.XMLHttpRequest) {
                const nativeOpen = XMLHttpRequest.prototype.open;
                XMLHttpRequest.prototype.open = function(...args) {
                    this.addEventListener('load', function() {
                        if (this.status === 419 || this.status === 401) {
                            window.location.replace(loginUrl);
                        }
                    });
                    nativeOpen.apply(this, args);
                };
            }
        })();

        // PWA & Background Push Service Worker Registration (unified canonical /sw.js)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js', { scope: '/', updateViaCache: 'none' }).then(function(reg) {
                    window.msnServiceWorkerReg = reg;
                }).catch(function(){});
            });
        }
    </script>
    @include('layouts.partials.fcm-client')
    @endauth

    @stack('scripts')
</body>
</html>
