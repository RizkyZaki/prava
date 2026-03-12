<x-filament-panels::page>
    <style>
        .fi-main{padding:0!important;margin:0!important;max-width:100%!important;width:100%!important;display:flex!important;flex-direction:column!important;height:calc(100dvh - 4rem)!important;overflow:hidden!important}
        .fi-page{flex:1!important;display:flex!important;flex-direction:column!important;min-height:0!important;height:100%!important;overflow:hidden!important}
        .fi-page-header-main-ctn{padding:0!important;padding-block:0!important;row-gap:0!important;gap:0!important;flex:1!important;display:flex!important;flex-direction:column!important;min-height:0!important;overflow:hidden!important}
        .fi-page-header-main-ctn>.fi-header{display:none!important}
        .fi-page-main{flex:1!important;display:flex!important;flex-direction:column!important;min-height:0!important;gap:0!important;padding:0!important;overflow:hidden!important}
        .fi-page-content{display:flex!important;flex-direction:column!important;flex:1!important;min-height:0!important;gap:0!important;row-gap:0!important;padding:0!important;overflow:hidden!important}

        .wa-scroll::-webkit-scrollbar{width:6px}
        .wa-scroll::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15);border-radius:10px}
        .wa-scroll::-webkit-scrollbar-track{background:transparent}
        .dark .wa-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1)}

        .wa-chat-bg{
            background-color:#eae6df;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cdefs%3E%3Cstyle%3E.a%7Bfill:%23cdc4b7;fill-opacity:.4%7D%3C/style%3E%3C/defs%3E%3Ccircle class='a' cx='20' cy='10' r='1.5'/%3E%3Ccircle class='a' cx='80' cy='70' r='1'/%3E%3Ccircle class='a' cx='150' cy='20' r='1.5'/%3E%3Ccircle class='a' cx='30' cy='150' r='2'/%3E%3Ccircle class='a' cx='170' cy='160' r='1.5'/%3E%3Ccircle class='a' cx='10' cy='80' r='.8'/%3E%3Ccircle class='a' cx='190' cy='90' r='1.2'/%3E%3Cpath class='a' d='M100 90l4-6h-8z'/%3E%3Cpath class='a' d='M130 130a3 3 0 1 0 6 0 3 3 0 1 0-6 0z'/%3E%3Cpath class='a' d='M50 40l-3-3h-2l3 3-3 3h2z'/%3E%3C/svg%3E");
        }
        .dark .wa-chat-bg{
            background-color:#0b141a;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cdefs%3E%3Cstyle%3E.a%7Bfill:%23172026;fill-opacity:.6%7D%3C/style%3E%3C/defs%3E%3Ccircle class='a' cx='20' cy='10' r='1.5'/%3E%3Ccircle class='a' cx='80' cy='70' r='1'/%3E%3Ccircle class='a' cx='150' cy='20' r='1.5'/%3E%3Ccircle class='a' cx='30' cy='150' r='2'/%3E%3Ccircle class='a' cx='170' cy='160' r='1.5'/%3E%3Ccircle class='a' cx='10' cy='80' r='.8'/%3E%3Ccircle class='a' cx='190' cy='90' r='1.2'/%3E%3Cpath class='a' d='M100 90l4-6h-8z'/%3E%3Cpath class='a' d='M130 130a3 3 0 1 0 6 0 3 3 0 1 0-6 0z'/%3E%3Cpath class='a' d='M50 40l-3-3h-2l3 3-3 3h2z'/%3E%3C/svg%3E");
        }

        .wa-bubble-out{background:#d9fdd3;border-radius:8px;box-shadow:0 1px 1px rgba(0,0,0,.06)}
        .wa-bubble-in{background:#ffffff;border-radius:8px;box-shadow:0 1px 1px rgba(0,0,0,.06)}
        .dark .wa-bubble-out{background:#005c4b}
        .dark .wa-bubble-in{background:#202c33}

        .wa-date-pill{background:#e1f3fb;color:#54656f;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:500;box-shadow:0 1px 1px rgba(0,0,0,.06)}
        .dark .wa-date-pill{background:#182229;color:#8696a0}
        .wa-system-pill{background:#ffecd2;color:#54656f;padding:5px 12px;border-radius:8px;font-size:12px;box-shadow:0 1px 1px rgba(0,0,0,.06)}
        .dark .wa-system-pill{background:#332f1c;color:#e9edef}

        .wa-conv-item{transition:background .15s}
        .wa-conv-item:hover{background:#f5f6f6}
        .dark .wa-conv-item:hover{background:#202c33}
        .wa-conv-active{background:#f0f2f5!important}
        .dark .wa-conv-active{background:#2a3942!important}

        .wa-topbar{background:#008069}
        .dark .wa-topbar{background:#202c33;border-bottom:1px solid #313d45}
        .wa-header-bar{background:#f0f2f5}
        .dark .wa-header-bar{background:#202c33}
        .wa-input-area{background:#f0f2f5}
        .dark .wa-input-area{background:#202c33}

        @keyframes wa-fadein{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}
        .wa-msg-anim{animation:wa-fadein .15s ease-out}
        @keyframes wa-pulse{0%,100%{opacity:1}50%{opacity:.4}}
        .wa-badge-pulse{animation:wa-pulse 2s ease-in-out infinite}
    </style>

    @php
        $avatarColors = [
            'A'=>'bg-[#00a884]','B'=>'bg-[#7c5ce3]','C'=>'bg-[#e36eae]','D'=>'bg-[#d4a53c]',
            'E'=>'bg-[#00b8d4]','F'=>'bg-[#e36b5c]','G'=>'bg-[#5b72e3]','H'=>'bg-[#0097a7]',
            'I'=>'bg-[#43a047]','J'=>'bg-[#e65100]','K'=>'bg-[#8e24aa]','L'=>'bg-[#558b2f]',
            'M'=>'bg-[#0288d1]','N'=>'bg-[#d81b60]','O'=>'bg-[#e53935]','P'=>'bg-[#2e7d32]',
            'Q'=>'bg-[#f9a825]','R'=>'bg-[#1565c0]','S'=>'bg-[#6a1b9a]','T'=>'bg-[#ad1457]',
            'U'=>'bg-[#00695c]','V'=>'bg-[#283593]','W'=>'bg-[#e65100]','X'=>'bg-[#00838f]',
            'Y'=>'bg-[#c62828]','Z'=>'bg-[#1b5e20]',
        ];
        $conv0 = $this->selectedConversation;
        $convInitial = strtoupper(substr($conv0?->customer_name ?? 'U', 0, 1));
        $convColor = $avatarColors[$convInitial] ?? 'bg-[#6a7175]';
    @endphp

    <div class="flex flex-1 min-h-0 overflow-hidden">

        {{-- ===== LEFT SIDEBAR ===== --}}
        <div class="w-[340px] lg:w-[380px] shrink-0 flex flex-col border-r border-gray-200 dark:border-[#313d45] bg-white dark:bg-[#111b21]">

            {{-- Top bar --}}
            <div class="wa-topbar px-4 py-3 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-[15px] font-semibold text-white">WhatsApp Prava</h3>
                    <p class="text-[11px] text-white/70 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-[#25d366] rounded-full inline-block"></span>
                        Online
                    </p>
                </div>
            </div>

            {{-- Search --}}
            <div class="px-2.5 py-1.5 bg-white dark:bg-[#111b21]">
                <div class="flex items-center gap-3 rounded-lg bg-[#f0f2f5] dark:bg-[#202c33] px-3 py-[7px]">
                    <svg class="w-4 h-4 text-[#54656f] dark:text-[#8696a0] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari atau mulai chat baru"
                        class="flex-1 bg-transparent text-[13px] text-[#111b21] dark:text-[#d1d7db] placeholder-[#667781] dark:placeholder-[#8696a0] outline-none border-none focus:ring-0"
                    >
                </div>
            </div>

            {{-- Conversations list --}}
            <div class="flex-1 overflow-y-auto wa-scroll">
                @forelse ($this->conversations as $conv)
                    @php
                        $lastMsg = $conv->messages->last();
                        $initial = strtoupper(substr($conv->customer_name ?? 'U', 0, 1));
                        $avatarBg = $avatarColors[$initial] ?? 'bg-[#6a7175]';
                        $isActive = $selectedConversationId === $conv->id;
                    @endphp
                    <button
                        wire:key="conv-{{ $conv->id }}"
                        wire:click="selectConversation({{ $conv->id }})"
                        type="button"
                        class="wa-conv-item w-full flex items-center gap-3 px-3 py-[10px] text-left {{ $isActive ? 'wa-conv-active' : '' }}"
                    >
                        <div class="w-[49px] h-[49px] rounded-full {{ $avatarBg }} flex items-center justify-center text-white font-semibold text-lg shrink-0">
                            {{ $initial }}
                        </div>
                        <div class="flex-1 min-w-0 border-b border-[#e9edef] dark:border-[#222d34] pb-[10px]">
                            <div class="flex items-center justify-between mb-px">
                                <span class="text-[16px] text-[#111b21] dark:text-[#e9edef] truncate font-normal">
                                    {{ e($conv->customer_name ?? 'User WhatsApp') }}
                                </span>
                                <span class="text-[12px] text-[#667781] dark:text-[#8696a0] shrink-0 ml-2">
                                    {{ $conv->last_message_at ? $conv->last_message_at->format('H:i') : '' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-[13px] text-[#667781] dark:text-[#8696a0] truncate pr-2">
                                    @if ($lastMsg)
                                        @if ($lastMsg->sender_type !== 'customer')
                                            <svg class="w-4 h-4 inline -mt-0.5 text-[#53bdeb]" viewBox="0 0 16 15" fill="currentColor"><path d="M15.01 3.316l-.478-.372a.365.365 0 00-.51.063L8.666 9.88a.32.32 0 01-.484.032l-.358-.325a.32.32 0 00-.484.032l-.378.48a.418.418 0 00.036.54l1.32 1.267c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 00-.064-.512zm-4.1 0l-.478-.372a.365.365 0 00-.51.063L4.566 9.88a.32.32 0 01-.484.032L1.892 7.78a.366.366 0 00-.516.005l-.423.433a.364.364 0 00.006.514l3.255 3.185a.32.32 0 00.484-.033l6.272-8.048a.365.365 0 00-.063-.512z"/></svg>
                                        @endif
                                        @if ($lastMsg->media_type)
                                            <svg class="w-3.5 h-3.5 inline -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5"/></svg>
                                        @endif
                                        {{ Str::limit(e($lastMsg->media_type && $lastMsg->body === "[{$lastMsg->media_type}]" ? ucfirst($lastMsg->media_type) : $lastMsg->body), 35) }}
                                    @else
                                        {{ $conv->phone }}
                                    @endif
                                </p>
                                @if ($conv->mode === 'admin')
                                    <span class="text-[10px] bg-[#25d366] text-white px-[6px] py-[1px] rounded-full font-semibold shrink-0 wa-badge-pulse">ADMIN</span>
                                @elseif ($conv->mode === 'ai')
                                    <span class="text-[10px] bg-[#7c5ce3] text-white px-[6px] py-[1px] rounded-full font-semibold shrink-0">AI</span>
                                @elseif ($conv->mode === 'selection')
                                    <span class="text-[10px] bg-[#667781] text-white px-[6px] py-[1px] rounded-full font-semibold shrink-0">BARU</span>
                                @endif
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="flex flex-col items-center justify-center h-full px-8 text-center">
                        <div class="w-20 h-20 rounded-full bg-[#f0f2f5] dark:bg-[#202c33] flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-[#c4ccd0] dark:text-[#3b4a54]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                        </div>
                        <p class="text-[#667781] dark:text-[#8696a0] text-sm">Belum ada percakapan aktif</p>
                        <p class="text-[#8696a0] dark:text-[#667781] text-xs mt-1">Chat baru akan muncul di sini</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===== RIGHT PANEL ===== --}}
        <div class="flex-1 flex flex-col bg-[#eae6df] dark:bg-[#0b141a] min-w-0">

            @if (! $selectedConversationId)
            {{-- Welcome screen --}}
            <div wire:key="wa-welcome" class="flex-1 flex flex-col items-center justify-center bg-[#f0f2f5] dark:bg-[#222e35]">
                <div class="text-center max-w-[480px] px-6">
                    <div class="mx-auto mb-8 relative w-[200px] h-[200px] flex items-center justify-center">
                        <div class="absolute w-[200px] h-[200px] rounded-full border border-[#25d366]/10"></div>
                        <div class="absolute w-[150px] h-[150px] rounded-full border border-[#25d366]/15"></div>
                        <div class="absolute w-[100px] h-[100px] rounded-full bg-[#25d366]/5"></div>
                        <svg class="w-16 h-16 text-[#25d366]/50" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </div>
                    <h2 class="text-[28px] font-light text-[#41525d] dark:text-[#e9edef]">Prava WhatsApp</h2>
                    <p class="text-[14px] text-[#667781] dark:text-[#8696a0] mt-3 leading-relaxed">
                        Kirim dan terima pesan WhatsApp langsung dari Prava ERP.<br>
                        Pilih percakapan di panel kiri untuk mulai.
                    </p>
                    <div class="mt-8 flex items-center justify-center gap-1.5 text-[12px] text-[#8696a0]">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        End-to-end encrypted via Meta Cloud API
                    </div>
                </div>
            </div>
            @else
            {{-- Chat view --}}
            <div wire:key="wa-chat-{{ $selectedConversationId }}" class="flex flex-col flex-1 min-h-0">

                {{-- Chat header --}}
                <div class="wa-header-bar px-4 py-2.5 border-b border-[#d1d7db] dark:border-[#313d45] flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full {{ $convColor }} flex items-center justify-center text-white font-semibold shrink-0">
                            {{ $convInitial }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[15px] text-[#111b21] dark:text-[#e9edef] truncate font-normal leading-tight">
                                {{ e($conv0?->customer_name ?? $conv0?->phone ?? '') }}
                            </h3>
                            <p class="text-[12px] text-[#667781] dark:text-[#8696a0] flex items-center gap-1 mt-px">
                                <span>{{ $conv0?->phone ?? '' }}</span>
                                @if ($conv0)
                                    <span>&middot;</span>
                                    @if ($conv0->mode === 'admin')
                                        <span class="text-[#e65100] font-medium">Admin</span>
                                    @elseif ($conv0->mode === 'ai')
                                        <span class="text-[#7c5ce3] font-medium">AI Aktif</span>
                                    @else
                                        <span>Menunggu</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button
                            wire:click="switchToAi"
                            wire:confirm="Alihkan ke mode AI?"
                            type="button"
                            class="text-[12px] font-medium text-white bg-[#7c5ce3] hover:bg-[#6a4fd4] px-3 py-1.5 rounded-lg transition-colors"
                        >AI Mode</button>
                        <button
                            wire:click="endChat"
                            wire:confirm="Akhiri percakapan ini?"
                            type="button"
                            class="text-[12px] font-medium text-white bg-[#ea4335] hover:bg-[#d33426] px-3 py-1.5 rounded-lg transition-colors"
                        >Akhiri</button>
                    </div>
                </div>

                {{-- Messages --}}
                <div
                    id="chatContainer"
                    class="wa-chat-bg wa-scroll flex-1 overflow-y-auto"
                >
                    <div class="max-w-[800px] w-full mx-auto px-4 sm:px-8 py-4 flex flex-col gap-[2px]">

                        @if ($this->messages->isNotEmpty())
                            <div class="flex justify-center my-2">
                                <span class="wa-date-pill">{{ $this->messages->first()->created_at->translatedFormat('d F Y') }}</span>
                            </div>
                        @endif

                        @foreach ($this->messages as $msg)
                            @php
                                $isCustomer = $msg->sender_type === 'customer';
                                $isSystem = $msg->sender_type === 'system';
                            @endphp

                            @if ($isSystem)
                                <div wire:key="msg-{{ $msg->id }}" class="flex justify-center my-1.5 wa-msg-anim">
                                    <span class="wa-system-pill">{{ e($msg->body) }}</span>
                                </div>
                            @else
                                <div wire:key="msg-{{ $msg->id }}" class="flex {{ $isCustomer ? 'justify-start' : 'justify-end' }} mb-[2px] wa-msg-anim">
                                    <div class="max-w-[75%] sm:max-w-[65%] {{ $isCustomer ? 'wa-bubble-in' : 'wa-bubble-out' }} px-2 pt-1.5 pb-2 min-w-[80px]">

                                        @if (! $isCustomer && $msg->sender_type === 'ai')
                                            <p class="text-[11px] font-semibold mb-0.5 px-1 text-[#7c5ce3]">
                                                🤖 AI
                                            </p>
                                        @endif

                                        @if ($msg->media_type && $msg->media_url)
                                            <div class="mb-1 rounded-md overflow-hidden">
                                                @if ($msg->media_type === 'image')
                                                    <a href="{{ $msg->media_url }}" target="_blank">
                                                        <img src="{{ $msg->media_url }}" alt="image" class="w-full max-h-[280px] object-cover hover:opacity-90 transition-opacity" loading="lazy">
                                                    </a>
                                                @elseif ($msg->media_type === 'video')
                                                    <video controls class="w-full max-h-[280px]">
                                                        <source src="{{ $msg->media_url }}" type="{{ $msg->media_mime ?? 'video/mp4' }}">
                                                    </video>
                                                @elseif ($msg->media_type === 'audio')
                                                    <div class="min-w-[220px] px-1">
                                                        <audio controls class="w-full h-[36px]">
                                                            <source src="{{ $msg->media_url }}" type="{{ $msg->media_mime ?? 'audio/ogg' }}">
                                                        </audio>
                                                    </div>
                                                @else
                                                    <a href="{{ $msg->media_url }}" target="_blank"
                                                       class="flex items-center gap-2 bg-black/5 dark:bg-white/5 rounded-lg px-3 py-2 hover:bg-black/10 dark:hover:bg-white/10 transition-colors">
                                                        <div class="w-9 h-9 rounded-lg bg-[#00a884] flex items-center justify-center shrink-0">
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-[12px] text-[#111b21] dark:text-[#e9edef] font-medium truncate">Dokumen</p>
                                                            <p class="text-[10px] text-[#667781] dark:text-[#8696a0]">Klik untuk download</p>
                                                        </div>
                                                        <svg class="w-4 h-4 text-[#8696a0] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif

                                        @if ($msg->body && (! $msg->media_type || $msg->body !== "[{$msg->media_type}]"))
                                            <p class="text-[14px] text-[#111b21] dark:text-[#e9edef] whitespace-pre-wrap break-words leading-[20px] px-1">{{ e($msg->body) }}</p>
                                        @endif

                                        <div class="flex items-center justify-end gap-0.5 mt-0.5 px-1">
                                            <span class="text-[10.5px] text-[#667781] dark:text-[#ffffff80] select-none">{{ $msg->created_at->format('H:i') }}</span>
                                            @if (! $isCustomer)
                                                <svg class="w-4 h-[13px] text-[#53bdeb]" viewBox="0 0 16 15" fill="currentColor"><path d="M15.01 3.316l-.478-.372a.365.365 0 00-.51.063L8.666 9.88a.32.32 0 01-.484.032l-.358-.325a.32.32 0 00-.484.032l-.378.48a.418.418 0 00.036.54l1.32 1.267c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 00-.064-.512zm-4.1 0l-.478-.372a.365.365 0 00-.51.063L4.566 9.88a.32.32 0 01-.484.032L1.892 7.78a.366.366 0 00-.516.005l-.423.433a.364.364 0 00.006.514l3.255 3.185a.32.32 0 00.484-.033l6.272-8.048a.365.365 0 00-.063-.512z"/></svg>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($this->messages->isEmpty() && $selectedConversationId)
                            <div class="flex items-center justify-center py-16">
                                <span class="wa-system-pill">Belum ada riwayat pesan</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Media preview --}}
                @if ($mediaFile)
                    <div class="wa-input-area px-4 pt-2 pb-0">
                        <div class="inline-flex items-center gap-2 bg-white dark:bg-[#2a3942] rounded-xl px-3 py-2 shadow-sm">
                            @if (str_starts_with($mediaFile->getMimeType(), 'image/'))
                                <img src="{{ $mediaFile->temporaryUrl() }}" class="h-14 w-14 object-cover rounded-lg" alt="preview">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-[#00a884] flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                </div>
                                <span class="text-xs text-[#667781] dark:text-[#8696a0] truncate max-w-[120px]">{{ $mediaFile->getClientOriginalName() }}</span>
                            @endif
                            <button wire:click="removeMedia" type="button" class="w-6 h-6 rounded-full bg-red-500/10 hover:bg-red-500/20 flex items-center justify-center transition-colors">
                                <svg class="w-3.5 h-3.5 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Input bar --}}
                <div class="wa-input-area px-3 py-[5px] flex items-end gap-1.5 shrink-0">
                    <label class="w-10 h-10 flex items-center justify-center rounded-full text-[#54656f] dark:text-[#8696a0] hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer transition-colors shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                        <input type="file" wire:model="mediaFile" class="hidden" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx">
                    </label>

                    <div class="flex-1 bg-white dark:bg-[#2a3942] rounded-lg min-h-[42px] flex items-center px-3">
                        <input
                            type="text"
                            wire:model="replyMessage"
                            wire:keydown.enter.prevent="{{ $mediaFile ? 'sendMedia' : 'sendReply' }}"
                            placeholder="{{ $mediaFile ? 'Tambahkan caption...' : 'Ketik pesan' }}"
                            class="flex-1 bg-transparent text-[15px] text-[#111b21] dark:text-[#d1d7db] placeholder-[#667781] outline-none border-none focus:ring-0 py-2"
                        >
                    </div>

                    <button
                        wire:click="{{ $mediaFile ? 'sendMedia' : 'sendReply' }}"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/5 transition-colors shrink-0 disabled:opacity-50"
                    >
                        <svg class="w-6 h-6 text-[#00a884]" viewBox="0 0 24 24" fill="currentColor"><path d="M1.101 21.757L23.8 12.028 1.101 2.3l.011 7.912 13.239 1.816-13.239 1.817-.011 7.912z"/></svg>
                    </button>
                </div>

                <div wire:loading wire:target="sendReply,sendMedia" class="wa-input-area px-4 py-1">
                    <p class="text-[11px] text-[#667781] dark:text-[#8696a0]">Mengirim...</p>
                </div>
            </div>
            @endif

        </div>
    </div>

    @assets
    @vite(['resources/js/app.js'])
    @endassets

    @script
    <script>
        // --- Echo / Reverb real-time listeners ---
        let echoChannel = null;

        function initEcho() {
            if (!window.Echo) return;

            echoChannel = window.Echo.channel('whatsapp-dashboard');

            echoChannel.listen('NewWhatsappMessage', (e) => {
                $wire.onNewMessage(e);
            });

            echoChannel.listen('ConversationUpdated', (e) => {
                $wire.onConversationUpdated(e);
            });

            console.log('[WA Dashboard] Echo subscribed to whatsapp-dashboard channel');
        }

        initEcho();

        // Cleanup on SPA navigation
        document.addEventListener('livewire:navigating', () => {
            if (echoChannel) {
                window.Echo.leaveChannel('whatsapp-dashboard');
                echoChannel = null;
            }
        });

        // --- Scroll to bottom ---
        $wire.on('scroll-to-bottom', () => {
            setTimeout(() => {
                const el = document.getElementById('chatContainer');
                if (el) el.scrollTop = el.scrollHeight;
            }, 50);
        });
    </script>
    @endscript
</x-filament-panels::page>
