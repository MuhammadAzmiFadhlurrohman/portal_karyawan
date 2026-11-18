<div id="chatbot-widget-container" 
     class="font-sans"
     :class="{ 'opacity-0 pointer-events-none': typeof bottomSheetOpen !== 'undefined' && bottomSheetOpen }"
     x-data="chatbotWidget()">

    <style>
        .chatbot-docked-tab {
            position: fixed !important;
            right: -6px !important;
            bottom: 86px !important;
            z-index: 50 !important;
            width: 30px !important;
            padding-top: 8px !important;
            padding-bottom: 10px !important;
            padding-left: 2px !important;
            padding-right: 6px !important;
            background: linear-gradient(180deg, #093c40 0%, #0d5f66 45%, #13808a 100%) !important;
            border-top-left-radius: 14px !important;
            border-bottom-left-radius: 14px !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            box-shadow: -3px 4px 14px rgba(2, 40, 54, 0.35) !important;
            border-left: 1.5px solid rgba(255, 255, 255, 0.25) !important;
            border-top: 1.5px solid rgba(255, 255, 255, 0.25) !important;
            border-bottom: 1.5px solid rgba(255, 255, 255, 0.15) !important;
            border-right: none !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .chatbot-docked-tab:hover,
        .chatbot-docked-tab:active,
        .chatbot-docked-tab:focus {
            right: 0 !important;
            transform: translateX(-4px) !important;
        }
        .chatbot-modal-box {
            position: fixed !important;
            right: 8px !important;
            bottom: 86px !important;
            z-index: 60 !important;
        }
        @media (min-width: 640px) {
            .chatbot-docked-tab {
                bottom: 48px !important;
                right: -6px !important;
            }
            .chatbot-modal-box {
                right: 48px !important;
                bottom: 48px !important;
            }
        }
        @media (min-width: 768px) {
            .chatbot-docked-tab {
                bottom: 32px !important;
                right: -6px !important;
            }
            .chatbot-modal-box {
                right: 48px !important;
                bottom: 32px !important;
            }
        }
    </style>

    <!-- Docked Side-Tab Trigger Button (Matching Image) -->
    <button @click="toggleChat()" 
            type="button"
            class="chatbot-docked-tab text-white flex flex-col items-center justify-center focus:outline-none cursor-pointer select-none group"
            title="Tanya Asisten Mandiri PT MSN"
            aria-label="Buka Chatbot Asisten">
        
        <!-- Top Chevron Icon (Smoothly rotates 180deg when open) -->
        <svg class="w-3 h-3 text-white/90 transition-transform duration-300 shrink-0"
             :class="isOpen ? 'rotate-180 translate-x-0.5' : 'group-hover:-translate-x-0.5'"
             fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>

        <!-- Robot Icon Sesuai Gambar -->
        <div class="my-1 shrink-0 transition-transform duration-200 group-hover:scale-110">
            <svg style="width: 17px; height: 17px;" viewBox="0 0 24 24" fill="#ffffff">
                <!-- Top Antenna -->
                <rect x="10.8" y="1.8" width="2.4" height="3.4" rx="1.2" fill="#ffffff"/>
                <!-- Left Ear -->
                <rect x="2.5" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                <!-- Right Ear -->
                <rect x="19.3" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                <!-- Head with Eye & Mouth Cutouts -->
                <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M8.5 5H15.5C17.6 5 18.5 5.9 18.5 8V14.8C18.5 16.9 17.6 17.8 15.5 17.8H8.5C6.4 17.8 5.5 16.9 5.5 14.8V8C5.5 5.9 6.4 5 8.5 5ZM8.8 9.8A1.25 1.25 0 108.8 12.3 1.25 1.25 0 108.8 9.8ZM15.2 9.8A1.25 1.25 0 1015.2 12.3 1.25 1.25 0 1015.2 9.8ZM8 14.3H9.6C9.85 14.3 10 14.5 10 14.75C10 15 9.85 15.2 9.6 15.2H8C7.75 15.2 7.55 15 7.55 14.75C7.55 14.5 7.75 14.3 8 14.3ZM11.2 14.3H12.8C13.05 14.3 13.25 14.5 13.25 14.75C13.25 15 13.05 15.2 12.8 15.2H11.2C10.95 15.2 10.75 15 10.75 14.75C10.75 14.5 10.95 14.3 11.2 14.3ZM14.4 14.3H16C16.25 14.3 16.45 14.5 16.45 14.75C16.45 15 16.25 15.2 16 15.2H14.4C14.15 15.2 13.95 15 13.95 14.75C13.95 14.5 14.15 14.3 14.4 14.3Z"/>
            </svg>
        </div>

        <!-- Vertical Text CHATBOT -->
        <span class="text-[8px] font-extrabold tracking-wider text-white uppercase select-none leading-none mt-0.5"
              style="writing-mode: vertical-rl; letter-spacing: 0.1em;">
            CHATBOT
        </span>
    </button>

    <!-- Chat Window Modal -->
    <div x-show="isOpen" 
         x-transition:enter="transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
         x-transition:enter-start="opacity-0 translate-y-8 translate-x-4 scale-90"
         x-transition:enter-end="opacity-100 translate-y-0 translate-x-0 scale-100"
         x-transition:leave="transition-all duration-200 ease-[cubic-bezier(0.4,0,1,1)]"
         x-transition:leave-start="opacity-100 translate-y-0 translate-x-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 translate-x-3 scale-90"
         class="chatbot-modal-box origin-bottom-right bg-white rounded-3xl shadow-[0_22px_50px_rgba(2,40,54,0.35)] border border-slate-200/90 flex flex-col overflow-hidden"
         style="width: 390px; max-width: calc(100vw - 1.5rem); height: 520px; max-height: calc(100vh - 95px); display: none;">
        
        <!-- Header -->
        <div class="px-4.5 py-3.5 text-white flex items-center justify-between shrink-0 relative overflow-hidden border-b border-white/10"
             style="background: linear-gradient(135deg, #0e4c50 0%, #155E63 50%, #17a9a9 100%) !important;">
            
            <!-- Subtle decorative background glow -->
            <div class="absolute -top-10 -right-10 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

            <div class="flex items-center gap-3 relative z-10 min-w-0">
                <!-- Avatar with online indicator -->
                <div class="relative shrink-0">
                    <div class="w-10 h-10 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center text-white shadow-inner">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="#ffffff">
                            <rect x="10.8" y="1.8" width="2.4" height="3.4" rx="1.2" fill="#ffffff"/>
                            <rect x="2.5" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                            <rect x="19.3" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                            <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M8.5 5H15.5C17.6 5 18.5 5.9 18.5 8V14.8C18.5 16.9 17.6 17.8 15.5 17.8H8.5C6.4 17.8 5.5 16.9 5.5 14.8V8C5.5 5.9 6.4 5 8.5 5ZM8.8 9.8A1.25 1.25 0 108.8 12.3 1.25 1.25 0 108.8 9.8ZM15.2 9.8A1.25 1.25 0 1015.2 12.3 1.25 1.25 0 1015.2 9.8ZM8 14.3H9.6C9.85 14.3 10 14.5 10 14.75C10 15 9.85 15.2 9.6 15.2H8C7.75 15.2 7.55 15 7.55 14.75C7.55 14.5 7.75 14.3 8 14.3ZM11.2 14.3H12.8C13.05 14.3 13.25 14.5 13.25 14.75C13.25 15 13.05 15.2 12.8 15.2H11.2C10.95 15.2 10.75 15 10.75 14.75C10.75 14.5 10.95 14.3 11.2 14.3ZM14.4 14.3H16C16.25 14.3 16.45 14.5 16.45 14.75C16.45 15 16.25 15.2 16 15.2H14.4C14.15 15.2 13.95 15 13.95 14.75C13.95 14.5 14.15 14.3 14.4 14.3Z"/>
                        </svg>
                    </div>
                    <!-- Online Pulse Dot -->
                    <span class="absolute -bottom-0.5 -right-0.5 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400 border-2 border-[#155E63]"></span>
                    </span>
                </div>

                <!-- Title & Subtitle -->
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <h3 class="font-heading font-bold text-[13.5px] sm:text-sm text-white tracking-tight leading-snug truncate" style="color: #ffffff !important;">Asisten Mandiri PT MSN</h3>
                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">AI</span>
                    </div>
                    <p class="text-[11px] text-teal-100/90 leading-tight mt-0.5 truncate" style="color: rgba(204, 251, 241, 0.9) !important;">Bantuan cepat HRIS, Tiketing &amp; Operasional</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-1.5 shrink-0 relative z-10">
                <!-- Tombol Hapus Riwayat Chat Langsung Tanpa Alert -->
                <button @click="clearChat()" 
                        type="button" 
                        class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-teal-100 hover:text-white flex items-center justify-center transition-all cursor-pointer border border-white/10 shadow-2xs active:scale-95"
                        title="Hapus Riwayat Chat"
                        aria-label="Hapus Riwayat Chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
                <!-- Tombol Tutup Chat -->
                <button @click="closeChat()" 
                        type="button" 
                        class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-teal-100 hover:text-white flex items-center justify-center transition-all cursor-pointer border border-white/10 shadow-2xs active:scale-95"
                        title="Tutup Chat"
                        aria-label="Tutup Chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat History -->
        <div id="chatbot-messages" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50 text-xs">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.sender === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.sender === 'user' 
                                ? 'bg-[#155E63] text-white rounded-2xl rounded-tr-none px-3.5 py-2.5 max-w-[85%] shadow-xs' 
                                : 'bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-tl-none px-3.5 py-2.5 max-w-[88%] shadow-xs'">
                        <p x-text="msg.text" class="leading-relaxed whitespace-pre-line"></p>

                        <!-- Action Button Menuju Halaman Terkait -->
                        <template x-if="msg.action_url && msg.action_label">
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <a :href="msg.action_url"
                                   style="background: linear-gradient(135deg, #155E63 0%, #18C4C4 100%) !important;"
                                   class="inline-flex items-center justify-between w-full px-3 py-2 rounded-xl text-white font-semibold text-[11px] shadow-xs hover:opacity-95 transition-all group">
                                    <span x-text="msg.action_label"></span>
                                    <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start">
                <div class="bg-white border border-slate-200 text-slate-500 rounded-2xl px-3 py-2 text-xs flex items-center gap-2 shadow-xs">
                    <span class="animate-pulse">Mengetik balasan...</span>
                </div>
            </div>
        </div>

        <!-- Quick Suggestions -->
        <div class="px-3 py-2 bg-white border-t border-slate-100 flex items-center gap-1.5 overflow-x-auto text-[11px] scrollbar-none shrink-0">
            <button @click="askPreset('Bagaimana cara absen masuk dan pulang?')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition cursor-pointer">
                📍 Cara Absen
            </button>
            <button @click="askPreset('Bagaimana cara mengajukan dana?')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition cursor-pointer">
                💰 Pengajuan Dana
            </button>
            <button @click="askPreset('Status pengajuan saya')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition cursor-pointer">
                📋 Status Pengajuan
            </button>
            <button @click="askPreset('Apakah saya sudah absen hari ini?')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition cursor-pointer">
                ⏱️ Presensi Hari Ini
            </button>
            <button @click="askPreset('Berapa sisa cuti saya?')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition cursor-pointer">
                🌴 Sisa Cuti
            </button>
            <button @click="askPreset('Bagaimana cara membuat pengajuan lembur?')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition cursor-pointer">
                💼 Klaim Lembur
            </button>
            <button @click="askPreset('Bagaimana cara tukar shift jadwal?')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition cursor-pointer">
                📅 Tukar Shift
            </button>
            <button @click="askPreset('Tiket aktif saya yang belum selesai')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition cursor-pointer">
                🎫 Tiket Saya
            </button>
        </div>

        <!-- Input Bar -->
        <form @submit.prevent="sendMessage()" class="p-3 bg-white border-t border-slate-200 flex items-center gap-1.5 shrink-0">
            <input type="text" 
                   id="chatbot-input-query"
                   x-ref="chatInput"
                   x-model="inputQuery" 
                   :placeholder="isListening ? '🎙️ Mendengarkan suara Anda...' : 'Ketik pertanyaan Anda...'" 
                   class="flex-1 bg-slate-50 border border-slate-200 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 rounded-xl px-3 py-2 text-xs text-slate-800 placeholder-slate-400 outline-none transition"
                   :disabled="loading" />
            <button type="button" 
                    @click="toggleVoiceInput()"
                    :title="isListening ? 'Berhenti mendengarkan' : 'Bicara untuk mengetik (Voice-to-Text)'"
                    :class="isListening ? 'bg-rose-50 text-rose-600 border border-rose-300' : 'bg-slate-100 hover:bg-teal-50 text-slate-600 hover:text-[#0D9488]'"
                    class="h-8 w-8 rounded-xl flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0 relative">
                <svg class="w-4 h-4" :class="isListening ? 'text-rose-600 animate-pulse' : 'text-slate-600'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                </svg>
                <span x-show="isListening" class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            </button>
            <button type="submit" 
                    :disabled="loading || !inputQuery.trim()" 
                    class="h-8 px-3.5 rounded-xl bg-[#155E63] hover:bg-[#18C4C4] disabled:opacity-50 text-white font-medium text-xs flex items-center justify-center transition cursor-pointer shrink-0">
                Kirim
            </button>
        </form>
    </div>
</div>

<script>
    function chatbotWidget() {
        const defaultGreeting = [
            {
                sender: 'bot',
                text: 'Halo {{ auth()->user()?->name ?? "Rekan MSN" }}! Saya asisten virtual PT MSN. Ada yang bisa saya bantu terkait jadwal, cuti, klaim, atau tiket gangguan hari ini?'
            }
        ];
        const storageKeyMessages = 'gomsn_chatbot_messages_{{ auth()->id() ?? "guest" }}';
        const storageKeyOpen = 'gomsn_chatbot_open_{{ auth()->id() ?? "guest" }}';

        let initialMessages = defaultGreeting;
        try {
            const saved = localStorage.getItem(storageKeyMessages);
            if (saved) {
                const parsed = JSON.parse(saved);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    initialMessages = parsed;
                }
            }
        } catch (e) {
            console.warn('Gagal membaca riwayat chat dari storage', e);
        }

        let isInitiallyOpen = false;
        try {
            isInitiallyOpen = localStorage.getItem(storageKeyOpen) === '1';
        } catch (e) {}

        return {
            isOpen: isInitiallyOpen,
            loading: false,
            inputQuery: '',
            isListening: false,
            speechRecognizer: null,
            messages: initialMessages,
            init() {
                if (this.isOpen) {
                    this.scrollToBottom();
                }
            },
            toggleVoiceInput() {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!SpeechRecognition) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Browser Tidak Mendukung',
                            text: 'Browser Anda belum mendukung input suara. Silakan gunakan Google Chrome.',
                            confirmButtonColor: '#155E63'
                        });
                    } else {
                        alert('Browser Anda belum mendukung input suara.');
                    }
                    return;
                }
                if (this.isListening) {
                    if (this.speechRecognizer) {
                        try { this.speechRecognizer.stop(); } catch(e) {}
                    }
                    this.isListening = false;
                    return;
                }

                if (this.speechRecognizer) {
                    try { this.speechRecognizer.abort(); } catch(e) {}
                    this.speechRecognizer = null;
                }

                try {
                    this.speechRecognizer = new SpeechRecognition();
                    this.speechRecognizer.lang = 'id-ID';
                    this.speechRecognizer.interimResults = true;
                    this.speechRecognizer.continuous = true;
                    this.speechRecognizer.maxAlternatives = 1;

                    const initialText = this.inputQuery;
                    this.isListening = true;

                    this.speechRecognizer.onstart = () => {
                        this.isListening = true;
                    };

                    this.speechRecognizer.onresult = (event) => {
                        let accumulatedFinal = '';
                        let interimSpoken = '';
                        for (let i = 0; i < event.results.length; ++i) {
                            if (event.results[i].isFinal) {
                                accumulatedFinal += event.results[i][0].transcript + ' ';
                            } else {
                                interimSpoken += event.results[i][0].transcript;
                            }
                        }
                        const liveText = (accumulatedFinal + interimSpoken).trim();
                        if (liveText) {
                            const prefix = initialText.trim() ? initialText.trim() + ' ' : '';
                            this.inputQuery = prefix + liveText;
                            this.$nextTick(() => {
                                const el = this.$refs.chatInput || document.getElementById('chatbot-input-query');
                                if (el) {
                                    try {
                                        const len = el.value.length;
                                        el.setSelectionRange(len, len);
                                    } catch (e) {}
                                    el.scrollLeft = el.scrollWidth;
                                    setTimeout(() => {
                                        if (el) {
                                            el.scrollLeft = el.scrollWidth;
                                            try {
                                                const len = el.value.length;
                                                el.setSelectionRange(len, len);
                                            } catch (e) {}
                                        }
                                    }, 15);
                                }
                            });
                        }
                    };

                    this.speechRecognizer.onerror = (event) => {
                        console.warn('[Chatbot-Voice] Error:', event.error);
                        if (event.error === 'no-speech') {
                            return;
                        }
                        this.isListening = false;
                        if (event.error === 'not-allowed' || event.error === 'permission-denied') {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Akses Mikrofon Belum Diizinkan',
                                    text: 'Izin mikrofon belum aktif atau diblokir. Pastikan mikrofon diizinkan di browser dan Windows Privacy Settings.',
                                    confirmButtonColor: '#155E63'
                                });
                            }
                        }
                    };

                    this.speechRecognizer.onend = () => {
                        this.isListening = false;
                    };

                    this.speechRecognizer.start();
                } catch (e) {
                    console.error('[Chatbot-Voice] Failed to start:', e);
                    this.isListening = false;
                }
            },
            toggleChat() {
                this.isOpen = !this.isOpen;
                try {
                    localStorage.setItem(storageKeyOpen, this.isOpen ? '1' : '0');
                } catch (e) {}
                if (this.isOpen) {
                    this.scrollToBottom();
                }
            },
            closeChat() {
                this.isOpen = false;
                try {
                    localStorage.setItem(storageKeyOpen, '0');
                } catch (e) {}
            },
            clearChat() {
                this.messages = [
                    {
                        sender: 'bot',
                        text: 'Halo {{ auth()->user()?->name ?? "Rekan MSN" }}! Saya asisten virtual PT MSN. Ada yang bisa saya bantu terkait jadwal, cuti, klaim, atau tiket gangguan hari ini?'
                    }
                ];
                try {
                    localStorage.removeItem(storageKeyMessages);
                } catch (e) {}
                this.scrollToBottom();
            },
            saveMessages() {
                try {
                    localStorage.setItem(storageKeyMessages, JSON.stringify(this.messages));
                } catch (e) {
                    console.warn('Gagal menyimpan chat ke storage', e);
                }
            },
            askPreset(query) {
                this.inputQuery = query;
                this.sendMessage();
            },
            async sendMessage() {
                if (this.isListening && this.speechRecognizer) {
                    try { this.speechRecognizer.stop(); } catch(e) {}
                    this.isListening = false;
                }
                const text = this.inputQuery.trim();
                if (!text || this.loading) return;

                this.messages.push({ sender: 'user', text: text });
                this.saveMessages();
                this.inputQuery = '';
                this.loading = true;
                this.scrollToBottom();

                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch("{{ route('api.chatbot.ask') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ message: text })
                    });

                    const resJson = await response.json();
                    if (resJson && resJson.data && resJson.data.response) {
                        this.messages.push({
                            sender: 'bot',
                            text: resJson.data.response,
                            action_url: resJson.data.action_url || null,
                            action_label: resJson.data.action_label || null
                        });
                    } else {
                        this.messages.push({ sender: 'bot', text: 'Maaf, terjadi kendala saat memproses balasan.' });
                    }
                } catch (e) {
                    this.messages.push({ sender: 'bot', text: 'Maaf, terjadi kesalahan koneksi jaringan.' });
                } finally {
                    this.saveMessages();
                    this.loading = false;
                    this.scrollToBottom();
                }
            },
            scrollToBottom() {
                setTimeout(() => {
                    const box = document.getElementById('chatbot-messages');
                    if (box) box.scrollTop = box.scrollHeight;
                }, 100);
            }
        };
    }
</script>
