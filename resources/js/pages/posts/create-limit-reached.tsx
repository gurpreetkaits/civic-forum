import AppLayout from '@/layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { useTranslation } from 'react-i18next';
import { AlertCircle, Calendar, CheckCircle } from 'lucide-react';

interface Props {
    limit: number;
    next_post_allowed: string;
    is_verified: boolean;
}

export default function PostCreateLimitReached({ limit, next_post_allowed, is_verified }: Props) {
    const { t } = useTranslation();

    const nextPostDate = new Date(next_post_allowed);
    const now = new Date();
    const hoursRemaining = Math.ceil((nextPostDate.getTime() - now.getTime()) / (1000 * 60 * 60));

    return (
        <AppLayout>
            <Head title="Daily Post Limit Reached" />

            <div className="flex min-h-[60vh] items-center justify-center px-4">
                <div className="mx-auto max-w-md text-center">
                    <div className="mb-6 flex justify-center">
                        <div className="rounded-full bg-orange-100 p-4 dark:bg-orange-900/20">
                            <AlertCircle className="h-16 w-16 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>

                    <h1 className="mb-3 text-3xl font-bold text-foreground">
                        Daily Post Limit Reached
                    </h1>

                    <p className="mb-6 text-lg text-muted-foreground">
                        You've reached your daily limit of <strong>{limit} post{limit > 1 ? 's' : ''}</strong>.
                        This helps maintain the quality of content on Civic Forum.
                    </p>

                    <div className="mb-8 rounded-lg border bg-card p-6">
                        <div className="mb-4 flex items-center justify-center gap-2 text-muted-foreground">
                            <Calendar className="h-5 w-5" />
                            <span className="font-medium">Next Post Available</span>
                        </div>
                        <div className="text-2xl font-bold text-foreground">
                            {hoursRemaining < 24
                                ? `in ${hoursRemaining} hour${hoursRemaining !== 1 ? 's' : ''}`
                                : nextPostDate.toLocaleDateString(undefined, {
                                      weekday: 'long',
                                      month: 'long',
                                      day: 'numeric',
                                  })}
                        </div>
                        <div className="mt-2 text-sm text-muted-foreground">
                            {nextPostDate.toLocaleTimeString(undefined, {
                                hour: 'numeric',
                                minute: '2-digit',
                            })}
                        </div>
                    </div>

                    {!is_verified && (
                        <div className="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/50 dark:bg-blue-950/20">
                            <div className="mb-2 flex items-center justify-center gap-2">
                                <CheckCircle className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                <span className="font-semibold text-blue-900 dark:text-blue-100">
                                    Want to post more?
                                </span>
                            </div>
                            <p className="mb-3 text-sm text-blue-800 dark:text-blue-200">
                                Verified users can create up to 5 posts per day!
                            </p>
                            <Link href="/settings/profile">
                                <Button variant="outline" size="sm" className="border-blue-300 dark:border-blue-700">
                                    Verify Your Account
                                </Button>
                            </Link>
                        </div>
                    )}

                    <div className="flex flex-col gap-3 sm:flex-row sm:justify-center">
                        <Link href="/">
                            <Button variant="outline">Back to Home</Button>
                        </Link>
                        <Link href="/settings/profile">
                            <Button>View My Profile</Button>
                        </Link>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
