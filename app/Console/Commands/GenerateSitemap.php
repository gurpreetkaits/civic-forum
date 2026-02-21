<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap for SEO';

    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create()
            ->add(Url::create('/')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0))
            ->add(Url::create('/search')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.6));

        // Add categories
        Category::all()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(Url::create("/categories/{$category->slug}")
                ->setLastModificationDate($category->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8));
        });

        // Add published posts
        Post::published()
            ->orderBy('published_at', 'desc')
            ->each(function (Post $post) use ($sitemap) {
                $sitemap->add(Url::create("/posts/{$post->slug}")
                    ->setLastModificationDate($post->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.7));
            });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
        $this->info('Location: ' . public_path('sitemap.xml'));

        return Command::SUCCESS;
    }
}
