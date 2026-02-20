<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

class OgImageController extends Controller
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;
    private const PADDING = 60;
    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)
            ->with(['user', 'category', 'state', 'city', 'images'])
            ->firstOrFail();

        $filename = "og-images/{$post->id}-{$post->updated_at->timestamp}.png";
        $disk = Storage::disk('local');

        if (!$disk->exists($filename)) {
            // Clean up old versions for this post
            foreach ($disk->files('og-images') as $file) {
                if (str_starts_with(basename($file), "{$post->id}-")) {
                    $disk->delete($file);
                }
            }
            $disk->put($filename, $this->generate($post));
        }

        return response($disk->get($filename), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function generate(Post $post): string
    {
        $manager = new ImageManager(new Driver());

        // Create base canvas with dark background
        $image = $manager->create(self::WIDTH, self::HEIGHT)
            ->fill('1a1a2e');

        // Draw accent bar at top
        $accentBar = $manager->create(self::WIDTH, 6)->fill('6366f1');
        $image->place($accentBar, 'top-left');

        // Draw content area background (slightly lighter)
        $contentBg = $manager->create(self::WIDTH - 40, self::HEIGHT - 46)->fill('16213e');
        $image->place($contentBg, 'top-left', 20, 26);

        $font = $this->resolveFont();
        $boldFont = $this->resolveBoldFont();
        $x = self::PADDING;

        // If post has an image, place it on the right side
        $hasImage = $post->images && $post->images->first();
        $textAreaWidth = $hasImage ? 720 : (self::WIDTH - self::PADDING * 2);

        if ($hasImage) {
            $this->placePostImage($manager, $image, $post);
        }

        // Category badge
        $y = 50;
        if ($post->category) {
            $badgeName = strtoupper($post->category->name);
            $image->text($badgeName, $x, $y, function (FontFactory $f) use ($font) {
                $f->filename($font);
                $f->size(18);
                $f->color('a5b4fc');
            });
            $y += 32;
        }

        // Title - word wrap manually
        $y += 10;
        $titleLines = $this->wordWrap($post->title, $boldFont, 36, $textAreaWidth);
        foreach (array_slice($titleLines, 0, 3) as $i => $line) {
            // If last allowed line and there are more lines, add ellipsis
            if ($i === 2 && count($titleLines) > 3) {
                $line = rtrim($line) . '...';
            }
            $image->text($line, $x, $y, function (FontFactory $f) use ($boldFont) {
                $f->filename($boldFont);
                $f->size(36);
                $f->color('e2e8f0');
            });
            $y += 48;
        }

        // Description (plain text, first ~2 lines)
        $y += 12;
        $plainBody = $this->stripMarkdown($post->body);
        $descLines = $this->wordWrap($plainBody, $font, 20, $textAreaWidth);
        foreach (array_slice($descLines, 0, 2) as $i => $line) {
            if ($i === 1 && count($descLines) > 2) {
                $line = rtrim($line) . '...';
            }
            $image->text($line, $x, $y, function (FontFactory $f) use ($font) {
                $f->filename($font);
                $f->size(20);
                $f->color('94a3b8');
            });
            $y += 30;
        }

        // Bottom bar: stats + author
        $bottomY = self::HEIGHT - 70;

        // Separator line
        $separator = $manager->create(self::WIDTH - 80, 1)->fill('334155');
        $image->place($separator, 'top-left', 40, $bottomY - 20);

        // Location
        $location = collect([$post->city?->name, $post->state?->name])->filter()->join(', ');

        // Stats line: votes · comments · location (plain ASCII — GD can't render emoji)
        $statsText = "{$post->vote_count} votes  ·  {$post->comment_count} comments";
        if ($location) {
            $statsText .= "  ·  {$location}";
        }

        $image->text($statsText, $x, $bottomY, function (FontFactory $f) use ($font) {
            $f->filename($font);
            $f->size(18);
            $f->color('64748b');
        });

        // Site branding
        $image->text('Civic Forum', self::WIDTH - self::PADDING, $bottomY, function (FontFactory $f) use ($boldFont) {
            $f->filename($boldFont);
            $f->size(20);
            $f->color('6366f1');
            $f->align('right');
        });

        // Author
        if ($post->user) {
            $image->text("by {$post->user->username}", $x, $bottomY + 28, function (FontFactory $f) use ($font) {
                $f->filename($font);
                $f->size(16);
                $f->color('475569');
            });
        }

        return (string) $image->toPng();
    }

    private function placePostImage(ImageManager $manager, $canvas, Post $post): void
    {
        $imagePath = $post->images->first()->image_path;
        $fullPath = Storage::disk('public')->path($imagePath);

        if (!file_exists($fullPath)) {
            return;
        }

        try {
            $thumb = $manager->read($fullPath);
            $thumb->cover(380, 380);

            // Place on right side with some margin
            $canvas->place($thumb, 'top-left', self::WIDTH - 380 - 40, 50);
        } catch (\Throwable) {
            // Skip if image can't be read
        }
    }

    private function wordWrap(string $text, string $fontFile, int $fontSize, int $maxWidth): array
    {
        $lines = [];
        $words = explode(' ', $text);
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine === '' ? $word : "{$currentLine} {$word}";

            // Estimate width: for GD fonts, approximate at ~0.6 * fontSize per char
            $estimatedWidth = strlen($testLine) * $fontSize * 0.55;

            if ($estimatedWidth > $maxWidth && $currentLine !== '') {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    private function stripMarkdown(string $md): string
    {
        return trim(preg_replace(
            [
                '/!\[.*?\]\(.*?\)/',       // images
                '/\[([^\]]*)\]\(.*?\)/',   // links → text
                '/#{1,6}\s+/',             // headings
                '/[*_~`>{}\[\]]/',         // formatting chars
                '/\n+/',                   // newlines → space
                '/\s{2,}/',               // collapse whitespace
            ],
            ['', '$1', '', '', ' ', ' '],
            $md
        ));
    }

    private function resolveFont(): string
    {
        // Try common font paths
        $candidates = [
            resource_path('fonts/Inter-Regular.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/TTF/DejaVuSans.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
            '/System/Library/Fonts/SFNSText.ttf',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // GD built-in (number 5 = largest built-in)
        return '5';
    }

    private function resolveBoldFont(): string
    {
        $candidates = [
            resource_path('fonts/Inter-Bold.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/TTF/DejaVuSans-Bold.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
            '/System/Library/Fonts/SFNSText.ttf',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return $this->resolveFont();
    }
}
