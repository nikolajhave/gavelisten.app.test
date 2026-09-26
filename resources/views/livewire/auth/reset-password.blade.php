<div class="min-h-screen flex flex-col items-center justify-center p-6 sm:p-8">
    <div class="w-full max-w-md">
        <div class="flex justify-center mb-8">
            <x-app-logo size="lg" />
        </div>

        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-6 sm:p-8 shadow-sm">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold tracking-tight text-neutral-900 dark:text-neutral-100">
                    {{ __('Reset Password') }}
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-2">
                    {{ __('Enter your new password below.') }}
                </p>
            </div>

            <form wire:submit="resetPassword" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('Email') }}
                    </label>
                    <input
                            type="email"
                            id="email"
                            name="email"
                            autocomplete="username"
                            wire:model="email"
                            class="w-full px-4 py-3 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base"
                    >
                    @error('email')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('New Password') }}
                    </label>
                    <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            wire:model="password"
                            class="w-full px-4 py-3 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base"
                            autofocus
                    >
                    @error('password')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full py-3 px-4 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 disabled:opacity-50 transition cursor-pointer"
                >
                    <span wire:loading.remove wire:target="resetPassword">{{ __('Reset Password') }}</span>
                    <span wire:loading wire:target="resetPassword">{{ __('Resetting password...') }}</span>
                </button>

                <div class="text-center pt-2">
                    <a href="{{ route('login') }}" class="text-xs font-medium text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white underline">
                        {{ __('Back to login') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
