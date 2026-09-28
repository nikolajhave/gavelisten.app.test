<?php

namespace App\Services;

use App\Models\Changelog;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SplFileInfo;

class ChangelogSyncService
{
    /**
     * Default changelog storage directory path.
     */
    public function defaultDirectory(): string
    {
        return database_path('changelogs');
    }

    /**
     * Sync all changelog files from the given directory into the database.
     *
     * @return array{
     *     synced: int,
     *     created: int,
     *     updated: int,
     *     items: array<int, array{file: string, slug: string, title: string, version: ?string, action: string, published_at: ?string}>
     * }
     */
    public function sync(?string $directory = null): array
    {
        $dir = $directory ?: $this->defaultDirectory();

        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $files = File::files($dir);

        // Sort files by filename for consistent ordering
        usort($files, fn (SplFileInfo $a, SplFileInfo $b) => strcmp($a->getFilename(), $b->getFilename()));

        $createdCount = 0;
        $updatedCount = 0;
        $items = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $parsed = $this->parseFile($file);
            if (! $parsed) {
                continue;
            }

            $changelog = null;

            if (! empty($parsed['slug'])) {
                $changelog = Changelog::where('slug', $parsed['slug'])->first();
            }

            if (! $changelog && ! empty($parsed['version'])) {
                $changelog = Changelog::where('version', $parsed['version'])->first();
            }

            if (! $changelog) {
                $changelog = Changelog::where('title', $parsed['title'])->first();
            }

            $action = 'created';

            if ($changelog) {
                $action = 'updated';
                $changelog->update([
                    'slug' => $parsed['slug'],
                    'version' => $parsed['version'],
                    'title' => $parsed['title'],
                    'content' => $parsed['content'],
                    'published_at' => $parsed['published_at'],
                ]);
                $updatedCount++;
            } else {
                $changelog = Changelog::create([
                    'slug' => $parsed['slug'],
                    'version' => $parsed['version'],
                    'title' => $parsed['title'],
                    'content' => $parsed['content'],
                    'published_at' => $parsed['published_at'],
                ]);
                $createdCount++;
            }

            $items[] = [
                'file' => $file->getFilename(),
                'slug' => $parsed['slug'],
                'title' => $parsed['title'],
                'version' => $parsed['version'],
                'action' => $action,
                'published_at' => $parsed['published_at']?->toDateTimeString(),
            ];
        }

