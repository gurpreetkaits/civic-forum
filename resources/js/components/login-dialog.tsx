import { router } from '@inertiajs/react';
import { createContext, useCallback, useContext, useEffect, useRef, useState, type PropsWithChildren } from 'react';
import { useTranslation } from 'react-i18next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const LoginDialogContext = createContext<{ open: () => void }>({ open: () => {} });

export function useLoginDialog() {
    return useContext(LoginDialogContext);
}

export function LoginDialogProvider({ children }: PropsWithChildren) {
    const [isOpen, setIsOpen] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const popupRef = useRef<Window | null>(null);
    const { t } = useTranslation();

    const open = useCallback(() => {
        setError(null);
        setIsOpen(true);
    }, []);

    useEffect(() => {
        function handleMessage(event: MessageEvent) {
            if (event.origin !== window.location.origin) return;

            if (event.data?.type === 'google-auth-success') {
                setIsOpen(false);
                popupRef.current = null;
                router.reload();
            } else if (event.data?.type === 'google-auth-error') {
                setError(t('auth.googleFailed'));
                popupRef.current = null;
            }
        }

        window.addEventListener('message', handleMessage);
        return () => window.removeEventListener('message', handleMessage);
    }, [t]);

    function handleGoogleLogin() {
        setError(null);

        const width = 500;
        const height = 600;
        const left = window.screenX + (window.outerWidth - width) / 2;
        const top = window.screenY + (window.outerHeight - height) / 2;

        const popup = window.open(
            '/auth/google?popup=1',
            'google-login',
            `width=${width},height=${height},left=${left},top=${top},popup=yes`,
        );

        popupRef.current = popup;

        // Poll in case popup is closed without completing auth
        const interval = setInterval(() => {
            if (popup && popup.closed) {
                clearInterval(interval);
                popupRef.current = null;
            }
        }, 500);
    }

    return (
        <LoginDialogContext.Provider value={{ open }}>
            {children}

            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('auth.loginTitle')}</DialogTitle>
                        <DialogDescription>{t('auth.loginDesc')}</DialogDescription>
                    </DialogHeader>

                    {error && (
                        <div className="text-sm font-medium text-red-600">
                            {error}
                        </div>
                    )}

                    <button
                        type="button"
                        onClick={handleGoogleLogin}
                        className="inline-flex w-full items-center justify-center gap-3 rounded-md border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <svg className="h-5 w-5" viewBox="0 0 24 24">
                            <path
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"
                                fill="#4285F4"
                            />
                            <path
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                fill="#34A853"
                            />
                            <path
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                fill="#FBBC05"
                            />
                            <path
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                fill="#EA4335"
                            />
                        </svg>
                        {t('auth.continueWithGoogle')}
                    </button>
                </DialogContent>
            </Dialog>
        </LoginDialogContext.Provider>
    );
}
