<template>
    <section id="reset-password">
        <div class="section-shadow"></div>
        <div class="back">
            <a href="/" @click.prevent="goTo('/')"></a>
        </div>
        <div class="section-wrapper">
            <div class="title">{{ trans('recover_password') }}</div>
            <form @submit.prevent="submit">
                <input type="text"
                       v-model="email"
                       id="email"
                       name="email"
                       class="form-control"
                       :class="{error: formErrors.email}"
                       :placeholder="trans('email')"
                       @input="emailChanged"
                >

                <button
                    type="submit"
                    class="btn green"
                    :class="{ process }"
                    v-html="process ? '&nbsp;' : trans('recover_now')"
                ></button>
            </form>
        </div>
    </section>
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';

    export default {
        data: () => ({
            email: null,
            process: false,

            formErrors: {
                email: null
            }
        }),

        mixins: [
            require('@general/lib/mixin').default
        ],
        methods: {
            ...mapActions({
                recoverPasswordStart: 'recoverPasswordStart'
            }),
            emailChanged(){
                if (this.formErrors.email) {
                    this.formErrors.email = null
                }
            },
            validate(){
                let valid = true
                if (!this.email) {
                    this.formErrors.email = true
                    valid = false
                }

                const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                if (!re.test(String(this.email).toLowerCase())) {
                    this.formErrors.email = true
                    valid = false
                }

                return valid
            },
            async sendRequest(){
                this.process = true

                try {
                    let response = await this.recoverPasswordStart({
                        email: this.email
                    })
                    console.log('[Password Reset Form] Result', { response })

                    this.process = false
                    if (response !== false) {
                        console.log('[Password Reset Form] Success')
                        this.showSuccessNotification(response.message)
                    }
                } catch (error) {
                    console.log('[Password Reset Form] Error', { error })
                }

                this.process = false
            },
            submit(){
                console.log('[Password Reset Form] Submit')
                if (this.validate()){
                    this.sendRequest()
                }
            }
        }
    }
</script>