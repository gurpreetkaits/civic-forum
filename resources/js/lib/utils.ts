import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs))
}

/**
 * Strip markdown syntax to produce plain text for OG descriptions.
 */
export function stripMarkdown(md: string): string {
    return md
        .replace(/!\[.*?\]\(.*?\)/g, '')       // images
        .replace(/\[([^\]]*)\]\(.*?\)/g, '$1') // links → text
        .replace(/#{1,6}\s+/g, '')             // headings
        .replace(/[*_~`>{}\[\]]/g, '')         // inline formatting chars
        .replace(/\n{2,}/g, ' ')               // double newlines → space
        .replace(/\n/g, ' ')                   // single newlines → space
        .replace(/\s{2,}/g, ' ')               // collapse whitespace
        .trim();
}

export function timeAgo(date: string): string {
    const now = new Date();
    const past = new Date(date);
    const seconds = Math.floor((now.getTime() - past.getTime()) / 1000);

    if (seconds < 0) {
        return 'just now';
    }

    const intervals: [number, string][] = [
        [31536000, 'year'],
        [2592000, 'month'],
        [604800, 'week'],
        [86400, 'day'],
        [3600, 'hour'],
        [60, 'minute'],
        [1, 'second'],
    ];

    for (const [intervalSeconds, label] of intervals) {
        const count = Math.floor(seconds / intervalSeconds);
        if (count >= 1) {
            return count === 1
                ? `1 ${label} ago`
                : `${count} ${label}s ago`;
        }
    }

    return 'just now';
}
