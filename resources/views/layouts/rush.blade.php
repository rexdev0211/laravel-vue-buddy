<!doctype html>
<html class="no-js" lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">

        <title>{{ config('view.title') }}</title>

        <meta name="description" content="{{ config('view.description') }}" />

        @if (\App::environment() == 'prod')
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=UA-119004020-2"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', 'UA-119004020-2');
            </script>
        @endif

        <!-- Favicon -->
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <link href="/apple_splash_640.png" sizes="640x1136" rel="apple-touch-startup-image" />
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=PYAaw2eBga">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=PYAaw2eBga">
        <link rel="icon" type="image/png" sizes="194x194" href="/favicon-194x194.png?v=PYAaw2eBga">
        <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png?v=PYAaw2eBga">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=PYAaw2eBga">
        <link rel="manifest" href="/webmanifest.json?v=PYAaw2eBga">
        <meta name="msapplication-config" content="/browserconfig.xml?v=PYAaw2eBga">
        <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#93be3d">
        <meta name="msapplication-TileColor" content="#00a300">
        <meta name="theme-color" content="#ffffff">

        <!--fonts-->
        <link rel="stylesheet" href="{{ asset('assets/fonts/font-styles.css') }}">

        <link rel="stylesheet" href="{{ mix('css/rush.min.css') }}">
    </head>
    <body>
        <div id="app" @click="userTapped">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script>
    window.app = {!! json_encode([
        'url'             => config('app.url'),
        'appDomain'       => config('const.APP_DOMAIN'),
        'debug'           => config('app.debug'),
        'strips_limit'    => config('const.RUSH_STRIPS_LIMIT', 1),
        'favorites_limit' => config('const.RUSH_FAVORITES_LIMIT', 5),
        'socketJsPort'    => config('const.NODEJS_PORT'),
    ]) !!};
    </script>
    <script src="//{{ config('const.NODEJS_DOMAIN') }}:{{ config('const.NODEJS_PORT') }}/socket.io/socket.io.js"></script>
    <script src="{{ asset('js/rush.js') }}"></script>

    @stack('scripts')

    @if (\App::environment() != 'local')
    <script>
    let worker;

    if('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
            .then(function(reg) {

                console.log('[Service worker] Registered', { reg: reg });

                window.workerReg = reg

                if (reg.waiting) {
                    worker = reg.waiting;
                }

                reg.onupdatefound = () => {
                    worker = reg.installing;

                    worker.onstatechange = () => {
                        if (worker.state == 'activated') {
                            location.reload(true)
                        }
                    };
                };
            });
    }
    </script>
    @endif
    </body>
</html>
