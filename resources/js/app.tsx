import '../css/app.css';
import './bootstrap';
import i18n from './i18n';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { LoginDialogProvider } from '@/components/login-dialog';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const locale = (props.initialPage.props as Record<string, unknown>).locale as string || 'en';
        i18n.changeLanguage(locale);

        if (import.meta.env.SSR) {
            hydrateRoot(el, <LoginDialogProvider><App {...props} /></LoginDialogProvider>);
            return;
        }

        createRoot(el).render(<LoginDialogProvider><App {...props} /></LoginDialogProvider>);
    },
    progress: {
        color: '#4B5563',
    },
});
