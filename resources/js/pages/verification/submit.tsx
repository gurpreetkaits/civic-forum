import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { Button } from '@/components/ui/button';
import { Loader2, ShieldCheck, AlertCircle, Camera, CheckCircle, ArrowLeft } from 'lucide-react';
import { PageProps } from '@/types';

export default function VerificationSubmit({ auth }: PageProps) {
    const { t } = useTranslation();
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState(false);
    const [selfieFrontFile, setSelfieFrontFile] = useState<File | null>(null);
    const [selfieBackFile, setSelfieBackFile] = useState<File | null>(null);
    const [selfieFrontPreview, setSelfieFrontPreview] = useState<string | null>(null);
    const [selfieBackPreview, setSelfieBackPreview] = useState<string | null>(null);

    const handleSelfieFrontChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setSelfieFrontFile(file);
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onloadend = () => {
                    setSelfieFrontPreview(reader.result as string);
                };
                reader.readAsDataURL(file);
            }
        }
    };

    const handleSelfieBackChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setSelfieBackFile(file);
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onloadend = () => {
                    setSelfieBackPreview(reader.result as string);
                };
                reader.readAsDataURL(file);
            }
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!selfieFrontFile || !selfieBackFile) {
            setError('Please upload both selfies (with ID front and back)');
            return;
        }

        setLoading(true);
        setError(null);

        try {
            const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('selfie_front', selfieFrontFile);
            formData.append('selfie_back', selfieBackFile);

            const response = await fetch('/api/verification/submit', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                credentials: 'include',
                body: formData,
            });

            if (!response.ok) {
                const text = await response.text();
                let errorMessage = 'Failed to submit verification request';

                try {
                    const data = JSON.parse(text);

                    if (data.errors) {
                        const validationErrors = Object.values(data.errors).flat();
                        errorMessage = validationErrors.join('\n');
                    } else {
                        errorMessage = data.error || data.message || errorMessage;
                    }
                } catch {
                    console.error('Server returned non-JSON response:', text.substring(0, 200));
                    if (response.status === 401) {
                        errorMessage = 'Please log in to submit verification';
                    } else if (response.status === 419) {
                        errorMessage = 'Session expired. Please refresh the page and try again.';
                    } else if (response.status === 422) {
                        errorMessage = 'Validation failed. Please check your files and try again.';
                    } else {
                        errorMessage = `Server error (${response.status}). Please try again.`;
                    }
                }

                throw new Error(errorMessage);
            }

            const data = await response.json();

            setSuccess(true);
            setTimeout(() => {
                router.visit('/settings/profile');
            }, 2000);
        } catch (err: any) {
            console.error('Verification submission error:', err);
            setError(err.message || 'Failed to submit verification request');
        } finally {
            setLoading(false);
        }
    };

    return (
        <AppLayout>
            <Head title={t('verification.title', 'Identity Verification')} />

            <div className="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="mb-6">
                    <Button
                        variant="ghost"
                        onClick={() => router.visit('/settings/profile')}
                        className="mb-4"
                    >
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Back to Profile
                    </Button>
                    <div className="flex items-center gap-3">
                        <ShieldCheck className="h-8 w-8 text-green-600" />
                        <h1 className="text-2xl font-semibold text-foreground">
                            {t('verification.title', 'Identity Verification')}
                        </h1>
                    </div>
                </div>

                {/* Content */}
                <div className="rounded-lg border bg-card p-8">
                    {success ? (
                        <div className="flex flex-col items-center justify-center py-12">
                            <div className="mb-4 rounded-full bg-green-100 p-6 dark:bg-green-900/20">
                                <CheckCircle className="h-16 w-16 text-green-600 dark:text-green-400" />
                            </div>
                            <h2 className="mb-3 text-2xl font-semibold text-foreground">Submitted Successfully!</h2>
                            <p className="text-center text-muted-foreground max-w-md">
                                Your verification request has been submitted. Our team will review it within 24-48 hours.
                            </p>
                        </div>
                    ) : (
                        <>
                            {error && (
                                <div className="mb-6 flex items-start gap-3 rounded-lg bg-red-50 p-4 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                                    <AlertCircle className="mt-0.5 h-5 w-5 flex-shrink-0" />
                                    <div>
                                        <p className="font-medium">{t('verification.error', 'Error')}</p>
                                        <p className="whitespace-pre-line mt-1">{error}</p>
                                    </div>
                                </div>
                            )}

                            <div className="mb-8 rounded-lg bg-blue-50 p-5 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                                <p className="font-semibold mb-3 text-base">
                                    {t('verification.instructionsTitle', 'Required for verification:')}
                                </p>
                                <ul className="space-y-2 list-inside list-disc">
                                    <li>Selfie holding your ID showing the <strong>front side</strong></li>
                                    <li>Selfie holding your ID showing the <strong>back side</strong></li>
                                </ul>
                                <p className="mt-4 text-sm text-blue-600 dark:text-blue-400">
                                    {t('verification.privacy', 'Your photos are stored securely and only visible to moderators. Both selfies are required to verify your identity.')}
                                </p>
                            </div>

                            <form onSubmit={handleSubmit} className="space-y-6">
                                {/* Selfie with ID Front */}
                                <div>
                                    <label className="mb-3 block text-sm font-medium text-foreground">
                                        Selfie with ID Front <span className="text-red-500">*</span>
                                    </label>
                                    <div className="relative">
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={handleSelfieFrontChange}
                                            className="hidden"
                                            id="selfie-front-upload"
                                            disabled={loading}
                                        />
                                        <label
                                            htmlFor="selfie-front-upload"
                                            className="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                                        >
                                            {selfieFrontPreview ? (
                                                <img src={selfieFrontPreview} alt="Selfie with ID front" className="max-h-64 rounded" />
                                            ) : (
                                                <>
                                                    <Camera className="mb-3 h-10 w-10 text-gray-400" />
                                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                        Click to upload selfie with ID front
                                                    </p>
                                                    <p className="mt-2 text-xs text-gray-500">
                                                        PNG or JPG (max 10MB)
                                                    </p>
                                                </>
                                            )}
                                        </label>
                                    </div>
                                </div>

                                {/* Selfie with ID Back */}
                                <div>
                                    <label className="mb-3 block text-sm font-medium text-foreground">
                                        Selfie with ID Back <span className="text-red-500">*</span>
                                    </label>
                                    <div className="relative">
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={handleSelfieBackChange}
                                            className="hidden"
                                            id="selfie-back-upload"
                                            disabled={loading}
                                        />
                                        <label
                                            htmlFor="selfie-back-upload"
                                            className="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                                        >
                                            {selfieBackPreview ? (
                                                <img src={selfieBackPreview} alt="Selfie with ID back" className="max-h-64 rounded" />
                                            ) : (
                                                <>
                                                    <Camera className="mb-3 h-10 w-10 text-gray-400" />
                                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                        Click to upload selfie with ID back
                                                    </p>
                                                    <p className="mt-2 text-xs text-gray-500">
                                                        PNG or JPG (max 10MB)
                                                    </p>
                                                </>
                                            )}
                                        </label>
                                    </div>
                                </div>

                                <div className="flex justify-end gap-3 pt-6">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => router.visit('/settings/profile')}
                                        disabled={loading}
                                    >
                                        Cancel
                                    </Button>
                                    <Button type="submit" disabled={loading || !selfieFrontFile || !selfieBackFile}>
                                        {loading ? (
                                            <>
                                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                Submitting...
                                            </>
                                        ) : (
                                            'Submit for Review'
                                        )}
                                    </Button>
                                </div>
                            </form>
                        </>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
