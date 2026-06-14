<x-filament-panels::page.simple>
    <form wire:submit="verify">
        {{ $this->form }}

        <div class="mt-6 flex flex-col gap-2">
            <x-filament::button type="submit" class="w-full">
                Verify
            </x-filament::button>

            <x-filament::button
                type="button"
                color="gray"
                wire:click="resend"
                class="w-full"
            >
                Resend OTP
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page.simple>
