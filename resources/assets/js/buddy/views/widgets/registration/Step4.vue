<template>
    <div dusk="registration-step-3" class="step">
        <div class="headline">{{ trans('reg.pick_nickname') }}</div>

        <form novalidate data-vv-scope="form3" @submit.prevent="submitForm" class="registration-nickname">
            <input :style="{'display': honeypot.hide ? 'none' : 'block'}" name="website" v-model="honeypot.value" />
            <div class="buddyname-data">
                <label class="registration-nickname">
                    <input
                        dusk="registration-nickname"
                        class="form-control"
                        type="text"
                        v-bind:placeholder="trans('reg.nickname')"
                        v-bind:data-vv-as="trans('reg.nickname')"
                        v-model="nickname"
                        v-validate="{required: true, min: 3, max: 32}"
                        :class="{'is-invalid-input': errors.has('form3.nickname')}" name="nickname"
                        required>

                    <span class="form-error" :class="{'is-visible': errors.has('form3.nickname')}">
                      {{ errors.first('form3.nickname') }}
                    </span>
                </label>
            </div>

            <div class="btns">
                <div class="registration-agreement">
                    <div class="checkbox-container">
                        <label dusk="registration-agreement" class="checkbox-label">
                            <input
                                type="checkbox"
                                name="terms"
                                v-bind:data-vv-as="trans('reg.terms_of_use')"
                                v-model="acceptTerms"
                                v-validate="'required'"
                            >
                            <span class="checkbox-custom"></span>

                            <i18n path="reg.accept_terms_privacy_text" tag="div" class="input-title">
                                <a @click="showPagePopup('terms')">{{ trans('reg.terms_of_use') }}</a>
                                <a @click="showPagePopup('privacy')">{{ trans('reg.privacy_policy') }}</a>
                            </i18n>
                        </label>
                    </div>
                    <div class="row reg-terms-errors" v-if="errors.has('form3.terms')">
                        <span class="small-12 columns form-error" :class="{'is-visible': errors.has('form3.terms')}">
                            {{ errors.first('form3.terms') }}
                        </span>
                    </div>
                </div>

                <button dusk="registration-next" class="btn green" id="button3" type="submit">{{ trans('reg.finish') }}</button>

            </div>
        </form>
    </div>
</template>

<script>
    import _ from 'lodash';
    import { VueReCaptcha } from 'vue-recaptcha-v3'

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
          VueReCaptcha
        },
        methods: {
            async recaptcha() {
                await this.$recaptchaLoaded()

                return await this.$recaptcha('login')
            },
            getCaptchaKey() {
                return window.RECAPTCHA_SITE_KEY;
            },
            onCaptchaVerify(response) {
                this.humanVerification = response;
            },
            onCaptchaExpired() {
                this.$refs.recaptcha.reset();
                this.humanVerification = false;
            },
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
                const token = await this.recaptcha();

                this.showLoadingButton('#button3')
                let validated = this.$validator.validateAll('form3')
                if (validated) {
                    let data = {
                        email: this.email,
                        password: this.password,
                        dob: this.dob,
                        name: this.nickname,
                        terms: this.acceptTerms,
                        honeypot: this.honeypot.value,
                        lang: app.lang,
                        lat: this.lat,
                        lng: this.lng,
                        address: this.address,
                        country: this.country,
                        country_code: this.country_code,
                        location_type: this.location_type,
                        locality: this.locality,
                        state: this.state,
                        recaptcha: token,
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

                            if (app.isDesktop) {
                              this.closeRegisterModal()
                            }

                            this.goTo('/welcome')
                        }
                    } catch (error) {
                        let errorFields = error.data.fields
                        if (_.intersection(errorFields, ['email', 'password', 'dob']).length !== 0) {
                            this.step = 1;
                        } else if (_.intersection(errorFields, ['photo']).length !== 0) {
                            this.step = 2;
                        } else if (_.intersection(errorFields, ['terms', 'captcha']).length !== 0) {
                            this.step = 3;
                        }
                    }
                }
                this.restoreLoadingButton('#button3')
            },
        },
        mounted() {
            this.honeypot.hide = true
        }
    }
</script>
<style>
.registration-nickname {
  max-width: 100%;
}
</style>