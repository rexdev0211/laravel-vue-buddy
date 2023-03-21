<template>
    <footer id="footer" class="app">
        <div class="footer-inner">
            <div class="column first">
                <div class="logo">
                    <a href="index.html">
                        <img src="/new/img/buddy-logo.svg" alt="Buddy | Buddies & Benefits" />
                    </a>
                </div>
            </div>
            <div class="column">
                <ul>
                    <li>
                        <a href="/content-page/guidelines">Buddy Guidelines</a>
                    </li>
                    <li>
                        <a href="/content-page/terms">{{ trans('home.terms') }}</a>
                    </li>
                    <li>
                        <a href="/content-page/privacy">{{ trans('home.privacy') }}</a>
                        
                    </li>
                </ul>
            </div>
            <div class="column">
                <ul>
                    <li>
                        <a href="javascript:void(0)" @click="help($event)">{{ trans('links.support') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>
</template>

<script>
    export default {
        mixins: [require('@general/lib/mixin').default],
        methods: {
            help(event) {
                if (typeof zE != "undefined") {
                    event.preventDefault()
                    zE.activate()
                }
            },
        },
    }
</script>
