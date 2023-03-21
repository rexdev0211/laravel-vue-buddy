<template>
  <transition :name="'fade'" mode="out-in">
    <CustomReveal
      v-if="register"
      :isVisible="register"
      revealId="card"
      class="register-modal"
      v-on:close-reveal-card="closeRegisterModal"
    >
      <div class="secondary-menu">
          <div class="secondary-menu-body register" id="card" data-reveal2>
            <div id="application-wrapper">
              <section class="onboarding"
                       :class="{'select-location': step === 3}"
              >
                <div class="inner">
                  <div class="section-header" v-if="step < 3">
                    <i class="back" @click="goStepBack"></i>

                    <div class="navigation">
                      <div class="dot" :class="{'active': step === 1}"></div>
                      <div class="dot" :class="{'active': step === 2}"></div>
                      <div class="dot" :class="{'active': step === 3}"></div>
                    </div>
                  </div>
                  <div dusk="registration-form" class="section-body">
                    <Step1 v-if="step === 1" v-bind:vars="$data"></Step1>
                    <Step2 v-else-if="step === 2" v-bind:vars="$data"></Step2>
                    <Location v-else-if="step === 3" v-bind:vars="$data"></Location>
                    <Step4 v-else-if="step === 4" v-bind:vars="$data"></Step4>
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
import {mapState, mapGetters} from 'vuex';
import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';
import Step1 from '@buddy/views/widgets/registration/Step1.vue';
import Step2 from '@buddy/views/widgets/registration/Step2.vue';
import Step4 from '@buddy/views/widgets/registration/Step4.vue';
import Location from '@buddy/views/widgets/registration/Location.vue'

export default {
  name: "RegisterModal",
  components: {
      CustomReveal,
      Step1,
      Step2,
      Step4,
      Location,
  },
  mixins: [require('@general/lib/mixin').default],
  data() {
    return {
      step: 1,
      nickname: '',
      dob: this.registerDate(), //this needs moment globally for vee-validate to work
      email: '',
      password: '',
      avatarPhoto: null,
      avatarPreview: null,
      avatarActions: {},
      acceptTerms: false,
      humanVerification: true,

      locationType: "",
      mapVisible: false,
      lat: 52.520389,
      lng: 13.40424,

      locality: 'Berlin',
      state: 'Berlin',
      country: 'Germany',
      country_code: 'DE',
      location: '',
      location_type: '',
      address_type: 'full_address',
      address: '',
      addressShow: '',
      addressError: false,

      // Inner components variables
      honeypot: {
        hide: false,
        value: '',
      },
    }
  },
  computed: {
      ...mapState({
          register: state => state.modal.register
      })
  },
  methods: {
    goStepBack(fallback) {
      if (this.step === 1) {
        this.closeRegisterModal();
      } else {
        this.step--;
      }
    }
  }
}
</script>