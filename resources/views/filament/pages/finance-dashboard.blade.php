<x-filament::page>
    <div class="space-y-6">
        <div>
            {{ $this->form }}
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <x-filament::card>
                <div class="text-sm text-gray-500">Total Billed</div>
                <div class="text-2xl font-semibold">{{ number_format($summary['total_billed'] ?? 0, 2) }}</div>
            </x-filament::card>
            <x-filament::card>
                <div class="text-sm text-gray-500">Total Collected</div>
                <div class="text-2xl font-semibold">{{ number_format($summary['total_collected'] ?? 0, 2) }}</div>
            </x-filament::card>
            <x-filament::card>
                <div class="text-sm text-gray-500">Outstanding</div>
                <div class="text-2xl font-semibold">{{ number_format($summary['outstanding'] ?? 0, 2) }}</div>
            </x-filament::card>
            <x-filament::card>
                <div class="text-sm text-gray-500">Collection %</div>
                <div class="text-2xl font-semibold">{{ number_format($summary['collection_percentage'] ?? 0, 2) }}%</div>
            </x-filament::card>
        </div>
    </div>
</x-filament::page>
