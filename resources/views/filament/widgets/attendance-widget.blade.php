<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            @if(!$todayAttendance)
                <span class="text-lg font-medium text-gray-600 dark:text-gray-400">
                    Not clocked in yet
                </span>
                
                <x-filament::button wire:click="clockIn" size="lg">
                    Clock In
                </x-filament::button>
            @elseif(!$todayAttendance->clock_out_time)
                <div class="flex flex-col">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Clocked In at</span>
                    <span class="text-2xl font-bold font-mono text-primary-600 dark:text-primary-400">
                        {{ \Carbon\Carbon::parse($todayAttendance->clock_in_time)->format('H:i') }}
                    </span>
                </div>

                <x-filament::button wire:click="clockOut" color="danger">
                    End Shift
                </x-filament::button>
            @else
                <div class="flex gap-8">
                    <div>
                        <div class="text-xs text-gray-500 uppercase">Start</div>
                        <div class="font-mono font-bold text-lg">
                            {{ \Carbon\Carbon::parse($todayAttendance->clock_in_time)->format('H:i') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase">End</div>
                        <div class="font-mono font-bold text-lg">
                            {{ \Carbon\Carbon::parse($todayAttendance->clock_out_time)->format('H:i') }}
                        </div>
                    </div>
                </div>
                
                <x-filament::badge color="success">
                    Completed
                </x-filament::badge>
            @endif

        </div>
    </x-filament::section>
</x-filament-widgets::widget>