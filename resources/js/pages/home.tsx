import AppLayout from '@/layouts/AppLayout';
import PostCard from '@/components/post-card';
import FilterSidebar from '@/components/filter-sidebar';
import { Head, Link } from '@inertiajs/react';
import { PaginatedData, Post, PageProps } from '@/types';
import { useTranslation } from 'react-i18next';

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

    return (
        <AppLayout>
            <Head title={t('home.title')}>
                <meta head-key="description" name="description" content={t('home.metaDescription')} />
                <meta head-key="og:title" property="og:title" content={t('home.title')} />
                <meta head-key="og:description" property="og:description" content={t('home.metaOgDescription')} />
            </Head>

            <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div className="flex flex-col gap-6 lg:flex-row">
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

                        {posts.data.length === 0 ? (
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
                                {posts.data.map((post) => (
                                    <PostCard key={post.id} post={post} />
                                ))}
                            </div>
                        )}

                        {/* Pagination */}
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
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
