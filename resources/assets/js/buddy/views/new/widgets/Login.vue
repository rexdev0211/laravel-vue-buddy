<template>
    <form @submit.prevent="submit">
        <input type="text"
               v-model="username"
               id="username"
               name="username"
               autocomplete="username"
               class="form-control"
               :class="{error: formErrors.username}"
               :placeholder="trans('home.login')"
               @input="usernameChanged"
        >

        <input type="password"
               v-model="password"
               id="password"
               name="password"
               autocomplete="current-password"
               class="form-control"
               :class="{error: formErrors.password}"
               :placeholder="trans('home.password')"
               @input="passwordChanged"
        >

        <button
            type="submit"
            class="btn green"
            :class="{ process }"
            v-html="process ? '&nbsp;' : trans('home.log_in')"
        ></button>

        <div class="form-footer">
            <div class="checkbox-container">
                <label class="checkbox-label">
                    <input type="checkbox" v-model="remember">
                    <span class="checkbox-custom"></span>
                    <div class="input-title">{{ trans('home.remember_me') }}</div>
                </label>
            </div>
            <a @click="recoverPassword">{{ trans('home.forgot_password') }}</a>
        </div>
    </form>
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';

    export default {
        data: () => ({
            username: null,
            password: null,
            remember: true,

            process: false,

            formErrors: {
                username: null,
                password: null
            }
        }),
        mixins: [
            require('@general/lib/mixin').default
        ],
        methods: {
            ...mapActions({
                login: 'login'
            }),
            recoverPassword() {
              const isMobile = app.isMobile;

              if (isMobile) {
                  location.href = '/recover-password';
              } else {
                  this.openRecoverPasswordModal();
              }
            },
            usernameChanged(){
                if (this.formErrors.username) {
                    this.formErrors.username = null
                }
            },
            passwordChanged(){
                if (this.formErrors.password) {
                    this.formErrors.password = null
                }
            },
            validate(){
                let valid = true
                if (!this.username) {
                    this.formErrors.username = true
                    valid = false
                }
                if (!this.password) {
                    this.formErrors.password = true
                    valid = false
                }
                return valid
            },
            async loginAndRedirect(){
                this.process = true

                try {
                    let loggedInSuccessfully = await this.login({
                        username: this.username,
                        password: this.password,
                        remember: this.remember
                    })
                    console.log('[Login Form] Result', { loggedInSuccessfully })

                    this.process = false
                    if (loggedInSuccessfully) {
                        location.pathname = '/discover'
                    }
                } catch (error) {
                    console.log('[Login Form] Error', { error })
                    this.showNotification(error.data, 'error');
                }

                this.process = false
            },
            submit(){
                console.log('[Login Form] Submit')
                if (this.validate()){
                    this.loginAndRedirect()
                }
            }
        }
    }
</script>

<style scoped>

</style>