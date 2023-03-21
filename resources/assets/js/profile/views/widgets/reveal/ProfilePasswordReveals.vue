<template>
  <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">
    <CustomReveal revealId="change-password" :isVisible="passwordFormVisible" v-on:close-reveal-change-password="closeChangePasswordModal">
        <section class="modal recover-password">
            <vue-custom-scrollbar ref="vueCustomScrollBar" class="inner" style="height: 100vh; overflow: scroll">
                <div class="section-header">
                    <i class="back" @click="closeChangePasswordModal" data-close aria-label="Close modal"></i>
                </div>

                <div class="section-body">
                    <div class="step">
                        <div class="buddyname-data">
                            <form data-vv-scope="password-form" @submit.prevent="changePassword">
                                <label>
                                    <input type="password" class="form-control" name="old_password" v-model="old_password" :class="{'is-invalid-input': errors.has('password-form.old_password')}" v-validate="'required|min:6'" v-bind:data-vv-as="trans('old_password')" v-bind:placeholder="trans('old_password')">

                                    <span class="form-error" :class="{'is-visible': errors.has('password-form.old_password')}">
                                        {{ errors.first('password-form.old_password') }}
                                    </span>
                                </label>
                                <label>
                                    <input ref="password" type="password" class="form-control" name="new_password" v-model="new_password" :class="{'is-invalid-input': errors.has('password-form.new_password')}" v-validate="'required|min:6'" v-bind:data-vv-as="trans('new_password')" v-bind:placeholder="trans('new_password')">

                                    <span class="form-error" :class="{'is-visible': errors.has('password-form.new_password')}">
                                            {{ errors.first('password-form.new_password') }}
                                        </span>
                                </label>
                                <label>
                                    <input type="password" class="form-control" name="new_password_confirmation" v-model="new_password_confirmation" :class="{'is-invalid-input': errors.has('password-form.new_password_confirmation')}" v-validate="'required|confirmed:password'" v-bind:data-vv-as="trans('new_password_confirmation')" v-bind:placeholder="trans('new_password_confirmation')">

                                    <span class="form-error" :class="{'is-visible': errors.has('password-form.new_password_confirmation')}">
                                            {{ errors.first('password-form.new_password_confirmation') }}
                                        </span>
                                </label>

                                <button type="submit" id="submit-password" class="btn" @click="changePassword">{{ trans('save') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </vue-custom-scrollbar>
        </section>
    </CustomReveal>
  </vue2-gesture>
</template>

<script>
    import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                old_password: '',
                new_password: '',
                new_password_confirmation: '',
                passwordFormVisible: false,
            }
        },
        components: {
            CustomReveal,
            vueCustomScrollbar
        },
        mounted() {
            app.$on('show-password-form', this.showPasswordForm);
        },
        beforeDestroy() {
            app.$off('show-password-form');
        },
        methods: {
            showPasswordForm() {
                this.old_password = ''
                this.new_password = ''
                this.new_password_confirmation = ''

                this.passwordFormVisible = true;
                this.old_password = this.new_password = this.new_password_confirmation = '';
                this.$validator.reset();
            },
            closeChangePasswordModal() {
                this.passwordFormVisible = false;
            },
            handleGesture(str, e) {
                if (str === 'swipeRight') {
                  this.closeChangePasswordModal();
                }
            },
            changePassword() {
                let callback = () => {
                    return this.$validator.validateAll('password-form').then((result) => {
                        if (result) {
                            let sendData = {
                                old_password: this.old_password,
                                new_password: this.new_password,
                                new_password_confirmation: this.new_password_confirmation,
                            };

                            return axios.post('/api/password/change', sendData)
                                .then((response) => {
                                    if(response.data == 'ok') {
                                        this.showSuccessNotification('password_modified');
                                        this.closeChangePasswordModal();

                                        return true;
                                    }
                                })
                                .catch((response) => {
                                    return false;
                                })
                        }
                    });
                };

                this.runLoadingFunction('#submit-password', callback);
            },
        },
        watch: {
          '$resize': function () {
            this.$refs.vueCustomScrollBar.$forceUpdate()
          }
        }
    }
</script>