<!doctype html>
<html class="layout-app no-js" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">

    <title>{{ config('view.title') }}</title>

    <meta name="description" content="Love hot guys? Chat with gay and bi men and share your kinky videos in Europe's hottest gay app. It's fast and free. If you want kinky fun, come and join Buddy.net!"/>
    <meta property="og:title" content="BUDDY.net - Meet gay men for kinky fun"/>
    <meta property="og:description" content="You want kinky fun? Find the hottest gay guys on BUDDY.net"/>
    <meta property="og:url" content="https://buddy.net"/>
    <meta property="og:site_name" content="BUDDY.net">
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://buddy.net/uploads/pages/buddy.png"/>
    <meta property="og:image:width" content="512"/>
    <meta property="og:image:height" content="512"/>

    <link rel="stylesheet" href="{{ mix('assets/css/out/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/ext/foundation-icons.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/ext/font-awesome.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/ext/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/ext/nouislider.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/font-styles.css') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    <script src="https://www.google.com/recaptcha/api.js?onload=vueRecaptchaApiLoaded&render=explicit" async defer></script>

    <!-- Favicon -->
    <link rel="apple-touch-icon" href="/main/img/favicon/black/apple-touch-icon.png?v=5">
    <link rel="apple-touch-icon-precomposed" href="/main/img/favicon/black/apple-touch-icon-precomposed.png?v=5">
    <link rel="apple-touch-icon" sizes="57x57" href="/main/img/favicon/black/apple-touch-icon-57x57.png?v=5">
    <link rel="apple-touch-icon" sizes="60x60" href="/main/img/favicon/black/apple-touch-icon-60x60.png?v=5">
    <link rel="apple-touch-icon" sizes="72x72" href="/main/img/favicon/black/apple-touch-icon-72x72.png?v=5">
    <link rel="apple-touch-icon" sizes="76x76" href="/main/img/favicon/black/apple-touch-icon-76x76.png?v=5">
    <link rel="apple-touch-icon" sizes="114x114" href="/main/img/favicon/black/apple-touch-icon-114x114.png?v=5">
    <link rel="apple-touch-icon" sizes="120x120" href="/main/img/favicon/black/apple-touch-icon-120x120.png?v=5">
    <link rel="apple-touch-icon" sizes="144x144" href="/main/img/favicon/black/apple-touch-icon-144x144.png?v=5">
    <link rel="apple-touch-icon" sizes="152x152" href="/main/img/favicon/black/apple-touch-icon-152x152.png?v=5">
    <link rel="apple-touch-icon" sizes="180x180" href="/main/img/favicon/black/apple-touch-icon-180x180.png?v=5">
    <link rel="icon" type="image/png" sizes="32x32" href="/main/img/favicon/black/favicon-32x32.png?v=5">
    <link rel="icon" type="image/png" sizes="16x16" href="/main/img/favicon/black/favicon-16x16.png?v=5">
    <link rel="mask-icon" href="/main/img/favicon/black/safari-pinned-tab.svg" color="#1D1D1D">
    <meta name="msapplication-TileColor" content="#004646">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <meta name="apple-mobile-web-app-status-bar-style" content="#1D1D1D">
    <meta name="theme-color" content="#1D1D1D">
    <link rel="manifest" href="/manifest.json">

    {{-- <link rel="stylesheet" v-if="isDesktop" href="{{ mix('/assets/css/out/desktop.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ mix('assets/css/out/mobile.min.css') }}">
    <link rel="stylesheet" href="{{ mix('main/css/app.min.css') }}">
</head>
<body class="mobile-breakpoint">

<div id="slim-elem" style="display: none;"></div>

<div id="app" style="height: 100%;" @click="tapped" :class="{[this.$route.name]: true}">
    <template v-if="!isApplicationLoading">
        @yield('content')

        <div class="buddyAtWork" @click="goTo('/discover')" v-if="isBuddyAtWork" style="display: none;" :class="{'show': isBuddyAtWork}">
            <div class="buddyAtWorkImg">
                <img src="/images/splash/we-ll-be-back-soon.png">
            </div>
        </div>

        <alert v-if="requirementsAlert.isShow" v-on:hide-alert="requirementsAlertHide" v-bind:clear="true">
            <div :class="['popup', 'popup-' + requirementsAlert.type, 'requirementsAlert', 'requirementsAlert-' + requirementsAlert.type]">
                <div class="popup-inner">
                    <img class="icon" :src="requirementsAlert.image" :alt="requirementsAlert.title" :style="requirementsAlert.imageStyle" />
                    <template v-if="requirementsAlert.type != 'change_settings'">
                        <div class="title">@{{ requirementsAlert.title }}</div>
                        <div class="description">
                            @{{ requirementsAlert.description }}
                            <span class="requirementsAlertProIcon" v-if="requirementsAlert.withProIcon" :class="{'pro': requirementsAlert.withProIcon}"></span>
                        </div>
                    </template>
                    <template v-else>
                        <div class="title">@{{ requirementsAlert.title }}</div>
                        <p>@{{ requirementsAlert.description }}</p>
                    </template>
                    <button class="btn" @click="requirementsAlertRedirect">@{{ requirementsAlert.button }}</button>
                </div>
            </div>
        </alert>
        <alert v-if="announceAlert.isShow" v-on:hide-alert="announceAlertHide" v-bind:clear="true">
            <div class="announceAlert">
                <img src="/images/splash/update.png" alt="Update Announce" />
                <p v-html="announceAlert.message"></p>
            </div>
        </alert>
    </template>
</div>

<!-- Scripts -->
@include('jsvars')
@stack('scripts')
<script>
    caches.keys().then(function (names) {
        if (names?.length > 0) {
            for (let name of names) {
                caches.delete(name)
            }
            window.location.reload(true)
        }
    })
</script>
<script src="{{ asset('assets/ext/swiper.min.js') }}"></script>
<script src="{{ asset('assets/ext/nouislider.min.js') }}"></script>
@if(config('const.ECHO_ENABLED'))
<script src="//{{ config('const.NODEJS_DOMAIN') }}:{{ config('const.NODEJS_PORT') }}/socket.io/socket.io.js"></script>
@endif
<script src="{{ mix('js/app.js') }}"></script>
<script src="{{ asset('assets/ext/jquery-scrolltofixed-min.js') }}"></script>

<!-- Start of buddynet Zendesk Widget script -->
<!--script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=626ad4fa-e894-4b1b-a9f9-7f62d2cdf6bf"> </script-->
<!-- End of buddynet Zendesk Widget script -->
@include('service_worker')

<link rel="stylesheet" href="{{ mix('assets/css/out/custom-common.min.css') }}">

</body>
</html>
