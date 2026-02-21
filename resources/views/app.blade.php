<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        {{-- Server-side OG meta tags for social media crawlers (works without SSR) --}}
        @if(isset($page['props']['post']['slug']))
            @php
                $ogPost = $page['props']['post'];
                $ogBaseUrl = config('app.url');
                $ogPostUrl = $ogBaseUrl . '/posts/' . $ogPost['slug'];
                $ogImageUrl = $ogPostUrl . '/og-image.png';
                $ogBody = strip_tags($ogPost['body'] ?? '');
                $ogDesc = \Illuminate\Support\Str::limit($ogBody, 200);
                $ogTitle = isset($ogPost['category']['translated_name'])
                    ? $ogPost['title'] . ' — ' . $ogPost['category']['translated_name']
                    : $ogPost['title'];
                $ogCity = $ogPost['city']['name'] ?? null;
                $ogState = $ogPost['state']['name'] ?? null;
                $ogLocation = collect([$ogCity, $ogState])->filter()->join(', ');
                $ogFullDesc = $ogLocation ? $ogLocation . ' — ' . $ogDesc : $ogDesc;
            @endphp
            <meta property="og:site_name" content="Civic Forum">
            <meta property="og:title" content="{{ $ogTitle }}">
            <meta property="og:description" content="{{ $ogFullDesc }}">
            <meta property="og:type" content="article">
            <meta property="og:url" content="{{ $ogPostUrl }}">
            <meta property="og:image" content="{{ $ogImageUrl }}">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            <meta property="og:image:alt" content="{{ $ogPost['title'] }}">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{{ $ogTitle }}">
            <meta name="twitter:description" content="{{ $ogDescription }}">
            <meta name="twitter:image" content="{{ $ogImage }}">
            <meta name="description" content="{{ $ogDescription }}">
            <link rel="canonical" href="{{ $postUrl }}">

            {{-- JSON-LD Structured Data for SEO --}}
            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Article",
                "headline": "{{ $post['title'] }}",
                "description": "{{ $ogDescription }}",
                "image": "{{ $ogImage }}",
                "datePublished": "{{ $post['published_at'] ?? $post['created_at'] }}",
                "dateModified": "{{ $post['updated_at'] }}",
                "author": {
                    "@type": "Person",
                    "name": "{{ $post['user']['name'] ?? 'Jan Rashtra' }}"
                },
                "publisher": {
                    "@type": "Organization",
                    "name": "Jan Rashtra",
                    "url": "{{ $baseUrl }}",
                    "logo": {
                        "@type": "ImageObject",
                        "url": "{{ $baseUrl }}/logo.png"
                    }
                },
                "mainEntityOfPage": {
                    "@type": "WebPage",
                    "@id": "{{ $postUrl }}"
                }
                @if(isset($post['category']))
                ,"articleSection": "{{ $post['category']['name'] }}"
                @endif
            }
            </script>
        @else
            {{-- Default site meta for non-post pages --}}
            <meta name="description" content="Jan Rashtra - India's civic forum for citizens to discuss local issues, governance, and community matters.">
            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "WebSite",
                "name": "Jan Rashtra",
                "url": "{{ config('app.url') }}",
                "description": "India's civic forum for citizens to discuss local issues, governance, and community matters.",
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "{{ config('app.url') }}/search?q={search_term_string}",
                    "query-input": "required name=search_term_string"
                }
            }
            </script>
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
