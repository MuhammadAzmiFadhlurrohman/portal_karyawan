import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Chart = Chart;
window.Swal = Swal;

// Global SweetAlert Toast di ujung kanan atas (top-end)
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    padding: '0.75rem 1.25rem',
    customClass: {
        popup: 'rounded-2xl shadow-2xl border border-slate-100 text-xs sm:text-sm font-sans select-none',
        title: 'font-semibold text-slate-800 text-xs sm:text-sm',
    },
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

window.Toast = Toast;

// Helper Global untuk memanggil SweetAlert Toast dari mana saja
window.showToast = (icon, message, title = '') => {
    return Toast.fire({
        icon: icon, // 'success', 'error', 'warning', 'info'
        title: title ? `<strong>${title}</strong><br><span class="text-xs font-normal text-slate-600">${message}</span>` : message
    });
};

Alpine.plugin(collapse);

window.Alpine = Alpine;
Alpine.start();

// Register Service Worker for PWA & Background Web Push (§6.4, §10.3)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/', updateViaCache: 'none' }).catch(() => {
            // Silent fallback in non-HTTPS local dev
        });
    });
}

// PWA Install Prompt Handling & Cross-Browser Trigger
window.__pwaInstallPrompt = null;
window.__pwaIsStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
window.__pwaIsInstalled = window.__pwaIsStandalone || (function () {
    try {
        return localStorage.getItem('gomsn_pwa_installed') === '1';
    } catch (e) {
        return false;
    }
})();

// Modern Chromium check for installed related apps (§6.4)
if ('getInstalledRelatedApps' in navigator) {
    navigator.getInstalledRelatedApps().then((apps) => {
        if (apps && apps.length > 0) {
            window.__pwaIsInstalled = true;
            try {
                localStorage.setItem('gomsn_pwa_installed', '1');
            } catch (e) {}
            window.dispatchEvent(new CustomEvent('pwa:installed-status-changed'));
        }
    }).catch(() => {});
}

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    window.__pwaInstallPrompt = e;
    window.dispatchEvent(new CustomEvent('pwa:install-ready'));
});

window.addEventListener('appinstalled', () => {
    window.__pwaInstallPrompt = null;
    window.__pwaIsInstalled = true;
    try {
        localStorage.setItem('gomsn_pwa_installed', '1');
    } catch (e) {}
    window.dispatchEvent(new CustomEvent('pwa:installed'));
});

window.openPwaApp = () => {
    window.showToast?.('info', 'Membuka Aplikasi Mobile PT MSN...');
    setTimeout(() => {
        window.location.href = '/?source=pwa_banner_open';
    }, 250);
};

window.triggerPwaInstall = async () => {
    if (window.__pwaIsInstalled) {
        window.openPwaApp();
        return 'installed';
    }

    const promptEvent = window.__pwaInstallPrompt;
    if (promptEvent) {
        await promptEvent.prompt();
        const choice = await promptEvent.userChoice;
        window.__pwaInstallPrompt = null;
        if (choice && choice.outcome === 'accepted') {
            window.__pwaIsInstalled = true;
            try {
                localStorage.setItem('gomsn_pwa_installed', '1');
            } catch (e) {}
            window.showToast?.('success', 'Aplikasi Mobile PT MSN sedang dipasang di perangkat Anda!');
            window.dispatchEvent(new CustomEvent('pwa:installed'));
        }
        return choice?.outcome || 'dismissed';
    }

    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const guideHtml = isIos
        ? `<div class="text-left text-xs sm:text-sm text-slate-600 space-y-2 leading-relaxed">
                <p class="font-medium text-slate-700">Untuk memasang aplikasi di <strong>iOS Safari</strong>:</p>
                <ol class="list-decimal list-inside space-y-1.5 bg-teal-50/70 border border-teal-200 rounded-xl p-3 text-slate-700">
                    <li>Ketuk ikon <strong>Bagikan (Share)</strong> di bilah bawah Safari.</li>
                    <li>Pilih menu <strong>"Tambah ke Layar Utama"</strong> (<em>Add to Home Screen</em>).</li>
                    <li>Ketuk <strong>"Tambah"</strong> di pojok kanan atas layar.</li>
                </ol>
           </div>`
        : `<div class="text-left text-xs sm:text-sm text-slate-600 space-y-2 leading-relaxed">
                <p class="font-medium text-slate-700">Untuk memasang aplikasi dari browser <strong>Android / Desktop</strong>:</p>
                <ol class="list-decimal list-inside space-y-1.5 bg-teal-50/70 border border-teal-200 rounded-xl p-3 text-slate-700">
                    <li>Ketuk ikon menu <strong>(&vellip;)</strong> atau ikon <strong>Install</strong> di bilah alamat browser.</li>
                    <li>Pilih <strong>"Install aplikasi"</strong> atau <strong>"Tambahkan ke Layar Utama"</strong>.</li>
                    <li>Konfirmasi dengan mengetuk tombol <strong>Install</strong>.</li>
                </ol>
           </div>`;

    if (window.Swal) {
        const result = await window.Swal.fire({
            icon: 'info',
            title: 'Pasang Aplikasi Mobile PT MSN',
            html: guideHtml,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#0D9488',
            showDenyButton: true,
            denyButtonText: 'Sudah Pasang Aplikasi',
            denyButtonColor: '#0f766e',
            backdrop: 'rgba(2, 32, 41, 0.65)',
            customClass: {
                popup: 'rounded-3xl shadow-2xl border border-slate-200 font-sans p-6 select-none',
                title: 'font-bold text-base sm:text-lg text-slate-900',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-md cursor-pointer',
                denyButton: 'px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-md cursor-pointer mr-2',
            },
        });

        if (result.isDenied) {
            window.__pwaIsInstalled = true;
            try {
                localStorage.setItem('gomsn_pwa_installed', '1');
            } catch (e) {}
            window.dispatchEvent(new CustomEvent('pwa:installed'));
            window.showToast?.('success', 'Status aplikasi diperbarui: Sudah Terpasang.');
            return 'installed';
        }
    } else {
        window.showToast?.('info', 'Buka menu browser Anda lalu pilih "Install aplikasi" atau "Tambahkan ke Layar Utama".');
    }

    return isIos ? 'ios' : 'manual';
};

// Live Network & Offline Sync Indicator (§10.3)
function updateConnectionBadge() {
    const badge = document.getElementById('network-status-badge');
    const dot = document.getElementById('network-status-dot');
    const label = document.getElementById('network-status-label');

    if (!badge || !dot || !label) return;

    if (navigator.onLine) {
        badge.className = 'inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 border border-emerald-200';
        dot.className = 'h-2 w-2 rounded-full bg-emerald-600';
        label.textContent = 'Online · Sinkron';
    } else {
        badge.className = 'inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-900 border border-amber-300';
        dot.className = 'h-2 w-2 rounded-full bg-amber-600 animate-pulse';
        label.textContent = 'Mode Offline (Antrean Lokal Aktif)';
    }
}

window.addEventListener('online', updateConnectionBadge);
window.addEventListener('offline', updateConnectionBadge);
document.addEventListener('DOMContentLoaded', updateConnectionBadge);
