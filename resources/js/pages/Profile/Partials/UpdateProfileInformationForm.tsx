import { useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function UpdateProfileInformation({
    status,
    className = '',
}: {
    status?: string;
    className?: string;
}) {
    const user = usePage().props.auth.user!;
    const { t } = useTranslation();

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-foreground">
                    {t('profile.profileInfo')}
                </h2>

                <p className="mt-1 text-sm text-muted-foreground">
                    {t('profile.profileInfoDesc')}
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-4">
                <div className="space-y-2">
                    <Label htmlFor="name">{t('auth.name')}</Label>
                    <Input
                        id="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        autoComplete="name"
                    />
                    {errors.name && (
                        <p className="text-sm text-destructive">{errors.name}</p>
                    )}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="email">{t('auth.email')}</Label>
                    <Input
                        id="email"
                        type="email"
                        value={user.email}
                        disabled
                        className="bg-muted"
                    />
                </div>

                <div className="flex items-center gap-4">
                    <Button type="submit" disabled={processing}>
                        {t('profile.save')}
                    </Button>

                    {recentlySuccessful && (
                        <p className="text-sm text-muted-foreground">
                            {t('profile.saved')}
                        </p>
                    )}
                </div>
            </form>
        </section>
    );
}
