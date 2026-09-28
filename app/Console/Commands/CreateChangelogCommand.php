<?php

namespace App\Console\Commands;

use App\Models\Changelog;
use App\Services\ChangelogSyncService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

#[Signature('changelog:new {--title= : Title of the update} {--content= : Content / description of what is new} {--ver= : Version string (e.g. 1.2.0)} {--draft : Save as draft without publishing} {--publish-at= : Schedule publication datetime (e.g. 2026-10-01 12:00)} {--db-only : Save directly to database without writing a markdown file} {--no-sync : Write markdown file only without syncing to database}')]
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
    public function handle(ChangelogSyncService $syncService): int
    {
        $title = $this->option('title');
        $content = $this->option('content');
        $version = $this->option('ver');
        $isDraft = (bool) $this->option('draft');
        $publishAtOption = $this->option('publish-at');
        $dbOnly = (bool) $this->option('db-only');
        $noSync = (bool) $this->option('no-sync');

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

        $trimmedTitle = trim((string) $title);
        $trimmedContent = trim((string) $content);
        $trimmedVersion = $version && trim((string) $version) !== '' ? trim((string) $version) : null;
        $slug = Str::slug(($trimmedVersion ? "{$trimmedVersion}-" : '').$trimmedTitle);

        $createdFilePath = null;

        if (! $dbOnly) {
            $createdFilePath = $syncService->createChangelogFile(
                title: $trimmedTitle,
                content: $trimmedContent,
                version: $trimmedVersion,
                publishedAt: $publishedAt,
                slug: $slug
            );
        }

        $changelog = null;

        if (! $noSync) {
            $changelog = Changelog::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $trimmedTitle,
                    'version' => $trimmedVersion,
                    'content' => $trimmedContent,
                    'published_at' => $publishedAt,
                ]
            );
        }

        $this->newLine();
        $this->components->info('Changelog created successfully!');

        if ($createdFilePath) {
            $this->components->twoColumnDetail('File', $createdFilePath);
            $this->components->twoColumnDetail('Git Tip', '<fg=yellow>Commit this file to your repository for Forge deployment</>');
        }

        if ($changelog) {
            $this->components->twoColumnDetail('Database ID', (string) $changelog->id);
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
        }

        return self::SUCCESS;
    }
}
