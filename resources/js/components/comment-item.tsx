import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { MessageSquare } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Comment } from '@/types';
import { timeAgo } from '@/lib/utils';
import VoteButtons from '@/components/vote-buttons';
import UserAvatar from '@/components/user-avatar';
import CommentForm from '@/components/comment-form';
import { useTranslation } from 'react-i18next';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

interface CommentItemProps {
    comment: Comment;
    postId: number;
    depth?: number;
}

const MAX_DEPTH = 3;

export default function CommentItem({
    comment,
    postId,
    depth = 0,
}: CommentItemProps) {
    const [showReplyForm, setShowReplyForm] = useState(false);
    const { t } = useTranslation();

    return (
        <div className={depth > 0 ? 'ml-3 sm:ml-6 border-l-2 border-muted pl-2 sm:pl-4' : ''}>
            <div className="flex gap-2 sm:gap-3 py-2">
                {/* Vote buttons */}
                <VoteButtons
                    votableType="comment"
                    votableId={comment.id}
                    voteCount={comment.vote_count}
                    userVote={comment.user_vote ?? null}
                    size="sm"
                />

                {/* Comment content */}
                <div className="flex-1 min-w-0">
                    {/* Comment header */}
                    <div className="flex items-center gap-2 text-xs text-muted-foreground">
                        {comment.user && (
                            <>
                                <UserAvatar user={comment.user} size="sm" />
                                <Link
                                    href={`/users/${comment.user.username}`}
                                    className="font-medium text-foreground hover:underline"
                                >
                                    {comment.user.username}
                                </Link>
                            </>
                        )}
                        <span>{timeAgo(comment.created_at)}</span>
                    </div>

                    {/* Comment body */}
                    <div className="prose prose-sm mt-1 max-w-none text-foreground prose-headings:text-foreground prose-a:text-primary prose-strong:text-foreground prose-code:text-foreground prose-pre:bg-muted prose-p:my-1">
                        <ReactMarkdown remarkPlugins={[remarkGfm]}>
                            {comment.body}
                        </ReactMarkdown>
                    </div>

                    {/* Comment actions */}
                    <div className="mt-1 flex items-center gap-2">
                        {depth < MAX_DEPTH && (
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-6 gap-1 px-2 text-xs text-muted-foreground hover:text-foreground"
                                onClick={() => setShowReplyForm(!showReplyForm)}
                            >
                                <MessageSquare className="h-3 w-3" />
                                {t('comments.reply')}
                            </Button>
                        )}
                    </div>

                    {/* Inline reply form */}
                    {showReplyForm && (
                        <div className="mt-2">
                            <CommentForm
                                postId={postId}
                                parentId={comment.id}
                                placeholder={t('comments.replyTo', { username: comment.user?.username ?? 'user' })}
                                onSuccess={() => setShowReplyForm(false)}
                            />
                        </div>
                    )}
                </div>
            </div>

            {/* Recursive replies */}
            {comment.replies && comment.replies.length > 0 && (
                <div className="mt-1">
                    {comment.replies.map((reply) => (
                        <CommentItem
                            key={reply.id}
                            comment={reply}
                            postId={postId}
                            depth={depth + 1}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