        return [
            'synced' => count($items),
            'created' => $createdCount,
            'updated' => $updatedCount,
            'items' => $items,
        ];
    }

    /**
     * Parse a changelog file or markdown content.
     *
     * @return array{
     *     slug: string,
     *     title: string,
     *     version: ?string,
     *     content: string,
     *     published_at: ?Carbon
     * }|null
     */
    public function parseFile(SplFileInfo|string $file): ?array
    {
        $filename = '';
        $raw = '';

        if ($file instanceof SplFileInfo) {
            $filename = $file->getFilename();
            $raw = File::get($file->getRealPath());
        } elseif (is_file($file)) {
            $filename = basename($file);
            $raw = File::get($file);
        } else {
            $raw = $file;
        }

        return $this->parseContent($raw, $filename);
    }

    /**
     * Parse markdown content and front matter.
     *
     * @return array{
     *     slug: string,
     *     title: string,
     *     version: ?string,
     *     content: string,
     *     published_at: ?Carbon
     * }|null
     */
    public function parseContent(string $content, string $filename = ''): ?array
    {
        $content = trim($content);
        if ($content === '') {
            return null;
        }

        $frontMatter = [];
        $body = $content;

        // Check for YAML front matter block: --- \n ... \n ---
        if (preg_match('/^---\r?\n(.*?)\r?\n---\r?\n(.*)$/s', $content, $matches)) {
            $frontMatter = $this->parseYamlFrontMatter($matches[1]);
            $body = trim($matches[2]);
        }

        // Determine title
        $title = $frontMatter['title'] ?? null;
        if (! $title) {
            // Extract from first # Heading or first non-empty line
            if (preg_match('/^#\s+(.+)$/m', $body, $headingMatch)) {
                $title = trim($headingMatch[1]);
                $body = trim((string) preg_replace('/^#\s+.+$/m', '', $body, 1));
            } else {
                $lines = explode("\n", $body);
                $title = trim($lines[0] ?? '');
            }
        }

        if (empty($title)) {
            return null;
        }

        // Determine version
        $version = $frontMatter['version'] ?? null;
        if (! $version && preg_match('/[\(\[]?(v?\d+\.\d+(?:\.\d+)?(?:-[a-zA-Z0-9.]+)?)[\]\)]?/', $title, $verMatch)) {
            $version = $verMatch[1];
        }

        // Determine slug
        $baseNameWithoutExt = $filename ? pathinfo($filename, PATHINFO_FILENAME) : '';
        $slug = $frontMatter['slug'] ?? null;
        if (! $slug) {
            if ($baseNameWithoutExt) {
                $slug = Str::slug($baseNameWithoutExt);
            } else {
                $slug = Str::slug(($version ? "{$version}-" : '').$title);
            }
        }

        // Determine publication date
        $publishedAt = null;
        $isDraft = filter_var($frontMatter['draft'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $isDraft) {
            if (! empty($frontMatter['published_at'])) {
                if (strtolower((string) $frontMatter['published_at']) !== 'null') {
                    $publishedAt = Carbon::parse($frontMatter['published_at']);
                }
            } elseif (! empty($frontMatter['date'])) {
                $publishedAt = Carbon::parse($frontMatter['date']);
            } elseif ($filename && preg_match('/^(\d{4}-\d{2}-\d{2})/', $filename, $dateMatch)) {
                $publishedAt = Carbon::parse($dateMatch[1]);
            } else {
                $publishedAt = now();
            }
        }

        return [
            'slug' => (string) $slug,
            'title' => (string) $title,
            'version' => $version ? (string) $version : null,
            'content' => $body,
            'published_at' => $publishedAt,
        ];
    }

    /**
     * Create a new changelog markdown file in the changelog directory.
     *
     * @return string The created file path
     */
    public function createChangelogFile(
        string $title,
        string $content,
        ?string $version = null,
        Carbon|DateTimeInterface|null $publishedAt = null,
        ?string $slug = null,
        ?string $directory = null,
    ): string {
        $dir = $directory ?: $this->defaultDirectory();

        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $datePrefix = $publishedAt ? Carbon::instance($publishedAt)->format('Y-m-d') : now()->format('Y-m-d');
        $slugName = $slug ?: Str::slug(($version ? "{$version}-" : '').$title);
        $filename = "{$datePrefix}-{$slugName}.md";
        $filePath = $dir.DIRECTORY_SEPARATOR.$filename;

        $publishedAtString = $publishedAt ? Carbon::instance($publishedAt)->format('Y-m-d H:i:s') : 'null';

        $fileContent = "---\n";
        $fileContent .= 'title: "'.addcslashes($title, '"')."\"\n";
        if ($version) {
            $fileContent .= "version: {$version}\n";
        }
        if ($publishedAt) {
            $fileContent .= "published_at: {$publishedAtString}\n";
        } else {
            $fileContent .= "draft: true\n";
        }
        $fileContent .= "slug: {$slugName}\n";
        $fileContent .= "---\n\n";
        $fileContent .= trim($content)."\n";

        File::put($filePath, $fileContent);

        return $filePath;
    }

    /**
     * Parse simple YAML front-matter key: value pairs.
     *
     * @return array<string, string>
     */
    protected function parseYamlFrontMatter(string $yaml): array
    {
        $data = [];
        $lines = explode("\n", $yaml);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Strip surrounding quotes
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                $data[$key] = $value;
            }
        }

        return $data;
    }
}
