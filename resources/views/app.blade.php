@inject('settings', 'App\Settings\GeneralSettings')
<!DOCTYPE html>
<!--suppress HtmlUnknownTarget -->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ $settings->siteName }}</title>
{{--    <title inertia>{{ config('app.name', 'Laravel') }}</title>--}}

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Scripts -->
    @routes
    @vite('resources/js/app.js')
    @inertiaHead

    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png?v=1">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png?v=1">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png?v=1">
    <link rel="manifest" href="/icons/site.webmanifest?v=1">
    <link rel="mask-icon" href="/icons/safari-pinned-tab.svg?v=1" color="#5bbad5">
    <link rel="shortcut icon" href="/icons/favicon.ico?v=1">
    <meta name="apple-mobile-web-app-title" content="Cart Scheduler">
    <meta name="application-name" content="Cart Scheduler">
    <meta name="msapplication-TileColor" content="#00aba9">
    <meta name="msapplication-config" content="/icons/browserconfig.xml?v=1">
    <meta name="theme-color" content="#ffffff">
</head>
<body class="font-sans antialiased dark:bg-slate-800">
@inertia
</body>
</html>
