<template>
    <div class="w-app column__layout fixed-bg-container">
        <main class="w-welcome full w-welcome-v2">
            <div class="row">
                <div class="medium-7 small-12 columns">
                    <div dusk="registration-form" class="w-register__form text-center">
                        <a class="b-btn__icon" type="button" @click="goStepBack">
                            <svg class="icon icon-arrow_back-navbar"><use v-bind:xlink:href="symbolsSvgUrl('icon-arrow_back')"></use></svg>
                        </a>

                        <div class="text" v-if="step < 3">
                            <ul class="b-steps">
                                <li :class="{'is-active': step === 1}"></li>
                                <li :class="{'is-active': step === 2}"></li>
                                <li :class="{'is-active': step === 3}"></li>
                            </ul><!--b-tabs__menu-->
                        </div>

                        <Step1 v-if="step === 1" v-bind:vars="$data"></Step1>
                        <Step2 v-if="step === 2" v-bind:vars="$data"></Step2>
                        <Step4 v-if="step === 3" v-bind:vars="$data"></Step4>

                    </div><!--w-register__form-->
                </div><!--col-->
            </div><!--row-->
        </main><!--w-welcome-->
    </div><!--w-app-->
</template>

<script>
    import Step1 from '@buddy/views/widgets/registration/Step1.vue';
    import Step2 from '@buddy/views/widgets/registration/Step2.vue';
    import Step4 from '@buddy/views/widgets/registration/Step4.vue';

    export default {
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
                lang: app.lang,

                //inner components variables
                honeypot: {
                    hide: false,
                    value: '',
                },
            }
        },
        mixins: [require('@general/lib/mixin').default],
        components: {
            Step1,
            Step2,
            Step4,
        },
        methods: {
            goStepBack(fallback) {
                if (this.step === 1) {
                    window.location.href = window.location.origin
                } else {
                    this.step--;
                }
            },
        }
    }
</script>
