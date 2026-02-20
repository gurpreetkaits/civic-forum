import { useEffect, useState } from 'react';
import { TocHeading, cn } from '@/lib/utils';
import { List } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface TableOfContentsProps {
    headings: TocHeading[];
}

export default function TableOfContents({ headings }: TableOfContentsProps) {
    const [activeId, setActiveId] = useState<string>('');
    const { t } = useTranslation();

    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        setActiveId(entry.target.id);
                        break;
                    }
                }
            },
            { rootMargin: '-80px 0px -60% 0px', threshold: 0 },
        );

        for (const heading of headings) {
            const el = document.getElementById(heading.id);
            if (el) observer.observe(el);
        }

        return () => observer.disconnect();
    }, [headings]);

    if (headings.length === 0) return null;

    const minLevel = Math.min(...headings.map((h) => h.level));

    return (
        <nav className="hidden lg:block w-56 shrink-0">
            <div className="sticky top-20">
                <div className="flex items-center gap-1.5 mb-3 text-sm font-semibold text-foreground">
                    <List className="h-4 w-4" />
                    {t('post.contents')}
                </div>
                <ul className="space-y-1 text-sm">
                    {headings.map((heading) => (
                        <li key={heading.id}>
                            <a
                                href={`#${heading.id}`}
                                onClick={(e) => {
                                    e.preventDefault();
                                    document.getElementById(heading.id)?.scrollIntoView({ behavior: 'smooth' });
                                }}
                                className={cn(
                                    'block py-1 leading-snug text-muted-foreground transition-colors hover:text-foreground',
                                    activeId === heading.id && 'text-foreground font-medium',
                                )}
                                style={{ paddingLeft: `${(heading.level - minLevel) * 12}px` }}
                            >
                                {heading.text}
                            </a>
                        </li>
                    ))}
                </ul>
            </div>
        </nav>
    );
}
