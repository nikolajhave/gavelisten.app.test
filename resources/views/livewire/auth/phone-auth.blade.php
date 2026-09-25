<div class="min-h-screen flex flex-col items-center justify-center p-6 sm:p-8">
    <div class="w-full max-w-md">
        <div class="flex justify-center mb-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 flex items-center justify-center text-blue-700 dark:text-blue-300 transition group-hover:scale-105 shadow-xs">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v13m0-13V4a2 2 0 112 2h-2zm0 0V4a2 2 0 10-2 2h2m0 0H4a2 2 0 00-2 2v3a2 2 0 002 2h16a2 2 0 002-2V10a2 2 0 00-2-2h-4"/>
                    </svg>
                </div>
                <span class="font-bold text-2xl sm:text-3xl tracking-tight text-neutral-900 dark:text-white">Gavelisten</span>
            </a>
        </div>

        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-6 sm:p-8 shadow-sm">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold tracking-tight text-neutral-900 dark:text-neutral-100">
                    @if ($step === 'phone')
                        {{ __('Log in or Sign up') }}
                    @elseif ($step === 'otp')
                        {{ __('Verify Your Phone') }}
                    @else
                        {{ __('What should we call you?') }}
                    @endif
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-2">
                    @if ($step === 'phone')
                        {{ __('Enter your mobile phone number to receive a one-time login code.') }}
                    @elseif ($step === 'otp')
                        {{ __('Enter the 6-digit verification code sent to your phone.') }}
                    @else
                        {{ __('Enter your name so friends and family can recognize your wishlist when sharing.') }}
                    @endif
                </p>
            </div>

            @if ($statusMessage)
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">
                    {{ $statusMessage }}
                </div>
            @endif

            @if ($step === 'phone')
                <form wire:submit="sendOtp" class="space-y-5">
                    <div>
                        <label for="phone"
                               class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            {{ __('Phone Number') }}
                        </label>
                        <input
                                type="tel"
                                id="phone"
                                name="tel"
                                autocomplete="tel"
                                inputmode="tel"
                                wire:model="phone"
                                placeholder="12 34 56 78"
                                class="w-full px-4 py-3 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base"
                                autofocus
                        >
                        @error('phone')
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full py-3 px-4 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 disabled:opacity-50 transition cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="sendOtp">{{ __('Continue with Phone') }}</span>
                        <span wire:loading wire:target="sendOtp">{{ __('Sending code...') }}</span>
                    </button>
                </form>

                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-neutral-200 dark:border-neutral-800"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white dark:bg-neutral-900 px-3 text-neutral-500 dark:text-neutral-400">
                            {{ __('Or continue with') }}
                        </span>
                    </div>
                </div>

                <div>
                    <a
                            href="{{ route('auth.google.redirect') }}"
                            class="w-full flex items-center justify-center gap-3 py-3 px-4 rounded-xl border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium text-neutral-800 dark:text-neutral-200 transition"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4"
                                  d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853"
                                  d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05"
                                  d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335"
                                  d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        {{ __('Continue with Google') }}
                    </a>
                </div>
            @elseif ($step === 'otp')
                <form wire:submit="verifyOtp" class="space-y-5">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="code" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                {{ __('Verification Code') }}
                            </label>
                            <button
                                    type="button"
                                    wire:click="editPhone"
                                    class="text-xs font-medium text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white underline cursor-pointer"
                            >
                                {{ __('Change phone') }}
                            </button>
                        </div>
                        <input
                                type="text"
                                id="code"
                                wire:model="code"
                                placeholder="123456"
                                maxlength="6"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                class="w-full px-4 py-3 text-center text-xl tracking-widest font-mono rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition"
                                autofocus
                        >
                        @error('code')
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full py-3 px-4 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 disabled:opacity-50 transition cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="verifyOtp">{{ __('Verify and Continue') }}</span>
                        <span wire:loading wire:target="verifyOtp">{{ __('Verifying...') }}</span>
                    </button>

                    <div class="text-center pt-2">
                        <button
                                type="button"
                                wire:click="resendOtp"
                                wire:loading.attr="disabled"
                                class="text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white cursor-pointer"
                        >
                            <span wire:loading.remove
                                  wire:target="resendOtp">{{ __("Didn't receive code? Resend") }}</span>
                            <span wire:loading wire:target="resendOtp">{{ __('Sending new code...') }}</span>
                        </button>
                    </div>
                </form>
            @elseif ($step === 'name')
                <form wire:submit="saveName" class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            {{ __('Your Name') }}
                        </label>
                        <input
                                type="text"
                                id="name"
                                wire:model="name"
                                placeholder="{{ __('e.g. Nikolaj') }}"
                                autocomplete="name"
                                class="w-full px-4 py-3 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base"
                                autofocus
                        >
                        @error('name')
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full py-3 px-4 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 disabled:opacity-50 transition cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="saveName">{{ __('Get Started') }}</span>
                        <span wire:loading wire:target="saveName">{{ __('Saving...') }}</span>
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-6 p-4 sm:p-5 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/60 text-sm text-neutral-700 dark:text-neutral-300 leading-relaxed shadow-xs">
            <p class="font-semibold text-neutral-900 dark:text-white mb-1.5">
                Velkommen til den nye udgave af Gavelisten
            </p>
            <p>
                Du kan logge ind med mobilnummer eller en google-konto. Hvis du ikke ser dine ønsker fra den gamle gaveliste, er du velkommen til at skrive til mig på <a href="tel:20231120"
                                                                                                                                                                          class="font-medium text-blue-600 dark:text-blue-400 underline hover:text-blue-700 dark:hover:text-blue-300">20231120</a>.
            </p>
            <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-400">
                Kh Nikolaj
            </p>

            <div class="font-semibold text-neutral-900 dark:text-white mb-2">
                Spørgsmål & Svar
            </div>
            <div class="mb-4">
                <div class="mb-2">
                    <div class="font-medium">Bliver mine ønsker overført fra det gamle system?</div>
                    <div>Ja, det kan vi sagtens – men det kræver, at jeg kan koble dit login (telefonnummer / email) sammen med det gamle system, hvor man ikke loggede ind.</div>
                </div>
                <div class="mb-2">
                    <div class="font-medium">Hvordan ser og redigerer jeg mine børns gavelister?</div>
                    <div>Det gør du ikke lige nu. Er på vej!</div>
                </div>
                <div class="mb-2">
                    <div class="font-medium">Kan det hele ikke bare være som før?</div>
                    <div>Nej, fætter Anders - det kan det ikke 😁</div>
                </div>
            </div>


        </div>
    </div>
</div>
