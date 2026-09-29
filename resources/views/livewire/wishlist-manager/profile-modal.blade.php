<div
    x-data="{ show: $wire.entangle('showProfileModal') }"
    x-show="show"
    x-cloak
    style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="if (show) { show = false; $wire.closeProfileModal(); }"
>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        {{-- Backdrop --}}
        <div
            @click="show = false; $wire.closeProfileModal()"
            x-show="show"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-neutral-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
        ></div>

        {{-- Modal Dialog --}}
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-left shadow-2xl transition-all w-full sm:my-8 sm:max-w-md p-6 sm:p-8"
        >
            <div class="flex items-center justify-between pb-4 border-b border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                            {{ __('Profile') }}
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            {{ __('Manage your account details and password.') }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="show = false; $wire.closeProfileModal()"
                    class="p-1 rounded-lg text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form wire:submit="saveProfile" class="mt-6 space-y-4">
                {{-- Name --}}
                <div>
                    <label for="profile-name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        {{ __('Your Name') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="profile-name"
                        wire:model="profileName"
                        placeholder="{{ __('e.g. Nikolaj') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base sm:text-sm"
                        autofocus
                    >
                    @error('profileName')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="profile-email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        {{ __('Email') }}
                    </label>
                    <input
                        type="email"
                        id="profile-email"
                        wire:model="profileEmail"
                        placeholder="din@email-adresse.dk"
                        class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base sm:text-sm"
                    >
                    @error('profileEmail')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="profile-phone" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        {{ __('Phone Number') }}
                    </label>
                    <input
                        type="tel"
                        id="profile-phone"
                        wire:model="profilePhone"
                        placeholder="+45 12 34 56 78"
                        class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base sm:text-sm"
                    >
                    @error('profilePhone')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="profile-password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        {{ __('New Password') }}
                    </label>
                    <input
                        type="password"
                        id="profile-password"
                        wire:model="profilePassword"
                        placeholder="{{ __('Leave blank to keep current password') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base sm:text-sm"
                    >
                    @error('profilePassword')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4">
                    <button
                        type="button"
                        @click="show = false; $wire.closeProfileModal()"
                        class="px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition cursor-pointer"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium text-sm hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 disabled:opacity-50 transition shadow-xs cursor-pointer data-loading:opacity-75"
                    >
                        <span wire:loading.remove wire:target="saveProfile">
                            {{ __('Save Changes') }}
                        </span>
                        <span wire:loading wire:target="saveProfile">
                            {{ __('Saving...') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
