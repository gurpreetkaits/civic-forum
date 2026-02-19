import AppLayout from '@/layouts/AppLayout';
import LocationPicker from '@/components/location-picker';
import { Head, useForm, usePage } from '@inertiajs/react';
import { PageProps, Post } from '@/types';
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
import { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';

interface Props extends PageProps {
    post: Post;
}

export default function PostEdit({ post }: Props) {
    const { categories } = usePage<PageProps>().props;
    const { t } = useTranslation();

    const { data, setData, put, processing, errors } = useForm({
        title: post.title,
        body: post.body,
        category_id: String(post.category_id),
        state_id: post.state_id || null,
        city_id: post.city_id || null,
        tags: post.tags?.map((t) => t.name).join(', ') || '',
    });

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        put(`/posts/${post.slug}`);
    }

    return (
        <AppLayout>
            <Head title={t('postForm.editTitle', { title: post.title })} />

            <div className="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 className="mb-6 text-2xl font-bold text-foreground">{t('postForm.editHeading')}</h1>

                <form onSubmit={handleSubmit} className="space-y-6 rounded-lg border bg-card p-6">
                    <div className="space-y-2">
                        <Label htmlFor="title">{t('postForm.titleLabel')}</Label>
                        <Input
                            id="title"
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            className={errors.title ? 'border-destructive' : ''}
                        />
                        {errors.title && <p className="text-sm text-destructive">{errors.title}</p>}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="category">{t('postForm.categoryLabel')}</Label>
                        <Select
                            value={data.category_id}
                            onValueChange={(val) => setData('category_id', val)}
                        >
                            <SelectTrigger>
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
                        {errors.category_id && <p className="text-sm text-destructive">{errors.category_id}</p>}
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
                            rows={8}
                            className={errors.body ? 'border-destructive' : ''}
                        />
                        {errors.body && <p className="text-sm text-destructive">{errors.body}</p>}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="tags">{t('postForm.tagsLabelEdit')}</Label>
                        <Input
                            id="tags"
                            value={data.tags}
                            onChange={(e) => setData('tags', e.target.value)}
                            placeholder={t('postForm.tagsPlaceholder')}
                        />
                    </div>

                    <div className="flex justify-end gap-3">
                        <Button type="submit" disabled={processing}>
                            {processing ? t('postForm.saving') : t('postForm.saveChanges')}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
