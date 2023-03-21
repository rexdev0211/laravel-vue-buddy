<template>
    <div id="application-wrapper">
        <section class="modal reset-password">
            <div class="inner">
                <div class="section-header">
                    <i class="back" @click="goStepBack"></i>
                </div>

                <div class="section-body">
                    <div class="step">
                        <div class="headline">{{ trans('reset_password') }}</div>
                        <div class="buddyname-data">
                            <div class="callout success" v-if="successMessage">
                                <p class="text-center">
                                    {{ successMessage }}
                                </p>
                            </div>
                            <div class="callout alert" v-if="errorMessage">
                                <p class="text-center">
                                    {{ errorMessage }}
                                </p>
                            </div>

                            <form id="reset__form" data-abide2 data-vv-scope="resetForm" @submit.prevent="submitForm" v-show="formVisible">
                                <label>
                                    <input class="form-control"type="email" v-bind:placeholder="trans('email')" v-bind:data-vv-as="trans('email')" required v-model="email" v-validate="'required|email'" :class="{'is-invalid-input': errors.has('resetForm.email')}" name="email">

                                    <span class="form-error" v-show="errors.has('resetForm.email')" :class="{'is-visible': errors.has('resetForm.email')}">
                                        {{ errors.first('resetForm.email') }}
                                    </span>
                                </label>
                                <label>
                                    <input class="form-control" type="password" v-bind:placeholder="trans('password')" v-bind:data-vv-as="trans('password')" required v-model="password" v-validate="'required|min:6'" :class="{'is-invalid-input': errors.has('resetForm.password')}" name="password" ref="password">

                                    <span class="form-error" v-show="errors.has('resetForm.password')" :class="{'is-visible': errors.has('resetForm.password')}">
                                        {{ errors.first('resetForm.password') }}
                                    </span>
                                </label>
                                <label>
                                    <input class="form-control" type="password" v-bind:placeholder="trans('confirm_password')" v-bind:data-vv-as="trans('confirm_password')" required v-model="password_confirmation" v-validate="'required|confirmed:password'" :class="{'is-invalid-input': errors.has('resetForm.password_confirmation')}" name="password_confirmation">

                                    <span class="form-error" v-show="errors.has('resetForm.password_confirmation')" :class="{'is-visible': errors.has('resetForm.password_confirmation')}">
                                        {{ errors.first('resetForm.password_confirmation') }}
                                    </span>
                                </label>

                                <button class="btn" type="submit">
                                    {{ trans('reset_password') }}
                                </button>
                            </form>
                        </div>
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
                email: '',
                password: '',
                password_confirmation: '',
                successMessage: '',
                errorMessage: '',
                formVisible: true
            }
        },
        props: ['token'],
        mixins: [require('@general/lib/mixin').default],
        methods: {
            goStepBack(fallback) {
                window.location.href = window.location.origin
            },
            submitForm() {
                this.$validator.validateAll('resetForm').then((result) => {
                    if (result) {
                        let data = {
                            email: this.email,
                            password: this.password,
                            password_confirmation: this.password_confirmation,
                            token: this.token
                        };

                        axios.post('/api/password/reset', data)
                            .then((response) => {
                                this.errorMessage = response.data.success ? null : this.trans(response.data.error);
                                this.successMessage = response.data.success ? this.trans(response.data.message) : null;

                                if(this.successMessage) {
                                    this.email = '';
                                    this.password = '';
                                    this.password_confirmation = '';
                                    return true;
                                }
                                else {
                                    this.showErrorNotification(response.data.message)
                                    return false;
                                }
                            })
                            .catch((response) => {
                                this.showErrorNotification(response.data.message)
                                return false;
                            })
                            .then((success) => {
                                if(success) {
                                    this.errors.clear(); //it doesn't work in first then()

                                    this.formVisible = false;
                                }
                            })
                    }
                });
            }
        }
    }
</script>
