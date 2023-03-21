<template>
    <div class="proPage" v-bind:class="{'proPage--oneAction': generalStatus === null && !userIsPro}">
        <template v-if="generalStatus === null && !userIsPro">
            <div class="logo-tagline">
                <div class="logo">
                    <img src="/main/img/buddy-pro/buddy-pro-logo.svg" alt="">
                </div>
                <div class="tagline">Enjoy even more benefits!</div>
            </div>
            <ProfileSubscriptionSlider/>
            <div class="box">
                <div class="plans" v-if="!paymentBlocked">
                    <div class="proPageSpinner" v-if="!paymentPackages.length">
                        <i class="fa fa-spinner fa-pulse"></i>
                    </div>
                    <div class="plan"
                        :class="{'withAdditionalInfo': paymentPackage.subtitle !== ''}"
                        :key="id"
                        v-for="(paymentPackage, id) in paymentPackages">
                        <div class="checkbox-container">
                            <label class="checkbox-label">
                                <input type="radio" id="subscription" v-on:click.prevent="showPrePaymentPopup(paymentPackage)">
                                <span class="checkbox-custom"></span>
                                <div class="input-title">{{ trans('payment.' + paymentPackage.translate) }}</div>
                            </label>
                        </div>
                        <div class="price">{{ paymentPackage.price }}<span v-if="paymentPackage.subtitle !== ''">{{ paymentPackage.subtitle }}{{ trans('payment.month') }}</span></div>
                    </div>
                </div>
                <div class="proPaymentPending" v-if="isApp">
                    {{ trans('payment.coming_soon') }}
                </div>
                <div class="proPaymentPending" v-if="paymentBlocked">
                    {{ trans('payment_blocked') }}
                </div>

                <button id="to-status" class="btn">{{ trans('upgrade_now') }}</button>

                <div class="promocode" v-if="!paymentBlocked">
                    <input class="promo-field" type="text" name="promocode"
                        v-model="promocodePopup.code"
                        :placeholder="trans('payment.your_promotion_code')">
                    <button class="btn" @click="submitPromoCode">{{ trans('ok') }}</button>
                </div>
                <span v-if="promocodePopup.error">{{ promocodePopup.error }}</span>
                <div class="proPageTerms" v-html="trans('pro_upgrade_message')"></div>
            </div>
        </template>

        <template v-else-if="generalStatus === null && userIsPro">
            <div class="status check" v-if="isDesktop">
              <div class="logo-tagline">
                <div class="logo">
                  <img src="/main/img/buddy-pro/buddy-pro-logo.svg" alt="">
                </div>
              </div>
              <div class="box">
                <div class="thankyou" :style="{'background-image': `url(/main/img/buddy-pro/buddy-thankyou.png)`}"></div>
              </div>
              <div class="box">
                <div class="note">
                  <div class="top">
                    <span>{{ trans('payment.active_until') }}</span>
                  </div>
                  <div class="bottom">
                    <div class="title">{{ proExpireDate }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="secondary-menu status check" v-if="isMobile">
                <div class="secondary-menu-body">
                    <div class="logo-tagline">
                        <div class="logo">
                            <img src="/main/img/buddy-pro/buddy-pro-logo.svg" alt="">
                        </div>
                    </div>
                    <div class="box">
                        <div class="thankyou" :style="{'background-image': `url(/main/img/buddy-pro/buddy-thankyou.png)`}"></div>
                    </div>
                    <div class="box">
                        <div class="note">
                            <div class="top">
                                <span>{{ trans('payment.active_until') }}</span>
                            </div>
                            <div class="bottom">
                                <div class="title">{{ proExpireDate }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else-if="generalStatus === 'success'">
            <div class="secondary-menu status success">
                <div class="secondary-menu-body">
                    <div class="logo-tagline">
                        <div class="logo">
                            <img src="/main/img/buddy-pro/buddy-pro-logo.svg" alt="">
                        </div>
                    </div>
                    <div class="box">
                        <div class="thankyou" :style="{'background-image': `url(/main/img/buddy-pro/buddy-thankyou.png)`}"></div>
                    </div>
                    <div class="box">
                        <div class="note">
                            <div class="top"></div>
                            <div class="bottom">
                                <div class="title">{{ userIsPro ? trans('successful_pro_upgrade') : trans('we_hope_you_enjoyed_pro') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else-if="generalStatus === 'error'">
            <div class="secondary-menu status error">
                <div class="secondary-menu-body">
                    <div class="logo-tagline">
                        <div class="logo">
                            <img src="/main/img/buddy-pro/buddy-pro-logo.svg" alt="">
                        </div>
                    </div>
                    <div class="box">
                        <div class="thankyou" :style="{'background-image': `url(/images/splash/avatar_sorry.png)`}"></div>
                    </div>
                    <div class="box">
                        <div class="note">
                            <div class="top"></div>
                            <div class="bottom">
                                <div class="title failed">{{ trans('failed_pro_upgrade') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <alert
            v-if="prePaymentPopup.visible"
            v-bind:clear="true"
            v-on:hide-alert="hidePrePaymentPopup"
        >
            <div class="popup">
                <div class="popup-inner">
                    <div class="title">
                        {{ prePaymentPopup.title }}
                        <div class="title-bottom">{{ trans(prePaymentPopup.recurring ? 'payment.renews_automatically_until_cancelled' : 'payment.one_time') }}</div>
                    </div>

                    <div class="description">{{ prePaymentPopup.message }}</div>

                    <div class="proConfirmAlertButtons">

                        <a class="btn"
                            v-if="issuers['flexpay']"
                            @click.prevent="initiatePayment('flexpay')"
                        >{{ trans('payment.credit_card') }}</a>

                        <b
                            v-if="issuers['segpay']"
                            style="margin: 20px 0 0 0"
                            @click.prevent="initiatePayment('segpay')"
                        >
                          Segpay
                        </b>

                        <div
                            v-if="issuers['2000charge'] || issuers['sofort']"
                            class="proConfirmAlertButtonsAlt"
                        >{{ trans('payment.alternative_payments') }}:</div>

                        <i
                            v-if="issuers['2000charge'] && !twokchargeIframeLoaded"
                            class="fa fa-spinner fa-pulse"
                        ></i>
                        <b
                            v-if="issuers['2000charge'] && twokchargeIframeLoaded"
                            @click="showTwokchargePopup(packageId)"
                        >
                          PaysafeCard - Giropay
                        </b>

                        <b
                            v-if="issuers['2000charge'] && twokchargeIframeLoaded"
                            @click="showPaypalPopup"
                        >
                          PayPal
                        </b>

                        <!-- <b
                            v-if="issuers['sofort']"
                            @click="showSofortPopup"
                        >Sofort</b> -->
                    </div>
                </div>
            </div>
        </alert>

        <alert
            v-if="sofortPopup.visible"
            v-bind:clear="true"
            v-bind:is-full-size="true"
            v-on:hide-alert="hideSofortWidget"
        >
            <div class="proConfirmAlert">
                <h3>SOFORT Überweisung</h3>
                <h5>{{ sofortPopup.title }}</h5>
                <div class="proConfirmForm">
                    <label for="email">Email*</label>
                    <input name="email" type="email" v-model="sofortPopup.email"/>
                    <div>
                        <div class="two-inputs">
                            <label for="first_name">First Name*</label>
                            <input name="first_name" type="text" v-model="sofortPopup.first_name"/>
                        </div>
                        <div class="two-inputs">
                            <label for="last_name">Last Name*</label>
                            <input name="last_name" type="text" v-model="sofortPopup.last_name"/>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <label for="country_code">Country*</label>
                    <select name="country_code" v-model="sofortPopup.country">
                        <option :value="countryCode" v-for="countryCode in sofortPopup.countries">
                            {{ countryCode }}
                        </option>
                    </select>
                </div>
                <div class="proConfirmAlertButtons">
                    <a @click="handleSofortPayment">Confirm</a>
                </div>
            </div>
        </alert>

      <alert
          v-if="paypalPopup.visible || paypalPopup.success"
          v-bind:clear="true"
          class="bb-popup--paypal"
          v-on:hide-alert="hidePaypalWidget"
      >
        <form data-vv-scope="paypal-form" @submit.prevent="submitPaypal">
          <div class="popup popup-paypal">
            <div class="popup-inner">
              <div class="title">
                {{ paypalPopup.title }}
                <div class="title-bottom" v-html="paypalPopup.message"></div>
              </div>

              <div class="description" v-show="!paypalPopup.success">
                <label>
                  <input type="email" name="email" v-model="paypalPopup.email" class="form-control" :placeholder="trans('email')" :class="{'is-invalid-input': errors.has('paypal-form.email')}" v-validate="'required|email'">
                  <span class="form-error" v-show="errors.has('paypal-form.email')" :class="{'is-visible': errors.has('paypal-form.email')}">
                    {{ errors.first('paypal-form.email') }}
                  </span>
                </label>
              </div>
              <div class="proConfirmAlertButtons" v-show="!paypalPopup.success">
                <button type="submit" id="submit-paypal" class="btn">{{ trans('submit_event') }}</button>
              </div>
            </div>
          </div>
        </form>
      </alert>

    </div>
</template>

<script>
    import _ from 'lodash'
    import {mapActions, mapMutations, mapState} from 'vuex'
    import ProfileSubscriptionSlider from '@profile/views/widgets/subscription/ProfileSubscriptionSlider.vue';
    import Alert from '@buddy/views/widgets/Alert.vue'

    export default {
        props: ['bottomMenuState'],
        mixins: [
            require('@general/lib/mixin').default,
        ],
        data() {
            return {
                generalStatus: null,
                paymentBlocked: false,

                issuer: null,
                issuerCredentials: null,
                packageId: 0,

                paymentPackages: [],
                issuers: {},

                twokchargeIframeLoaded: false,
                twokchargeIframe: null,

                prePaymentPopup: {
                    visible: false,
                    recurring: false,
                    message: '',
                    title: '',
                },
                sofortPopup: {
                    visible: false,
                    countries: [],
                    country: 'DE',
                    first_name: '',
                    last_name: '',
                    email: '',
                },
                paypalPopup: {
                    visible: false,
                    email: '',
                    title: 'Paypal',
                    message: '',
                    success: false
                },
                promocodePopup: {
                    code: '',
                    error: '',
                },
            }
        },
        components: {
            ProfileSubscriptionSlider,
            Alert,
        },
        computed: {
            ...mapState({
                userIsPro: 'userIsPro',
                profile: 'profile',
            }),
            proExpireDate() {
                return moment(this.profile.pro_expires_at).format('D MMMM YYYY')
            },
            origin() {
                return `https://${window.APP_DOMAIN}`;
            },
        },
        methods: {
            ...mapMutations([
                'setUserIsPro',
                'setDiscreetMode',
            ]),
            ...mapActions([
                'loadCurrentUserInfo',
            ]),
            setStatus(status) {
                this.generalStatus = status
                if (status === 'error') {
                    this.$emit('bottomMenuState', true)
                }
            },
            hideSofortWidget() {
                this.sofortPopup.visible = false
            },
            hidePaypalWidget() {
                this.paypalPopup.visible = false
                this.paypalPopup.success = false;
            },
            submitPaypal() {
                let callback = () => {
                  return this.$validator.validateAll('paypal-form').then((result) => {
                      if (result) {
                        let sendData = {
                            paypalEmail: this.paypalPopup.email,
                            duration: this.packageId
                        }

                        return axios.post('/api/subscription/paypal', sendData)
                            .then((response) => {
                                if (response.data === 'ok') {
                                    this.paypalPopup.visible = false;
                                    this.paypalPopup.title = 'Thank You';
                                    this.paypalPopup.message = this.trans('payment.paypal.successMessage')
                                    this.paypalPopup.success = true;

                                    return true;
                                }
                            })
                            .catch((response) => {
                                return false;
                            })
                      }
                  })
                }

                this.runLoadingFunction('#submit-paypal', callback);
            },
            showPrePaymentPopup(paymentPackage) {
                this.packageId = paymentPackage.key
                this.issuers = paymentPackage.issuers

                this.prePaymentPopup.recurring = paymentPackage.recurring
                this.prePaymentPopup.title = this.trans('payment.upgrade.' + paymentPackage.translate)
                this.prePaymentPopup.message = this.trans('payment.please_select_payment_option')
                this.prePaymentPopup.visible = true
            },
            showTwokchargePopup(packageId) {
                let amount = _.find(this.paymentPackages, { key: packageId }).amount
                let data = {
                    action: 'set-form',

                    TWO_THOUSAND_CHARGE_KEY: this.issuerCredentials['2000charge'],
                    TWO_THOUSAND_CHARGE_MODE: null,
                    TWO_THOUSAND_CHARGE_FORM_OPTIONS: {
                        COMPANY_NAME: 'BUDDY PRO',
                        DESCRIPTION: this.prePaymentPopup.title,
                        EXTRA: 'Activate PRO',
                        AMOUNT: amount,
                        CURRENCY: "EUR",
                        LANGUAGE: 'US',
                    },

                    ALTERNATIVE_PAYMENTS_KEY: this.issuerCredentials['2000charge'],
                    ALTERNATIVE_PAYMENTS_MODE: null,
                    ALTERNATIVE_PAYMENTS_FORM_OPTIONS: {
                        COMPANY_NAME: 'BUDDY PRO',
                        DESCRIPTION: this.prePaymentPopup.title,
                        EXTRA: 'Activate PRO',
                        AMOUNT: amount,
                        CURRENCY: "EUR",
                        LANGUAGE: 'US',
                    },
                    themeStyle: 'dark',
                    customCss: null,
                }
                
                this.twokchargeIframe.contentWindow.postMessage(JSON.stringify(data), '*')

                data = { action: 'set-form-values' }
                this.twokchargeIframe.contentWindow.postMessage(JSON.stringify(data), '*')

                // Show twokchargeIframe
                jQuery(this.twokchargeIframe).fadeIn()
            },
            showPaypalPopup() {
                this.prePaymentPopup.visible = false;
                this.paypalPopup.message = this.trans('payment.paypal.message')
                this.paypalPopup.visible = true;
            },
            showSofortPopup() {
                this.hidePrePaymentPopup()
                this.sofortPopup.visible = true
            },
            showPromocodePopup() {
                this.promocodePopup.code = ''
                this.promocodePopup.error = ''
            },

            hidePrePaymentPopup() {
                this.prePaymentPopup.visible = false
            },

            async fetchSettings() {
                let response = await axios.post('/api/subscription/settings')
                if (response.status === 200) {
                    let data = response.data
                    this.paymentPackages = data.packages
                    this.paymentBlocked = data.payment_blocked
                    this.issuerCredentials = data.credentials
                    this.sofortPopup.countries = data.sofort_countries
                }
            },
            async submitPromoCode() {
                let response = await axios.post('/api/subscription/promocode', {
                    code: this.promocodePopup.code
                })
                if (response.status === 200) {
                    let data = response.data
                    this.setUserIsPro(true)
                    this.setDiscreetMode(data.discreetMode)
                    this.loadCurrentUserInfo(true)
                    this.$emit('bottomMenuState', true)
                    this.setStatus('success')
                }
            },
            async initiatePayment(issuer) {
                console.log('[initiatePayment] Request', { issuer })
                let response = await axios.post('/api/subscription/initiate', {
                    issuer,
                    package_id: this.packageId,
                })
                console.log('[initiatePayment] Response', { response })
                if (response.status === 200) {
                    window.location.href = response.data.redirect
                } else {
                    this.setStatus('fail')
                    this.hidePrePaymentPopup()
                }
            },
            async handleSofortPayment() {
                console.log('[handleSofortPayment] Request')
                let response = await axios.post('/api/subscription/initiate', {
                    issuer: '2000charge-sofort',
                    package_id: this.packageId,
                    data: {
                        country: this.sofortPopup.country,
                        first_name: this.sofortPopup.first_name,
                        last_name: this.sofortPopup.last_name,
                        email: this.sofortPopup.email,
                    }
                })
                console.log('[handleSofortPayment] Response', { response })
                if (response.status === 200) {
                    this.paymentBlocked = true
                    this.hideSofortWidget()
                    window.location.href = response.data.redirect
                }
            },

            bindTwokchargeListener() {
                let self = this
                $(window).on("message", function (e) {
                    e.preventDefault()

                    let widget = (typeof e.originalEvent.data == 'string' ?
                        JSON.parse(e.originalEvent.data)
                        :
                        {}
                    )

                    switch (widget.action) {
                        case 'hide-iframe': {
                            console.log('[Twok Listener] hide-iframe', { widget })
                            jQuery(self.twokchargeIframe).fadeOut()
                            break;
                        }
                        case 'submit-payment': {
                            console.log('[Twok Listener] Request submit-payment', { widget })
                            axios.post('/api/subscription/initiate', {
                                issuer: '2000charge',
                                package_id: self.packageId,
                                data: widget,
                            }).then(({data}) => {
                                console.log('[Twok Listener] Response submit-payment', { widget, data })
                                self.paymentBlocked = true
                                self.hidePrePaymentPopup()
                                window.location.href = data.redirect
                            })
                            break;
                        }
                    }
                });
            },
            createTwokchargeIframe() {
                let iframe = document.createElement("iframe")
                iframe.setAttribute("frameBorder", "0")
                iframe.setAttribute("allowtransparency", "true")

                let cssAttrs = [
                    "z-index: 9999;",
                    "background: transparent;",
                    "background: rgba(0,0,0,0.005);",
                    "border: 0px none transparent;",
                    "overflow-x: hidden;",
                    "overflow-y: auto;",
                    "margin: 0;",
                    "padding: 0;",
                    "-webkit-tap-highlight-color: transparent;",
                    "-webkit-touch-callout: none;",
                    "position: fixed;",
                    "width: 100%;",
                    "height: 100%;",
                    "top: 0;",
                    "left: 0;"
                ]
                iframe.style.cssText = cssAttrs.join('\n')
                iframe.src = 'https://widget.2000charge.com/v22k/checkout.html' // Live
                //iframe.src = '/checkout.html'; // Test
                jQuery(iframe).hide()
                document.body.appendChild(iframe)

                let self = this
                iframe.onload = function () {
                    self.twokchargeIframeLoaded = true
                }

                return iframe
            },
        },
        mounted() {
            this.fetchSettings()

            if (!this.twokchargeIframe) {
                this.twokchargeIframe = this.createTwokchargeIframe()
            }
            this.bindTwokchargeListener()
        },
        updated() {
          if (!app.isMobile) {
            this.$parent.$parent.$refs.vueCustomScrollbar.$forceUpdate()
          }
        },
        beforeDestroy() {
            if (this.twokchargeIframe) {
                this.twokchargeIframe.remove()
            }
        }
    }
</script>
<style scoped>
.secondary-menu-body {
  padding-bottom: 50px !important;
}
</style>