<template>
    <div dusk="registration-step-5" class="b-box">
        <div class="small-12 columns reg-text-finish w-form__text text-center">
            {{ trans('reg.you_made_it') }}
        </div>

<!--        <div-->
<!--            class="small-offset-1 small-10 reg-recaptcha"-->
<!--        >-->
<!--          <vue-recaptcha :sitekey="getCaptchaKey()" @verify="onCaptchaVerify" @expired="onCaptchaExpired" ref="recaptcha"></vue-recaptcha>-->
<!--        </div>-->

        <form novalidate data-vv-scope="form5" @submit.prevent="submitForm" class="text-center reg-accept-form">
            <input :style="{'display': honeypot.hide ? 'none' : 'block'}" name="website" v-model="honeypot.value" />
            <div class="row reg-accept-controls">
                <div class="small-1 small-offset-1 columns reg-accept-input">
                    <label dusk="registration-agreement" class="b-checkbox desktop">
                        <input
                            type="checkbox"
                            name="terms"
                            v-bind:data-vv-as="trans('reg.terms_of_use')"
                            v-model="acceptTerms"
                            v-validate="'required'"
                        />
                        <svg class="icon icon-Checkbox--register"><use v-bind:xlink:href="symbolsSvgUrl('icon-Checkbox')"></use></svg>
                    </label><!--b-checkbox-->
                </div>

                <i18n path="reg.accept_terms_privacy_text" tag="div" class="small-10 columns reg-accept-label">
                    <a @click="showPagePopup('terms')">{{ trans('reg.terms_of_use') }}</a>
                    <a @click="showPagePopup('privacy')">{{ trans('reg.privacy_policy') }}</a>
                </i18n>
            </div>

            <div class="row reg-terms-errors" v-if="errors.has('form5.terms')">
                <span class="small-12 columns form-error" :class="{'is-visible': errors.has('form5.terms')}">
                    {{ errors.first('form5.terms') }}
                </span>
            </div>

            <div class="row align-middle reg-btn-finish" style="margin-top: 25px">
                <div :class="{'columns': true, 'small-12': isDesktop, 'small-12': isMobile}">
                    <button dusk="registration-next" class="bb-button-green" id="button5" type="submit">{{ trans('reg.finish') }}</button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import _ from 'lodash';
    // import VueRecaptcha from 'vue-recaptcha';

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['vars'],
        data() {
            return this.vars;
        },
        computed: {
            env() {
                return window.APP_ENV
            }
        },
        components: {
            // VueRecaptcha
        },
        methods: {
            // getCaptchaKey() {
            //     return window.RECAPTCHA_SITE_KEY;
            // },
            // onCaptchaVerify(response) {
            //     this.humanVerification = response;
            // },
            // onCaptchaExpired() {
            //     this.$refs.recaptcha.reset();
            //     this.humanVerification = false;
            // },
            showPagePopup(page) {
                app.showLightLoading(true);

                let title = page == 'terms' ? this.trans('reg.terms_of_use') : this.trans('reg.privacy_policy');

                axios.get('/api/getStaticPage/'+app.lang+'/'+page)
                    .then(({data}) => {
                        this.showSuccessNotification(data.content);
                        app.showLightLoading(false);
                    })
                    .catch(e => {
                        app.showLightLoading(false);
                    })
            },
            async submitForm() {
                if (!this.humanVerification) {
                    this.showErrorNotification('reg.human_verification_required');
                    return;
                }

                this.showLoadingButton('#button5')
                let validated = this.$validator.validateAll('form5')
                if (validated) {
                    let data = {
                        name: this.nickname,
                        dob: this.dob,
                        email: this.email,
                        password: this.password,
                        terms: this.acceptTerms,
                        // recaptcha: this.humanVerification,
                        lat: this.lat,
                        lng: this.lng,
                        address: this.address,
                        location_type: this.location_type,
                        lang: app.lang,
                        locality: this.locality,
                        state: this.state,
                        country: this.country,
                        country_code: this.country_code,
                        honeypot: this.honeypot.value,
                    }

                    if (this.avatarPhoto) {
                        data.photo = this.avatarPhoto
                        data.actions = JSON.stringify(this.avatarActions)
                    }

                    let formData = this.getFormData(data)
                    try {
                        let response = await axios.post('/api/register/validateAndRegister', formData)
                        if (response.status === 200) {
                            await auth.login(this.email, this.password)
                            this.goTo('/welcome');
                        }
                    } catch (error) {
                        let errorFields = error.data.fields
                        if (_.intersection(errorFields, ['name', 'dob']).length !== 0) {
                            this.step = 1;
                        } else if (_.intersection(errorFields, ['email', 'password']).length !== 0) {
                            this.step = 2;
                        } else if (_.intersection(errorFields, ['photo']).length !== 0) {
                            this.step = 3;
                        } else if (_.intersection(errorFields, ['lat', 'lng', 'address', 'location_type']).length !== 0) {
                            this.step = 4;
                        } else if (_.intersection(errorFields, ['terms']).length !== 0) {
                            this.step = 5;
                        }
                    }
                }
                this.restoreLoadingButton('#button5')
            },
        },
        mounted() {
            this.honeypot.hide = true
        }
    }
</script>
