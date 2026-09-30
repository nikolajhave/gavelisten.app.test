<?php

namespace App\Livewire;

use App\Models\Changelog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ChangelogModal extends Component
{
    public ?Changelog $changelog = null;

    public bool $showModal = false;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user) {
            $unread = $user->unreadChangelog();
            $this->showModal = $unread !== null;
            $this->changelog = $unread ?? Changelog::latestPublished()->first();
        }
    }

    #[On('open-changelog')]
    public function open(?int $changelogId = null): void
    {
        if ($changelogId) {
            $this->changelog = Changelog::published()->find($changelogId);
        } else {
            $this->changelog = Changelog::latestPublished()->first();
        }

        if ($this->changelog) {
            $this->showModal = true;
        }
    }

    public function dismiss(): void
    {
        $user = Auth::user();

        if ($user && $this->changelog) {
            $user->markChangelogAsRead($this->changelog);
        }

        $this->showModal = false;
    }

    public function render(): View
    {
        return view('livewire.changelog-modal');
    }
}
