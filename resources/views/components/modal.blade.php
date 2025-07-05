@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { let GOHRdex = this.focusables().indexOf(document.activeElement); if (index === (this.focusables().length - 1)) { index = -1; } return this.focusables()[index + 1] },
        prevFocusable() { let index = this.focusables().indexOf(document.activeElement); if (index === 0) { index = this.focusables().length; } return this.focusables()[index - 1] },
        trapFocus() {
            if (! this.show) return;
            this.firstFocusable() && this.firstFocusable().focus()
            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Tab') return
                if (e.shiftKey) {
                    if (document.activeElement === this.firstFocusable()) {
                        this.lastFocusable().focus()
                        e.preventDefault()
                    }
                } else {
                    if (document.activeElement === this.lastFocusable()) {
                        this.firstFocusable().focus()
                        e.preventDefault()
                    }
                }
            })
        },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            trapFocus();
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <div class="px-6 py-4">
            <div class="text-lg font-medium text-gray-900">
                {{ $title }}
            </div>

            <div class="mt-4 text-sm text-gray-600">
                {{ $content }}
            </div>
        </div>

        <div class="flex flex-row justify-end px-6 py-4 bg-gray-100 text-right">
            {{ $footer }}
        </div>
    </div>
</div>
