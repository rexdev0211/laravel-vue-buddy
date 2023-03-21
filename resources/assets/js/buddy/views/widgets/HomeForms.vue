<template>
    <div style="height: 100%;">
        <div class="home-page-screen1">
            <div class="home-page-screen1__navbar">
                <a href="/" class="b-logo"><img  src="/assets/img/logos/BB_Logo_white_small.png" alt=""></a>

                <LangChangeBar></LangChangeBar>
            </div>

            <div class="home-page-screen1__content">
                <div class="home-page-screen1__tagline" v-html="trans('home_old.tagline')">
                </div>

                <!--<div class="home-page-screen1__subtag" v-html="trans('home_old.responsible_dating')"></div>-->

                <div class="home-page-screen1__text" v-html="trans('home_old.home_text')">
                </div>

                <div class="home-page-screen1__invite" v-html="trans('home_old.join_start')">
                </div>

                <div class="home-page-screen1__buttons">
                    <button dusk="registration-link" @click="goTo('/register')" class="bb-button-green bb-button-green--big" v-html="trans('home_old.get_started')"></button>
                    <button v-if="isDesktop" @click="loginScroll" class="bb-button-grey bb-button-grey--big" v-html="trans('login')"></button>
                    <button v-if="isMobile" @click="goTo('/log-in')" class="bb-button-grey" v-html="trans('login')"></button>
                </div>

                <div class="home-page-screen1__down" v-if="isMobile">
                    <svg class="icon icon-Scroll_down" @click="scrollDown"><use v-bind:xlink:href="symbolsSvgUrl('icon-Scroll_down')"></use></svg>
                </div>
            </div>

            <div class="home-page-screen1__down" v-if="isDesktop">
                <svg class="icon icon-Scroll_down" @click="scrollDown"><use v-bind:xlink:href="symbolsSvgUrl('icon-Scroll_down')"></use></svg>
            </div>
        </div>

        <div class="home-page-screen2">
            <div class="home-page-screen2__content">
                <div class="home-page-screen2__login" v-if="isDesktop">
                    <form class="w-login__form" data-abide2 novalidate data-vv-scope="loginForm" @submit.prevent="login">
                        <div class="title">{{ trans('login') }}</div>

                        <div data-abide-error class="alert callout" v-show="error_message">
                            <p class="text-center">
                                <i class="fi-alert"></i> {{ error_message }}
                            </p>
                        </div>

                        <label>
                            <input dusk="login" class="gray-input" type="text" autocomplete="email" v-bind:placeholder="trans('email_or_username')" v-bind:data-vv-as="trans('email_or_username')" required v-model="email" v-validate="'required'" :class="{'is-invalid-input': errors.has('loginForm.email')}" name="email">

                            <span class="form-error" :class="{'is-visible': errors.has('loginForm.email')}">
                              {{ errors.first('loginForm.email') }}
                            </span>
                        </label>

                        <label>
                            <input dusk="password" class="gray-input" type="password" autocomplete="current-password" id="password" v-bind:placeholder="trans('password')" v-bind:data-vv-as="trans('password')" required v-validate="'required'" v-model="password" :class="{'is-invalid-input': errors.has('loginForm.password')}"  name="password">

                            <span class="form-error" :class="{'is-visible': errors.has('loginForm.password')}">
                              {{ errors.first('loginForm.password') }}
                            </span>
                        </label>

                        <button dusk="submit-login-form" class="bb-button-grey bb-button-grey--big">{{ trans('login') }}</button>

                        <a v-on:click="forgotPassword()" href="javascript:void(0)">{{ trans("forgot_password") }}</a>

                        <label class="label-first margin-top-15" for="checkbox1">
                            {{ trans('stay_logged_in') }}
                            <input id="checkbox1" type="checkbox" value="1" v-model="remember_me" style="display: none;">
                            <svg class="icon icon-Checkbox"><use v-bind:xlink:href="symbolsSvgUrl('icon-Checkbox')"></use></svg>
                        </label>
                    </form>
                </div>

                <div class="home-page-screen2__side">
                    <section>
                        <div class="home-page-screen2__content-title" v-html="trans('home_old.screen_title1')"></div>
                        <span class="home-page-screen2__content-subtitle"v-html="trans('home_old.screen_sub1')"></span>
                        <span v-html="trans('home_old.screen_text1')"></span>
                    </section>

                    <section>
                        <div class="home-page-screen2__content-title" v-html="trans('home_old.screen_title2')"></div>
                        <span class="home-page-screen2__content-subtitle"v-html="trans('home_old.screen_sub2')"></span>
                        <span v-html="trans('home_old.screen_text2')"></span>
                    </section>

                    <section>
                        <div class="home-page-screen2__content-title" v-html="trans('home_old.screen_title3')"></div>
                        <span class="home-page-screen2__content-subtitle"v-html="trans('home_old.screen_sub3')"></span>
                        <span v-html="trans('home_old.screen_text3')"></span>
                    </section>
                </div>
            </div>

            <div class="home-page-screen2__buttons" v-if="isMobile">
                <button @click="goTo('/register')" class="bb-button-green" v-html="trans('home_old.get_started')"></button>
                <button @click="goTo('/log-in')" class="bb-button-grey" v-html="trans('login')"></button>
            </div>

            <div class="home-page-screen2__footer">
                <section>
                    <div>
                        <span v-html="trans('company')"></span>
                        <router-link to="/page/about_us">{{ trans('links.about_us') }}</router-link>
                        <a href="https://blog.barebuddy.com/">Blog</a>
                        <router-link to="/page/support">{{ trans('links.support') }}</router-link>
                        <a href="https://cs.segpay.com/" target="_blank">{{ trans('links.billing_support') }}</a>
                    </div>
                    <div>
                        <span v-html="trans('legal')"></span>
                        <router-link to="/page/privacy">{{ trans('links.privacy') }}</router-link>
                        <router-link to="/page/terms">{{ trans('links.terms') }}</router-link>
                        <router-link to="/page/2257">18 U.S.C. 2257</router-link>
                        <router-link to="/page/imprint">{{ trans('links.imprint') }}</router-link>
                    </div>
                </section>

                <div>
                    <span v-html="trans('social_media')"></span>
                    <div class="home-page-screen2__footer-icons">
                        <a href="https://www.instagram.com/buddy.date/" target="_blank"><i class="fa fa-instagram"></i></a>
                        <a href="https://www.facebook.com/barebuddy/" target="_blank"><i @click="" class="fa fa-facebook-square"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import LangChangeBar from '@buddy/views/widgets/LangChangeBar.vue';

    export default {
        data() {
            return {
                email: '',
                password: '',
                remember_me: true,
                error_message: '',
            }
        },
        mixins: [require('@general/lib/mixin').default],
        components: {
            LangChangeBar,
        },
        methods: {
            scrollDown() {
                let offset = document.querySelector('.home-page-screen2').offsetTop;

                $('#js-login-page').animate({scrollTop: offset}, 500)
            },
            forgotPassword() {
                sessionStorage.setItem('home.loginEmail', this.email);
                this.$router.push('/recover-password');
            },
            login() {
                return this.$validator.validateAll('loginForm').then((result) => {
                    if(result) {
                        let callback = () => {
                            return auth.login(this.email, this.password, this.remember_me)
                                .then(response => {
                                    this.error_message = '';
                                    app.goTo('/discover');
                                })
                                .catch(error => {
                                    this.error_message = error;
                                });
                        };

                        this.runLoadingFunction('#login', callback);
                    }
                });
            },
            loginScroll() {
                this.scrollDown();

                $('input[name=email]').focus();
            }
        }
    }
</script>
