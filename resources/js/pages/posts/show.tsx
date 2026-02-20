import AppLayout from '@/layouts/AppLayout';
import VoteButtons from '@/components/vote-buttons';
import CommentSection from '@/components/comment-section';
import ImageGallery from '@/components/image-gallery';
import PostMeta from '@/components/post-meta';
import UserAvatar from '@/components/user-avatar';
import TableOfContents from '@/components/table-of-contents';
import { Head, Link, usePage } from '@inertiajs/react';
import { Post, GroupedComments, CommentCounts, PageProps } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { timeAgo, stripMarkdown, extractHeadings, slugify } from '@/lib/utils';
import { useTranslation } from 'react-i18next';
import { LinkIcon, Check, MessageSquare, HelpCircle } from 'lucide-react';
import { useMemo, useState } from 'react';
import ReactMarkdown, { Components } from 'react-markdown';
import remarkGfm from 'remark-gfm';

interface Props extends PageProps {
    post: Post;
    comments: GroupedComments;
    commentCounts: CommentCounts;
}

export default function PostShow({ post, comments, commentCounts }: Props) {
    const { auth, ziggy } = usePage<PageProps>().props;
    const canEdit = auth.user?.id === post.user_id || auth.user?.is_admin;
    const { t } = useTranslation();
    const [copied, setCopied] = useState(false);

    const baseUrl = ziggy.url;
    const postUrl = `${baseUrl}/posts/${post.slug}`;
    const plainBody = stripMarkdown(post.body);
    const description = plainBody.length > 200 ? plainBody.substring(0, 197) + '...' : plainBody;
    const ogImage = post.images?.[0]
        ? `${baseUrl}/storage/${post.images[0].image_path}`
        : undefined;
    const ogTitle = post.category
        ? `${post.title} — ${post.category.translated_name}`
        : post.title;
    const locationLabel = [post.city?.name, post.state?.name].filter(Boolean).join(', ');
    const ogDescription = locationLabel
        ? `${locationLabel} — ${description}`
        : description;

    const headings = useMemo(() => extractHeadings(post.body), [post.body]);

    // Custom renderers that add id attributes to headings for anchor links
    const markdownComponents = useMemo<Components>(() => {
        const makeHeading = (Tag: 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6') => {
            const Component = ({ children, ...props }: React.HTMLAttributes<HTMLHeadingElement>) => {
                const text = typeof children === 'string' ? children : String(children ?? '');
                const id = slugify(text);
                return <Tag id={id} {...props}>{children}</Tag>;
            };
            Component.displayName = Tag;
            return Component;
        };
        return {
            h1: makeHeading('h1'),
            h2: makeHeading('h2'),
            h3: makeHeading('h3'),
            h4: makeHeading('h4'),
            h5: makeHeading('h5'),
            h6: makeHeading('h6'),
        };
    }, []);

    return (
        <AppLayout>
            <Head title={`${post.title} — Civic Forum`}>
                <meta head-key="description" name="description" content={description} />
                <meta head-key="og:site_name" property="og:site_name" content="Civic Forum" />
                <meta head-key="og:title" property="og:title" content={ogTitle} />
                <meta head-key="og:description" property="og:description" content={ogDescription} />
                <meta head-key="og:type" property="og:type" content="article" />
                <meta head-key="og:url" property="og:url" content={postUrl} />
                {ogImage && <meta head-key="og:image" property="og:image" content={ogImage} />}
                {ogImage && <meta head-key="og:image:alt" property="og:image:alt" content={post.title} />}
                <meta head-key="twitter:card" name="twitter:card" content={ogImage ? 'summary_large_image' : 'summary'} />
                <meta head-key="twitter:title" name="twitter:title" content={ogTitle} />
                <meta head-key="twitter:description" name="twitter:description" content={ogDescription} />
                {ogImage && <meta head-key="twitter:image" name="twitter:image" content={ogImage} />}
                <meta head-key="article:published_time" property="article:published_time" content={post.published_at ?? post.created_at} />
                <meta head-key="article:author" property="article:author" content={post.user?.name ?? ''} />
                {post.category && <meta head-key="article:section" property="article:section" content={post.category.translated_name} />}
                <link rel="canonical" href={postUrl} />
            </Head>

            <div className="mx-auto max-w-6xl px-2 py-4 sm:px-6 sm:py-6 lg:px-8">
                <div className="flex gap-6">
                {/* Table of Contents — left sidebar, desktop only */}
                {headings.length > 0 && <TableOfContents headings={headings} />}

                {/* Main content column */}
                <div className="min-w-0 flex-1">
                <div className="overflow-hidden rounded-lg border bg-card">
                    <div className="flex gap-2 p-3 sm:gap-4 sm:p-6">
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
                            <div className="mb-2 flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-muted-foreground">
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

                            <h1 className="mb-3 text-lg sm:text-2xl font-bold text-foreground">
                                {post.title}
                            </h1>

                            <PostMeta post={post} />

                            {/* Post body */}
                            <div className="prose prose-sm sm:prose mt-4 max-w-none text-foreground prose-headings:text-foreground prose-a:text-primary prose-strong:text-foreground prose-code:text-foreground prose-pre:bg-muted prose-headings:scroll-mt-20">
                                <ReactMarkdown remarkPlugins={[remarkGfm]} components={markdownComponents}>
                                    {post.body}
                                </ReactMarkdown>
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

                            {/* Owner/admin actions */}
                            {canEdit && (
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

                {/* Comments — tabbed by type */}
                <div className="mt-6">
                    <h2 className="mb-4 text-lg font-semibold text-foreground">
                        {t('post.commentsHeading', { count: post.comment_count })}
                    </h2>
                    <Tabs defaultValue="discussion">
                        <TabsList className="w-full">
                            <TabsTrigger value="discussion" className="flex-1 gap-1.5">
                                <MessageSquare className="h-4 w-4" />
                                <span>{t('tabs.discussion')}</span>
                                {commentCounts.discussion > 0 && (
                                    <Badge variant="secondary" className="ml-1 h-5 min-w-5 px-1 text-xs">
                                        {commentCounts.discussion}
                                    </Badge>
                                )}
                            </TabsTrigger>
                            <TabsTrigger value="question" className="flex-1 gap-1.5">
                                <HelpCircle className="h-4 w-4" />
                                <span>{t('tabs.questions')}</span>
                                {commentCounts.question > 0 && (
                                    <Badge variant="secondary" className="ml-1 h-5 min-w-5 px-1 text-xs">
                                        {commentCounts.question}
                                    </Badge>
                                )}
                            </TabsTrigger>
                        </TabsList>
                        <TabsContent value="discussion">
                            <CommentSection comments={comments.discussion} postId={post.id} commentType="discussion" />
                        </TabsContent>
                        <TabsContent value="question">
                            <CommentSection comments={comments.question} postId={post.id} commentType="question" />
                        </TabsContent>
                    </Tabs>
                </div>
                </div>
                </div>
            </div>
        </AppLayout>
    );
}
