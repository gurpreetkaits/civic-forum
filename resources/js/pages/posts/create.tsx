import AppLayout from '@/layouts/AppLayout';
import LocationPicker from '@/components/location-picker';
import ImageUploadZone from '@/components/image-upload-zone';
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
import { FormEvent, useState } from 'react';
import { useTranslation } from 'react-i18next';

export default function PostCreate() {
    const { categories } = usePage<PageProps>().props;
    const [images, setImages] = useState<File[]>([]);
    const [processing, setProcessing] = useState(false);
    const { t } = useTranslation();

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
