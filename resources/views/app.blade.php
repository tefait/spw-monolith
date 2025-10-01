<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css" rel="stylesheet">
    <link href="https://cdn-uicons.flaticon.com/2.6.0/uicons-bold-rounded/css/uicons-bold-rounded.css" rel="stylesheet">
    <link href="https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-chubby/css/uicons-solid-chubby.css" rel="stylesheet">
    <link href="https://cdn-uicons.flaticon.com/2.6.0/uicons-brands/css/uicons-brands.css" rel="stylesheet">
    <link href="https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css" rel="stylesheet">
    <title inertia>{{ config('app.name', 'SIPEKA') }}</title>

    {{-- @routes --}}
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>

<body class="font-display antialiased">
    @inertia
</body>

</html>