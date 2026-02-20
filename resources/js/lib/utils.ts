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

export function slugify(text: string): string {
    return text
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
}

export interface TocHeading {
    level: number;
    text: string;
    id: string;
}

/**
 * Extract headings from markdown source for a table of contents.
 */
export function extractHeadings(markdown: string): TocHeading[] {
    const headings: TocHeading[] = [];
    const lines = markdown.split('\n');

    for (const line of lines) {
        const match = line.match(/^(#{1,4})\s+(.+)$/);
        if (match) {
            const text = match[2]
                .replace(/\*\*(.+?)\*\*/g, '$1')
                .replace(/\*(.+?)\*/g, '$1')
                .replace(/`(.+?)`/g, '$1')
                .replace(/\[([^\]]*)\]\(.*?\)/g, '$1')
                .trim();
            headings.push({
                level: match[1].length,
                text,
                id: slugify(text),
            });
        }
    }

    return headings;
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
