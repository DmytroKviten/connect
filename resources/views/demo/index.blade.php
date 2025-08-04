{{-- resources/views/devices/index.blade.php --}}
@extends('layouts.base')

@section('content')
       @include('layouts.header')
<div class="max-w-6xl mx-auto px-6 py-16">

    <h1 class="text-3xl font-extrabold mb-10 tracking-tight text-white">
        Підключені&nbsp;пристрої
    </h1>

    {{-- якщо пристроїв немає --}}
    @if ($devices->isEmpty())
        <div class="flex flex-col items-center bg-glass/40 backdrop-blur-md
                    p-10 rounded-2xl border border-white/10 shadow-xl">
            <svg class="w-14 h-14 mb-6 stroke-muted" fill="none" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round"
                 viewBox="0 0 24 24">
                <path d="M12 9v3m0 0v3m0-3h3m-3 0H9"/>
                <circle cx="12" cy="12" r="9"/>
            </svg>
            <p class="text-lg font-semibold mb-2">
                На жаль, підключених пристроїв поки немає.
            </p>
            <p class="text-sm text-muted max-w-md text-center">
                Коли ви підʼєднаєте першу розетку, вона з’явиться в цьому списку.
            </p>
        </div>
    @else
    {{-- коли пристрої є --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($devices as $device)
                <div class="relative flex flex-col bg-glass/40 backdrop-blur
                            border border-white/10 rounded-2xl p-6 shadow-lg
                            transition hover:shadow-accent/30">

                    {{-- іконка типу (placeholder) --}}
                    <div class="absolute -top-5 -right-5 bg-accent/20 p-3 rounded-full">
                        <svg class="w-5 h-5 stroke-accent" fill="none" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round"
                             viewBox="0 0 24 24">
                            <path d="M4 9h16M4 15h16"/>
                        </svg>
                    </div>

                    {{-- назва бренду й моделі --}}
                    <h2 class="text-lg font-semibold mb-1">
                        {{ $device->brand ?? 'Shelly' }} {{ $device->model ?? '' }}
                    </h2>

                    {{-- UID (MAC) --}}
                    <p class="text-sm text-muted">
                        UID: {{ $device->uid }}
                    </p>

                    {{-- IP та SSID --}}
                    <div class="mb-2 flex flex-col text-sm text-neutral-300">
                        <div>
                            <span class="font-semibold">IP:</span>
                            <span>{{ $device->ip ?? 'невідомо' }}</span>
                        </div>
                    </div>

                    {{-- footer картки --}}
                    <div class="mt-auto flex items-center justify-between pt-4 border-t border-white/5">
                        <span class="text-xs text-muted">
                            Додано {{ $device->created_at->diffForHumans() }}
                        </span>

                        {{-- 👉 коли буде сторінка-деталі — route("devices.show", $device) --}}
                        <a href="{{ route('devices.show', $device) }}"
                           class="inline-flex items-center gap-1 text-accent text-sm font-medium hover:underline">
                            Деталі
                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
