import AppLayout from '@/layouts/AppLayout';
import PostCard from '@/components/post-card';
import { Head, Link, usePage } from '@inertiajs/react';
import { PaginatedData, Post, State, City, PageProps } from '@/types';
import { useTranslation } from 'react-i18next';

interface Props extends PageProps {
    state: State;
    city: City;
    posts: PaginatedData<Post>;
}

export default function CityShow({ state, city, posts }: Props) {
    const { t } = useTranslation();
    const { ziggy } = usePage<PageProps>().props;
    const pageUrl = `${ziggy.url}/states/${state.code}/${city.id}`;
    const description = t('location.civicIssuesCity', { city: city.name, state: state.name });

    return (
        <AppLayout
            header={
                <div>
                    <div className="mb-1 text-sm text-muted-foreground">
                        <Link href={`/states/${state.code}`} className="hover:underline">
                            {state.name}
                        </Link>
                        {' / '}
                    </div>
                    <h1 className="text-2xl font-bold text-foreground">{city.name}</h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {t('location.civicIssuesCity', { city: city.name, state: state.name })}
                    </p>
                </div>
            }
        >
            <Head title={`${city.name}, ${state.name} — Civic Forum`}>
                <meta head-key="description" name="description" content={description} />
                <meta head-key="og:title" property="og:title" content={`${city.name}, ${state.name} — Civic Forum`} />
                <meta head-key="og:description" property="og:description" content={description} />
                <meta head-key="og:url" property="og:url" content={pageUrl} />
                <link rel="canonical" href={pageUrl} />
            </Head>

            <div className="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                {posts.data.length === 0 ? (
                    <div className="rounded-lg border bg-card p-8 text-center">
                        <p className="text-muted-foreground">{t('location.noPostsCity', { name: city.name })}</p>
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
        </AppLayout>
    );
}
