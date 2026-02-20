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
import { LinkIcon, Check } from 'lucide-react';
import { useState } from 'react';

interface Props extends PageProps {
    post: Post;
    comments: Comment[];
}

export default function PostShow({ post, comments }: Props) {
    const { auth, ziggy } = usePage<PageProps>().props;
    const isOwner = auth.user?.id === post.user_id;
    const { t } = useTranslation();
    const [copied, setCopied] = useState(false);

    const baseUrl = ziggy.url;
    const postUrl = `${baseUrl}/posts/${post.slug}`;
    const description = post.body.substring(0, 160);
    const ogImage = post.images?.[0]
        ? `${baseUrl}/storage/${post.images[0].image_path}`
        : undefined;

    return (
        <AppLayout>
            <Head title={`${post.title} — Civic Forum`}>
                <meta head-key="description" name="description" content={description} />
                <meta head-key="og:title" property="og:title" content={post.title} />
                <meta head-key="og:description" property="og:description" content={description} />
                <meta head-key="og:type" property="og:type" content="article" />
                <meta head-key="og:url" property="og:url" content={postUrl} />
                {ogImage && <meta head-key="og:image" property="og:image" content={ogImage} />}
                <meta head-key="twitter:card" name="twitter:card" content={ogImage ? 'summary_large_image' : 'summary'} />
                <meta head-key="twitter:title" name="twitter:title" content={post.title} />
                <meta head-key="twitter:description" name="twitter:description" content={description} />
                {ogImage && <meta head-key="twitter:image" name="twitter:image" content={ogImage} />}
                <link rel="canonical" href={postUrl} />
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

                            {/* Share */}
                            <div className="mt-4 flex items-center gap-4 border-t pt-4">
                                <button
                                    onClick={() => {
                                        navigator.clipboard.writeText(postUrl).then(() => {
                                            setCopied(true);
                                            setTimeout(() => setCopied(false), 2000);
                                        });
                                    }}
                                    className="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors"
                                >
                                    {copied ? (
                                        <>
                                            <Check className="h-4 w-4 text-green-500" />
                                            {t('common.linkCopied')}
                                        </>
                                    ) : (
                                        <>
                                            <LinkIcon className="h-4 w-4" />
                                            {t('common.copyLink')}
                                        </>
                                    )}
                                </button>
                            </div>

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
