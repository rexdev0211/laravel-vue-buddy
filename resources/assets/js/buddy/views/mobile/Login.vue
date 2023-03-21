<template>
    <div class="w-views">
        <div class="w-view">
            <div class="w-navbar">
                <div class="b-navbar centered">
                    <div class="navbar-left">
                        <button class="b-btn__icon" type="button" @click="goBack('/')">
                            <svg class="icon icon-arrow_back-navbar">
                                <use v-bind:xlink:href="symbolsSvgUrl('icon-arrow_back')"></use>
                            </svg>
                        </button>
                    </div>

                    <div class="text">{{ trans('members_login') }}</div>

                    <div class="navbar-right"></div>
                </div><!--b-navbar-->
            </div><!--w-navbar-->

            <div class="w-pages">
                <div class="b-page no-toolbar">
                    <div class="w-page__content">
                        <form class="w-login__form" data-abide2 novalidate data-vv-scope="loginForm"
                              @submit.prevent="login">
                            <div data-abide-error class="alert callout" v-show="error_message">
                                <p class="text-center">
                                    <i class="fi-alert"></i> {{ error_message }}
                                </p>
                            </div>

                            <div class="row">
                                <div class="small-10 small-offset-1 columns">
                                    <label>
                                        <input dusk="login"
                                               class="gray-input"
                                               type="text"
                                               autocomplete="email"
                                               v-bind:placeholder="trans('email_or_username')"
                                               v-bind:data-vv-as="trans('email_or_username')"
                                               required
                                               v-model="email"
                                               v-validate="'required'"
                                               :class="{'is-invalid-input': errors.has('loginForm.email')}"
                                               name="email"
                                        >

                                        <span class="form-error" :class="{'is-visible': errors.has('loginForm.email')}">
                                          {{ errors.first('loginForm.email') }}
                                        </span>
                                    </label>
                                </div>

                                <div class="small-10 small-offset-1 columns">
                                    <label>
                                        <input dusk="password" class="gray-input" type="password"
                                               autocomplete="current-password" id="password"
                                               v-bind:placeholder="trans('password')"
                                               v-bind:data-vv-as="trans('password')" required v-validate="'required'"
                                               v-model="password"
                                               :class="{'is-invalid-input': errors.has('loginForm.password')}"
                                               name="password">

                                        <span class="form-error"
                                              :class="{'is-visible': errors.has('loginForm.password')}">
                                          {{ errors.first('loginForm.password') }}
                                        </span>
                                    </label>
                                </div>
                            </div><!--row-->

                            <div class="row">
                                <fieldset class="small-12 columns">
                                    <button dusk="submit-login-form" class="bb-button-grey" id="login" type="submit"
                                            :disabled="$validator.errors.any()">{{ trans("login") }}
                                    </button>
                                </fieldset>
                            </div>

                            <p class="text-center margin-top-15">
                                <a v-on:click="forgotPassword()" href="javascript:void(0)">{{ trans("forgot_password")
                                    }}</a>
                            </p>

                            <fieldset class="w-checkbox small-12 columns">
                                <label class="label-first" for="checkbox1">
                                    {{ trans('stay_logged_in') }}
                                    <input id="checkbox1" type="checkbox" value="1" v-model="remember_me"
                                           style="display: none;">
                                    <svg class="icon icon-Checkbox">
                                        <use v-bind:xlink:href="symbolsSvgUrl('icon-Checkbox')"></use>
                                    </svg>
                                </label>
                            </fieldset><!--w-checkbox-->
                        </form><!--w-login__form-->
                    </div>
                </div><!--b-page-->
            </div><!--w-pages-->
        </div><!--w-view-->
    </div><!--w-views-->
</template>

<style scoped>
    .b-btn__icon {
        color: #8a8a8a !important;
    }

    .icon-arrow_back-navbar {
        fill: #aaaaaa;
        height: 24px;
        width: 24px;
    }

    .b-page {
        background: #292929;
    }

    .w-navbar {
        background-color: #292929;
    }

    .w-navbar .text {
        color: #fff;
        text-transform: none;
    }
</style>

<script>
    import auth from '@general/lib/auth';

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
        methods: {
            forgotPassword() {
                sessionStorage.setItem('home.loginEmail', this.email);
                this.$router.push('/recover-password');
            },
            login() {
                return this.$validator.validateAll('loginForm').then((result) => {
                    if (result) {
                        let callback = () => {
                            return auth.login(this.email, this.password, this.remember_me)
                                .then(response => {
                                    this.error_message = ''
                                    //verify one signal player on login
                                    this.verifyOneSignalPlayer()
                                    app.goTo('/discover')
                                })
                                .catch(error => {
                                    this.error_message = error
                                });
                        };
                        this.runLoadingFunction('#login', callback);
                    }
                });
            },
        },
        mounted() {
            $('input[name="email"]').focus()
        },
    }
</script>
