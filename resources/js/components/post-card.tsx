import { Link } from '@inertiajs/react';
import { MessageSquare, MapPin, Clock, Eye } from 'lucide-react';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Post } from '@/types';
import { timeAgo } from '@/lib/utils';
import VoteButtons from '@/components/vote-buttons';
import UserAvatar from '@/components/user-avatar';
import { useTranslation } from 'react-i18next';

interface PostCardProps {
    post: Post;
    showCategory?: boolean;
}

export default function PostCard({ post, showCategory = true }: PostCardProps) {
    const { t } = useTranslation();

    const bodyExcerpt =
        post.body.length > 150 ? post.body.slice(0, 150) + '...' : post.body;

    const locationParts: string[] = [];
    if (post.city?.name) {
        locationParts.push(post.city.name);
    }
    if (post.state?.code) {
        locationParts.push(post.state.code);
    }
    const locationText = locationParts.join(', ');

    const thumbnail = post.images?.[0];

    return (
        <Card className="flex gap-0 p-0 overflow-hidden">
            {/* Vote column */}
            <div className="flex flex-col items-center justify-start bg-muted/30 px-2 py-3">
                <VoteButtons
                    votableType="post"
                    votableId={post.id}
                    voteCount={post.vote_count}
                    userVote={post.user_vote ?? null}
                    size="sm"
                />
            </div>

            {/* Content column */}
            <div className="flex flex-1 gap-3 py-3 pr-4">
                <div className="flex flex-1 flex-col gap-1.5 min-w-0">
                    {/* Meta line */}
                    <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                        {showCategory && post.category && (
                            <Link href={`/categories/${post.category.slug}`}>
                                <Badge
                                    variant="secondary"
                                    className="text-[10px] px-1.5 py-0 hover:bg-secondary/60"
                                >
                                    {post.category.translated_name}
                                </Badge>
                            </Link>
                        )}

                        {post.user && (
                            <span className="flex items-center gap-1">
                                <UserAvatar user={post.user} size="sm" />
                                <Link
                                    href={`/users/${post.user.username}`}
                                    className="font-medium hover:underline"
                                >
                                    {post.user.username}
                                </Link>
                            </span>
                        )}

                        {locationText && (
                            <span className="flex items-center gap-0.5">
                                <MapPin className="h-3 w-3" />
                                {locationText}
                            </span>
                        )}

                        <span className="flex items-center gap-0.5">
                            <Clock className="h-3 w-3" />
                            {timeAgo(post.created_at)}
                        </span>
                    </div>

                    {/* Title */}
                    <Link
                        href={`/posts/${post.slug}`}
                        className="text-base font-semibold leading-tight text-foreground hover:underline"
                    >
                        {post.title}
                    </Link>

                    {/* Body excerpt */}
                    <p className="text-sm text-muted-foreground leading-snug line-clamp-2">
                        {bodyExcerpt}
                    </p>

                    {/* Footer actions */}
                    <div className="mt-1 flex items-center gap-4 text-xs text-muted-foreground">
                        <Link
                            href={`/posts/${post.slug}`}
                            className="flex items-center gap-1 font-medium hover:text-foreground"
                        >
                            <MessageSquare className="h-3.5 w-3.5" />
                            {post.comment_count}{' '}
                            {post.comment_count === 1 ? t('common.comment') : t('common.comments')}
                        </Link>
                        <span className="flex items-center gap-1">
                            <Eye className="h-3.5 w-3.5" />
                            {post.view_count}
                        </span>
                    </div>
                </div>

                {/* Thumbnail */}
                {thumbnail && (
                    <Link href={`/posts/${post.slug}`} className="hidden shrink-0 sm:block">
                        <img
                            src={`/storage/${thumbnail.thumbnail_path ?? thumbnail.image_path}`}
                            alt=""
                            className="h-24 w-32 rounded-md object-cover"
                        />
                    </Link>
                )}
            </div>
        </Card>
    );
}
