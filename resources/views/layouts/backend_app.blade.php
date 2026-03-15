<!DOCTYPE html>
<html lang="en">

<head data-baseurl="{{ url('/') }}">
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <script>
        window.laravel = {
            csrfToken: '{{ csrf_token() }}',
            baseurl: '{{ url('/') }}',
            app_name: '{{ config('app.name') }}'
        };
    </script>

    <title>{{ config('app.name', 'ERP') }}</title>

    @vite(['resources/css/app.css', 'resources/js/admin_app.js'])
</head>

<body>
    <div id="app" class="wrapper"></div>
</body>

</html>
