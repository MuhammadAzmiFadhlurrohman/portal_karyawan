<div id="chatbot-widget-container" 
     class="fixed bottom-[78px] right-3.5 sm:right-6 md:bottom-6 md:right-6 z-30 font-sans transition-all duration-300"
     :class="{ 'opacity-0 pointer-events-none scale-75': typeof bottomSheetOpen !== 'undefined' && bottomSheetOpen }"
     x-data="chatbotWidget()">
    <!-- Floating Trigger Button -->
    <button @click="toggleChat()" 
            type="button"
            class="h-11 w-11 sm:h-12 sm:w-12 rounded-full bg-gradient-to-tr from-[#155E63] to-[#18C4C4] text-white shadow-xl hover:shadow-2xl flex items-center justify-center transition-transform transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-teal-200 cursor-pointer"
            title="Tanya Asisten Mandiri PT MSN"
            aria-label="Buka Chatbot Asisten">
        <svg x-show="!isOpen" class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        <svg x-show="isOpen" class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <!-- Chat Window Modal -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="fixed right-3 bottom-[114px] sm:right-6 md:right-6 md:bottom-20 bg-white rounded-3xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden mb-2 z-50"
         style="width: 390px; max-width: calc(100vw - 1.5rem); max-height: calc(100vh - 130px); display: none;">
        
        <!-- Header -->
        <div class="p-4 text-white flex items-center justify-between shrink-0"
             style="background: linear-gradient(135deg, #155E63 0%, #18C4C4 100%) !important;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center font-bold text-white text-base shrink-0">
                    🤖
                </div>
                <div>
                    <h3 class="font-heading font-bold text-sm tracking-tight leading-tight">Asisten Mandiri PT MSN</h3>
                    <p class="text-[11px] text-teal-100">Bantuan cepat HRIS, Tiketing &amp; Operasional</p>
                </div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <!-- Tombol Hapus Riwayat Chat Langsung Tanpa Alert -->
                <button @click="clearChat()" 
                        type="button" 
                        class="text-teal-100 hover:text-white hover:bg-white/10 p-1.5 rounded-lg transition-colors cursor-pointer"
                        title="Hapus Riwayat Chat"
                        aria-label="Hapus Riwayat Chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
                <!-- Tombol Tutup Chat -->
                <button @click="closeChat()" 
                        type="button" 
                        class="text-teal-100 hover:text-white hover:bg-white/10 p-1.5 rounded-lg transition-colors cursor-pointer"
                        title="Tutup Chat"
                        aria-label="Tutup Chat">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat History -->
        <div id="chatbot-messages" class="flex-1 p-4 overflow-y-auto space-y-3 min-h-[280px] max-h-[380px] bg-slate-50 text-xs">
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
        <form @submit.prevent="sendMessage()" class="p-3 bg-white border-t border-slate-200 flex items-center gap-2 shrink-0">
            <input type="text" 
                   x-model="inputQuery" 
                   placeholder="Ketik pertanyaan Anda..." 
                   class="flex-1 bg-slate-50 border border-slate-200 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 rounded-xl px-3 py-2 text-xs text-slate-800 placeholder-slate-400 outline-none transition"
                   :disabled="loading" />
            <button type="submit" 
                    :disabled="loading || !inputQuery.trim()" 
                    class="h-8 px-4 rounded-xl bg-[#155E63] hover:bg-[#18C4C4] disabled:opacity-50 text-white font-medium text-xs flex items-center justify-center transition cursor-pointer shrink-0">
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
            messages: initialMessages,
            init() {
                if (this.isOpen) {
                    this.scrollToBottom();
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
