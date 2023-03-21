<template>
    <footer id="footer">
        <div class="footer-inner">
            <div class="column first">
                <div class="logo">
                    <a href="index.html">
                        <img src="/new/img/buddy-logo.svg" alt="Buddy | Buddies & Benefits" />
                    </a>
                </div>
            </div>
            <div class="column">
                <div class="title">{{ trans('home.company') }}</div>
                <ul>
                    <li>
                        <a href="/page/about_us">{{ trans('home.about_us') }}</a>
                    </li>
                    <li>
                        <a href="https://intobuddy.com">{{ trans('home.blog') }}</a>
                    </li>
                    <li>
                        <a href="/page/support" @click="help($event)">{{ trans('home.help') }}</a>
                    </li>
                    <li>
                        <a href="https://cs.segpay.com">{{ trans('home.support') }}</a>
                    </li>
                </ul>
            </div>
            <div class="column">
                <div class="title">{{ trans('home.legal') }}</div>
                <ul>
                    <li>
                        <a href="/page/privacy">{{ trans('home.privacy') }}</a>
                    </li>
                    <li>
                        <a href="/page/terms">{{ trans('home.terms') }}</a>
                    </li>
                    <li>
                        <a href="/page/2257">{{ trans('home.usc') }}</a>
                    </li>
                    <li>
                        <a href="/page/imprint">{{ trans('home.imprint') }}</a>
                    </li>
                </ul>
            </div>
            <div class="column">
                <div class="title">{{ trans('home.languages') }}</div>
                <ul>
                    <li>
                        <a @click.prevent="setAppLanguage('en')">English</a>
                    </li>
                    <li>
                        <a @click.prevent="setAppLanguage('de')">Deutsch</a>
                    </li>
                    <li>
                        <a @click.prevent="setAppLanguage('fr')">Français</a>
                    </li>
                    <li>
                        <a @click.prevent="setAppLanguage('it')">Italiano</a>
                    </li>
                    <li>
                        <a @click.prevent="setAppLanguage('nl')">Nederlands</a>
                    </li>
                    <li>
                        <a @click.prevent="setAppLanguage('es')">Español</a>
                    </li>
                    <li>
                        <a @click.prevent="setAppLanguage('pt')">Português</a>
                    </li>
                </ul>
            </div>
            <div class="column">
                <div class="title">{{ trans('home.follow_us') }}</div>
                <ul class="socials">
                    <li>
                        <a href="https://www.instagram.com/buddy_net/" target="_blank" rel="nofollow" title="Instagram"><i class="instagram"></i></a>
                    </li>
                    <li>
                        <a href="https://twitter.com/buddy_net" target="_blank" rel="nofollow" title="Twitter"><i class="twitter"></i></a>
                    </li>
                    <li>
                        <a href="https://www.facebook.com/buddydate" target="_blank" rel="nofollow" title="Facebook"><i class="fb"></i></a>
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
