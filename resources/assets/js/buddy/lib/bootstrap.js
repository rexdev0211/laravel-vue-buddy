//SAFARI bug - added babel-polyfill
import 'babel-polyfill';

import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import VueI18n from 'vue-i18n';
import VeeValidate, { Validator } from 'vee-validate';
import {MediaQueries} from 'vue-media-queries';

import moment from 'moment';

import auth from '@general/lib/auth';
import 'lity/dist/lity.min.css';
import de from 'vee-validate/dist/locale/de';
import fr from 'vee-validate/dist/locale/fr';
import {getUserLanguage} from '@general/lib/lang/helpers';
import mixin from '@general/lib/mixin';

// import * as VueGoogleMaps from 'vue2-google-maps';
let VueGoogleMaps = null;
if (window.MAP_PROVIDER == 'gmap') {
    // VueGoogleMaps = require('vue2-google-maps');
}

window.Vue = Vue;
window.auth = auth;

Vue.config.productionTip = false

var VueGesture = require('vue2-gesture');

Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(VueGesture);

if (window.MAP_PROVIDER == 'gmap') {
    Vue.use(VueGoogleMaps, {
        load: {
            key: window.GMAP_API_KEY,
            language: 'en', //get responses from google maps in english
            region: 'DE',
            // libraries: 'places', // This is required if you use the Autocomplete plugin
            // OR: libraries: 'places,drawing'
            // OR: libraries: 'places,drawing,visualization'
        }
    });
}


const mediaQueries = new MediaQueries();
window.mediaQueries = mediaQueries;
Vue.use(mediaQueries);

window.VueI18n = VueI18n;
Vue.use(VueI18n);

Validator.localize('de', de);
Validator.localize('fr', fr);
Vue.use(VeeValidate, {
    locale: getUserLanguage()
});


window.Vuex = Vuex;

window._ = require('lodash');

window.moment = moment;

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.interceptors.response.use(function (response) {
    return response;
}, function (error) {
    console.log('[Axios response interceptor] Response intercepted', { response: error.response })

    // if ([401, 422, 500].includes(error.response.status)) {
    //     mixin.methods.showErrorNotification(error.response.data)
    // }

    if (error.response.status === 400 && (error.response.data.error == 'Unauthenticated' || error.response.data.error == 'Unauthenticated.')) {
        app.logout()
        app.$store.commit('logout')
    }

    if (error.response.status === 401) {
        //app.logout()
        //app.$store.commit('logout')
    }

    return Promise.reject(error.response)
});

if (auth.isAuthenticated()) {
    auth._setAxiosAuthorizationHeader();
    console.log('Set auth header')
}

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

require('foundation-sites');
require('motion-ui');
