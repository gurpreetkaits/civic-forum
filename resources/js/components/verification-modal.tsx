import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Loader2, ShieldCheck, AlertCircle, Camera, CheckCircle } from 'lucide-react';

interface VerificationModalProps {
    isOpen: boolean;
    onClose: () => void;
    onVerified: () => void;
    userEmail: string;
}

export default function VerificationModal({
    isOpen,
    onClose,
    onVerified,
    userEmail,
}: VerificationModalProps) {
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
            formData.append('_token', csrfToken); // Add CSRF token to FormData
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
                // Try to parse error response
                const text = await response.text();
                let errorMessage = 'Failed to submit verification request';

                try {
                    const data = JSON.parse(text);

                    // Check for validation errors
                    if (data.errors) {
                        const validationErrors = Object.values(data.errors).flat();
                        errorMessage = validationErrors.join('\n');
                    } else {
                        errorMessage = data.error || data.message || errorMessage;
                    }
                } catch {
                    // Response is not JSON, might be HTML error page
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
                onClose();
                window.location.reload();
            }, 2000);
        } catch (err: any) {
            console.error('Verification submission error:', err);
            setError(err.message || 'Failed to submit verification request');
        } finally {
            setLoading(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 p-4">
            <div className="relative w-full max-w-lg rounded-lg bg-white shadow-xl dark:bg-gray-900">
                {/* Header */}
                <div className="flex items-center justify-between rounded-t-lg border-b p-4">
                    <div className="flex items-center gap-2">
                        <ShieldCheck className="h-5 w-5 text-green-600" />
                        <h2 className="text-lg font-semibold">{t('verification.title', 'Identity Verification')}</h2>
                    </div>
                    <button
                        onClick={onClose}
                        className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        disabled={loading}
                    >
                        <span className="sr-only">Close</span>
                        ×
                    </button>
                </div>

                {/* Content */}
                <div className="p-6">
                    {success ? (
                        <div className="flex flex-col items-center justify-center py-8">
                            <div className="mb-4 rounded-full bg-green-100 p-4 dark:bg-green-900/20">
                                <CheckCircle className="h-12 w-12 text-green-600 dark:text-green-400" />
                            </div>
                            <h3 className="mb-2 text-xl font-semibold text-foreground">Submitted Successfully!</h3>
                            <p className="text-center text-sm text-muted-foreground">
                                Your verification request has been submitted. Our team will review it within 24-48 hours.
                            </p>
                        </div>
                    ) : (
                        <>
                            {error && (
                                <div className="mb-4 flex items-start gap-2 rounded-lg bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                                    <AlertCircle className="mt-0.5 h-4 w-4 flex-shrink-0" />
                                    <div>
                                        <p className="font-medium">{t('verification.error', 'Error')}</p>
                                        <p className="whitespace-pre-line">{error}</p>
                                    </div>
                                </div>
                            )}

                            <div className="mb-6 rounded-lg bg-blue-50 p-4 text-sm text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                                <p className="font-medium mb-2">{t('verification.instructionsTitle', 'Required for verification:')}</p>
                                <ul className="space-y-1 list-inside list-disc">
                                    <li>Selfie holding your ID showing the <strong>front side</strong></li>
                                    <li>Selfie holding your ID showing the <strong>back side</strong></li>
                                </ul>
                                <p className="mt-3 text-xs text-blue-600 dark:text-blue-400">
                                    {t('verification.privacy', 'Your photos are stored securely and only visible to moderators. Both selfies are required to verify your identity.')}
                                </p>
                            </div>

                            <form onSubmit={handleSubmit} className="space-y-4">
                                {/* Selfie with ID Front */}
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-foreground">
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
                                            className="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-6 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                                        >
                                            {selfieFrontPreview ? (
                                                <img src={selfieFrontPreview} alt="Selfie with ID front" className="max-h-48 rounded" />
                                            ) : (
                                                <>
                                                    <Camera className="mb-2 h-8 w-8 text-gray-400" />
                                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                                        Click to upload selfie with ID front
                                                    </p>
                                                    <p className="mt-1 text-xs text-gray-500">
                                                        PNG or JPG (max 10MB)
                                                    </p>
                                                </>
                                            )}
                                        </label>
                                    </div>
                                </div>

                                {/* Selfie with ID Back */}
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-foreground">
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
                                            className="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-6 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                                        >
                                            {selfieBackPreview ? (
                                                <img src={selfieBackPreview} alt="Selfie with ID back" className="max-h-48 rounded" />
                                            ) : (
                                                <>
                                                    <Camera className="mb-2 h-8 w-8 text-gray-400" />
                                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                                        Click to upload selfie with ID back
                                                    </p>
                                                    <p className="mt-1 text-xs text-gray-500">
                                                        PNG or JPG (max 10MB)
                                                    </p>
                                                </>
                                            )}
                                        </label>
                                    </div>
                                </div>

                                <div className="flex justify-end gap-3 pt-4">
                                    <Button type="button" variant="outline" onClick={onClose} disabled={loading}>
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
        </div>
    );
}
