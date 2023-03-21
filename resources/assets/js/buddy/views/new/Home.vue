<template>
    <div>
        <section class="hero" id="hero">
            <div class="hero-wrapper">
                <div class="hero-inner fly">
                    <Lang/>
                    <div class="logo">
                        <a href="/">
                            <img src="/new/img/buddy-logo.svg" alt="Buddy | Buddies & Benefits" />
                        </a>
                    </div>
                    <h1 class="hero-title">{{ trans('home.tagline') }}</h1>
                    <div class="descr">
                        {{ trans('home.fun') }}
                    </div>
                    <div class="login">
                        <Login/>
                        <div class="join-box">
                            <div class="title">{{ trans('home.new_here') }}</div>
                            <a @click="register" class="btn green">{{ trans('join_now') }}</a>
                        </div>
                    </div>
                    <div class="socials-box">
                        <div class="follow-us-cloud"></div>
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
                    <div class="load-more pulsate-fwd">
                        <div class="down"></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="find" class="left">
            <div class="section-background"></div>
            <div class="section-shadow"></div>
            <div class="section-wrapper">
                <div class="section-info fly">
                    <div class="icon-box find"></div>
                    <div class="title" v-html="trans('home.wants')"></div>
                    <div class="descr" v-html="trans('home.wants_desc')"></div>
                </div>
            </div>
        </section>

        <section id="share" class="right">
            <div class="section-background"></div>
            <div class="section-shadow"></div>
            <div class="section-wrapper">
                <div class="section-info fly">
                    <div class="icon-box share"></div>
                    <div class="title" v-html="trans('home.videos')"></div>
                    <div class="descr" v-html="trans('home.videos_desc')"></div>
                </div>
            </div>
        </section>

        <section id="browse" class="left">
            <div class="section-background"></div>
            <div class="section-shadow"></div>
            <div class="section-wrapper">
                <div class="section-info fly">
                    <div class="icon-box browse"></div>
                    <div class="title" v-html="trans('home.calendar')"></div>
                    <div class="descr" v-html="trans('home.calendar_desc')"></div>
                </div>
            </div>
        </section>

        <section id="download" class="left">
            <div class="section-wrapper">
                <div class="illustration-box">
                    <img src="/new/img/backgrounds/download-illustration-mobile.png" srcset="/new/img/backgrounds/download-illustration-mobile@2x.png 2x, /new/img/backgrounds/download-illustration-mobile@3x.png 3x" class="mobile fly" alt="Buddy App" />
                    <img src="/new/img/backgrounds/download-illustration-desktop.png" srcset="/new/img/backgrounds/download-illustration-desktop@2x.png 2x, /new/img/backgrounds/download-illustration-desktop@3x.png 3x" class="desktop fly" alt="Buddy App" />
                </div>
                <div class="section-info fly">
                    <div class="title" v-html="trans('home.everywhere')"></div>
                    <div class="descr" v-html="trans('home.everywhere_desc')"></div>
                    <a @click="register" class="btn green">{{ trans('home.sign_up_now') }}</a>
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
                </div>
            </div>
        </section>

        <section id="sign-up" class="left">
            <div class="section-wrapper">
                <div class="illustration-box">
                    <div class="pattern"></div>
                    <img src="/new/img/buddy.svg" class="fly" alt="Buddy" />
                </div>
                <div class="section-info fly">
                    <div class="title" v-html="trans('home.generation')"></div>
                    <div class="descr" v-html="trans('home.generation_desc')"></div>
                    <a @click="register" class="btn green">{{ trans('home.sign_up_now') }}</a>
                </div>
            </div>
        </section>
        <!-- <announce :type="'welcome-back'">
        </announce> -->
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
