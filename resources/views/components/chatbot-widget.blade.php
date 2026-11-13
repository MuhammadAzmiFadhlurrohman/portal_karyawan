<div id="chatbot-widget-container" 
     class="fixed bottom-[78px] right-3.5 sm:right-6 md:bottom-6 md:right-6 z-30 font-sans transition-all duration-300"
     :class="{ 'opacity-0 pointer-events-none scale-75': typeof bottomSheetOpen !== 'undefined' && bottomSheetOpen }"
     x-data="chatbotWidget()">
    <!-- Floating Trigger Button -->
    <button @click="toggleChat()" 
            type="button"
            class="h-10 w-10 sm:h-11 sm:w-11 md:h-12 md:w-12 rounded-full bg-gradient-to-tr from-[#155E63] to-[#18C4C4] text-white shadow-xl hover:shadow-2xl flex items-center justify-center transition-transform transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-teal-200 cursor-pointer"
            title="Tanya Asisten Mandiri PT MSN"
            aria-label="Buka Chatbot Asisten">
        <svg x-show="!isOpen" class="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        <svg x-show="isOpen" class="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;">
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
         class="fixed inset-x-3 bottom-[114px] sm:inset-x-auto sm:right-6 md:right-6 md:bottom-20 sm:w-[400px] max-h-[580px] bg-white rounded-3xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden mb-2 z-50"
         style="display: none;">
        
        <!-- Header -->
        <div class="p-4 text-white flex items-center justify-between"
             style="background: linear-gradient(135deg, #155E63 0%, #18C4C4 100%) !important;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center font-bold text-white text-base">
                    🤖
                </div>
                <div>
                    <h3 class="font-heading font-bold text-sm tracking-tight leading-tight">Asisten Mandiri PT MSN</h3>
                    <p class="text-[11px] text-teal-100">Bantuan cepat HRIS, Tiketing &amp; Operasional</p>
                </div>
            </div>
            <button @click="isOpen = false" class="text-teal-100 hover:text-white p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Chat History -->
        <div id="chatbot-messages" class="flex-1 p-4 overflow-y-auto space-y-3 min-h-[300px] max-h-[380px] bg-slate-50 text-xs">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.sender === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.sender === 'user' 
                                ? 'bg-[#155E63] text-white rounded-2xl rounded-tr-none px-3.5 py-2.5 max-w-[85%] shadow-xs' 
                                : 'bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-tl-none px-3.5 py-2.5 max-w-[85%] shadow-xs whitespace-pre-line'">
                        <p x-text="msg.text"></p>
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
        <div class="px-3 py-2 bg-white border-t border-slate-100 flex items-center gap-1.5 overflow-x-auto text-[11px] scrollbar-none">
            <button @click="askPreset('Berapa sisa cuti saya?')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition">
                🌴 Sisa Cuti
            </button>
            <button @click="askPreset('Apakah saya sudah absen hari ini?')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition">
                ⏱️ Presensi Hari Ini
            </button>
            <button @click="askPreset('Tiket aktif saya yang belum selesai')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition">
                🎫 Tiket Saya
            </button>
            <button @click="askPreset('Bagaimana cara mengajukan klaim lembur?')" type="button" class="whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-full font-medium transition">
                💼 Info Lembur
            </button>
        </div>

        <!-- Input Bar -->
        <form @submit.prevent="sendMessage()" class="p-3 bg-white border-t border-slate-200 flex items-center gap-2">
            <input type="text" 
                   x-model="inputQuery" 
                   placeholder="Ketik pertanyaan Anda..." 
                   class="flex-1 bg-slate-50 border border-slate-200 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 rounded-xl px-3 py-2 text-xs text-slate-800 placeholder-slate-400 outline-none transition"
                   :disabled="loading" />
            <button type="submit" 
                    :disabled="loading || !inputQuery.trim()" 
                    class="h-8 px-4 rounded-xl bg-[#155E63] hover:bg-[#18C4C4] disabled:opacity-50 text-white font-medium text-xs flex items-center justify-center transition">
                Kirim
            </button>
        </form>
    </div>
</div>

<script>
    function chatbotWidget() {
        return {
            isOpen: false,
            loading: false,
            inputQuery: '',
            messages: [
                {
                    sender: 'bot',
                    text: 'Halo {{ auth()->user()?->name ?? "Rekan MSN" }}! Saya asisten virtual PT MSN. Ada yang bisa saya bantu terkait jadwal, cuti, klaim, atau tiket gangguan hari ini?'
                }
            ],
            toggleChat() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.scrollToBottom();
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
                        this.messages.push({ sender: 'bot', text: resJson.data.response });
                    } else {
                        this.messages.push({ sender: 'bot', text: 'Maaf, terjadi kendala saat memproses balasan.' });
                    }
                } catch (e) {
                    this.messages.push({ sender: 'bot', text: 'Maaf, terjadi kesalahan koneksi jaringan.' });
                } finally {
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

