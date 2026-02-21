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
            <meta name="twitter:description" content="{{ $ogFullDesc }}">
            <meta name="twitter:image" content="{{ $ogImageUrl }}">
            <meta name="description" content="{{ $ogDesc }}">
            <link rel="canonical" href="{{ $ogPostUrl }}">
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
