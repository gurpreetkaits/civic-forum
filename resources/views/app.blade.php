<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        {{-- Server-side OG meta tags for social media crawlers (works without SSR) --}}
        @if(isset($page['props']['post']))
            @php
                $post = $page['props']['post'];
                $baseUrl = config('app.url');
                $postUrl = $baseUrl . '/posts/' . $post['slug'];
                $ogImage = $postUrl . '/og-image.png';
                $plainBody = strip_tags(preg_replace(['/!\[.*?\]\(.*?\)/', '/\[([^\]]*)\]\(.*?\)/', '/#{1,6}\s+/', '/[*_~`>{}\[\]]/'], ['', '$1', '', ''], $post['body'] ?? ''));
                $description = \Illuminate\Support\Str::limit($plainBody, 200);
                $ogTitle = isset($post['category']['translated_name'])
                    ? $post['title'] . ' — ' . $post['category']['translated_name']
                    : $post['title'];
                $location = collect([$post['city']['name'] ?? null, $post['state']['name'] ?? null])->filter()->join(', ');
                $ogDescription = $location ? $location . ' — ' . $description : $description;
            @endphp
            <meta property="og:site_name" content="Civic Forum">
            <meta property="og:title" content="{{ $ogTitle }}">
            <meta property="og:description" content="{{ $ogDescription }}">
            <meta property="og:type" content="article">
            <meta property="og:url" content="{{ $postUrl }}">
            <meta property="og:image" content="{{ $ogImage }}">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            <meta property="og:image:alt" content="{{ $post['title'] }}">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{{ $ogTitle }}">
            <meta name="twitter:description" content="{{ $ogDescription }}">
            <meta name="twitter:image" content="{{ $ogImage }}">
            <meta name="description" content="{{ $description }}">
            <link rel="canonical" href="{{ $postUrl }}">
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
