<script>
window.DOMAIN = '{{ config('const.DOMAIN') }}';

window.SHARING_DOMAIN = '{{ config('const.SHARING_DOMAIN') }}';

window.FREE_FAVORITES_LIMIT = {{ config('const.FREE_FAVORITES_LIMIT', 99) }};
window.FREE_BLOCKS_LIMIT    = {{ config('const.FREE_BLOCKS_LIMIT', 99) }};

window.APP_URL = '{{ config('app.url') }}';
window.APP_ENV = '{{ \App::environment() }}';
window.CHAT_DOMAIN = '{{ config('const.CHAT_DOMAIN') }}';
window.NODEJS_DOMAIN = '{{ config('const.NODEJS_DOMAIN') }}';
window.NODEJS_PORT = {{ config('const.NODEJS_PORT', 2052) }};
window.GMAP_API_KEY = '{{ config('const.GMAP_API_KEY') }}';
window.MAP_PROVIDER = '{{ config('const.MAP_PROVIDER') }}';
window.LOAD_CHAT_MESSAGES_LIMIT = {{ config('const.LOAD_CHAT_MESSAGES_LIMIT', 70) }};
window.LOAD_USERS_AROUND_LIMIT = {{ config('const.LOAD_USERS_AROUND_LIMIT', 48) }};
window.REFRESH_LAST_ACTIVE_SECONDS = {{ config('const.REFRESH_LAST_ACTIVE_SECONDS', 120) }};
window.MINIMUM_AGE = {{ config('const.MINIMUM_AGE') }};
window.RECAPTCHA_SITE_KEY = '{{ config('const.RECAPTCHA_SITE_KEY') }}';
window.MAX_PUBLIC_PICTURES_AMOUNT = {{ config('const.MAX_PUBLIC_PICTURES_AMOUNT', 4) }};
window.MAX_PUBLIC_VIDEOS_AMOUNT = {{ config('const.MAX_PUBLIC_VIDEOS_AMOUNT', 4) }};
window.REFRESH_ADDRESS_FOR_LAT_LNG_CHANGE_METERS = {{ config('const.REFRESH_ADDRESS_FOR_LAT_LNG_CHANGE_METERS', 200) }};
window.DISTANCE_METERS_DIFFERENCE_RELOAD_USERS_AROUND = {{ config('const.DISTANCE_METERS_DIFFERENCE_RELOAD_USERS_AROUND', 500) }};
window.REFRESH_USERS_AROUND_INTERVAL_SECONDS = {{ config('const.REFRESH_USERS_AROUND_INTERVAL_SECONDS', 600) }};
window.PRE_SEARCH_USERS_AROUND_KM = {{ config('const.PRE_SEARCH_USERS_AROUND_KM', 50) }};
window.LOAD_EVENTS_DATES_PER_PAGE = {{ config('const.LOAD_EVENTS_DATES_PER_PAGE', 10) }};
window.LOAD_CLUBS_PER_PAGE = {{ config('const.LOAD_CLUBS_PER_PAGE', 20) }};
window.LOAD_EVENTS_PER_DATE_INITIAL = {{ config('const.LOAD_EVENTS_PER_DATE_INITIAL', 3) }};
window.LOAD_EVENTS_PER_DATE_NEXT = {{ config('const.LOAD_EVENTS_PER_DATE_NEXT', 15) }};
window.MAX_EVENT_PHOTOS = {{ config('const.MAX_EVENT_PHOTOS', 4) }};
window.MAX_EVENT_VIDEOS = {{ config('const.MAX_EVENT_VIDEOS', 4) }};
window.BB_USER_ID = '{{ config('const.BB_USER_ID') }}';
window.APP_DOMAIN = '{{ config('const.APP_DOMAIN') }}';
window.START_NUDITY_RATING = {{ config('const.START_NUDITY_RATING', 0.4) }};
window.LOAD_VISITORS_LIMIT = {{ config('const.LOAD_VISITORS_LIMIT', 24) }};
window.LOAD_TAPS_LIMIT = {{ config('const.LOAD_TAPS_LIMIT', 24) }};
window.LOAD_CHAT_WINDOWS_LIMIT = {{ config('const.LOAD_CHAT_WINDOWS_LIMIT', 100) }};
window.FREE_EVENTS_LIMIT = {{ config('const.FREE_EVENTS_LIMIT', 99) }};
</script>