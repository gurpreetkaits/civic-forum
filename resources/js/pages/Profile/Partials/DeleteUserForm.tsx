import { useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

export default function DeleteUserForm({
    className = '',
}: {
    className?: string;
}) {
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const { t } = useTranslation();

    const {
        delete: destroy,
        processing,
    } = useForm({});

    const deleteUser: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => setConfirmingUserDeletion(false),
        });
    };

    return (
        <section className={`space-y-6 ${className}`}>
            <header>
                <h2 className="text-lg font-medium text-foreground">
                    {t('profile.deleteAccount')}
                </h2>

                <p className="mt-1 text-sm text-muted-foreground">
                    {t('profile.deleteAccountDesc')}
                </p>
            </header>

            <Button
                variant="destructive"
                onClick={() => setConfirmingUserDeletion(true)}
            >
                {t('profile.deleteAccountButton')}
            </Button>

            <Dialog open={confirmingUserDeletion} onOpenChange={setConfirmingUserDeletion}>
                <DialogContent>
                    <form onSubmit={deleteUser}>
                        <DialogHeader>
                            <DialogTitle>{t('profile.deleteAccountConfirm')}</DialogTitle>
                            <DialogDescription>
                                {t('profile.deleteAccountConfirmDesc')}
                            </DialogDescription>
                        </DialogHeader>

                        <DialogFooter className="mt-6">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setConfirmingUserDeletion(false)}
                            >
                                {t('profile.cancel')}
                            </Button>
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={processing}
                            >
                                {t('profile.deleteAccountButton')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </section>
    );
}
