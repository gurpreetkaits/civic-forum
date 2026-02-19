import AppLayout from '@/layouts/AppLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import { useTranslation } from 'react-i18next';

export default function Edit({
    status,
}: PageProps<{ status?: string }>) {
    const { t } = useTranslation();

    return (
        <AppLayout>
            <Head title={t('profile.heading')} />

            <div className="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 className="mb-6 text-xl font-semibold text-foreground">
                    {t('profile.heading')}
                </h1>

                <div className="space-y-6">
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
