import { usePage } from '@inertiajs/react';
import { Comment, PageProps } from '@/types';
import CommentItem from '@/components/comment-item';
import CommentForm from '@/components/comment-form';
import { useTranslation } from 'react-i18next';
import { useLoginDialog } from '@/components/login-dialog';

interface CommentSectionProps {
    comments: Comment[];
    postId: number;
}

export default function CommentSection({
    comments,
    postId,
}: CommentSectionProps) {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;
    const { t } = useTranslation();
    const { open: openLoginDialog } = useLoginDialog();

    const topLevelComments = comments.filter(
        (comment) => !comment.parent_id,
    );

    return (
        <div className="space-y-4">
            <h3 className="text-lg font-semibold">
                {t('comments.heading', { count: comments.length })}
            </h3>

            {/* Add a comment form for authenticated users */}
            {user ? (
                <div className="rounded-lg border bg-card p-4">
                    <p className="mb-2 text-sm font-medium text-muted-foreground">
                        {t('comments.addComment')}
                    </p>
                    <CommentForm
                        postId={postId}
                        parentId={null}
                        placeholder={t('comments.placeholder')}
                    />
                </div>
            ) : (
                <div className="rounded-lg border bg-muted/30 p-4 text-center text-sm text-muted-foreground">
                    <button
                        type="button"
                        onClick={openLoginDialog}
                        className="font-medium text-foreground underline-offset-4 hover:underline"
                    >
                        {t('comments.loginLink')}
                    </button>{' '}
                    {t('comments.loginPrompt')}
                </div>
            )}

            {/* Comment list */}
            {topLevelComments.length > 0 ? (
                <div className="space-y-1">
                    {topLevelComments.map((comment) => (
                        <CommentItem
                            key={comment.id}
                            comment={comment}
                            postId={postId}
                        />
                    ))}
                </div>
            ) : (
                <p className="py-8 text-center text-sm text-muted-foreground">
                    {t('comments.noComments')}
                </p>
            )}
        </div>
    );
}
