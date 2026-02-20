import AppLayout from '@/layouts/AppLayout';
import LocationPicker from '@/components/location-picker';
import ImageUploadZone from '@/components/image-upload-zone';
import VerificationModal from '@/components/verification-modal';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { FormEvent, useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { ShieldAlert, AlertCircle } from 'lucide-react';

export default function PostCreate() {
    const { categories } = usePage<PageProps>().props;
    const auth = usePage<PageProps>().props.auth;
    const [images, setImages] = useState<File[]>([]);
    const [processing, setProcessing] = useState(false);
    const [showVerification, setShowVerification] = useState(false);
    const [isVerified, setIsVerified] = useState(auth.user?.is_verified ?? false);
    const { t } = useTranslation();
    // Check verification status on mount
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
                    'Accept': 'application/json',
                },
                credentials: 'include',
            });

            if (!response.ok) {
                console.error('Verification status check failed:', response.status);
                return;
            }

            const data = await response.json();
            if (data.is_verified) {
                setIsVerified(true);
            }
        } catch (error) {
            console.error('Failed to check verification status:', error);
        }
    };

    const handleVerified = () => {
        setIsVerified(true);
        router.reload();
    };

    // If user is not verified, show verification prompt
    if (!isVerified) {
        return (
            <AppLayout>
                <Head title={t('verification.title')} />
                <div className="flex min-h-[50vh] items-center justify-center px-4">
                    <div className="mx-auto max-w-md text-center">
                        <div className="mb-4 flex justify-center">
                            <div className="rounded-full bg-yellow-100 p-4 dark:bg-yellow-900/20">
                                <ShieldAlert className="h-12 w-12 text-yellow-600 dark:text-yellow-400" />
                            </div>
                        </div>
                        <h1 className="mb-2 text-2xl font-bold">{t('verification.required', 'Verification Required')}</h1>
                        <p className="mb-6 text-gray-600 dark:text-gray-400">
                            {t('verification.postCreateMessage', 'You need to verify your identity before creating a post. This helps maintain the quality and authenticity of content on Civic Forum.')}
                        </p>
                        <Button onClick={() => setShowVerification(true)}>
                            {t('verification.verifyNow', 'Verify Now')}
                        </Button>
                    </div>
                </div>

                <VerificationModal
                    isOpen={showVerification}
                    onClose={() => setShowVerification(false)}
                    onVerified={handleVerified}
                    userEmail={auth.user?.email || ''}
                />
            </AppLayout>
        );
    }

    const { data, setData, errors } = useForm({
        title: '',
        body: '',
        category_id: '',
        state_id: null as number | null,
        city_id: null as number | null,
        tags: '',
    });

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        setProcessing(true);

        const formData = new FormData();
        formData.append('title', data.title);
        formData.append('body', data.body);
        formData.append('category_id', data.category_id);
        if (data.state_id) formData.append('state_id', String(data.state_id));
        if (data.city_id) formData.append('city_id', String(data.city_id));
        if (data.tags) formData.append('tags', data.tags);
        images.forEach((img, i) => {
            formData.append(`images[${i}]`, img);
        });

        router.post('/posts', formData as any, {
            forceFormData: true,
            onFinish: () => setProcessing(false),
        });
    }

    return (
        <AppLayout>
            <Head title={t('postForm.createTitle')} />

            <div className="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 className="mb-6 text-2xl font-bold text-foreground">
                    {t('postForm.createHeading')}
                </h1>

                {(usePage<PageProps>().props.errors as Record<string, string>).rate_limit && (
                    <div className="mb-6 rounded-lg border border-orange-200 bg-orange-50 p-4 dark:border-orange-900/50 dark:bg-orange-950/20">
                        <div className="flex items-start gap-3">
                            <AlertCircle className="h-5 w-5 flex-shrink-0 text-orange-600 dark:text-orange-400" />
                            <div>
                                <h3 className="font-semibold text-orange-900 dark:text-orange-100">
                                    Daily Post Limit Reached
                                </h3>
                                <p className="mt-1 text-sm text-orange-800 dark:text-orange-200">
                                    {(usePage<PageProps>().props.errors as Record<string, string>).rate_limit}
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6 rounded-lg border bg-card p-6">
                    <div className="space-y-2">
                        <Label htmlFor="title">{t('postForm.titleLabel')}</Label>
                        <Input
                            id="title"
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            placeholder={t('postForm.titlePlaceholder')}
                            className={errors.title ? 'border-destructive' : ''}
                        />
                        {errors.title && (
                            <p className="text-sm text-destructive">{errors.title}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="category">{t('postForm.categoryLabel')}</Label>
                        <Select
                            value={data.category_id}
                            onValueChange={(val) => setData('category_id', val)}
                        >
                            <SelectTrigger className={errors.category_id ? 'border-destructive' : ''}>
                                <SelectValue placeholder={t('postForm.categoryPlaceholder')} />
                            </SelectTrigger>
                            <SelectContent>
                                {categories.map((cat) => (
                                    <SelectItem key={cat.id} value={String(cat.id)}>
                                        {cat.translated_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.category_id && (
                            <p className="text-sm text-destructive">{errors.category_id}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label>{t('postForm.locationLabel')}</Label>
                        <LocationPicker
                            stateId={data.state_id}
                            cityId={data.city_id}
                            onStateChange={(id) => setData('state_id', id)}
                            onCityChange={(id) => setData('city_id', id)}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="body">{t('postForm.descriptionLabel')}</Label>
                        <Textarea
                            id="body"
                            value={data.body}
                            onChange={(e) => setData('body', e.target.value)}
                            placeholder={t('postForm.descriptionPlaceholder')}
                            rows={8}
                            className={errors.body ? 'border-destructive' : ''}
                        />
                        {errors.body && (
                            <p className="text-sm text-destructive">{errors.body}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label>{t('postForm.imagesLabel')}</Label>
                        <ImageUploadZone images={images} onImagesChange={setImages} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="tags">{t('postForm.tagsLabel')}</Label>
                        <Input
                            id="tags"
                            value={data.tags}
                            onChange={(e) => setData('tags', e.target.value)}
                            placeholder={t('postForm.tagsPlaceholder')}
                        />
                    </div>

                    <div className="flex justify-end gap-3">
                        <Button type="submit" disabled={processing}>
                            {processing ? t('postForm.publishing') : t('postForm.publishPost')}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
