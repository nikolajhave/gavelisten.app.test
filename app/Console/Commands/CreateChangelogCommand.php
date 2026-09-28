<?php

namespace App\Console\Commands;

use App\Models\Changelog;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

#[Signature('changelog:new {--title= : Title of the update} {--content= : Content / description of what is new} {--ver= : Version string (e.g. 1.2.0)} {--draft : Save as draft without publishing} {--publish-at= : Schedule publication datetime (e.g. 2026-10-01 12:00)}')]
#[Description('Create a new changelog / what\'s new notification entry')]
class CreateChangelogCommand extends Command
{
    /**
     * Alternative command aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = [
        'changelog:add',
        'changelog:create',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $title = $this->option('title');
        $content = $this->option('content');
        $version = $this->option('ver');
        $isDraft = (bool) $this->option('draft');
        $publishAtOption = $this->option('publish-at');

        $isInteractivePrompt = ($title === null && $content === null && $this->input->isInteractive());

        if ($isInteractivePrompt) {
            $title = text(
                label: 'What is the title of this update?',
                placeholder: 'e.g., Nyt design og forbedret søgning',
                required: true
            );

            $version = text(
                label: 'Version number (optional, press Enter to skip)',
                placeholder: 'e.g., 1.2.0'
            );

            $content = textarea(
                label: "What's new in this update? (Markdown is supported)",
                placeholder: "- Ny funktion til deling\n- Hurtigere indlæsning\n- Fejlrettelser",
                required: true
            );

            $shouldPublish = confirm(
                label: 'Publish this update immediately so users will see it?',
                default: true
            );

            $publishedAt = $shouldPublish ? now() : null;
        } else {
            if (! is_string($title) || trim($title) === '') {
                $this->components->error('The --title option is required.');

                return self::FAILURE;
            }

            if (! is_string($content) || trim($content) === '') {
                $this->components->error('The --content option is required.');

                return self::FAILURE;
            }

            $publishedAt = null;

            if ($publishAtOption) {
                try {
                    $publishedAt = Carbon::parse($publishAtOption);
                } catch (\Exception $e) {
                    $this->components->error('Invalid date format for --publish-at: '.$e->getMessage());

                    return self::FAILURE;
                }
            } elseif (! $isDraft) {
                $publishedAt = now();
            }
        }

        $changelog = Changelog::create([
            'title' => trim((string) $title),
            'version' => $version && trim((string) $version) !== '' ? trim((string) $version) : null,
            'content' => trim((string) $content),
            'published_at' => $publishedAt,
        ]);

        $this->newLine();
        $this->components->info('Changelog entry created successfully! (ID: '.$changelog->id.')');
        $this->newLine();

        $this->components->twoColumnDetail('ID', (string) $changelog->id);
        $this->components->twoColumnDetail('Title', $changelog->title);
        $this->components->twoColumnDetail('Version', $changelog->version ?? '<fg=gray>None</>');
        $this->components->twoColumnDetail(
            'Status',
            $changelog->published_at
                ? ($changelog->published_at->isFuture()
                    ? '<fg=yellow>Scheduled for '.$changelog->published_at->toDateTimeString().'</>'
                    : '<fg=green>Published ('.$changelog->published_at->toDateTimeString().')</>')
                : '<fg=yellow>Draft (Unpublished)</>'
        );

        return self::SUCCESS;
    }
}
