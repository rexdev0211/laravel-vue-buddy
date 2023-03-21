<template>
  <transition :name="'fade'" mode="out-in">
    <CustomReveal
      v-if="recoverPassword"
      :isVisible="recoverPassword"
      revealId="card"
      class="recover-password-modal"
      v-on:close-reveal-card="closeRecoverPasswordModal"
    >
      <div class="secondary-menu">
          <div class="secondary-menu-body recover-password" id="card" data-reveal2>
            <div id="application-wrapper">
              <section class="modal">
                <div class="inner">
                  <div class="section-header">
                    <i class="back" @click="closeModal"></i>
                  </div>
                  <div class="section-body">
                     <div class="step">
                        <div class="headline">{{ trans('recover_password') }}</div>
                        <div class="recover">
                           <form id="recover__form" data-abide2 data-vv-scope="recoverForm"  @submit.prevent="submit">
                              <div class="form-inner">
                                 <label>
                                    <input
                                       type="email"
                                        v-model="email"
                                        id="email"
                                        name="email"
                                        class="form-control"
                                        :placeholder="trans('email')"
                                        :data-vv-as="trans('email')"
                                        v-validate="'required|email'"
                                        :class="{error: formErrors.email}"
                                        @input="emailChanged"
                                        required>

                                    <span class="form-error" v-show="errors.has('recoverForm.email')" :class="{'is-visible': errors.has('recoverForm.email')}">
                                        {{ errors.first('recoverForm.email') }}
                                    </span>
                                </label>
                                <button
                                    type="submit"
                                    class="btn green"
                                    :class="{ process }"
                                    v-html="process ? '&nbsp;' : trans('recover_now')">
                                 </button>
                              </div>
                           </form>
                        </div>

                        <a href="#" class="btn darker">Need help?</a>
                     </div>
                  </div>
                </div>
              </section>
            </div>
          </div>
      </div>
    </CustomReveal>
  </transition>
</template>

<script>
import {mapState, mapGetters, mapActions} from 'vuex';
import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';

export default {
  name: "RecoverPasswordModal",
  components: {
      CustomReveal
  },
  mixins: [require('@general/lib/mixin').default],
  data() {
    return {
      email: null,
      process: false,

      formErrors: {
          email: null
      }
    }
  },
  computed: {
      ...mapState({
          recoverPassword: state => state.modal.recoverPassword
      })
  },
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
      },
      closeModal(fallback) {
         this.closeRecoverPasswordModal();
      }
  }
}
</script>