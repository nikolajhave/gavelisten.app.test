@props([
    'size' => 'lg',
    'href' => null,
    'linked' => true,
    'name' => 'Gavelisten',
])

@php
    $iconSizes = [
        'sm' => 'w-9 h-9 rounded-xl',
        'md' => 'w-10 h-10 rounded-xl',
        'lg' => 'w-12 h-12 rounded-2xl',
    ];

    $svgSizes = [
        'sm' => 'w-5 h-5',
        'md' => 'w-6 h-6',
        'lg' => 'w-7 h-7',
    ];

    $textSizes = [
        'sm' => 'text-lg',
        'md' => 'text-xl',
        'lg' => 'text-2xl sm:text-3xl',
    ];

    $gapSizes = [
        'sm' => 'gap-2.5',
        'md' => 'gap-3',
        'lg' => 'gap-3',
    ];

    $targetUrl = $href ?? route('home');
@endphp

@if ($linked)
    <a href="{{ $targetUrl }}" {{ $attributes->merge(['class' => 'flex items-center ' . ($gapSizes[$size] ?? 'gap-3') . ' group']) }}>
        <div class="{{ $iconSizes[$size] ?? $iconSizes['lg'] }} bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 flex items-center justify-center text-blue-700 dark:text-blue-300 transition group-hover:scale-105 shadow-xs">
            <x-icons.gift class="{{ $svgSizes[$size] ?? $svgSizes['lg'] }}" />
        </div>
        <span class="font-bold {{ $textSizes[$size] ?? $textSizes['lg'] }} tracking-tight text-neutral-900 dark:text-white">
            {{ $name }}
        </span>
    </a>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center ' . ($gapSizes[$size] ?? 'gap-3')]) }}>
        <div class="{{ $iconSizes[$size] ?? $iconSizes['lg'] }} bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 flex items-center justify-center text-blue-700 dark:text-blue-300 shadow-xs">
            <x-icons.gift class="{{ $svgSizes[$size] ?? $svgSizes['lg'] }}" />
        </div>
        <span class="font-bold {{ $textSizes[$size] ?? $textSizes['lg'] }} tracking-tight text-neutral-900 dark:text-white">
            {{ $name }}
        </span>
    </div>
@endif
