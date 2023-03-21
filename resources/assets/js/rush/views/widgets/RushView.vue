<template>
    <div class="rushView"
        :style="{'background-image': strip.image ? 'url('+ strip.image +')' : '', 'background-size': strip.image ? 'contain' : 'cover', 'opacity': opacity.value}"
        :class="{'rushBubbleBackground': strip.type == 'bubble'}"
        v-touch:swipe.right="previousRush"
         v-touch:swipe.left="nextRush"
    >
        <div class="rushViewArrows">
            <svg class="icon rushViewLeftArrow" @click="previousRush"><use v-bind:xlink:href="getSvg('icon-arrow_back')"></use></svg>
            <svg class="icon rushViewRightArrow" @click="nextRush"><use v-bind:xlink:href="getSvg('icon-arrow_next')"></use></svg>
        </div>
        <div class="stripsDots">
            <div v-for="strip in strips" class="stripsDot">
                <span :class="{'active': strip.id <= strip_id, 'animate': strip.id == strip_id}"></span>
            </div>
        </div>
        <div v-if="strip.profile_attached"
            class="rushUserProfile"
           @click="goToProfile">
            <img :src="author ? author.profile_photo : ''" :alt="author ? author.name : ''" />
        </div>
        <div @click="closeView" class="icon-with-shadow closePage">
            <svg class="icon"><use v-bind:xlink:href="getSvg('icon-close')"></use></svg>
        </div>

        <div class="rushTapHolder rushTapHolder--left" @click="previousStrip"></div>
        <div class="rushTapHolder rushTapHolder--right" @click="nextStrip"></div>

        <div class="rushBubble" v-if="strip.type == 'bubble'" @click="nextStrip">
            <span>{{ strip.message }}</span>
        </div>

        <h2>{{ title }}</h2>
        <div class="rushDaysStreak icon-with-light-shadow icon-with-shadow-texted" @click="showStreakTooltip">
            <svg class="icon"><use v-bind:xlink:href="getSvg('icon-bunny')"></use></svg>
            <span>{{ streak }}</span>
            <div class="tooltip tooltip-top-left" v-if="isStreakTooltipVisible">
                {{ trans('rush.running_for_x_days', {days: streak}) }}
            </div>
        </div>
        <div class="favorites" @click="favorite">
            <div class="favoriteIcon icon-with-light-shadow icon-with-shadow-texted">
                <svg v-if="favorited" class="icon"><use v-bind:xlink:href="getSvg('icon-favorite_active')"></use></svg>
                <svg v-else class="icon"><use v-bind:xlink:href="getSvg('icon-favorite')"></use></svg>
            </div>
            <span>{{ total_favs > 0 ? total_favs : '' }}</span>
        </div>
        <div class="claps" @click="clap">
            <div class="clapsIcon icon-with-light-shadow icon-with-shadow-texted clapsIconReflected" :class="{'clapped': claps > 0}">
                <span v-for="n in clapped"><i></i><b></b></span>
                <i v-for="n in clapped"></i>
                <b v-for="n in clapped">+{{ claps }}</b>
            </div>
            <span>{{ strip.total_claps ? strip.total_claps : '' }}</span>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

import {
    _type as userType
} from '@rush/modules/user'

import {
    _type as dialogType
} from '@rush/modules/dialog.js'

import {
    _type as requirementsType
} from '@rush/modules/requirements'

