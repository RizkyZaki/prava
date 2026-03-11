<x-filament-panels::page :full-height="true">
    <style>
        /*
         * Fullscreen WhatsApp Dashboard
         * Override every Filament wrapper layer to fill the viewport
         * while keeping the Filament sidebar untouched.
         * Topbar = 4rem, so usable height = 100dvh - 4rem
         */

        /* 1. The <main> tag: kill padding, max-width, make it a flex column filling height */
        .fi-main {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            height: calc(100dvh - 4rem) !important;
            overflow: hidden !important;
        }

        /* 2. .fi-page (has .fi-height-full class) */
        .fi-page.fi-height-full {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            height: 100% !important;
            overflow: hidden !important;
        }

        /* 3. .fi-page-header-main-ctn: remove py-8 and gap-y-8 */
        .fi-page-header-main-ctn {
            padding: 0 !important;
            padding-block: 0 !important;
            padding-top: -100px !important;
            padding-bottom: 0 !important;
            row-gap: 0 !important;
            gap: 0 !important;
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        /* 4. Hide the header (title + breadcrumbs) */
        .fi-page-header-main-ctn > .fi-header {
            display: none !important;
        }

        /* 5. .fi-page-main */
        .fi-page-main {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            gap: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        /* 6. .fi-page-content: change from grid to flex */
        .fi-page-content {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 !important;
            min-height: 0 !important;
            gap: 0 !important;
            row-gap: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        .wa-sidebar::-webkit-scrollbar, .wa-messages::-webkit-scrollbar { width: 6px; }
        .wa-sidebar::-webkit-scrollbar-thumb, .wa-messages::-webkit-scrollbar-thumb { background: #b5b5b5; border-radius: 10px; }
        .wa-sidebar::-webkit-scrollbar-track, .wa-messages::-webkit-scrollbar-track { background: transparent; }
        .wa-chat-pattern {
            background-color: #efeae2;
            background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23d6cfc4' fill-opacity='0.25'%3E%3Ccircle cx='10' cy='10' r='2'/%3E%3Ccircle cx='50' cy='30' r='1.5'/%3E%3Ccircle cx='30' cy='60' r='2'/%3E%3Ccircle cx='70' cy='70' r='1'/%3E%3Ccircle cx='60' cy='10' r='1.5'/%3E%3Ccircle cx='20' cy='40' r='1'/%3E%3Cpath d='M5 55l3-3 3 3M65 45l2-2 2 2' stroke='%23d6cfc4' stroke-opacity='0.3' fill='none' stroke-width='0.8'/%3E%3C/g%3E%3C/svg%3E");
        }
        .dark .wa-chat-pattern {
            background-color: #0b141a;
            background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23182229' fill-opacity='0.6'%3E%3Ccircle cx='10' cy='10' r='2'/%3E%3Ccircle cx='50' cy='30' r='1.5'/%3E%3Ccircle cx='30' cy='60' r='2'/%3E%3Ccircle cx='70' cy='70' r='1'/%3E%3Ccircle cx='60' cy='10' r='1.5'/%3E%3Ccircle cx='20' cy='40' r='1'/%3E%3Cpath d='M5 55l3-3 3 3M65 45l2-2 2 2' stroke='%23182229' stroke-opacity='0.5' fill='none' stroke-width='0.8'/%3E%3C/g%3E%3C/svg%3E");
        }
        .wa-bubble-out {
            background-color: #d9fdd3;
            position: relative;
        }
        .wa-bubble-out::before {
            content: '';
            position: absolute;
            top: 0;
            right: -8px;
            width: 0;
            height: 0;
            border-left: 8px solid #d9fdd3;
            border-top: 8px solid transparent;
        }
        .wa-bubble-in {
            background-color: #ffffff;
            position: relative;
        }
        .wa-bubble-in::before {
            content: '';
            position: absolute;
            top: 0;
            left: -8px;
            width: 0;
            height: 0;
            border-right: 8px solid #ffffff;
            border-top: 8px solid transparent;
        }
        .dark .wa-bubble-out {
            background-color: #005c4b;
        }
        .dark .wa-bubble-out::before {
            border-left-color: #005c4b;
        }
        .dark .wa-bubble-in {
            background-color: #202c33;
        }
        .dark .wa-bubble-in::before {
            border-right-color: #202c33;
        }
        .wa-system-msg {
            background-color: #ffecd2;
            box-shadow: 0 1px 1px rgba(0,0,0,0.08);
        }
        .dark .wa-system-msg {
            background-color: #3a2e1a;
        }
        .wa-conv-item:hover { background-color: #f5f6f6; }
        .dark .wa-conv-item:hover { background-color: #202c33; }
        .wa-conv-active { background-color: #f0f2f5; }
        .dark .wa-conv-active { background-color: #2a3942; }
        .wa-topbar { background-color: #008069; }
        .dark .wa-topbar { background-color: #202c33; }
        .wa-header { background-color: #f0f2f5; }
        .dark .wa-header { background-color: #202c33; }
        .wa-input-bar { background-color: #f0f2f5; }
        .dark .wa-input-bar { background-color: #202c33; }
        .wa-search { background-color: #f0f2f5; }
        .dark .wa-search { background-color: #202c33; }

        @keyframes wa-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .wa-unread-badge {
            animation: wa-pulse 2s ease-in-out infinite;
        }
    </style>

    @php
        $avatarColors = [
            'A' => 'bg-blue-500', 'B' => 'bg-purple-500', 'C' => 'bg-pink-500', 'D' => 'bg-amber-500',
            'E' => 'bg-cyan-500', 'F' => 'bg-rose-500', 'G' => 'bg-indigo-500', 'H' => 'bg-teal-500',
            'I' => 'bg-emerald-500', 'J' => 'bg-orange-500', 'K' => 'bg-violet-500', 'L' => 'bg-lime-600',
            'M' => 'bg-sky-500', 'N' => 'bg-fuchsia-500', 'O' => 'bg-red-500', 'P' => 'bg-green-600',
            'Q' => 'bg-yellow-600', 'R' => 'bg-blue-600', 'S' => 'bg-purple-600', 'T' => 'bg-pink-600',
            'U' => 'bg-teal-600', 'V' => 'bg-indigo-600', 'W' => 'bg-amber-600', 'X' => 'bg-cyan-600',
            'Y' => 'bg-rose-600', 'Z' => 'bg-emerald-600',
        ];
    @endphp

    <div
        class="flex flex-1 min-h-0 overflow-hidden"
        wire:poll.5s="poll"
    >
        {{-- ===== LEFT PANEL: Conversations ===== --}}
        <div class="w-[380px] shrink-0 flex flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-[#111b21]">

            {{-- Green top bar --}}
            <div class="wa-topbar px-5 py-3 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-white">WhatsApp CS</h3>
                    <p class="text-[11px] text-green-100 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-green-300 rounded-full inline-block"></span>
                        Online
                    </p>
                </div>
                <span class="text-[10px] text-green-100 bg-white/15 px-2 py-0.5 rounded-full">
                    {{ $conversations->count() }} chat
                </span>
            </div>

            {{-- Search / Filter --}}
            <div class="px-3 py-2 bg-white dark:bg-[#111b21] border-b border-gray-100 dark:border-gray-700/50">
                <div class="wa-search flex items-center gap-2 rounded-lg px-3 py-1.5 dark:bg-[#202c33]">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 shrink-0" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari percakapan..."
                        class="flex-1 bg-transparent text-xs text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none"
                    >
                </div>
            </div>

            {{-- Conversations list --}}
            <div class="flex-1 overflow-y-auto wa-sidebar">
                @forelse ($conversations as $conv)
                    @php
                        $lastMsg = $conv->messages->last();
                        $initial = strtoupper(substr($conv->customer_name ?? 'U', 0, 1));
                        $avatarColor = $avatarColors[$initial] ?? 'bg-gray-400';
                    @endphp
                    <button
                        wire:key="conv-{{ $conv->id }}"
                        wire:click="selectConversation({{ $conv->id }})"
                        class="wa-conv-item w-full px-3 py-3 flex items-center gap-3 transition-colors
                            {{ $selectedConversationId === $conv->id ? 'wa-conv-active' : '' }}"
                    >
                        {{-- Avatar --}}
                        <div class="w-12 h-12 rounded-full {{ $avatarColor }} flex items-center justify-center text-white font-bold text-base shrink-0 shadow-sm">
                            {{ $initial }}
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 text-left min-w-0 border-b border-gray-100 dark:border-gray-700/30 pb-3">
                            <div class="flex items-center justify-between mb-0.5">
                                <h4 class="text-[15px] font-normal text-gray-900 dark:text-gray-100 truncate">
                                    {{ e($conv->customer_name ?? 'User WhatsApp') }}
                                </h4>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 shrink-0 ml-2">
                                    {{ $conv->last_message_at ? $conv->last_message_at->format('H:i') : '' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-[13px] text-gray-500 dark:text-gray-400 truncate pr-2">
                                    @if ($lastMsg)
                                        @if ($lastMsg->sender_type !== 'customer')
                                            <x-heroicon-s-check class="w-3.5 h-3.5 inline text-blue-400 -mt-0.5" />
                                        @endif
                                        {{ Str::limit(e($lastMsg->body), 40) }}
                                    @else
                                        {{ $conv->phone }}
                                    @endif
                                </p>
                                <div class="flex items-center gap-1 shrink-0">
                                    @if ($conv->mode === 'admin')
                                        <span class="text-[9px] bg-orange-500 text-white px-1.5 py-0.5 rounded-full font-bold wa-unread-badge">
                                            ADMIN
                                        </span>
                                    @elseif ($conv->mode === 'ai')
                                        <span class="text-[9px] bg-indigo-500 text-white px-1.5 py-0.5 rounded-full font-bold">
                                            AI
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="p-10 text-center">
                        <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800/50 mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                        </div>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada antrean admin</p>
                        <p class="text-xs text-gray-300 dark:text-gray-600 mt-1">Chat baru akan muncul di sini</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===== RIGHT PANEL: Chat Window ===== --}}
        <div class="flex-1 flex flex-col bg-[#efeae2] dark:bg-[#0b141a]">

            @if ($selectedConversationId && $this->selectedConversation)
            <div wire:key="chat-{{ $selectedConversationId }}" class="flex flex-col flex-1 min-h-0">
                {{-- Chat header --}}
                <div class="wa-header px-4 py-2.5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @php
                            $selInitial = strtoupper(substr($this->selectedConversation->customer_name ?? 'U', 0, 1));
                            $selColor = $avatarColors[$selInitial] ?? 'bg-gray-400';
                        @endphp
                        <div class="w-10 h-10 rounded-full {{ $selColor }} flex items-center justify-center text-white font-bold shadow-sm">
                            {{ $selInitial }}
                        </div>
                        <div>
                            <h3 class="font-medium text-[15px] text-gray-900 dark:text-gray-100 leading-tight">
                                {{ e($this->selectedConversation->customer_name ?? $this->selectedConversation->phone) }}
                            </h3>
                            <p class="text-[12px] text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                {{ $this->selectedConversation->phone }}
                                <span class="mx-1 text-gray-300">|</span>
                                @if ($this->selectedConversation->mode === 'admin')
                                    <span class="text-orange-500 font-medium">Mode Admin</span>
                                @elseif ($this->selectedConversation->mode === 'ai')
                                    <span class="text-indigo-500 font-medium">Mode AI</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            wire:click="switchToAi"
                            wire:confirm="Aktifkan kembali mode AI untuk {{ e($this->selectedConversation->customer_name ?? $this->selectedConversation->phone) }}?"
                            class="text-xs bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5"
                        >
                            <x-heroicon-s-cpu-chip class="w-3.5 h-3.5" />
                            AI Mode
                        </button>
                        <button
                            wire:click="endChat"
                            wire:confirm="Akhiri sesi chat dengan {{ e($this->selectedConversation->customer_name ?? $this->selectedConversation->phone) }}?"
                            class="text-xs bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5"
                        >
                            <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                            Akhiri
                        </button>
                    </div>
                </div>

                {{-- Messages --}}
                <div
                    id="chatContainer"
                    class="wa-chat-pattern wa-messages flex-1 px-[10%] py-4 overflow-y-auto flex flex-col gap-2"
                    x-data
                    x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                    @scroll-to-bottom.window="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                >
                    {{-- Date separator --}}
                    @if ($messages->isNotEmpty())
                        <div class="flex justify-center mb-2">
                            <span class="wa-system-msg dark:text-gray-200 text-[11px] px-3 py-1 rounded-md text-gray-600 font-medium shadow-sm">
                                {{ $messages->first()->created_at->translatedFormat('d F Y') }}
                            </span>
                        </div>
                    @endif

                    @forelse ($messages as $msg)
                        @php
                            $isCustomer = $msg->sender_type === 'customer';
                            $isSystem = $msg->sender_type === 'system';
                        @endphp

                        @if ($isSystem)
                            <div wire:key="msg-{{ $msg->id }}" class="flex justify-center my-1">
                                <div class="wa-system-msg dark:text-amber-200 text-[12px] px-3 py-1 rounded-md text-amber-800">
                                    {{ e($msg->body) }}
                                </div>
                            </div>
                        @else
                            <div wire:key="msg-{{ $msg->id }}" class="flex {{ $isCustomer ? 'justify-start' : 'justify-end' }}">
                                <div class="max-w-[65%] min-w-[120px] px-2.5 pt-1.5 pb-1 rounded-lg shadow-sm relative
                                    {{ $isCustomer ? 'wa-bubble-in' : 'wa-bubble-out' }}"
                                >
                                    @if (! $isCustomer && $msg->sender_type !== 'customer')
                                        <p class="text-[11px] font-semibold mb-0.5 leading-tight
                                            {{ $msg->sender_type === 'ai' ? 'text-indigo-600 dark:text-indigo-300' : 'text-[#008069] dark:text-green-400' }}">
                                            {{ $msg->sender_type === 'ai' ? 'ðŸ¤– AI Assistant' : 'ðŸ‘¨â€ðŸ’» Admin' }}
                                        </p>
                                    @endif
                                    <p class="text-[14.2px] text-gray-900 dark:text-gray-100 whitespace-pre-wrap wrap-break-word leading-snug pr-12">{{ e($msg->body) }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 text-right -mt-3 float-right ml-2 mb-0.5">
                                        {{ $msg->created_at->format('H:i') }}
                                        @if (! $isCustomer)
                                            <x-heroicon-s-check class="w-3.5 h-3.5 inline text-blue-400 -mt-0.5 ml-0.5" />
                                        @endif
                                    </p>
                                    <div class="clear-both"></div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="m-auto text-center">
                            <div class="wa-system-msg dark:text-gray-300 text-[12px] px-4 py-2 rounded-md text-gray-600 inline-block">
                                Belum ada riwayat pesan
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Reply input bar --}}
                <div class="wa-input-bar px-4 py-2.5 flex items-center gap-2">
                    <div class="flex-1 flex items-center bg-white dark:bg-[#2a3942] rounded-lg shadow-sm px-4 py-2"
                         x-data
                         @keydown.enter.prevent="$wire.sendReply()"
                    >
                        <x-heroicon-o-face-smile class="w-6 h-6 text-gray-400 dark:text-gray-500 shrink-0 mr-3 cursor-pointer hover:text-gray-500" />
                        <input
                            type="text"
                            wire:model="replyMessage"
                            placeholder="Ketik pesan..."
                            class="flex-1 bg-transparent text-[15px] text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:outline-none"
                        >
                    </div>
                    <button
                        wire:click="sendReply"
                        wire:loading.attr="disabled"
                        class="w-11 h-11 rounded-full bg-[#008069] hover:bg-[#017561] text-white flex items-center justify-center transition shrink-0 shadow-sm disabled:opacity-50"
                    >
                        <x-heroicon-s-paper-airplane class="w-5 h-5 rotate-0" />
                    </button>
                </div>
            </div>
            @else
                {{-- Welcome screen --}}
                <div class="flex-1 flex items-center justify-center bg-[#f0f2f5] dark:bg-[#222e35]">
                    <div class="text-center max-w-lg px-6">
                        {{-- WhatsApp logo --}}
                        <div class="mx-auto mb-8 w-[220px] h-[220px] relative flex items-center justify-center">
                            <div class="absolute inset-0 rounded-full bg-linear-to-b from-[#25d366]/10 to-transparent"></div>
                            <svg class="w-44 h-44 text-[#25d366]/30 dark:text-[#25d366]/15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </div>

                        <h2 class="text-[28px] font-light text-gray-700 dark:text-gray-200 mb-3 tracking-tight">WhatsApp Customer Service</h2>
                        <p class="text-[15px] text-gray-500 dark:text-gray-400 leading-relaxed">
                            Kirim dan terima pesan WhatsApp langsung dari Prava ERP.
                        </p>
                        <p class="text-[14px] text-gray-400 dark:text-gray-500 mt-1">
                            Pilih percakapan di panel kiri untuk mulai membalas.
                        </p>

                        <div class="mt-8 pt-5 border-t border-gray-200/60 dark:border-gray-700/40">
                            <p class="text-[12px] text-gray-400 dark:text-gray-500 flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                End-to-end encrypted via Meta WhatsApp Cloud API
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
