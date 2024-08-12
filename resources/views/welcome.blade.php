<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name', 'API - Base Code') }}</title>
    <meta name="author" content="Saugi, Miguel Alejandro González Antúnez" />
    <meta name="keywords" content="api,restfull,startpoint" />

    <style type="text/css">
        body {
            height: 100vh;
            background-color: #4158D0;
            background-image: linear-gradient(43deg, #4158D0 0%, #C850C0 46%, #FFCC70 100%);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: white;
            font-style: oblique;
        }

        .centered-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
    </style>
</head>

<body>
    <div class="centered-content">
        <h1>{{ config('app.name', 'API - Base Code') }}</h1>
        <h3>{{ __('Versión :number', ['number' => config('app.version')]) }}</h3>
        <h3><a href="{{ route('scribe') }}">{{ __('Documentación') }}</a></h3>
    </div>
</body>

</html>