export default {
    mixins: [
        require('@rush/lib/mixin').default,
    ],
    props: ['rushId'],
    data() {
        return  {
            hideStreakTooltipTimeout: null,
            isStreakTooltipVisible: false,
            nextTimeout: null,
            timeout:     null,
            claps:       0,
            clapped:     0,
            favorited:   false,

            author:      null,
            rush_id:     0,
            strip_id:    0,
            total_favs:  0,
            title:       '',

            streak:      0,
            strips:      [],
            strip: {
                profile_attached: false,
                type:             null,
                message:          '',
                image:            null,
                total_claps:      0,
            },
            opacity: {
                value: 1,
                timeout: null,
            },
        }
    },
    computed: {
        ...mapGetters({
            queue:       userType.getters.rush.queue,
            favorites:   userType.getters.rush.favorites,
            userProfile: userType.getters.profile,
        }),
    },
    methods: {
        ...mapActions({
            showRequirement: requirementsType.actions.show
        }),
        showStreakTooltip() {
            this.isStreakTooltipVisible = true
            clearTimeout(this.hideStreakTooltipTimeout)

            let v = this
            this.hideStreakTooltipTimeout = setTimeout(() => {
                v.isStreakTooltipVisible = false
            }, 2000)
        },
        nextStrip() {
            let v = this

            clearTimeout(v.nextTimeout)
            if (v.strip_id > 0){
                let next_strip = v.strips.filter(strip => strip.id > v.strip_id).shift()

                if (next_strip) v.showStrip(next_strip)
                else v.nextRush()
            }
        },
        previousStrip() {
            let v = this

            clearTimeout(v.nextTimeout)
            if (v.strip_id > 0 || v.strips.length){
                let previous_strip = v.strips.filter(strip => v.strip_id ? strip.id < v.strip_id : true).pop()

                if (previous_strip) v.showStrip(previous_strip)
                else v.previousRush()
            }
        },
        showStrip(strip) {
            let v = this

            v.strip_id               = strip.id
            v.strip.type             = strip.type
            v.strip.message          = strip.message
            v.strip.image            = strip.image
            v.strip.total_claps      = strip.total_claps
            v.strip.profile_attached = strip.profile_attached
            v.claps                  = strip.my_applauses
            v.clapped                = 0

            axios.post('/api/rush/' + v.rushId + '/'+ v.strip_id +'/view')
                 .catch((error) => {
                     console.log(error)
                 });

            // v.nextTimeout = setTimeout(() => {
            //     v.nextStrip()
            // }, 10000)
        },
        getRush() {
            let v = this
            clearTimeout(v.nextTimeout)

            v.strips = []
            if (v.rush_id && !v.rushId) {
                v.favorited         = false
                v.author            = null
                v.rush_id           = 0
                v.strip_id          = 0
                v.total_favs        = 0
                v.title             = ''
                v.streak            = 0
                v.strip.type        = 'bubble'
                v.strip.message     = ''
                v.strip.image       = null
                v.strip.total_claps = 0
            } else if (v.rushId){
                axios.get('/api/rush/' + v.rushId)
                     .then(({data}) => {
                         if (data.success) {
                             v.author     = data.rush.author
                             v.rush_id    = data.rush.id
                             v.strips     = data.rush.strips
                             v.favorited  = data.rush.favorite.is_favorite
                             v.total_favs = data.rush.favorite.total
                             v.title      = data.rush.title
                             v.streak     = data.rush.streak

                             if (data.rush.latestViewedStripId == data.rush.strips[data.rush.strips.length - 1].id) {
                                 // if user already seen all slides we need to show all slides again from beginning
                                 v.showStrip(data.rush.strips[0])
                             } else {
                                 // otherwise show new slide
                                 v.showStrip(data.rush.strips.find(item => item.id > data.rush.latestViewedStripId))
                             }
                             v.opacity.value = 1
                         }
                     })
                     .catch((error) => {
                         console.log(error)
                     });
            }
        },
        clap() {
            if (this.claps < 10) {
                this.clapped = this.clapped + 1
                this.claps   = this.claps + 1
                clearTimeout(this.timeout)
                let self = this
                this.timeout = setTimeout(() => {
                    self.updateClaps()
                }, 500)
            }
        },
        updateClaps() {
            let v = this

            axios.post('/api/rush/' + v.rushId + '/'+ v.strip_id +'/applause', {
                    claps: v.claps
                 })
                 .then(({data}) => {
                     if (data.success) {
                         v.strips = v.strips.map(item => {
                             if (item.id == data.stripId) {
                                 item.my_applauses = data.claps
                                 item.total_claps  = data.total_claps
                             }

                             return item
                         })

                         if (v.strip_id == data.stripId) v.strip.total_claps = data.total_claps
                     }
                 })
                 .catch((error) => {
                     console.log(error)
                 });
        },
        favorite() {
            let v = this

            if (v.favorited || v.userProfile.isPro || v.favorites.length < app.favorites_limit) {
                axios.post('/api/rush/' + v.rushId + '/favorite')
                     .then(({data}) => {
                         if (data.success) {
                             v.favorited  = data.favorite.is_favorite
                             v.total_favs = data.favorite.total

                             v.refreshFavorites(data.favorites)
                         }
                     })
                     .catch((error) => {
                         console.log(error)
                     });
            } else {
                v.showRequirement({
                    type:        'stars_and_strips',
                    title:       v.trans('pro_slides_4_title'),
                    description: v.trans('rush.stars_and_strips_upgrade'),
                    button:      v.trans('upgrade_now'),
                })
            }
        },
        goToProfile() {
            if (this.isDesktop && !this.isApp) {
                window.location = '/discover?userId='+ this.author.id
            } else {
                window.location = '/user/'+ this.author.id
            }
        },
        previousRush() {
            let v = this
            let type = v.$route.name == 'rush.view' ? 'rushes' : 'favorites'

            let currentRushIndex = v.queue[type].indexOf(v.rush_id)
            if (currentRushIndex > -1) {
                if (v.queue[type][currentRushIndex - 1]) {
                    v.opacity.value = 0

                    setTimeout(() => {
                        let rushId = v.queue[type][currentRushIndex - 1]
                        if (v.$route.params.rushId != rushId) v.$router.push({name: v.$route.name, params: {rushId}})
                    }, 200)
                } else {
                    v.closeView()
                }
            } else {
                v.closeView()
            }
        },
        nextRush() {
            let v = this
            let type = v.$route.name == 'rush.view' ? 'rushes' : 'favorites'

            let currentRushIndex = v.queue[type].indexOf(v.rush_id)
            if (currentRushIndex > -1) {
                if (v.queue[type][currentRushIndex + 1]) {
                    v.opacity.value = 0

                    setTimeout(() => {
                        let rushId = v.queue[type][currentRushIndex + 1]
                        if (v.$route.params.rushId != rushId) v.$router.push({name: v.$route.name, params: {rushId}})
                    }, 200)
                } else {
                    v.closeView()
                }
            } else {
                v.closeView()
            }
        },
        closeView() {
            let v = this

            clearTimeout(this.nextTimeout)
            if (this.$route.name != 'rush')
                this.$router.push({name: 'rush'})
        },
    },
    watch: {
        rushId(){
            this.getRush()
        },
    },
    mounted() {
        $('html, body').scrollTop(0);
        this.getRush()
    },
    beforeDestroy() {
        clearTimeout(this.nextTimeout)
    },
}
</script>
