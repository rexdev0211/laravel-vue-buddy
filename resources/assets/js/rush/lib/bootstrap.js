window._ = require('lodash')

try {
    window.$ = window.jQuery = require('jquery')
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios')
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.moment   = require('moment')
window.Vue      = require('vue')

import Vuex from 'vuex'
import VueI18n from 'vue-i18n'
import VueRouter from 'vue-router'
import Vue2TouchEvents from 'vue2-touch-events'
import {MediaQueries} from 'vue-media-queries'
import auth from './auth'

auth.login()

const mediaQueries = new MediaQueries()
window.mediaQueries = mediaQueries

Vue.use(Vuex)
Vue.use(VueI18n)
Vue.use(VueRouter)
Vue.use(Vue2TouchEvents)
Vue.use(mediaQueries)

window.Vuex = Vuex
window.VueI18n = VueI18n
window.VueRouter = VueRouter

window.symbolsSvgUrl = icon => {
    return '/assets/img/svg/symbols.svg#' + icon
}
