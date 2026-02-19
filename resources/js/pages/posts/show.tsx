import AppLayout from '@/layouts/AppLayout';
import VoteButtons from '@/components/vote-buttons';
import CommentSection from '@/components/comment-section';
import ImageGallery from '@/components/image-gallery';
import PostMeta from '@/components/post-meta';
import UserAvatar from '@/components/user-avatar';
import { Head, Link, usePage } from '@inertiajs/react';
import { Post, Comment, PageProps } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { timeAgo } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

interface Props extends PageProps {
    post: Post;
    comments: Comment[];
}

export default function PostShow({ post, comments }: Props) {
    const { auth } = usePage<PageProps>().props;
    const isOwner = auth.user?.id === post.user_id;
    const { t } = useTranslation();

    return (
        <AppLayout>
            <Head title={`${post.title} — Civic Forum`}>
                <meta head-key="description" name="description" content={post.body.substring(0, 160)} />
                <meta head-key="og:title" property="og:title" content={post.title} />
                <meta head-key="og:description" property="og:description" content={post.body.substring(0, 160)} />
                <meta head-key="og:type" property="og:type" content="article" />
                <link rel="canonical" href={`/posts/${post.slug}`} />
            </Head>

            <div className="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                <div className="overflow-hidden rounded-lg border bg-card">
                    <div className="flex gap-4 p-6">
                        {/* Vote column */}
                        <div className="shrink-0">
                            <VoteButtons
                                votableType="post"
                                votableId={post.id}
                                voteCount={post.vote_count}
                                userVote={post.user_vote ?? null}
                            />
                        </div>

                        {/* Content */}
                        <div className="min-w-0 flex-1">
                            <div className="mb-2 flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                                <div className="flex items-center gap-2">
                                    {post.user && <UserAvatar user={post.user} size="sm" />}
                                    <Link
                                        href={`/users/${post.user?.username}`}
                                        className="font-medium text-foreground hover:underline"
                                    >
                                        {post.user?.username}
                                    </Link>
                                </div>
                                <span>&middot;</span>
                                <span>{timeAgo(post.created_at)}</span>
                                <span>&middot;</span>
                                <span>{t('post.views', { count: post.view_count })}</span>
                            </div>

                            <h1 className="mb-3 text-2xl font-bold text-foreground">
                                {post.title}
                            </h1>

                            <PostMeta post={post} />

                            {/* Post body */}
                            <div className="prose mt-4 max-w-none text-foreground whitespace-pre-wrap">
                                {post.body}
                            </div>

                            {/* Images */}
                            {post.images && post.images.length > 0 && (
                                <div className="mt-4">
                                    <ImageGallery images={post.images} />
                                </div>
                            )}

                            {/* Tags */}
                            {post.tags && post.tags.length > 0 && (
                                <div className="mt-4 flex flex-wrap gap-2">
                                    {post.tags.map((tag) => (
                                        <Badge key={tag.id} variant="outline" className="text-xs">
                                            #{tag.name}
                                        </Badge>
                                    ))}
                                </div>
                            )}

                            {/* Owner actions */}
                            {isOwner && (
                                <div className="mt-4 flex gap-2 border-t pt-4">
                                    <Link href={`/posts/${post.slug}/edit`}>
                                        <Button variant="outline" size="sm">
                                            {t('post.edit')}
                                        </Button>
                                    </Link>
                                    <Link
                                        href={`/posts/${post.slug}`}
                                        method="delete"
                                        as="button"
                                        onBefore={() => confirm(t('post.deleteConfirm'))}
                                    >
                                        <Button variant="outline" size="sm" className="text-destructive">
                                            {t('post.delete')}
                                        </Button>
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Comments */}
                <div className="mt-6">
                    <h2 className="mb-4 text-lg font-semibold text-foreground">
                        {t('post.commentsHeading', { count: post.comment_count })}
                    </h2>
                    <CommentSection comments={comments} postId={post.id} />
                </div>
            </div>
        </AppLayout>
    );
}
