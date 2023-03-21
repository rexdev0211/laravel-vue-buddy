<template>
    <div style="height: 100%;">
        <div class="home-page-screen1 home-page-screen1-app">
            <div>
                <div class="lang-bar-container">
                    <LangChangeBar></LangChangeBar>
                </div>

                <div style="text-align: center; margin-top: 50px;">
                    <a href="/"><img  src="/assets/img/logos/BB_Logo_white_small.png" alt=""></a>
                </div>

                <div class="home-page-screen1__tagline home-page-screen1__tagline-app" v-html="trans('home_old.tagline')">
                </div>
            </div>

            <div class="home-page-screen1__content">
                <div class="home-page-screen1__buttons">
                    <button dusk="registration-link" @click="goTo('/register')" class="bb-button-green bb-button-green--big" v-html="trans('home_old.get_started')"></button>
                    <button v-if="isMobile" @click="goTo('/log-in')" class="bb-button-grey" v-html="trans('login')"></button>
                </div>

                <div style="height: 35px">
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .lang-bar-container {
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
    }
</style>

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
                                    this.$router.push('/discover');
                                })
                                .catch(error => {
                                    this.error_message = error;
                                    this.password = '';
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