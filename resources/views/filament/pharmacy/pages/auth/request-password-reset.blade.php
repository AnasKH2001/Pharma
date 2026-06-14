<x-filament-panels::page.simple>
    <x-filament-panels::form wire:submit="request">
        {{ $this->form }}

        <x-filament::button type="submit" class="w-full">
            Send OTP
        </x-filament::button>
    </x-filament-panels::form>
    <x-slot name="subheading">
        <a href="{{ filament()->getLoginUrl() }}">
            Back to login
        </a>
    </x-slot>
</x-filament-panels::page.simple>
