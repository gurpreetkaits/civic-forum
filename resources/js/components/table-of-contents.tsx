import { useEffect, useState } from 'react';
import { TocHeading, cn } from '@/lib/utils';
import { List, ChevronDown } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface TableOfContentsProps {
    headings: TocHeading[];
}

export default function TableOfContents({ headings }: TableOfContentsProps) {
    const [activeId, setActiveId] = useState<string>('');
    const [isOpen, setIsOpen] = useState(true);
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
        <nav className="hidden lg:block shrink-0">
            <div className="sticky top-20 w-52">
                <button
                    onClick={() => setIsOpen(!isOpen)}
                    className="flex w-full items-center gap-1.5 mb-3 text-sm font-semibold text-foreground hover:text-foreground/80 transition-colors"
                >
                    <List className="h-4 w-4" />
                    {t('post.contents')}
                    <ChevronDown
                        className={cn(
                            'h-3.5 w-3.5 ml-auto transition-transform duration-200',
                            !isOpen && '-rotate-90',
                        )}
                    />
                </button>
                <div
                    className={cn(
                        'grid transition-[grid-template-rows] duration-200',
                        isOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]',
                    )}
                >
                    <div className="overflow-hidden">
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
                </div>
            </div>
        </nav>
    );
}
