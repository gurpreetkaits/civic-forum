import AppLayout from '@/layouts/AppLayout';
import PostCard from '@/components/post-card';
import UserAvatar from '@/components/user-avatar';
import { Head, Link, usePage } from '@inertiajs/react';
import { PaginatedData, Post, User, Comment, PageProps } from '@/types';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { timeAgo } from '@/lib/utils';
import { useTranslation } from 'react-i18next';
import { useInfiniteScroll } from '@/hooks/use-infinite-scroll';
import { CalendarDays, Loader2, MapPin, MessageSquare, Settings, Star } from 'lucide-react';

interface ProfileComment extends Comment {
    post?: {
        id: number;
        title: string;
        slug: string;
    };
}

interface Props extends PageProps {
    profileUser: User & { created_at?: string };
    posts: PaginatedData<Post>;
    comments: PaginatedData<ProfileComment>;
}

export default function UserShow({ profileUser, posts, comments }: Props) {
    const { t } = useTranslation();
    const { auth, ziggy } = usePage<PageProps>().props;
    const pageUrl = `${ziggy.url}/users/${profileUser.username}`;
    const description = profileUser.bio || `${profileUser.name} on Civic Forum`;
    const { items, sentinelRef, loading } = useInfiniteScroll(posts);
    const isOwnProfile = auth.user?.id === profileUser.id;

    return (
        <AppLayout>
            <Head title={`${profileUser.username} — Civic Forum`}>
                <meta head-key="description" name="description" content={description} />
                <meta head-key="og:title" property="og:title" content={`${profileUser.username} — Civic Forum`} />
                <meta head-key="og:description" property="og:description" content={description} />
                <meta head-key="og:url" property="og:url" content={pageUrl} />
                <link rel="canonical" href={pageUrl} />
            </Head>

            <div className="mx-auto max-w-4xl px-2 py-4 sm:px-6 sm:py-6 lg:px-8">
                {/* Profile header */}
                <div className="rounded-lg border bg-card">
                    {/* Cover area */}
                    <div className="h-24 rounded-t-lg bg-gradient-to-r from-primary/20 via-primary/10 to-primary/5 sm:h-32" />

                    {/* Avatar + info */}
                    <div className="px-4 pb-4 sm:px-6 sm:pb-6">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div className="flex items-end gap-3 sm:gap-4">
                                <div className="-mt-8 rounded-full border-4 border-card bg-card sm:-mt-12">
                                    <UserAvatar user={profileUser} size="lg" />
                                </div>
                                <div className="min-w-0 pb-1">
                                    <h1 className="text-lg sm:text-xl font-bold text-foreground truncate">
                                        {profileUser.name}
                                    </h1>
                                    <p className="text-xs sm:text-sm text-muted-foreground">@{profileUser.username}</p>
                                </div>
                            </div>

                            {isOwnProfile && (
                                <Link href="/settings/profile">
                                    <Button variant="outline" size="sm" className="gap-1.5">
                                        <Settings className="h-3.5 w-3.5" />
                                        {t('nav.settings')}
                                    </Button>
                                </Link>
                            )}
                        </div>

                        {profileUser.bio && (
                            <p className="mt-4 text-sm text-foreground">{profileUser.bio}</p>
                        )}

                        <div className="mt-3 sm:mt-4 flex flex-wrap gap-x-4 gap-y-1.5 sm:gap-x-5 sm:gap-y-2 text-xs sm:text-sm text-muted-foreground">
                            <span className="flex items-center gap-1.5">
                                <Star className="h-4 w-4" />
                                {t('user.reputation', { count: profileUser.reputation })}
                            </span>
                            {profileUser.state && (
                                <span className="flex items-center gap-1.5">
                                    <MapPin className="h-4 w-4" />
                                    <Link
                                        href={`/states/${profileUser.state.code}`}
                                        className="hover:underline"
                                    >
                                        {profileUser.state.name}
                                    </Link>
                                    {profileUser.city && `, ${profileUser.city.name}`}
                                </span>
                            )}
                            {profileUser.created_at && (
                                <span className="flex items-center gap-1.5">
                                    <CalendarDays className="h-4 w-4" />
                                    {t('user.joined', { date: new Date(profileUser.created_at).toLocaleDateString(undefined, { month: 'long', year: 'numeric' }) })}
                                </span>
                            )}
                        </div>

                        {/* Stats row */}
                        <div className="mt-3 sm:mt-4 flex gap-4 sm:gap-6">
                            <div className="text-center">
                                <p className="text-lg font-semibold text-foreground">{posts.total}</p>
                                <p className="text-xs text-muted-foreground">{t('user.postsLabel')}</p>
                            </div>
                            <div className="text-center">
                                <p className="text-lg font-semibold text-foreground">{comments.total}</p>
                                <p className="text-xs text-muted-foreground">{t('user.commentsLabel')}</p>
                            </div>
                            <div className="text-center">
                                <p className="text-lg font-semibold text-foreground">{profileUser.reputation}</p>
                                <p className="text-xs text-muted-foreground">{t('user.reputationLabel')}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Tabs */}
                <Tabs defaultValue="posts" className="mt-6">
                    <TabsList className="w-full justify-start">
                        <TabsTrigger value="posts" className="gap-1.5">
                            {t('user.postsTab', { count: posts.total })}
                        </TabsTrigger>
                        <TabsTrigger value="comments" className="gap-1.5">
                            <MessageSquare className="h-3.5 w-3.5" />
                            {t('user.commentsTab', { count: comments.total })}
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="posts" className="mt-4">
                        {items.length === 0 ? (
                            <div className="rounded-lg border bg-card p-8 text-center">
                                <p className="text-muted-foreground">{t('user.noPosts')}</p>
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
                    </TabsContent>

                    <TabsContent value="comments" className="mt-4">
                        {comments.data.length === 0 ? (
                            <div className="rounded-lg border bg-card p-8 text-center">
                                <p className="text-muted-foreground">{t('user.noComments')}</p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {comments.data.map((comment) => (
                                    <div key={comment.id} className="rounded-lg border bg-card p-4">
                                        {comment.post && (
                                            <div className="mb-2 flex items-center gap-2 text-xs text-muted-foreground">
                                                <MessageSquare className="h-3 w-3" />
                                                <span>{t('user.commentedOn')}</span>
                                                <Link
                                                    href={`/posts/${comment.post.slug}`}
                                                    className="font-medium text-foreground hover:underline"
                                                >
                                                    {comment.post.title}
                                                </Link>
                                            </div>
                                        )}
                                        <p className="text-sm text-foreground">{comment.body}</p>
                                        <p className="mt-2 text-xs text-muted-foreground">
                                            {timeAgo(comment.created_at)}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        )}
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}
