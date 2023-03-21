<template>
    <div class="cookies-bar" v-if="showCookiesBar">
        <div class="row">
            <div class="columns large-7 small-9">
                {{ trans('cookies_message') }}
                <router-link to="/page/privacy">{{ trans('learn_more') }}</router-link>
            </div>
            <div class="columns large-5 small-3">
                <button dusk="accept-cookie-policy" class="btn green" v-on:click="acceptCookies">OK</button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                showCookiesBar: !localStorage.getItem('cookiesAccepted')
            }
        },
        methods: {
            acceptCookies() {
                localStorage.setItem('cookiesAccepted', true);

                this.showCookiesBar = !localStorage.getItem('cookiesAccepted');
            }
        }
    }
</script>