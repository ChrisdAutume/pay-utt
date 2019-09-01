<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>EtuPay - Erreur {{ $e->getStatusCode() }}</title>
    <link href="{{ @asset('css/errors.css') }}" rel="stylesheet" />
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />

	<link rel="apple-touch-icon" sizes="76x76" href="{{ @asset('img/logo.png') }}" />
	<link rel="icon" type="image/png" href="{{ @asset('favicon.ico') }}" />
</head>
<body class="bg-purple">

        <div class="stars">
            <div class="central-body">
                    <img class="image-logo" src="{{ @asset('images/logo.png') }}" width="200px">
                    <img class="image-404" src="{{ @asset('images/errors/404.svg') }}" width="300px">
            </div>
            <div class="objects">
                <img class="object_rocket" src="{{ @asset('images/errors/rocket.svg') }}" width="40px">
                <div class="earth-moon">
                    <img class="object_earth" src="{{ @asset('images/errors/earth.svg') }}" width="100px">
                    <img class="object_moon" src="{{ @asset('images/errors/moon.svg') }}" width="80px">
                </div>
                <div class="box_astronaut">
                    <img class="object_astronaut" src="{{ @asset('images/errors/astronaut.svg') }}" width="140px">
                </div>
            </div>
            <div class="glowing_stars">
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>

            </div>

        </div>
<!-- {{ $e->getStatusCode() }} - {{ $e->getMessage() }}-->
    </body>
    </html>
