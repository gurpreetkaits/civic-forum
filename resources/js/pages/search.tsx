import AppLayout from '@/layouts/AppLayout';
import PostCard from '@/components/post-card';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { PaginatedData, Post, PageProps } from '@/types';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { FormEvent, useState } from 'react';
import { useTranslation } from 'react-i18next';

interface Props extends PageProps {
    posts?: PaginatedData<Post>;
    query: string;
}

export default function Search({ posts, query }: Props) {
    const [searchTerm, setSearchTerm] = useState(query || '');
    const { t } = useTranslation();
    const { ziggy } = usePage<PageProps>().props;
    const pageUrl = query ? `${ziggy.url}/search?q=${encodeURIComponent(query)}` : `${ziggy.url}/search`;

    function handleSearch(e: FormEvent) {
        e.preventDefault();
        if (searchTerm.trim()) {
            router.get('/search', { q: searchTerm.trim() }, { preserveState: true });
        }
    }

    return (
        <AppLayout>
            <Head title={query ? t('search.titleWithQuery', { query }) : t('search.titleDefault')}>
                <meta head-key="og:title" property="og:title" content={query ? t('search.titleWithQuery', { query }) : t('search.titleDefault')} />
                <meta head-key="og:url" property="og:url" content={pageUrl} />
                <link rel="canonical" href={pageUrl} />
            </Head>

            <div className="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 className="mb-6 text-2xl font-bold text-foreground">{t('search.title')}</h1>

                <form onSubmit={handleSearch} className="mb-6 flex gap-2">
                    <Input
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        placeholder={t('search.placeholder')}
                        className="flex-1"
                    />
                    <Button type="submit">
                        {t('search.button')}
                    </Button>
                </form>

                {query && posts && (
                    <>
                        <p className="mb-4 text-sm text-muted-foreground">
                            {t('search.resultsCount', { count: posts.total, query })}
                        </p>

                        {posts.data.length === 0 ? (
                            <div className="rounded-lg border bg-card p-8 text-center">
                                <p className="text-muted-foreground">{t('search.noResults')}</p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {posts.data.map((post) => (
                                    <PostCard key={post.id} post={post} />
                                ))}
                            </div>
                        )}

                        {posts.last_page > 1 && (
                            <div className="mt-6 flex items-center justify-center gap-2">
                                {posts.links.map((link, i) => (
                                    <Link
                                        key={i}
                                        href={link.url || '#'}
                                        className={`rounded px-3 py-1 text-sm ${
                                            link.active
                                                ? 'bg-primary text-primary-foreground'
                                                : link.url
                                                  ? 'bg-card text-foreground hover:bg-accent'
                                                  : 'cursor-default text-muted-foreground'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        preserveScroll
                                    />
                                ))}
                            </div>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
