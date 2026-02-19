import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { useTranslation } from 'react-i18next';

interface CommentFormProps {
    postId: number;
    parentId: number | null;
    onSuccess?: () => void;
    placeholder?: string;
}

export default function CommentForm({
    postId,
    parentId,
    onSuccess,
    placeholder,
}: CommentFormProps) {
    const { t } = useTranslation();
    const { data, setData, post, processing, reset, errors } = useForm({
        body: '',
        parent_id: parentId,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/posts/${postId}/comments`, {
            preserveScroll: true,
            onSuccess: () => {
                reset('body');
                onSuccess?.();
            },
        });
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-2">
            <Textarea
                value={data.body}
                onChange={(e) => setData('body', e.target.value)}
                placeholder={placeholder || t('comments.writeComment')}
                rows={3}
                className="resize-none"
            />
            {errors.body && (
                <p className="text-sm text-destructive">{errors.body}</p>
            )}
            <div className="flex justify-end">
                <Button
                    type="submit"
                    size="sm"
                    disabled={processing || !data.body.trim()}
                    className=""
                >
                    {processing ? t('comments.posting') : parentId ? t('comments.replyButton') : t('comments.commentButton')}
                </Button>
            </div>
        </form>
    );
}
