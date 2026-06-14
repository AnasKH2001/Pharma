<x-filament-panels::page.simple>
    <x-filament-panels::form wire:submit="register">
        {{ $this->form }}

        <x-filament::button type="submit" class="w-full">
            Register
        </x-filament::button>
    </x-filament-panels::form>

    <x-slot name="subheading">
        <a href="{{ filament()->getLoginUrl() }}">
            Already registered? Log in
        </a>
    </x-slot>
</x-filament-panels::page.simple>
