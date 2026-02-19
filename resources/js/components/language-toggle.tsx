import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { Languages } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const locales = [
    { code: 'en', label: 'English' },
    { code: 'hi', label: 'हिन्दी' },
] as const;

export default function LanguageToggle() {
    const { i18n } = useTranslation();

    const switchLocale = (locale: string) => {
        i18n.changeLanguage(locale);
        router.post('/locale', { locale }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const currentLabel = locales.find((l) => l.code === i18n.language)?.label ?? 'English';

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="sm" className="gap-1.5">
                    <Languages className="h-4 w-4" />
                    <span className="hidden sm:inline">{currentLabel}</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {locales.map((locale) => (
                    <DropdownMenuItem
                        key={locale.code}
                        onClick={() => switchLocale(locale.code)}
                        className={i18n.language === locale.code ? 'bg-accent' : ''}
                    >
                        {locale.label}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
