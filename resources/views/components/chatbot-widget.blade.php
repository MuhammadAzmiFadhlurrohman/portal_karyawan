<div id="chatbot-widget-container" 
     class="fixed bottom-[78px] right-3.5 sm:right-6 md:bottom-6 md:right-6 z-30 font-sans transition-all duration-300"
     :class="{ 'opacity-0 pointer-events-none scale-75': typeof bottomSheetOpen !== 'undefined' && bottomSheetOpen }"
     x-data="chatbotWidget()">
    <!-- Floating Trigger Button -->
    <button @click="toggleChat()" 
            type="button"
            class="relative h-11 w-11 sm:h-12 sm:w-12 rounded-2xl bg-gradient-to-tr from-[#0F4C50] via-[#155E63] to-[#0D9488] text-white shadow-xl hover:shadow-[#0D9488]/30 flex items-center justify-center transition-all duration-300 transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-teal-300/40 cursor-pointer border border-white/20"
            title="Tanya Asisten Mandiri PT MSN"
            aria-label="Buka Chatbot Asisten">
        <svg x-show="!isOpen" class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
        </svg>
        <svg x-show="isOpen" class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span x-show="!isOpen" class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-white"></span>
    </button>

    <!-- Chat Window Modal -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-180"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="fixed inset-x-3 bottom-[114px] sm:inset-x-auto sm:right-6 md:right-6 md:bottom-20 sm:w-[410px] max-h-[590px] bg-white rounded-3xl shadow-[0_20px_50px_rgba(15,76,80,0.22)] border border-slate-200/80 flex flex-col overflow-hidden mb-2 z-50 backdrop-blur-sm"
         style="display: none;">
        
        <!-- Header -->
        <div class="relative p-4 text-white flex items-center justify-between border-b border-white/10 overflow-hidden"
             style="background: linear-gradient(135deg, #093E42 0%, #0F4C50 50%, #0D9488 100%) !important;">
            <!-- Subtle Decorative Ambient Circle -->
            <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-white/5 pointer-events-none blur-lg"></div>

            <div class="flex items-center gap-3 z-10">
                <div class="relative w-9 h-9 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-inner shrink-0">
                    <svg class="w-5 h-5 text-teal-200" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
                    </svg>
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-[#0F4C50]"></span>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <h3 class="font-heading font-bold text-sm tracking-tight text-white leading-tight">Asisten Mandiri MSN</h3>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-emerald-400/20 text-emerald-200 border border-emerald-400/30">ONLINE</span>
                    </div>
                    <p class="text-[11px] text-teal-100/90 font-normal mt-0.5">HRIS, Tiketing &amp; Operasional</p>
                </div>
            </div>
            
            <div class="flex items-center gap-1.5 z-10">
                <!-- Tombol Hapus Riwayat Chat -->
                <button @click="clearChat()" 
                        type="button" 
                        class="w-7 h-7 rounded-xl bg-white/10 hover:bg-white/20 text-teal-100 hover:text-white border border-white/10 flex items-center justify-center transition-all cursor-pointer"
                        title="Hapus Riwayat Chat"
                        aria-label="Hapus Riwayat Chat">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
                <!-- Tombol Tutup Chat -->
                <button @click="closeChat()" 
                        type="button" 
                        class="w-7 h-7 rounded-xl bg-white/10 hover:bg-white/20 text-teal-100 hover:text-white border border-white/10 flex items-center justify-center transition-all cursor-pointer"
                        title="Tutup Chat"
                        aria-label="Tutup Chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat History -->
        <div id="chatbot-messages" class="flex-1 p-4 overflow-y-auto space-y-3.5 min-h-[300px] max-h-[385px] bg-[#F8FAFC] text-xs">
            <template x-for="(msg, index) in messages" :key="index">
                <div>
                    <!-- User Message -->
                    <template x-if="msg.sender === 'user'">
                        <div class="flex justify-end">
                            <div class="bg-gradient-to-tr from-[#0F4C50] to-[#155E63] text-white rounded-2xl rounded-br-xs px-3.5 py-2.5 max-w-[85%] shadow-xs leading-relaxed text-xs">
                                <p x-text="msg.text" class="whitespace-pre-line"></p>
                            </div>
                        </div>
                    </template>

                    <!-- Bot Message -->
                    <template x-if="msg.sender === 'bot'">
                        <div class="flex justify-start items-start gap-2">
                            <div class="w-6 h-6 rounded-lg bg-teal-50 border border-teal-200/60 flex items-center justify-center text-teal-700 shrink-0 mt-0.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                </svg>
                            </div>
                            <div class="bg-white border border-slate-200/80 text-slate-700 rounded-2xl rounded-tl-xs px-3.5 py-3 max-w-[88%] shadow-xs">
                                <div x-html="formatMessage(msg.text)" class="leading-relaxed whitespace-pre-line text-xs font-normal"></div>

                                <!-- Action Button Menuju Halaman Terkait -->
                                <template x-if="msg.action_url && msg.action_label">
                                    <div class="mt-2.5 pt-2 border-t border-slate-100">
                                        <a :href="msg.action_url"
                                           class="inline-flex items-center justify-between w-full px-3 py-2 rounded-xl text-white font-medium text-[11px] shadow-xs hover:shadow-md transition-all duration-200 group active:scale-[0.98]"
                                           style="background: linear-gradient(135deg, #0F4C50 0%, #0D9488 100%) !important;">
                                            <span class="flex items-center gap-1.5 truncate font-semibold" x-text="msg.action_label"></span>
                                            <span class="w-5 h-5 rounded-lg bg-white/15 flex items-center justify-center shrink-0 ml-2 group-hover:translate-x-0.5 transition-transform">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Loading Typing Indicator -->
            <div x-show="loading" class="flex justify-start items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-teal-50 border border-teal-200/60 flex items-center justify-center text-teal-700 shrink-0">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>
                <div class="bg-white border border-slate-200/80 rounded-2xl rounded-tl-xs px-3.5 py-2.5 shadow-xs flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0F4C50] animate-bounce" style="animation-delay: 0ms"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0D9488] animate-bounce" style="animation-delay: 150ms"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#18C4C4] animate-bounce" style="animation-delay: 300ms"></span>
                    <span class="text-[11px] text-slate-400 font-medium ml-1">Menyiapkan jawaban...</span>
                </div>
            </div>
        </div>

        <!-- Quick Suggestions -->
        <div class="px-3 py-2 bg-white/95 border-t border-slate-100 flex items-center gap-1.5 overflow-x-auto text-[11px] scrollbar-none">
            <button @click="askPreset('Bagaimana cara absen masuk dan pulang?')" type="button" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-slate-100 hover:bg-teal-50 hover:text-teal-800 hover:border-teal-300 text-slate-600 border border-slate-200/60 font-medium transition-all cursor-pointer shadow-2xs active:scale-95">
                📍 Cara Absen
            </button>
            <button @click="askPreset('Bagaimana cara mengajukan dana?')" type="button" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-slate-100 hover:bg-teal-50 hover:text-teal-800 hover:border-teal-300 text-slate-600 border border-slate-200/60 font-medium transition-all cursor-pointer shadow-2xs active:scale-95">
                💰 Pengajuan Dana
            </button>
            <button @click="askPreset('Status pengajuan saya')" type="button" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-slate-100 hover:bg-teal-50 hover:text-teal-800 hover:border-teal-300 text-slate-600 border border-slate-200/60 font-medium transition-all cursor-pointer shadow-2xs active:scale-95">
                📋 Status Pengajuan
            </button>
            <button @click="askPreset('Apakah saya sudah absen hari ini?')" type="button" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-slate-100 hover:bg-teal-50 hover:text-teal-800 hover:border-teal-300 text-slate-600 border border-slate-200/60 font-medium transition-all cursor-pointer shadow-2xs active:scale-95">
                ⏱️ Presensi Hari Ini
            </button>
            <button @click="askPreset('Berapa sisa cuti saya?')" type="button" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-slate-100 hover:bg-teal-50 hover:text-teal-800 hover:border-teal-300 text-slate-600 border border-slate-200/60 font-medium transition-all cursor-pointer shadow-2xs active:scale-95">
                🌴 Sisa Cuti
            </button>
            <button @click="askPreset('Bagaimana cara membuat pengajuan lembur?')" type="button" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-slate-100 hover:bg-teal-50 hover:text-teal-800 hover:border-teal-300 text-slate-600 border border-slate-200/60 font-medium transition-all cursor-pointer shadow-2xs active:scale-95">
                ⏰ Pengajuan Lembur
            </button>
            <button @click="askPreset('Bagaimana cara tukar shift jadwal?')" type="button" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-slate-100 hover:bg-teal-50 hover:text-teal-800 hover:border-teal-300 text-slate-600 border border-slate-200/60 font-medium transition-all cursor-pointer shadow-2xs active:scale-95">
                📅 Tukar Shift
            </button>
            <button @click="askPreset('Tiket aktif saya yang belum selesai')" type="button" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-slate-100 hover:bg-teal-50 hover:text-teal-800 hover:border-teal-300 text-slate-600 border border-slate-200/60 font-medium transition-all cursor-pointer shadow-2xs active:scale-95">
                🎫 Tiket Saya
            </button>
        </div>

        <!-- Input Bar -->
        <form @submit.prevent="sendMessage()" class="p-3 bg-white border-t border-slate-100 flex items-center gap-2">
            <div class="relative flex-1">
                <input type="text" 
                       x-model="inputQuery" 
                       placeholder="Tulis pertanyaan Anda..." 
                       class="w-full bg-slate-50 hover:bg-slate-100/60 focus:bg-white border border-slate-200/80 focus:border-[#0D9488] focus:ring-2 focus:ring-[#0D9488]/15 rounded-xl pl-3.5 pr-3 py-2 text-xs text-slate-800 placeholder-slate-400 outline-none transition-all duration-200"
                       :disabled="loading" />
            </div>
            <button type="submit" 
                    :disabled="loading || !inputQuery.trim()" 
                    class="h-8.5 px-3.5 rounded-xl text-white font-semibold text-xs flex items-center gap-1.5 shadow-xs transition-all duration-200 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:shadow-md active:scale-95 shrink-0"
                    style="background: linear-gradient(135deg, #0F4C50 0%, #0D9488 100%) !important;">
                <span>Kirim</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                </svg>
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
            messages: initialMessages,
            init() {
                if (this.isOpen) {
                    this.scrollToBottom();
                }
            },
            formatMessage(text) {
                if (!text) return '';
                let safe = String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
                // Format **tebal** menjadi <strong class="font-semibold text-slate-900">
                safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong class="font-semibold text-slate-900">$1</strong>');
                return safe;
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
                if (confirm('Hapus semua riwayat percakapan chat ini?')) {
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
                }
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

