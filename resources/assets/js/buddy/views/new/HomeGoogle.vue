<template>
    <div>
        <section id="hero" class="hero app">
            <div class="hero-wrapper">
                <div class="hero-inner fly">
                    <div class="hero-inner-app">
                        <div class="shadow"></div>
                        <div class="logo">
                            <a href="/">
                                <img src="/new/img/buddy-logo.svg" alt="Buddy | Buddies & Benefits" />
                            </a>
                        </div>
                        <h1 class="hero-title">{{ trans('home.tagline') }}</h1>
                        <div class="descr">
                            {{ trans('home.fun') }}
                        </div>
                    </div>
                    <div class="section-wrapper">
                        <div class="section-info fly">
                            <div class="descr">We are a new community of open, kinky and judgment-free people. Whether you're gay, bi, trans, queer or without any label - become part of the BUDDY community and explore your kinks.</div>
                        </div>
                        <div class="load-more pulsate-fwd">
                            <div class="down"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="download" class="app left">
            <div class="section-wrapper">
                <div class="illustration-box">
                    <img src="/new/img/backgrounds/download-illustration-mobile.png" srcset="/new/img/backgrounds/download-illustration-mobile@2x.png 2x, /new/img/backgrounds/download-illustration-mobile@3x.png 3x" class="mobile fly" alt="Buddy App" />
                    <img src="/new/img/backgrounds/download-illustration-desktop.png" srcset="/new/img/backgrounds/download-illustration-desktop@2x.png 2x, /new/img/backgrounds/download-illustration-desktop@3x.png 3x" class="desktop fly" alt="Buddy App" />
                </div>
                <div class="section-info fly">
                    <div class="title" v-html="trans('home.everywhere')"></div>
                    <div class="descr" v-html="'Download the BUDDY app or use our website.<br><br>Join now and try it out!'"></div>
                    <div class="stores">
                        <div class="store">
                            <a href="https://itunes.apple.com/app/buddy-gay-dating-chat/id1463608052" target="_blank" rel="nofollow" title="Buddy in App Store">
                                <img src="/new/img/appstore.png" srcset="/new/img/appstore@2x.png 2x" alt="App Store" />
                            </a>
                        </div>
                        <div class="store">
                            <a href="https://play.google.com/store/apps/details?id=net.buddy" target="_blank" rel="nofollow" title="Buddy in Google Play">
                                <img src="/new/img/playstore.png" srcset="/new/img/playstore@2x.png 2x" alt="App Store" />
                            </a>
                        </div>
                    </div>
                    <a href="https://buddy.net/" class="btn green">Website</a>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import $ from "jquery";
    import scrollify from "jquery-scrollify";
    import easing from "jquery.easing";

    import Lang from "@buddy/views/new/widgets/Lang";
    import Login from "@buddy/views/new/widgets/Login";
    // import Announce from "@buddy/views/new/widgets/Announce";

    export default {
        components: {
            Lang,
            Login,
            // Announce,
        },
        data: () => ({
            timer: 0,
            window: $(window)
        }),
        mixins: [
            require('@general/lib/mixin').default
        ],
        methods: {
            register() {
              const isMobile = app.isMobile;

              if (isMobile) {
                  location.href = '/register';
              } else {
                  this.openRegisterModal();
              }

            },
            getLangText(lang) {
                if (this.isMobile) {
                    return this.trans(`${lang}_short`)
                } else {
                    return this.trans(`${lang}`)
                }
            },
            fireScrollControl(){
                let self = this
                let window_top = this.window.scrollTop()
                let window_height = this.window.height()
                let view_port_s = window_top
                let view_port_e = window_top + window_height

                $('.parallax').each(function(){
                    let obj = $(this)
                    obj.css({
                        'background-position' : 'center ' +
                            ( view_port_e - ( obj.offset().top + obj.height() - obj.height() / 2  ) ) / (-6)
                            + 'px'
                    })
                })

                if (this.timer) {
                    clearTimeout(this.timer)
                }

                $('.fly').each(function(){
                    let block = $(this)
                    let block_top = block.offset().top
                    let block_height = block.height()

                    if (block_top < view_port_e) {
                        self.timer = setTimeout(function(){
                            block.addClass('show-block')
                        }, 50)
                    }
                })
            },
            init(){
                $.scrollify({
                    section : "#hero, #find, #browse, #download, #share, #sign-up, #footer",
                    interstitialSection: ".cookies-bar, #footer",
                    easing: "easeOutQuad",
                    scrollSpeed: 600,
                    offset: 0,
                    standardScrollElements: "#footer, .announce-body",
                    scrollbars: true,
                    overflowScroll: true,
                    setHeights: true,
                    updateHash: false,
                    touchScroll: true,
                })

                $('.languages').click(function() {
                    $('#languages').toggleClass('open');
                })

                $('.down').click(function() {
                    $.scrollify.next()
                })

                $(window).scroll(this.fireScrollControl)
                $(window).resize(this.fireScrollControl)

                this.fireScrollControl()
            }
        },
        mounted() {
            console.log('[Home] Mounted')
            const backUrl = this.$route.meta.back;

            if (backUrl === '/register') {
                this.openRegisterModal();
            } else if (backUrl === '/recover-password') {
                this.openRecoverPasswordModal();
            }

            this.init()
        }
    }
</script>
