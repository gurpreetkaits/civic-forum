import { Link } from '@inertiajs/react';
import { MapPin, Clock } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Post } from '@/types';
import { timeAgo } from '@/lib/utils';

interface PostMetaProps {
    post: Post;
}

export default function PostMeta({ post }: PostMetaProps) {
    const locationParts: string[] = [];
    if (post.city?.name) {
        locationParts.push(post.city.name);
    }
    if (post.state?.name) {
        locationParts.push(post.state.name);
    }
    const locationText = locationParts.join(', ');

    return (
        <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
            {post.category && (
                <Link href={`/categories/${post.category.slug}`}>
                    <Badge variant="secondary" className="hover:bg-secondary/60">
                        {post.category.translated_name}
                    </Badge>
                </Link>
            )}

            {locationText && (
                <Link
                    href={post.state ? `/?state_id=${post.state.id}` : '#'}
                    className="flex items-center gap-1 hover:text-foreground"
                >
                    <MapPin className="h-3 w-3" />
                    <span>{locationText}</span>
                </Link>
            )}

            <span className="flex items-center gap-1">
                <Clock className="h-3 w-3" />
                <span>{timeAgo(post.created_at)}</span>
            </span>
        </div>
    );
}
