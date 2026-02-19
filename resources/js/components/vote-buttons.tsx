import { useState } from 'react';
import { router } from '@inertiajs/react';
import { ChevronUp, ChevronDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface VoteButtonsProps {
    votableType: 'post' | 'comment';
    votableId: number;
    voteCount: number;
    userVote: number | null;
    size?: 'sm' | 'default';
}

export default function VoteButtons({
    votableType,
    votableId,
    voteCount,
    userVote,
    size = 'default',
}: VoteButtonsProps) {
    const [optimisticCount, setOptimisticCount] = useState(voteCount);
    const [optimisticVote, setOptimisticVote] = useState(userVote);
    const [isVoting, setIsVoting] = useState(false);

    const handleVote = (value: 1 | -1) => {
        if (isVoting) return;

        const previousCount = optimisticCount;
        const previousVote = optimisticVote;

        let newVote: number | null;
        let countDelta: number;

        if (optimisticVote === value) {
            // Undo the vote
            newVote = null;
            countDelta = -value;
        } else if (optimisticVote === null) {
            // New vote
            newVote = value;
            countDelta = value;
        } else {
            // Switching vote direction
            newVote = value;
            countDelta = value * 2;
        }

        setOptimisticVote(newVote);
        setOptimisticCount(optimisticCount + countDelta);
        setIsVoting(true);

        router.post(
            '/votes',
            {
                votable_type: votableType,
                votable_id: votableId,
                value: value,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onError: () => {
                    setOptimisticVote(previousVote);
                    setOptimisticCount(previousCount);
                },
                onFinish: () => {
                    setIsVoting(false);
                },
            },
        );
    };

    const iconSize = size === 'sm' ? 'h-4 w-4' : 'h-5 w-5';
    const buttonSize = size === 'sm' ? 'h-6 w-6' : 'h-8 w-8';
    const textSize = size === 'sm' ? 'text-xs' : 'text-sm';

    return (
        <div className="flex flex-col items-center gap-0.5">
            <Button
                variant="ghost"
                size="icon"
                className={cn(
                    buttonSize,
                    'rounded-sm p-0',
                    optimisticVote === 1
                        ? 'text-foreground'
                        : 'text-muted-foreground hover:text-foreground',
                )}
                onClick={() => handleVote(1)}
                disabled={isVoting}
                aria-label="Upvote"
            >
                <ChevronUp className={iconSize} />
            </Button>

            <span
                className={cn(
                    'font-semibold leading-none',
                    textSize,
                    optimisticVote === 1 && 'text-foreground',
                    optimisticVote === -1 && 'text-foreground',
                    optimisticVote === null && 'text-muted-foreground',
                )}
            >
                {optimisticCount}
            </span>

            <Button
                variant="ghost"
                size="icon"
                className={cn(
                    buttonSize,
                    'rounded-sm p-0',
                    optimisticVote === -1
                        ? 'text-foreground'
                        : 'text-muted-foreground hover:text-foreground',
                )}
                onClick={() => handleVote(-1)}
                disabled={isVoting}
                aria-label="Downvote"
            >
                <ChevronDown className={iconSize} />
            </Button>
        </div>
    );
}
