<footer class="mt-16 border-t border-neutral-200 dark:border-neutral-800 py-8 bg-neutral-50/50 dark:bg-neutral-950/50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-500 dark:text-neutral-400">
        <p>
            {{ __('Powered by') }} <a href="{{ route('home') }}" class="font-semibold hover:text-neutral-900 dark:hover:text-white transition">Gavelisten</a> — {{ __('Easy wishlist sharing for every occasion.') }}
        </p>
        <p>
            <a href="{{ route('login') }}" class="underline hover:text-neutral-900 dark:hover:text-white transition">{{ __('Create your own wishlist for free') }}</a>
        </p>
    </div>
</footer>
