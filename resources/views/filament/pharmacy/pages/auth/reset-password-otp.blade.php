<x-filament-panels::page.simple>
    <form wire:submit="resetPassword">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" class="w-full">
                Reset Password
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page.simple>
