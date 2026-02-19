import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { useLoginDialog } from '@/components/login-dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import { Separator } from '@/components/ui/separator';
import { PageProps } from '@/types';
import { Github, Menu, Plus } from 'lucide-react';
import LanguageToggle from '@/components/language-toggle';

export default function AppLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const { auth, categories } = usePage<PageProps>().props;
    const user = auth.user;
    const { t } = useTranslation();
    const { open: openLoginDialog } = useLoginDialog();

    return (
        <div className="min-h-screen bg-background">
            <nav className="sticky top-0 z-50 border-b bg-background">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-14 items-center justify-between">
                        <div className="flex items-center gap-6">
                            <Link href="/" className="text-lg font-semibold tracking-tight">
                                {t('nav.civicForum')}
                            </Link>

                            <div className="hidden items-center gap-1 md:flex">
                                <Link href="/">
                                    <Button variant="ghost" size="sm">
                                        {t('nav.home')}
                                    </Button>
                                </Link>
                                <Link href="/search">
                                    <Button variant="ghost" size="sm">
                                        {t('nav.search')}
                                    </Button>
                                </Link>
                                <a href="https://github.com/gurpreetkaits/civic-forum/issues" target="_blank" rel="noopener noreferrer">
                                    <Button variant="ghost" size="sm" className="gap-1.5">
                                        <Github className="h-4 w-4" />
                                        {t('nav.suggestIssues')}
                                    </Button>
                                </a>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            <LanguageToggle />

                            {user ? (
                                <>
                                    <Link href="/posts/create">
                                        <Button size="sm">
                                            <Plus className="mr-1 h-4 w-4" />
                                            {t('nav.newPost')}
                                        </Button>
                                    </Link>

                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" size="sm" className="gap-2">
                                                <Avatar className="h-6 w-6">
                                                    {user.avatar_path && (
                                                        <AvatarImage src={`/storage/${user.avatar_path}`} />
                                                    )}
                                                    <AvatarFallback className="text-xs">
                                                        {user.name.charAt(0).toUpperCase()}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <span className="hidden sm:inline">{user.username}</span>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" className="w-48">
                                            <DropdownMenuItem asChild>
                                                <Link href={`/users/${user.username}`}>
                                                    {t('nav.myProfile')}
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem asChild>
                                                <Link href="/settings/profile">
                                                    {t('nav.settings')}
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem asChild>
                                                <Link href="/logout" method="post" as="button" className="w-full">
                                                    {t('nav.logOut')}
                                                </Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </>
                            ) : (
                                <Button variant="ghost" size="sm" onClick={openLoginDialog}>
                                    {t('nav.logIn')}
                                </Button>
                            )}

                            <Sheet>
                                <SheetTrigger asChild>
                                    <Button variant="ghost" size="icon" className="md:hidden">
                                        <Menu className="h-5 w-5" />
                                    </Button>
                                </SheetTrigger>
                                <SheetContent side="right" className="w-72">
                                    <div className="mt-6 flex flex-col gap-1">
                                        <Link href="/" className="rounded-md px-3 py-2 text-sm font-medium hover:bg-accent">
                                            {t('nav.home')}
                                        </Link>
                                        <Link href="/search" className="rounded-md px-3 py-2 text-sm font-medium hover:bg-accent">
                                            {t('nav.search')}
                                        </Link>
                                        <a href="https://github.com/gurpreetkaits/civic-forum/issues" target="_blank" rel="noopener noreferrer" className="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium hover:bg-accent">
                                            <Github className="h-4 w-4" />
                                            {t('nav.suggestIssues')}
                                        </a>
                                        <Separator className="my-3" />
                                        <p className="px-3 text-xs font-medium uppercase tracking-wider text-muted-foreground">{t('nav.categories')}</p>
                                        {categories?.map((cat) => (
                                            <Link
                                                key={cat.id}
                                                href={`/categories/${cat.slug}`}
                                                className="rounded-md px-3 py-1.5 text-sm hover:bg-accent"
                                            >
                                                {cat.translated_name}
                                            </Link>
                                        ))}
                                    </div>
                                </SheetContent>
                            </Sheet>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="border-b bg-background">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main>{children}</main>

            <footer className="mt-12 border-t py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <p className="text-sm text-muted-foreground">
                            {t('footer.tagline')}
                        </p>
                        <div className="flex gap-4 text-sm text-muted-foreground">
                            <Link href="/" className="hover:text-foreground">{t('nav.home')}</Link>
                            <Link href="/search" className="hover:text-foreground">{t('nav.search')}</Link>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
}
