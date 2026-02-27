@php
    $user = filament()->auth()->user();
    $profile = $user?->employeeProfile;
    $fotoProfil = $profile?->foto_profil ? asset('storage/' . $profile->foto_profil) : null;
@endphp

<x-filament::dropdown
    :placement="($position === \Filament\Enums\UserMenuPosition::Topbar) ? 'bottom-end' : 'top-end'"
    :teleport="$position === \Filament\Enums\UserMenuPosition::Topbar"
    :attributes="
        \Filament\Support\prepare_inherited_attributes($attributes)
            ->class(['fi-user-menu'])
    "
>
    <x-slot name="trigger">
        <button
            aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}"
            type="button"
            class="fi-user-menu-trigger"
        >
            @if($fotoProfil)
                <img src="{{ $fotoProfil }}" alt="Foto Profil" class="rounded-full w-8 h-8 object-cover" />
            @else
                <x-filament-panels::avatar.user :user="$user" loading="lazy" />
            @endif
        </button>
    </x-slot>
    {{ $slot }}
</x-filament::dropdown>
