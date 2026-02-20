import AppLayout from '@/layouts/AppLayout';
import PostCard from '@/components/post-card';
import FilterSidebar from '@/components/filter-sidebar';
import { Head, Link, usePage } from '@inertiajs/react';
import { PaginatedData, Post, PageProps } from '@/types';
import { useTranslation } from 'react-i18next';
import { useInfiniteScroll } from '@/hooks/use-infinite-scroll';
import { Loader2 } from 'lucide-react';

interface Props extends PageProps {
    posts: PaginatedData<Post>;
    filters: {
        category?: string;
        state_id?: string;
        sort?: string;
    };
}

export default function Home({ posts, filters }: Props) {
    const { t } = useTranslation();
    const { ziggy } = usePage<PageProps>().props;
    const { items, sentinelRef, loading, hasMore } = useInfiniteScroll(posts);

    return (
        <AppLayout>
            <Head title={t('home.title')}>
                <meta head-key="description" name="description" content={t('home.metaDescription')} />
                <meta head-key="og:title" property="og:title" content={t('home.title')} />
                <meta head-key="og:description" property="og:description" content={t('home.metaOgDescription')} />
                <meta head-key="og:url" property="og:url" content={ziggy.url} />
                <link rel="canonical" href={ziggy.url} />
            </Head>

            {/* Purpose statement */}
            <div className="mx-auto max-w-7xl px-2 pt-4 sm:px-6 sm:pt-6 lg:px-8">
                <div className="rounded-lg border bg-card p-3 sm:p-5 text-center">
                    <h2 className="text-sm sm:text-base font-semibold text-foreground">
                        {t('purpose.heading')}
                    </h2>
                    <p className="mt-1.5 sm:mt-2 text-xs sm:text-sm leading-relaxed text-muted-foreground">
                        {t('purpose.description')}
                    </p>
                </div>
            </div>

            <div className="mx-auto max-w-7xl px-2 py-4 sm:px-6 sm:py-6 lg:px-8">
                <div className="flex flex-col gap-4 sm:gap-6 lg:flex-row">
                    {/* Sidebar */}
                    <aside className="w-full shrink-0 lg:w-64">
                        <FilterSidebar currentFilters={filters} baseUrl="/" />
                    </aside>

                    {/* Main Feed */}
                    <div className="flex-1">
                        <div className="mb-4 flex items-center justify-between">
                            <h1 className="text-lg font-semibold text-foreground">
                                {filters.category
                                    ? t('home.filteredHeading')
                                    : t('home.trendingHeading')}
                            </h1>
                        </div>

                        {items.length === 0 ? (
                            <div className="rounded-lg border bg-card p-8 text-center">
                                <p className="text-muted-foreground">{t('home.noPosts')}</p>
                                <Link
                                    href="/posts/create"
                                    className="mt-2 inline-block text-sm font-medium text-foreground underline-offset-4 hover:underline"
                                >
                                    {t('home.beFirst')}
                                </Link>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {items.map((post) => (
                                    <PostCard key={post.id} post={post} />
                                ))}
                            </div>
                        )}

                        <div ref={sentinelRef} className="py-4">
                            {loading && (
                                <div className="flex justify-center">
                                    <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
