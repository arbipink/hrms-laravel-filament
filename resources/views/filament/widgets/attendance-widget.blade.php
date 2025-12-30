<x-filament-widgets::widget>
    <x-filament::section class="relative overflow-hidden ring-1 ring-gray-950/5 dark:ring-white/10">
        
        {{-- Background Decoration (Subtle Gradient) --}}
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-primary-500/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-secondary-500/10 blur-3xl"></div>

        <div 
            class="relative flex flex-col md:flex-row items-center justify-between gap-6"
            x-data="{ 
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
                date: new Date().toLocaleDateString([], { weekday: 'long', day: 'numeric', month: 'long' }),
                init() {
                    setInterval(() => {
                        this.time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    }, 1000);
                }
            }"
        >
            {{-- Left Side: Greeting & Live Clock --}}
            <div class="flex flex-col items-center md:items-start text-center md:text-left space-y-1">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                    <span x-text="date"></span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                        </span>
                        <span x-text="time" class="font-mono"></span>
                    </span>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $this->getGreeting() }}, {{ Auth::user()->name }}! 
                    <span class="inline-block hover:animate-spin cursor-default">
                        @if($this->getGreeting() == 'Good Evening') 🌙 @elseif($this->getGreeting() == 'Good Morning') ☀️ @else 👋 @endif
                    </span>
                </h2>
            </div>

            {{-- Right Side: Action Area --}}
            <div class="flex items-center justify-center md:justify-end w-full md:w-auto">
                
                {{-- STATE 1: NOT CLOCKED IN --}}
                @if(!$todayAttendance)
                    <div class="group relative">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-primary-600 to-secondary-600 rounded-lg blur opacity-30 group-hover:opacity-75 transition duration-1000 group-hover:duration-200 animate-tilt"></div>
                        <button 
                            wire:click="clockIn"
                            class="relative flex items-center gap-3 px-8 py-4 bg-white dark:bg-gray-900 rounded-lg leading-none text-gray-900 dark:text-white ring-1 ring-gray-900/5 dark:ring-white/10 shadow-xl hover:scale-[1.02] transition-transform"
                        >
                            <span class="text-2xl">☕</span>
                            <div class="text-left">
                                <div class="font-bold text-lg">Clock In</div>
                                <div class="text-xs text-gray-500">Let's start the day</div>
                            </div>
                        </button>
                    </div>

                {{-- STATE 2: WORKING (CLOCKED IN) --}}
                @elseif(!$todayAttendance->clock_out_time)
                    <div class="flex flex-col md:flex-row items-center gap-6 bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-100 dark:border-white/5">
                        
                        {{-- Status Pill --}}
                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 rounded-full flex items-center justify-center text-xl bg-white dark:bg-gray-800 shadow-sm">
                                👨‍💻
                            </div>
                            <div class="text-left">
                                <div class="text-xs text-gray-500">Clocked In at</div>
                                <div class="font-mono font-bold text-lg text-primary-600">
                                    {{ \Carbon\Carbon::parse($todayAttendance->clock_in_time)->format('H:i') }}
                                </div>
                            </div>
                        </div>

                        {{-- Vertical Divider (Desktop) --}}
                        <div class="hidden md:block w-px h-10 bg-gray-200 dark:bg-gray-700"></div>

                        {{-- Late/Present Status --}}
                        <div>
                            @if($todayAttendance->status === 'LATE')
                                <x-filament::badge color="danger" icon="heroicon-m-exclamation-triangle">
                                    Running Late
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="success" icon="heroicon-m-check-badge">
                                    On Time
                                </x-filament::badge>
                            @endif
                        </div>

                        {{-- Clock Out Button --}}
                        <x-filament::button 
                            wire:click="clockOut"
                            color="danger"
                            outlined
                            class="hover:bg-red-50 dark:hover:bg-red-900/20"
                        >
                            End Shift 🛑
                        </x-filament::button>
                    </div>

                {{-- STATE 3: DAY FINISHED --}}
                @else
                    <div class="flex items-center gap-4 animate-in slide-in-from-bottom-2 fade-in duration-500">
                        <div class="text-center p-3 rounded-lg bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-900/20">
                            <div class="text-2xl mb-1">🎉</div>
                            <div class="text-xs font-bold text-green-700 dark:text-green-400">Complete</div>
                        </div>
                        
                        <div class="flex gap-4 text-center">
                            <div>
                                <div class="text-xs text-gray-400 uppercase tracking-wider">Start</div>
                                <div class="font-mono font-bold">{{ \Carbon\Carbon::parse($todayAttendance->clock_in_time)->format('H:i') }}</div>
                            </div>
                            <div class="text-gray-300">➜</div>
                            <div>
                                <div class="text-xs text-gray-400 uppercase tracking-wider">End</div>
                                <div class="font-mono font-bold">{{ \Carbon\Carbon::parse($todayAttendance->clock_out_time)->format('H:i') }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>