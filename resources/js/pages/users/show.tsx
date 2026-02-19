import AppLayout from '@/layouts/AppLayout';
import PostCard from '@/components/post-card';
import UserAvatar from '@/components/user-avatar';
import { Head, Link } from '@inertiajs/react';
import { PaginatedData, Post, User, Comment, PageProps } from '@/types';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { timeAgo } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

interface Props extends PageProps {
    profileUser: User;
    posts: PaginatedData<Post>;
    comments: Comment[];
}

export default function UserShow({ profileUser, posts, comments }: Props) {
    const { t } = useTranslation();

    return (
        <AppLayout>
            <Head title={`${profileUser.username} — Civic Forum`} />

            <div className="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                {/* Profile header */}
                <div className="mb-6 rounded-lg border bg-card p-6">
                    <div className="flex items-start gap-4">
                        <UserAvatar user={profileUser} size="lg" />
                        <div>
                            <h1 className="text-xl font-bold text-foreground">
                                {profileUser.name}
                            </h1>
                            <p className="text-sm text-muted-foreground">@{profileUser.username}</p>
                            {profileUser.bio && (
                                <p className="mt-2 text-sm text-foreground">{profileUser.bio}</p>
                            )}
                            <div className="mt-2 flex gap-4 text-sm text-muted-foreground">
                                <span>{t('user.reputation', { count: profileUser.reputation })}</span>
                                {profileUser.state && (
                                    <span>
                                        <Link
                                            href={`/states/${profileUser.state.code}`}
                                            className="hover:underline"
                                        >
                                            {profileUser.state.name}
                                        </Link>
                                        {profileUser.city && `, ${profileUser.city.name}`}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Tabs */}
                <Tabs defaultValue="posts">
                    <TabsList>
                        <TabsTrigger value="posts">
                            {t('user.postsTab', { count: posts.total })}
                        </TabsTrigger>
                        <TabsTrigger value="comments">
                            {t('user.commentsTab', { count: comments.length })}
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="posts" className="mt-4">
                        {posts.data.length === 0 ? (
                            <div className="rounded-lg border bg-card p-8 text-center">
                                <p className="text-muted-foreground">{t('user.noPosts')}</p>
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
                    </TabsContent>

                    <TabsContent value="comments" className="mt-4">
                        {comments.length === 0 ? (
                            <div className="rounded-lg border bg-card p-8 text-center">
                                <p className="text-muted-foreground">{t('user.noComments')}</p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {comments.map((comment) => (
                                    <div key={comment.id} className="rounded-lg border bg-card p-4">
                                        <div className="mb-2 text-xs text-muted-foreground">
                                            {timeAgo(comment.created_at)} &middot;{' '}
                                            <Link
                                                href={`/posts/${comment.post_id}`}
                                                className="font-medium text-foreground underline-offset-4 hover:underline"
                                            >
                                                {t('user.viewPost')}
                                            </Link>
                                        </div>
                                        <p className="text-sm text-foreground">{comment.body}</p>
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
