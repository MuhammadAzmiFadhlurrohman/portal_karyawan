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

// Register Service Worker for PWA & Offline Queue Readiness (§6.4, §10.3)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Silent fallback in non-HTTPS local dev
        });
    });
}

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
