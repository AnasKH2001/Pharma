<x-filament-panels::page.simple>
    <style>
        .fi-simple-main {
            max-width: 48rem !important;
        }
    </style>

    <x-filament-panels::form wire:submit="register">
        {{ $this->form }}

        <x-filament::button type="submit" class="w-full mt-4">
            Register
        </x-filament::button>
    </x-filament-panels::form>

    <x-slot name="subheading">
        <a href="{{ filament()->getLoginUrl() }}">
            Already registered? Log in
        </a>
    </x-slot>
</x-filament-panels::page.simple>
