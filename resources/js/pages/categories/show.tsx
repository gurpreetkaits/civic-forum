import AppLayout from '@/layouts/AppLayout';
import PostCard from '@/components/post-card';
import FilterSidebar from '@/components/filter-sidebar';
import { Head, Link, usePage } from '@inertiajs/react';
import { PaginatedData, Post, Category, PageProps } from '@/types';
import { useTranslation } from 'react-i18next';

interface Props extends PageProps {
    category: Category;
    posts: PaginatedData<Post>;
    filters: {
        state_id?: string;
        sort?: string;
    };
}

export default function CategoryShow({ category, posts, filters }: Props) {
    const { t } = useTranslation();
    const { ziggy } = usePage<PageProps>().props;
    const pageUrl = `${ziggy.url}/categories/${category.slug}`;

    return (
        <AppLayout
            header={
                <div>
                    <h1 className="text-2xl font-bold text-foreground">{category.translated_name}</h1>
                    {category.translated_description && (
                        <p className="mt-1 text-sm text-muted-foreground">{category.translated_description}</p>
                    )}
                </div>
            }
        >
            <Head title={`${category.translated_name} — Civic Forum`}>
                <meta head-key="description" name="description" content={category.translated_description} />
                <meta head-key="og:title" property="og:title" content={`${category.translated_name} — Civic Forum`} />
                <meta head-key="og:description" property="og:description" content={category.translated_description} />
                <meta head-key="og:url" property="og:url" content={pageUrl} />
                <link rel="canonical" href={pageUrl} />
            </Head>

            <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div className="flex flex-col gap-6 lg:flex-row">
                    <aside className="w-full shrink-0 lg:w-64">
                        <FilterSidebar
                            currentFilters={{ ...filters, category_slug: category.slug }}
                            baseUrl={`/categories/${category.slug}`}
                        />
                    </aside>

                    <div className="flex-1">
                        {posts.data.length === 0 ? (
                            <div className="rounded-lg border bg-card p-8 text-center">
                                <p className="text-muted-foreground">{t('location.noPostsCategory', { name: category.translated_name })}</p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {posts.data.map((post) => (
                                    <PostCard key={post.id} post={post} showCategory={false} />
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
