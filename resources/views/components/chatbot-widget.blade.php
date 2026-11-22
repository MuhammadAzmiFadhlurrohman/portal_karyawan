<div id="chatbot-widget-container" 
     class="font-sans"
     :class="{ 'opacity-0 pointer-events-none': typeof bottomSheetOpen !== 'undefined' && bottomSheetOpen }"
     x-data="chatbotWidget()">

    <style>
        /* Docked Side-Tab Trigger Button (Pinned on right edge of screen when chat is closed) */
        .chatbot-docked-tab {
            position: fixed !important;
            right: 0 !important;
            bottom: 86px !important;
            z-index: 50 !important;
            width: 34px !important;
            padding-top: 10px !important;
            padding-bottom: 12px !important;
            padding-left: 5px !important;
            padding-right: 7px !important;
            background: linear-gradient(180deg, #093c40 0%, #0d5f66 45%, #13808a 100%) !important;
            border-top-left-radius: 16px !important;
            border-bottom-left-radius: 16px !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            box-shadow: -4px 6px 18px rgba(2, 40, 54, 0.35) !important;
            border-left: 1.5px solid rgba(255, 255, 255, 0.3) !important;
            border-top: 1.5px solid rgba(255, 255, 255, 0.3) !important;
            border-bottom: 1.5px solid rgba(255, 255, 255, 0.2) !important;
            border-right: none !important;
            cursor: pointer !important;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), filter 0.2s ease !important;
        }
        @media (min-width: 640px) {
            .chatbot-docked-tab {
                width: 32px !important;
                bottom: 28px !important;
                padding: 10px 6px 12px 5px !important;
            }
        }
        .chatbot-docked-tab:hover {
            transform: translateX(-4px) !important;
            filter: brightness(1.1) !important;
        }

        /* Header Action Buttons (Sampah & Tutup X) */
        .chatbot-header-btn {
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            min-height: 36px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 12px !important;
            background: rgba(255, 255, 255, 0.16) !important;
            border: 1px solid rgba(255, 255, 255, 0.28) !important;
            color: #ffffff !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            flex-shrink: 0 !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12) !important;
        }
        .chatbot-header-btn:hover {
            background: rgba(255, 255, 255, 0.28) !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
        }
        .chatbot-header-btn:active {
            transform: scale(0.92) !important;
        }
        .chatbot-header-btn.is-trash:hover {
            background: rgba(239, 68, 68, 0.85) !important;
            border-color: rgba(248, 113, 113, 0.6) !important;
        }
        .chatbot-header-btn svg {
            width: 18px !important;
            height: 18px !important;
            stroke-width: 2.2 !important;
            display: block !important;
        }
        @media (min-width: 640px) {
            .chatbot-header-btn {
                width: 30px !important;
                height: 30px !important;
                min-width: 30px !important;
                min-height: 30px !important;
                border-radius: 10px !important;
            }
            .chatbot-header-btn svg {
                width: 15px !important;
                height: 15px !important;
            }
        }

        [x-cloak] {
            display: none !important;
        }
        .chatbot-docked-tab[style*="display: none"],
        .chatbot-docked-tab[style*="display:none"] {
            display: none !important;
        }

        /* Chat Window Modal Box - Floating Symmetrical Popup */
        .chatbot-modal-box {
            display: flex;
            flex-direction: column;
            position: fixed !important;
            right: 12px !important;
            bottom: 84px !important;
            left: auto !important;
            z-index: 60 !important;
            width: 380px !important;
            max-width: calc(100vw - 24px) !important;
            height: 520px !important;
            max-height: calc(100vh - 96px) !important;
            background: #ffffff !important;
            overflow: hidden !important;
            overscroll-behavior: contain !important;
            border-radius: 24px !important;
            border: 1px solid rgba(226, 232, 240, 0.95) !important;
            box-shadow: 0 20px 50px -10px rgba(2, 40, 54, 0.38) !important;
            margin: 0 !important;
        }
        @media (min-width: 640px) {
            .chatbot-modal-box {
                right: 20px !important;
                bottom: 20px !important;
                left: auto !important;
                width: 350px !important;
                max-width: calc(100vw - 40px) !important;
                height: 465px !important;
                max-height: calc(100vh - 96px) !important;
                margin: 0 !important;
                border-radius: 20px !important;
                box-shadow: 0 18px 45px -12px rgba(2, 40, 54, 0.30) !important;
            }
        }
        @media (min-width: 1024px) {
            .chatbot-modal-box {
                width: 360px !important;
                height: 485px !important;
                max-height: calc(100vh - 104px) !important;
            }
        }
        .chatbot-modal-box[style*="display: none"],
        .chatbot-modal-box[style*="display:none"] {
            display: none !important;
        }

        /* Rigid Sticky Header - NEVER Scrolls Away */
        .chatbot-header-bar {
            flex-shrink: 0 !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 30 !important;
            width: 100% !important;
        }

        /* Independent Scrollable Message Area */
        #chatbot-messages {
            flex: 1 1 0% !important;
            min-height: 0 !important;
            height: 100% !important;
            max-height: 100% !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            overscroll-behavior: contain !important;
            -webkit-overflow-scrolling: touch;
            display: flex !important;
            flex-direction: column !important;
            gap: 12px !important;
            padding: 14px 12px !important;
            background: #f8fafc !important;
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.45) transparent;
        }
        #chatbot-messages::-webkit-scrollbar {
            width: 5px;
        }
        #chatbot-messages::-webkit-scrollbar-track {
            background: transparent;
        }
        #chatbot-messages::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.45);
            border-radius: 9999px;
        }
        #chatbot-messages::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.65);
        }

        /* Rigid Footer & Input Bars */
        .chatbot-quick-chips {
            flex-shrink: 0 !important;
        }
        .chatbot-input-bar {
            flex-shrink: 0 !important;
        }

        /* Chat Message Rows, Circular Avatars & Proportional Bubbles */
        .chatbot-msg-row {
            display: flex;
            align-items: flex-start !important;
            gap: 8px !important;
            width: 100% !important;
            min-width: 0 !important;
        }
        .chatbot-msg-row.is-user {
            justify-content: flex-end !important;
        }
        .chatbot-msg-row.is-bot {
            justify-content: flex-start !important;
        }
        .chatbot-msg-row[style*="display: none"],
        .chatbot-msg-row[style*="display:none"] {
            display: none !important;
        }

        .chatbot-avatar {
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            min-height: 32px !important;
            border-radius: 9999px !important;
            flex-shrink: 0 !important;
            margin-top: 2px !important;
            overflow: hidden !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .chatbot-avatar.is-bot {
            background: linear-gradient(135deg, #0e4c50 0%, #155E63 55%, #17a9a9 100%) !important;
            color: #ffffff !important;
            border: 1.5px solid #ffffff !important;
            box-shadow: 0 2px 6px rgba(14, 76, 80, 0.22) !important;
        }
        .chatbot-avatar.is-user {
            background: #e2e8f0 !important;
            border: 1.5px solid #ffffff !important;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.14) !important;
        }

        .chatbot-bubble {
            min-width: 0 !important;
            border-radius: 16px !important;
            box-sizing: border-box !important;
        }
        .chatbot-bubble-user {
            max-width: calc(100% - 64px) !important;
            padding: 9px 14px !important;
            background: linear-gradient(135deg, #0e5257 0%, #155E63 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(14, 76, 80, 0.22) !important;
        }
        .chatbot-bubble-bot {
            max-width: calc(100% - 48px) !important;
            padding: 10px 14px !important;
            background: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06) !important;
        }

        .chatbot-msg-text {
            margin: 0 !important;
            padding: 0 !important;
            font-size: 12.5px !important;
            line-height: 1.55 !important;
            color: inherit !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            white-space: normal !important;
        }
        .chatbot-msg-content {
            white-space: pre-line !important;
        }

        .chatbot-action-wrap {
            margin-top: 10px !important;
            padding-top: 9px !important;
            border-top: 1px solid #f1f5f9 !important;
        }
        .chatbot-action-btn {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 8px !important;
            width: 100% !important;
            padding: 8px 12px !important;
            border-radius: 10px !important;
            background: #f0fdfa !important;
            border: 1px solid #99f6e4 !important;
            color: #0f5c5c !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            line-height: 1.35 !important;
            text-decoration: none !important;
            transition: background-color 0.15s ease, border-color 0.15s ease, transform 0.12s ease !important;
        }
        .chatbot-action-btn:hover {
            background: #ccfbf1 !important;
            border-color: #5eead4 !important;
        }
        .chatbot-action-btn:active {
            transform: scale(0.985) !important;
        }

        .chatbot-suggestions-wrap {
            margin-top: 10px !important;
            padding-top: 9px !important;
            border-top: 1px solid #f1f5f9 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 6px !important;
        }
        html.dark .chatbot-modal-box {
            background: #131c2e !important;
            border-color: rgba(51, 65, 85, 0.9) !important;
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.65) !important;
        }
        html.dark #chatbot-messages {
            background: #0f172a !important;
        }
        html.dark .chatbot-bubble-bot {
            background: #162235 !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }
        html.dark .chatbot-action-wrap,
        html.dark .chatbot-suggestions-wrap {
            border-top-color: #1e293b !important;
        }
        html.dark .chatbot-action-btn {
            background: rgba(20, 184, 166, 0.16) !important;
            border-color: rgba(45, 212, 191, 0.35) !important;
            color: #5eead4 !important;
        }
        html.dark .chatbot-action-btn:hover {
            background: rgba(20, 184, 166, 0.26) !important;
        }

        /* Compact Desktop Internal Scale */
        @media (min-width: 640px) {
            .chatbot-header-bar {
                padding: 10px 14px !important;
            }
            .chatbot-header-avatar {
                width: 32px !important;
                height: 32px !important;
                border-radius: 10px !important;
            }
            .chatbot-header-avatar svg {
                width: 18px !important;
                height: 18px !important;
            }
            .chatbot-header-bar h3 {
                font-size: 12.5px !important;
            }
            .chatbot-header-bar p {
                font-size: 10px !important;
            }

            #chatbot-messages {
                padding: 12px 11px !important;
                gap: 9px !important;
            }
            .chatbot-msg-row {
                gap: 7px !important;
            }
            .chatbot-avatar {
                width: 26px !important;
                height: 26px !important;
                min-width: 26px !important;
                min-height: 26px !important;
                margin-top: 1px !important;
            }
            .chatbot-avatar.is-bot svg {
                width: 14px !important;
                height: 14px !important;
            }
            .chatbot-bubble {
                border-radius: 13px !important;
            }
            .chatbot-bubble-user {
                max-width: 78% !important;
                padding: 7px 11px !important;
            }
            .chatbot-bubble-bot {
                max-width: 84% !important;
                padding: 8px 11px !important;
            }
            .chatbot-msg-text {
                font-size: 11.5px !important;
                line-height: 1.5 !important;
            }
            .chatbot-action-wrap {
                margin-top: 8px !important;
                padding-top: 7px !important;
            }
            .chatbot-action-btn {
                padding: 6px 10px !important;
                border-radius: 8px !important;
                font-size: 11px !important;
            }
            .chatbot-suggestions-wrap {
                margin-top: 8px !important;
                padding-top: 7px !important;
                gap: 5px !important;
            }
            .chatbot-suggestions-wrap button {
                padding: 5px 9px !important;
                border-radius: 9px !important;
            }
            .chatbot-suggestions-wrap button span:first-child {
                font-size: 11px !important;
            }
            .chatbot-quick-chips {
                padding: 6px 10px !important;
                gap: 5px !important;
            }
            .chatbot-quick-chips button {
                padding: 3.5px 9px !important;
                font-size: 10.5px !important;
            }
            .chatbot-input-bar {
                padding: 10px 12px !important;
                gap: 8px !important;
            }
            #chatbot-input-query {
                padding: 8.5px 12px !important;
                font-size: 13px !important;
                border-radius: 12px !important;
                min-height: 40px !important;
            }
            .chatbot-input-bar button[type="button"] {
                width: 38px !important;
                height: 38px !important;
                border-radius: 12px !important;
            }
            .chatbot-input-bar button[type="button"] svg {
                width: 17px !important;
                height: 17px !important;
            }
            .chatbot-input-bar button[type="submit"] {
                height: 38px !important;
                padding: 0 14px !important;
                font-size: 12.5px !important;
                border-radius: 12px !important;
            }
        }

        /* Typing cursor animation */
        @keyframes chatbotCursorBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        .chatbot-typing-cursor {
            display: inline-block;
            width: 2px;
            height: 1.1em;
            background-color: #155E63;
            margin-left: 2px;
            vertical-align: -0.15em;
            animation: chatbotCursorBlink 0.8s infinite;
        }

        /* Clean Single Border on Chatbot Input (Prevents global focus-visible double outline) */
        #chatbot-input-query {
            outline: none !important;
            outline-offset: 0 !important;
            border: 1.5px solid #cbd5e1 !important;
            box-shadow: none !important;
        }
        #chatbot-input-query:focus,
        #chatbot-input-query:focus-visible {
            outline: none !important;
            outline-offset: 0 !important;
            border-color: #0d9488 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.14) !important;
        }
    </style>

    <!-- Docked Side-Tab Trigger Button (Only visible when chat is closed) -->
    <button x-cloak
            x-show="!isOpen"
            x-transition:enter="transition-all duration-300 ease-out"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition-all duration-200 ease-in"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-4"
            @click.stop="toggleChat()" 
            type="button"
            class="chatbot-docked-tab text-white flex flex-col items-center justify-center focus:outline-none cursor-pointer select-none group"
            title="Tanya Asisten Mandiri PT MSN"
            aria-label="Buka Chatbot Asisten">
        
        <!-- Top Chevron Icon -->
        <svg class="w-3.5 h-3.5 text-white/95 transition-transform duration-300 shrink-0 group-hover:-translate-x-0.5"
             fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>

        <!-- Robot Icon -->
        <div class="my-1.5 shrink-0 transition-transform duration-200 group-hover:scale-110">
            <svg style="width: 19px; height: 19px;" viewBox="0 0 24 24" fill="#ffffff">
                <rect x="10.8" y="1.8" width="2.4" height="3.4" rx="1.2" fill="#ffffff"/>
                <rect x="2.5" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                <rect x="19.3" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M8.5 5H15.5C17.6 5 18.5 5.9 18.5 8V14.8C18.5 16.9 17.6 17.8 15.5 17.8H8.5C6.4 17.8 5.5 16.9 5.5 14.8V8C5.5 5.9 6.4 5 8.5 5ZM8.8 9.8A1.25 1.25 0 108.8 12.3 1.25 1.25 0 108.8 9.8ZM15.2 9.8A1.25 1.25 0 1015.2 12.3 1.25 1.25 0 1015.2 9.8ZM8 14.3H9.6C9.85 14.3 10 14.5 10 14.75C10 15 9.85 15.2 9.6 15.2H8C7.75 15.2 7.55 15 7.55 14.75C7.55 14.5 7.75 14.3 8 14.3ZM11.2 14.3H12.8C13.05 14.3 13.25 14.5 13.25 14.75C13.25 15 13.05 15.2 12.8 15.2H11.2C10.95 15.2 10.75 15 10.75 14.75C10.75 14.5 10.95 14.3 11.2 14.3ZM14.4 14.3H16C16.25 14.3 16.45 14.5 16.45 14.75C16.45 15 16.25 15.2 16 15.2H14.4C14.15 15.2 13.95 15 13.95 14.75C13.95 14.5 14.15 14.3 14.4 14.3Z"/>
            </svg>
        </div>

        <!-- Vertical Text CHATBOT -->
        <span class="text-[9px] font-black tracking-widest text-white uppercase select-none leading-none mt-1"
              style="writing-mode: vertical-rl; letter-spacing: 0.12em;">
            CHATBOT
        </span>
    </button>

    <!-- Chat Window Modal Box -->
    <div x-cloak
         x-show="isOpen"
         x-transition:enter="transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition-all duration-200 ease-in"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         class="chatbot-modal-box flex flex-col origin-bottom-right"
         @scroll="$el.scrollTop = 0"
         @click.outside="closeChat()"
         @keydown.escape.window="closeChat()"
         style="display: none;">
        
        <!-- Header -->
        <div class="chatbot-header-bar px-3.5 py-3 sm:px-4 sm:py-3.5 text-white flex items-center justify-between shrink-0 relative overflow-hidden border-b border-white/10"
             style="background: linear-gradient(135deg, #0e4c50 0%, #155E63 50%, #17a9a9 100%) !important;">
        
            <!-- Subtle decorative background glow -->
            <div class="absolute -top-10 -right-10 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

            <div class="flex items-center gap-2.5 sm:gap-3 relative z-10 min-w-0 flex-1 mr-2">
                <!-- Avatar with online indicator -->
                <div class="relative shrink-0">
                    <div class="chatbot-header-avatar w-9 h-9 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center text-white shadow-inner">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="#ffffff">
                            <rect x="10.8" y="1.8" width="2.4" height="3.4" rx="1.2" fill="#ffffff"/>
                            <rect x="2.5" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                            <rect x="19.3" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                            <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M8.5 5H15.5C17.6 5 18.5 5.9 18.5 8V14.8C18.5 16.9 17.6 17.8 15.5 17.8H8.5C6.4 17.8 5.5 16.9 5.5 14.8V8C5.5 5.9 6.4 5 8.5 5ZM8.8 9.8A1.25 1.25 0 108.8 12.3 1.25 1.25 0 108.8 9.8ZM15.2 9.8A1.25 1.25 0 1015.2 12.3 1.25 1.25 0 1015.2 9.8ZM8 14.3H9.6C9.85 14.3 10 14.5 10 14.75C10 15 9.85 15.2 9.6 15.2H8C7.75 15.2 7.55 15 7.55 14.75C7.55 14.5 7.75 14.3 8 14.3ZM11.2 14.3H12.8C13.05 14.3 13.25 14.5 13.25 14.75C13.25 15 13.05 15.2 12.8 15.2H11.2C10.95 15.2 10.75 15 10.75 14.75C10.75 14.5 10.95 14.3 11.2 14.3ZM14.4 14.3H16C16.25 14.3 16.45 14.5 16.45 14.75C16.45 15 16.25 15.2 16 15.2H14.4C14.15 15.2 13.95 15 13.95 14.75C13.95 14.5 14.15 14.3 14.4 14.3Z"/>
                        </svg>
                    </div>
                    <!-- Online Pulse Dot -->
                    <span class="absolute -bottom-0.5 -right-0.5 flex h-2.5 w-2.5 sm:h-3 sm:w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 sm:h-3 sm:w-3 bg-emerald-400 border-2 border-[#155E63]"></span>
                    </span>
                </div>

                <!-- Title & Subtitle -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5">
                        <h3 class="font-heading font-bold text-xs sm:text-sm text-white tracking-tight leading-snug truncate" style="color: #ffffff !important;">Asisten Mandiri PT MSN</h3>
                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-emerald-400/25 text-emerald-200 border border-emerald-400/30 shrink-0">AI</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-teal-100/90 leading-tight mt-0.5 truncate" style="color: rgba(204, 251, 241, 0.9) !important;">Bantuan cepat HRIS, Tiketing &amp; Operasional</p>
                </div>
            </div>

            <!-- Action Buttons (Sampah & Tutup X) -->
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0 relative z-10">
                <!-- Tombol Hapus Riwayat Chat Langsung Tanpa Alert -->
                <button @click.stop.prevent="clearChat()" 
                        type="button" 
                        class="chatbot-header-btn is-trash"
                        title="Hapus Riwayat Chat"
                        aria-label="Hapus Riwayat Chat">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
                <!-- Tombol Tutup Chat -->
                <button @click.stop.prevent="closeChat()" 
                        type="button" 
                        class="chatbot-header-btn is-close"
                        title="Tutup Chat"
                        aria-label="Tutup Chat">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat History -->
        @php
            $chatUserAvatarUrl = auth()->check() ? auth()->user()->avatar_url : null;
            $chatUserInitials = auth()->check() ? (auth()->user()->initials ?? strtoupper(substr(auth()->user()->name ?? 'U', 0, 2))) : 'U';
        @endphp
        <div id="chatbot-messages">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.sender === 'user' ? 'chatbot-msg-row is-user' : 'chatbot-msg-row is-bot'">
                    
                    <!-- Bot Circular Avatar (WhatsApp style) -->
                    <template x-if="msg.sender !== 'user'">
                        <div class="chatbot-avatar is-bot">
                            <svg style="width: 17px; height: 17px;" viewBox="0 0 24 24" fill="#ffffff">
                                <rect x="10.8" y="1.8" width="2.4" height="3.4" rx="1.2" fill="#ffffff"/>
                                <rect x="2.5" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                                <rect x="19.3" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                                <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M8.5 5H15.5C17.6 5 18.5 5.9 18.5 8V14.8C18.5 16.9 17.6 17.8 15.5 17.8H8.5C6.4 17.8 5.5 16.9 5.5 14.8V8C5.5 5.9 6.4 5 8.5 5ZM8.8 9.8A1.25 1.25 0 108.8 12.3 1.25 1.25 0 108.8 9.8ZM15.2 9.8A1.25 1.25 0 1015.2 12.3 1.25 1.25 0 1015.2 9.8ZM8 14.3H9.6C9.85 14.3 10 14.5 10 14.75C10 15 9.85 15.2 9.6 15.2H8C7.75 15.2 7.55 15 7.55 14.75C7.55 14.5 7.75 14.3 8 14.3ZM11.2 14.3H12.8C13.05 14.3 13.25 14.5 13.25 14.75C13.25 15 13.05 15.2 12.8 15.2H11.2C10.95 15.2 10.75 15 10.75 14.75C10.75 14.5 10.95 14.3 11.2 14.3ZM14.4 14.3H16C16.25 14.3 16.45 14.5 16.45 14.75C16.45 15 16.25 15.2 16 15.2H14.4C14.15 15.2 13.95 15 13.95 14.75C13.95 14.5 14.15 14.3 14.4 14.3Z"/>
                            </svg>
                        </div>
                    </template>

                    <!-- Message Bubble -->
                    <div :class="msg.sender === 'user' ? 'chatbot-bubble chatbot-bubble-user' : 'chatbot-bubble chatbot-bubble-bot'">
                        <p class="chatbot-msg-text"><span class="chatbot-msg-content" x-text="msg.isTyping ? (msg.text || '').trimStart() : (msg.text || '').trim()"></span><span x-show="msg.isTyping" class="chatbot-typing-cursor"></span></p>

                        <!-- Action Button Menuju Halaman Terkait -->
                        <template x-if="!msg.isTyping && msg.action_url && msg.action_label">
                            <div class="chatbot-action-wrap">
                                <a :href="msg.action_url" class="chatbot-action-btn group">
                                    <span class="truncate" x-text="msg.action_label"></span>
                                    <svg style="width: 14px; height: 14px; flex-shrink: 0;" class="transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                            </div>
                        </template>

                        <!-- 2 Rekomendasi Pertanyaan Hanya Jika Bot Butuh Klarifikasi / Tidak Mengerti (Tidak Ditampilkan Bila Ada Jawaban/Aksi) -->
                        <template x-if="!msg.isTyping && msg.suggestions && msg.suggestions.length > 0 && !msg.action_url && (!msg.text || (!msg.text.includes('Data Infrastruktur') && !msg.text.includes('Detail Distribusi') && !msg.text.includes('Data Pelanggan')))">
                            <div class="chatbot-suggestions-wrap transition-opacity duration-300">
                                <p class="text-[10px] font-semibold text-slate-500 flex items-center gap-1.5 mb-0.5">
                                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M11 3a1 1 0 10-2 0v1a1 1 0 002 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.001z"/>
                                    </svg>
                                    <span>Rekomendasi pertanyaan:</span>
                                </p>
                                <template x-for="(sug, sIdx) in msg.suggestions.slice(0, 2)" :key="sIdx">
                                    <button type="button"
                                            @click="askPreset(sug)"
                                            class="w-full text-left px-2.5 py-1.5 rounded-xl bg-teal-50/80 hover:bg-teal-100/90 text-teal-900 border border-teal-200/80 transition-all flex items-center justify-between group cursor-pointer shadow-2xs hover:shadow-xs active:scale-[0.98]">
                                        <span class="text-[11.5px] font-medium leading-snug line-clamp-2 pr-1.5 text-teal-900" x-text="sug"></span>
                                        <span class="shrink-0 w-4.5 h-4.5 rounded-lg bg-teal-600/10 group-hover:bg-teal-600 group-hover:text-white text-teal-700 flex items-center justify-center transition-colors">
                                            <svg class="w-3 h-3 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- User Circular Avatar (WhatsApp style) -->
                    <template x-if="msg.sender === 'user'">
                        <div class="chatbot-avatar is-user">
                            @if ($chatUserAvatarUrl)
                                <img src="{{ $chatUserAvatarUrl }}"
                                     alt="Profil Saya"
                                     onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                     class="w-full h-full object-cover object-top rounded-full">
                                <div style="display: none; font-size: 11px;" class="w-full h-full rounded-full bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white items-center justify-center font-bold">
                                    {{ $chatUserInitials }}
                                </div>
                            @else
                                <div style="font-size: 11px;" class="w-full h-full rounded-full bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white flex items-center justify-center font-bold">
                                    {{ $chatUserInitials }}
                                </div>
                            @endif
                        </div>
                    </template>
                </div>
            </template>

            <!-- Loading Indicator with Bot Avatar -->
            <div x-cloak x-show="loading" class="chatbot-msg-row is-bot" style="display: none;">
                <div class="chatbot-avatar is-bot">
                    <svg style="width: 17px; height: 17px;" viewBox="0 0 24 24" fill="#ffffff">
                        <rect x="10.8" y="1.8" width="2.4" height="3.4" rx="1.2" fill="#ffffff"/>
                        <rect x="2.5" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                        <rect x="19.3" y="9.2" width="2.2" height="5.6" rx="1.1" fill="#ffffff"/>
                        <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M8.5 5H15.5C17.6 5 18.5 5.9 18.5 8V14.8C18.5 16.9 17.6 17.8 15.5 17.8H8.5C6.4 17.8 5.5 16.9 5.5 14.8V8C5.5 5.9 6.4 5 8.5 5ZM8.8 9.8A1.25 1.25 0 108.8 12.3 1.25 1.25 0 108.8 9.8ZM15.2 9.8A1.25 1.25 0 1015.2 12.3 1.25 1.25 0 1015.2 9.8ZM8 14.3H9.6C9.85 14.3 10 14.5 10 14.75C10 15 9.85 15.2 9.6 15.2H8C7.75 15.2 7.55 15 7.55 14.75C7.55 14.5 7.75 14.3 8 14.3ZM11.2 14.3H12.8C13.05 14.3 13.25 14.5 13.25 14.75C13.25 15 13.05 15.2 12.8 15.2H11.2C10.95 15.2 10.75 15 10.75 14.75C10.75 14.5 10.95 14.3 11.2 14.3ZM14.4 14.3H16C16.25 14.3 16.45 14.5 16.45 14.75C16.45 15 16.25 15.2 16 15.2H14.4C14.15 15.2 13.95 15 13.95 14.75C13.95 14.5 14.15 14.3 14.4 14.3Z"/>
                    </svg>
                </div>
                <div class="chatbot-bubble chatbot-bubble-bot flex items-center gap-2" style="padding: 9px 14px;">
                    <div class="flex items-center gap-1 py-0.5">
                        <span class="w-1.5 h-1.5 bg-[#155E63] rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-1.5 h-1.5 bg-[#155E63] rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-1.5 h-1.5 bg-[#155E63] rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                    <span class="text-[11.5px] text-slate-500 font-medium">Asisten sedang mengetik...</span>
                </div>
            </div>

            <!-- Bottom Spacer to ensure last message is fully visible above toolbar -->
            <div style="height: 2px; flex-shrink: 0;"></div>
        </div>

        <!-- Quick Suggestions -->
        <div class="chatbot-quick-chips px-3 py-2 bg-white border-t border-slate-100 flex items-center gap-1.5 overflow-x-auto text-[11px] scrollbar-none shrink-0">
            <button @click="askPreset('Bagaimana cara absen masuk dan pulang?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Cara Absen</span>
            </button>
            <button @click="askPreset('Bagaimana cara absen dinas luar?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17a2 2 0 100-4 2 2 0 000 4zm8 0a2 2 0 100-4 2 2 0 000 4zM3 13l2-6h14l2 6M5 13h14v4H5v-4z"/></svg>
                <span>Dinas Luar</span>
            </button>
            <button @click="askPreset('Bagaimana cara isi resume pekerjaan tiket?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Resume Tiket</span>
            </button>
            <button @click="askPreset('Bagaimana cara mengajukan dana?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Pengajuan Dana</span>
            </button>
            <button @click="askPreset('Status pengajuan saya')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Status Pengajuan</span>
            </button>
            <button @click="askPreset('Apakah saya sudah absen hari ini?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Presensi Hari Ini</span>
            </button>
            <button @click="askPreset('Berapa sisa cuti saya?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Sisa Cuti</span>
            </button>
            <button @click="askPreset('Bagaimana cara membuat pengajuan lembur?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Klaim Lembur</span>
            </button>
            <button @click="askPreset('Bagaimana cara catat penanganan core dan joint closure?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Core &amp; JC</span>
            </button>
            <button @click="askPreset('Bagaimana cara membuat tugas operasional tasks?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
                <span>Tugas / Tasks</span>
            </button>
            <button @click="askPreset('Bagaimana cara melihat kalender absensi di dashboard?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Kalender Absen</span>
            </button>
            <button @click="askPreset('Bagaimana cara tukar shift jadwal?')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Tukar Shift</span>
            </button>
            <button @click="askPreset('Tiket aktif saya yang belum selesai')" type="button" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/60 rounded-full font-medium transition cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>Tiket Saya</span>
            </button>
        </div>

        <!-- Input Bar -->
        <form @submit.prevent="sendMessage()" class="chatbot-input-bar p-3 sm:p-3.5 bg-white border-t border-slate-200 flex items-center gap-2 shrink-0">
            <input type="text" 
                   id="chatbot-input-query"
                   x-ref="chatInput"
                   x-model="inputQuery" 
                   :placeholder="isListening ? '🎙️ Mendengarkan suara Anda...' : 'Ketik pertanyaan Anda...'" 
                   class="flex-1 min-w-0 bg-slate-50 rounded-xl px-3.5 sm:px-4 py-2.5 sm:py-2.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 transition"
                   :disabled="loading" />
            <button type="button" 
                    @click="toggleVoiceInput()"
                    :title="isListening ? 'Berhenti mendengarkan' : 'Bicara untuk mengetik (Voice-to-Text)'"
                    :class="isListening ? 'bg-rose-50 text-rose-600 border border-rose-300' : 'bg-slate-100 hover:bg-teal-50 text-slate-600 hover:text-[#0D9488]'"
                    class="h-10 w-10 rounded-xl flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0 relative">
                <svg class="w-5 h-5" :class="isListening ? 'text-rose-600 animate-pulse' : 'text-slate-600'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                </svg>
                <span x-show="isListening" class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            </button>
            <button type="submit" 
                    :disabled="loading || !inputQuery.trim()" 
                    class="h-10 px-4 sm:px-5 rounded-xl bg-[#155E63] hover:bg-[#18C4C4] disabled:opacity-50 text-white font-medium text-xs sm:text-sm flex items-center justify-center transition cursor-pointer shrink-0 active:scale-95">
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
                    parsed.forEach(m => {
                        m.isTyping = false;
                        // Bersihkan rekomendasi lama pada pesan yang sudah terjawab
                        if (m.sender === 'bot' && (m.action_url || (m.text && (m.text.includes('Data Infrastruktur') || m.text.includes('Detail Distribusi') || m.text.includes('Data Pelanggan'))))) {
                            m.suggestions = null;
                        }
                    });
                    initialMessages = parsed;
                }
            }
        } catch (e) {
            console.warn('Gagal membaca riwayat chat dari storage', e);
        }

        return {
            isOpen: false,
            loading: false,
            inputQuery: '',
            isListening: false,
            speechRecognizer: null,
            activeTypingInterval: null,
            messages: initialMessages,
            init() {
                this.messages.forEach(m => {
                    m.isTyping = false;
                    if (m.sender === 'bot' && (m.action_url || (m.text && (m.text.includes('Data Infrastruktur') || m.text.includes('Detail Distribusi') || m.text.includes('Data Pelanggan'))))) {
                        m.suggestions = null;
                    }
                });
                try {
                    localStorage.setItem(storageKeyMessages, JSON.stringify(this.messages));
                } catch(e) {}
                this.$nextTick(() => {
                    const modal = document.querySelector('.chatbot-modal-box');
                    if (modal && modal.scrollTop !== 0) modal.scrollTop = 0;
                    if (this.isOpen) {
                        this.scrollToBottom();
                    }
                });
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
                    setTimeout(() => {
                        const modal = document.querySelector('.chatbot-modal-box');
                        if (modal && modal.scrollTop !== 0) modal.scrollTop = 0;
                        this.$refs.chatInput?.focus({ preventScroll: true });
                    }, 350);
                }
            },
            clearActiveTyping() {
                if (this.activeTypingInterval) {
                    clearInterval(this.activeTypingInterval);
                    this.activeTypingInterval = null;
                }
                this.messages.forEach(m => {
                    if (m.isTyping) {
                        m.isTyping = false;
                    }
                });
            },
            typeMessage(fullText, msgIndex, onComplete) {
                this.clearActiveTyping();
                if (typeof msgIndex !== 'number' || !this.messages[msgIndex]) return;

                const textToType = typeof fullText === 'string' ? fullText : String(fullText || '');
                const totalLength = textToType.length;

                this.messages[msgIndex].isTyping = true;
                this.messages[msgIndex].text = '';

                if (totalLength === 0) {
                    this.messages[msgIndex].isTyping = false;
                    return;
                }

                let chunkSize = 1;
                let delay = 18;

                if (totalLength > 500) {
                    chunkSize = Math.max(4, Math.ceil(totalLength / 80));
                    delay = 10;
                } else if (totalLength > 200) {
                    chunkSize = 3;
                    delay = 14;
                } else if (totalLength > 80) {
                    chunkSize = 2;
                    delay = 16;
                } else {
                    chunkSize = 1;
                    delay = 20;
                }

                let currentIndex = 0;
                const box = document.getElementById('chatbot-messages');

                this.activeTypingInterval = setInterval(() => {
                    if (!this.messages[msgIndex]) {
                        this.clearActiveTyping();
                        return;
                    }

                    currentIndex = Math.min(currentIndex + chunkSize, totalLength);
                    this.messages[msgIndex].text = textToType.slice(0, currentIndex);

                    if (box) {
                        box.scrollTop = box.scrollHeight;
                    }
                    const modal = document.querySelector('.chatbot-modal-box');
                    if (modal && modal.scrollTop !== 0) modal.scrollTop = 0;

                    if (currentIndex >= totalLength) {
                        clearInterval(this.activeTypingInterval);
                        this.activeTypingInterval = null;
                        this.messages[msgIndex].isTyping = false;
                        this.messages[msgIndex].text = textToType;
                        this.saveMessages();
                        this.scrollToBottom();
                        if (onComplete) onComplete();
                    }
                }, delay);
            },
            closeChat() {
                this.isOpen = false;
                try {
                    localStorage.setItem(storageKeyOpen, '0');
                } catch (e) {}
            },
            clearChat() {
                this.clearActiveTyping();
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

                this.clearActiveTyping();
                this.messages.push({ sender: 'user', text: text, isTyping: false });
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
                    this.loading = false;

                    if (resJson && resJson.data && resJson.data.response) {
                        this.messages.push({
                            sender: 'bot',
                            text: '',
                            isTyping: true,
                            action_url: resJson.data.action_url || null,
                            action_label: resJson.data.action_label || null,
                            suggestions: (resJson.data.suggestions && Array.isArray(resJson.data.suggestions) && resJson.data.suggestions.length > 0)
                                ? resJson.data.suggestions.slice(0, 2)
                                : null
                        });
                        const newMsgIndex = this.messages.length - 1;
                        this.typeMessage(resJson.data.response, newMsgIndex);
                    } else {
                        this.messages.push({ sender: 'bot', text: '', isTyping: true });
                        const newMsgIndex = this.messages.length - 1;
                        this.typeMessage('Maaf, terjadi kendala saat memproses balasan.', newMsgIndex);
                    }
                } catch (e) {
                    this.loading = false;
                    this.messages.push({ sender: 'bot', text: '', isTyping: true });
                    const newMsgIndex = this.messages.length - 1;
                    this.typeMessage('Maaf, terjadi kesalahan koneksi jaringan.', newMsgIndex);
                }
            },
            scrollToBottom() {
                this.$nextTick(() => {
                    const modal = document.querySelector('.chatbot-modal-box');
                    if (modal && modal.scrollTop !== 0) modal.scrollTop = 0;
                    const box = document.getElementById('chatbot-messages');
                    if (box) box.scrollTop = box.scrollHeight;
                });
            }
        };
    }
</script>
