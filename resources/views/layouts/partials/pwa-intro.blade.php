<div id="pwa-intro-screen" class="fixed inset-0 z-[99999] flex flex-col items-center justify-center bg-[#06111b] select-none pointer-events-auto transition-opacity duration-700 ease-out" style="display: none; background-image: radial-gradient(ellipse 60% 45% at 50% 48%, #0b2a40, transparent 70%);">
    <div class="stage w-full flex justify-center px-4">
        <div class="logo relative w-full max-w-[680px] aspect-[1500/381]" id="pwa-intro-logo">
            <div class="glow absolute inset-0 w-full h-full"></div>
            <img class="blue absolute inset-0 w-full h-full object-contain pointer-events-none" alt="" src="{{ asset('assets/logo/intro-logo-blue.png') }}">
            <img class="white absolute inset-0 w-full h-full object-contain pointer-events-none" alt="Portal MSN" src="{{ asset('assets/logo/intro-logo-white.png') }}">
            <div class="shine absolute inset-0 w-full h-full pointer-events-none"></div>
        </div>
    </div>
</div>

<style>
#pwa-intro-screen {
    --bg: #06111b;
    --bg-glow: #0b2a40;
    --accent: #0f98d6;
    --ease: cubic-bezier(.16, 1, .3, 1);
    --logo-white: url('{{ asset("assets/logo/intro-logo-white.png") }}');
}
#pwa-intro-logo .glow {
    background: radial-gradient(ellipse 50% 40% at 50% 55%, rgba(15, 152, 214, .35), transparent 70%);
    opacity: 0;
    filter: blur(20px);
}
#pwa-intro-logo .blue {
    opacity: 0;
    transform: translateX(-14%) scaleX(1.12);
    filter: blur(10px);
    transform-origin: right center;
}
#pwa-intro-logo .white {
    clip-path: inset(0 100% 0 0);
    opacity: 0;
}
#pwa-intro-logo .shine {
    -webkit-mask-image: var(--logo-white);
    mask-image: var(--logo-white);
    -webkit-mask-size: 100% 100%;
    mask-size: 100% 100%;
    background: linear-gradient(100deg, transparent 35%, rgba(120, 210, 255, .95) 50%, transparent 65%) -150% 0/60% 100% no-repeat;
    opacity: 0;
    mix-blend-mode: screen;
}
#pwa-intro-logo.play .glow {
    animation: pwaIntroGlow 2.6s .2s ease-out both;
}
#pwa-intro-logo.play .blue {
    animation: pwaIntroStreak .95s .15s var(--ease) both;
}
#pwa-intro-logo.play .white {
    animation: pwaIntroWipe .9s .75s var(--ease) both;
}
#pwa-intro-logo.play .shine {
    animation: pwaIntroSweep 1s 1.75s ease-in-out both;
}
#pwa-intro-logo.play {
    animation: pwaIntroSettle 3.4s 0s ease-out both;
}
@keyframes pwaIntroStreak {
    0% { opacity: 0; transform: translateX(-14%) scaleX(1.12); filter: blur(10px); }
    30% { opacity: 1; }
    100% { opacity: 1; transform: none; filter: blur(0); }
}
@keyframes pwaIntroWipe {
    0% { opacity: 1; clip-path: inset(0 100% 0 0); }
    100% { opacity: 1; clip-path: inset(0 0 0 0); }
}
@keyframes pwaIntroGlow {
    0% { opacity: 0; }
    40% { opacity: 1; }
    100% { opacity: .45; }
}
@keyframes pwaIntroSweep {
    0% { opacity: 1; background-position: -150% 0; }
    100% { opacity: 1; background-position: 250% 0; }
}
@keyframes pwaIntroSettle {
    0% { transform: scale(1.04); }
    100% { transform: scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    #pwa-intro-logo.play .glow,
    #pwa-intro-logo.play .blue,
    #pwa-intro-logo.play .white,
    #pwa-intro-logo.play .shine,
    #pwa-intro-logo.play {
        animation: none !important;
    }
    #pwa-intro-logo .blue,
    #pwa-intro-logo .white {
        opacity: 1 !important;
        transform: none !important;
        filter: none !important;
        clip-path: none !important;
    }
    #pwa-intro-logo .glow {
        opacity: .4 !important;
    }
}
</style>

<script>
(function() {
    try {
        var isStandalone = window.matchMedia('(display-mode: standalone)').matches 
            || window.navigator.standalone === true 
            || document.referrer.includes('android-app://')
            || window.location.search.includes('standalone=true');
        
        var hasSeenIntro = sessionStorage.getItem('pwa_intro_played');

        // Play intro when installed app (standalone) is opened, or first visit per session on mobile/desktop
        if (isStandalone && !hasSeenIntro) {
            var screen = document.getElementById('pwa-intro-screen');
            var logo = document.getElementById('pwa-intro-logo');
            if (screen && logo) {
                sessionStorage.setItem('pwa_intro_played', 'true');
                screen.style.display = 'flex';
                logo.classList.remove('play');
                void logo.offsetWidth;
                logo.classList.add('play');

                var dismissIntro = function() {
                    screen.classList.add('opacity-0');
                    screen.style.pointerEvents = 'none';
                    setTimeout(function() {
                        screen.style.display = 'none';
                    }, 700);
                };

                // Allow user to tap/click to dismiss early or auto fade out after animation finishes
                screen.addEventListener('click', dismissIntro);
                setTimeout(dismissIntro, 2900);
            }
        }
    } catch(e) {
        console.error('Intro animation error:', e);
    }
})();
</script>
