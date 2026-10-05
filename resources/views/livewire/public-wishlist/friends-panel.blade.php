<div class="py-3">
    <div class="px-4 pb-3 flex items-center justify-between">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
            {{ __('Friends') }}
        </h3>
        @if ($this->friends->isNotEmpty())
            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                {{ $this->friends->count() }}
            </span>
        @endif
    </div>

    <div class="py-1 max-h-52 overflow-y-auto overscroll-contain">
        @if ($this->friends->isEmpty())
            <div class="px-4 py-4 text-center text-sm text-neutral-500 dark:text-neutral-400">
                <div class="w-9 h-9 mx-auto mb-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-400 dark:text-neutral-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <p class="font-medium text-xs text-neutral-900 dark:text-neutral-100">{{ __('No friends yet') }}</p>
            </div>
        @else
            @foreach ($this->friends as $friend)
                <x-wishlist.friend-item :friend="$friend" wire:key="public-friend-item-{{ $friend->id }}" />
            @endforeach
        @endif
    </div>

    <x-wishlist.friend-search
        :friends="$this->friends"
        :query="$friendSearchQuery"
        :results="$this->friendSearchResults"
        key-prefix="public-"
    />
</div>
