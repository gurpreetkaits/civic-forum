import AppLayout from '@/layouts/AppLayout';
import { PageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { useState, useEffect } from 'react';
import { ShieldCheck, ShieldAlert, CheckCircle, AlertCircle } from 'lucide-react';

export default function Edit({
    status,
}: PageProps<{ status?: string }>) {
    const auth = usePage<PageProps>().props.auth;
    const [isVerified, setIsVerified] = useState(auth.user?.is_verified ?? false);
    const [verificationDate, setVerificationDate] = useState<string | null>(auth.user?.verified_at ?? null);
    const { t } = useTranslation();
    useEffect(() => {
        if (!isVerified) {
            checkVerificationStatus();
        }
    }, []);

    const checkVerificationStatus = async () => {
        try {
            const response = await fetch('/api/verification/status', {
                headers: {
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                credentials: 'include',
            });
            const data = await response.json();
            if (data.is_verified) {
                setIsVerified(true);
                setVerificationDate(data.verified_at);
            }
        } catch (error) {
            console.error('Failed to check verification status:', error);
        }
    };


    return (
        <AppLayout>
            <Head title={t('profile.heading')} />

            <div className="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 className="mb-6 text-xl font-semibold text-foreground">
                    {t('profile.heading')}
                </h1>

                <div className="space-y-6">
                    {/* Verification Status Card */}
                    <div className="rounded-lg border bg-card p-6">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                {isVerified ? (
                                    <ShieldCheck className="h-6 w-6 text-green-600" />
                                ) : (
                                    <ShieldAlert className="h-6 w-6 text-yellow-600" />
                                )}
                                <div>
                                    <h2 className="font-semibold">{t('verification.status', 'Identity Verification')}</h2>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        {isVerified ? (
                                            <span className="flex items-center gap-1 text-green-600">
                                                <CheckCircle className="h-4 w-4" />
                                                {t('verification.verified', 'Verified on')} {verificationDate ? new Date(verificationDate).toLocaleDateString() : ''}
                                            </span>
                                        ) : (
                                            <span className="flex items-center gap-1 text-yellow-600">
                                                <AlertCircle className="h-4 w-4" />
                                                {t('verification.notVerified', 'Not verified')}
                                            </span>
                                        )}
                                    </p>
                                </div>
                            </div>
                            {!isVerified && (
                                <Button onClick={() => router.visit('/verification/submit')} variant="outline">
                                    {t('verification.verifyNow', 'Verify Now')}
                                </Button>
                            )}
                        </div>
                        <p className="mt-3 text-sm text-gray-500 dark:text-gray-400">
                            {t('verification.profileDescription', 'Verifying your identity allows you to create posts and contribute to discussions.')}
                        </p>
                    </div>

                    <div className="rounded-lg border bg-card p-6">
                        <UpdateProfileInformationForm
                            status={status}
                            className="max-w-xl"
                        />
                    </div>

                    <div className="rounded-lg border bg-card p-6">
                        <DeleteUserForm className="max-w-xl" />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
