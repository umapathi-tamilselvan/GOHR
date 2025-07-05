<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div>
        @if (Auth::user()->hasRole('Super Admin'))
            @include('dashboard.partials.super-admin', ['data' => $data])
        @elseif (Auth::user()->hasRole('HR'))
            @include('dashboard.partials.hr', ['data' => $data])
        @elseif (Auth::user()->hasRole('Manager'))
            @include('dashboard.partials.manager', ['data' => $data])
        @elseif (Auth::user()->hasRole('Employee'))
            @include('dashboard.partials.employee', ['data' => $data])
        @endif
    </div>
</x-app-layout>
