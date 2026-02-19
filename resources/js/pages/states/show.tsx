import AppLayout from '@/layouts/AppLayout';
import PostCard from '@/components/post-card';
import FilterSidebar from '@/components/filter-sidebar';
import { Head, Link, usePage } from '@inertiajs/react';
import { PaginatedData, Post, State, PageProps } from '@/types';
import { useTranslation } from 'react-i18next';

interface Props extends PageProps {
    state: State;
    posts: PaginatedData<Post>;
    filters: {
        category?: string;
        sort?: string;
    };
}

export default function StateShow({ state, posts, filters }: Props) {
    const { t } = useTranslation();
    const { ziggy } = usePage<PageProps>().props;
    const pageUrl = `${ziggy.url}/states/${state.code}`;
    const description = t('location.civicIssuesIn', { name: state.name, code: state.code });

    return (
        <AppLayout
            header={
                <div>
                    <h1 className="text-2xl font-bold text-foreground">{state.name}</h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {t('location.civicIssuesIn', { name: state.name, code: state.code })}
                        {state.type === 'ut' && t('location.unionTerritory')}
                    </p>
                </div>
            }
        >
            <Head title={`${state.name} Civic Issues — Civic Forum`}>
                <meta head-key="description" name="description" content={description} />
                <meta head-key="og:title" property="og:title" content={`${state.name} Civic Issues — Civic Forum`} />
                <meta head-key="og:description" property="og:description" content={description} />
                <meta head-key="og:url" property="og:url" content={pageUrl} />
                <link rel="canonical" href={pageUrl} />
            </Head>

            <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div className="flex flex-col gap-6 lg:flex-row">
                    <aside className="w-full shrink-0 lg:w-64">
                        <FilterSidebar
                            currentFilters={{ ...filters, state_id: String(state.id) }}
                            baseUrl={`/states/${state.code}`}
                        />
                    </aside>

                    <div className="flex-1">
                        {posts.data.length === 0 ? (
                            <div className="rounded-lg border bg-card p-8 text-center">
                                <p className="text-muted-foreground">{t('location.noPostsState', { name: state.name })}</p>
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
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
