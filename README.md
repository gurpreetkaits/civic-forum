# Civic Forum

An open-source platform for Indian citizens to discuss and raise civic issues. Built with Laravel, React, Inertia.js, and Tailwind CSS.

## Features

- Post and discuss civic issues across 15 categories (Infrastructure, Healthcare, Education, etc.)
- Location-based filtering by Indian states and cities
- Upvote/downvote system with reputation tracking
- Nested comment threads
- Image uploads for posts
- Full-text search
- Bilingual support (English + Hindi)
- SEO-friendly with SSR and sitemap

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** React 18, TypeScript, Tailwind CSS, Radix UI
- **Routing:** Inertia.js with SSR
- **i18n:** react-i18next (English + Hindi)
- **Build:** Vite

## Setup

```bash
# Clone
git clone https://github.com/gurpreetkaits/civic-forum.git
cd civic-forum

# Install dependencies
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Run
npm run dev
php artisan serve
```

## Contributing

Found a bug or have a feature idea? [Open an issue](https://github.com/gurpreetkaits/civic-forum/issues) on GitHub.

## License

MIT
