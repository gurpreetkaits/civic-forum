import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { User } from '@/types';
import { cn } from '@/lib/utils';

interface UserAvatarProps {
    user: User;
    size?: 'sm' | 'md' | 'lg';
}

const sizeClasses = {
    sm: 'h-6 w-6 text-xs',
    md: 'h-8 w-8 text-sm',
    lg: 'h-16 w-16 text-xl',
};

function getAvatarSrc(avatarPath: string): string {
    if (avatarPath.startsWith('http://') || avatarPath.startsWith('https://')) {
        return avatarPath;
    }
    return `/storage/${avatarPath}`;
}

export default function UserAvatar({ user, size = 'md' }: UserAvatarProps) {
    const fallbackLetter = user.name ? user.name.charAt(0).toUpperCase() : '?';

    return (
        <Avatar className={cn(sizeClasses[size])}>
            {user.avatar_path && (
                <AvatarImage
                    src={getAvatarSrc(user.avatar_path)}
                    alt={user.name}
                    referrerPolicy="no-referrer"
                />
            )}
            <AvatarFallback className="bg-muted text-muted-foreground">
                {fallbackLetter}
            </AvatarFallback>
        </Avatar>
    );
}
