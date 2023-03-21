<template>
    <div id="application-wrapper">
        <section class="modal recover-password">
            <div class="inner">
                <div class="section-header">
                    <i class="back" @click="goStepBack"></i>
                </div>

                <div class="section-body">
                    <div class="step">
                        <div class="headline">{{ trans('recover_password') }}</div>
                        <div class="buddyname-data">
                            <form id="recover__form" data-abide2 data-vv-scope="recoverForm" @submit.prevent="submitForm">
                                <label>
                                    <input
                                        v-model="email"
                                        type="email"
                                        name="email"
                                        class="form-control"
                                        :placeholder="trans('email')"
                                        :data-vv-as="trans('email')"
                                        v-validate="'required|email'"
                                        :class="{'is-invalid-input': errors.has('recoverForm.email')}"
                                        required>

                                    <span class="form-error" v-show="errors.has('recoverForm.email')" :class="{'is-visible': errors.has('recoverForm.email')}">
                                        {{ errors.first('recoverForm.email') }}
                                    </span>
                                </label>

                                <button class="btn" type="submit">
                                    {{ trans('recover_now') }}
                                </button>
                            </form>
                        </div>

                        <a href="#" class="btn darker">Need help?</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                email: ''
            }
        },
        mixins: [require('@general/lib/mixin').default],
        created() {
            this.email = sessionStorage.getItem('home.loginEmail');
            sessionStorage.removeItem('home.loginEmail');
        },
        methods: {
            goStepBack(fallback) {
                window.location.href = window.location.origin
            },
            async submitForm() {
                let valid = await this.$validator.validateAll('recoverForm')
                if (!valid) {
                    return
                }

                let response = await axios.post('/api/password/email', {
                    email: this.email,
                    lang: app.lang
                })

                if (response.status === 200) {
                    this.showSuccessNotification(response.data.message)
                }
            }
        }
    }
</script>
