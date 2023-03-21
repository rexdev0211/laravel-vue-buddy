<template>
    <div class="rushView rushWizard rushBubbleBackground"
        :style="{'background-image': strip.image ? 'url('+ strip.image +')' : '', 'background-size': strip.image ? 'contain' : 'cover'}"
        v-touch:swipe.right="previousStrip"
         v-touch:swipe.left="nextStrip"
    >
        <div class="rushViewArrows">
            <svg class="icon rushViewLeftArrow" @click="previousStrip"><use v-bind:xlink:href="getSvg('icon-arrow_back')"></use></svg>
            <svg class="icon rushViewRightArrow" @click="nextStrip"><use v-bind:xlink:href="getSvg('icon-arrow_next')"></use></svg>
        </div>
        <div class="stripsDots">
            <div v-for="strip in strips" class="stripsDot">
                <span :class="{'active': strip.id <= strip_id || strip_id == 0}"></span>
            </div>
            <div class="stripsDot active" v-if="strip_id == 0"></div>
        </div>
        <router-link :to="{name: 'rush'}" class="icon-with-shadow closePage">
            <svg class="icon" @click="previousStrip"><use v-bind:xlink:href="getSvg('icon-close')"></use></svg>
        </router-link>
        <div class="icon icon-with-shadow sendStrip" v-if="strip_id == 0" @click="sendStrip"><svg><use v-bind:xlink:href="getSvg('icon-send_active')"></use></svg></div>
        <div class="icon icon-with-shadow sendStrip" v-else-if="strip_id == strips[strips.length - 1].id" @click="nextStrip"><svg><use v-bind:xlink:href="getSvg('icon-add')"></use></svg></div>
        <div class="icon icon-with-shadow deleteContent" @click="deleteStrip" v-if="strip_id > 0"><svg><use v-bind:xlink:href="getSvg('icon-litter')"></use></svg></div>
        <div class="icon icon-with-shadow deleteContent" @click="reset()" v-else-if="strip.type"><svg><use v-bind:xlink:href="getSvg('icon-litter')"></use></svg></div>

        <div class="rushBubble" v-if="strip.type == 'bubble'">
            <textarea v-model="strip.message" placeholder="Type your message here"></textarea>
        </div>
        <div class="rushImages" v-if="strip.type == 'image' && open">
            <div class="rushImage rushImageUpload">
                <svg class="icon"><use v-bind:xlink:href="getSvg('icon-plus')"></use></svg>
                <input type="file" v-on:change="uploadImage($event, true)" />
            </div>
            <div class="rushImage" v-for="image in rushImages" @click="setImage(image)">
                <img :src="image.small" />
            </div>

            <hr />

            <div class="rushImage" v-for="image in userImages" @click="setImage(image, true)">
                <img :src="image.small" />
            </div>

        </div>
        <div class="rushSetType" v-if="strip_id == 0 && strip.type == null">
            <span class="photo" @click="setType('image')"><svg class="icon"><use v-bind:xlink:href="getSvg('icon-photo_active')"></use></svg></span>
            <span class="video" v-if="false"><svg class="icon"><use v-bind:xlink:href="getSvg('icon-video_active')"></use></svg></span>
            <span class="bubble" @click="setType('bubble')"><svg class="icon"><use v-bind:xlink:href="getSvg('icon-write_active')"></use></svg></span>
        </div>

        <input v-if="strip_id == 0" type="text" v-model="title" placeholder="Your STRIP title here"/>
        <h2 v-else>{{ title }}</h2>
        <div v-if="strip.profile_attached || strip_id == 0"
            class="rushUserProfile"
           @click="toggleProfileAttached"
           :class="{'removeable': strip.profile_attached && strip_id == 0, 'disabled': !strip.profile_attached}">
            <img :src="userProfile ? userProfile.profile_photo : ''" :alt="userProfile ? userProfile.name : ''" />
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

import {
    _type as announceType
} from '@rush/modules/announce'

import {
    _type as dialogType
} from '@rush/modules/dialog'

import {
    _type as headerType
} from '@rush/modules/header'

import {
    _type as myRushesType
} from '@rush/modules/myRushes'

import {
    _type as userType
} from '@rush/modules/user'

export default {
    mixins: [
        require('@rush/lib/mixin').default,
    ],
    props: ['rushId'],
    data() {
        return  {
            open: true,

            title:    '',
            rush_id:  0,
            strip_id: 0,
            strips:   [],
            strip: {
                profile_attached: true,
                type: null,
                message: '',
                image: null,
                image_id: null,
                image_path: null,
                imageIsLoading: false,
            },
        }
    },
    computed: {
        ...mapGetters({
            userProfile: userType.getters.profile,
            userImages:  userType.getters.images,
            rushImages:  userType.getters.rush.images,
            announce:    announceType.getters.announce,
        })
    },
    methods: {
        ...mapActions({
            showAnnounce:          announceType.actions.show,
            addMyRushes:           myRushesType.actions.add,
            editMyRush:            myRushesType.actions.edit,
            deleteMyRush:          myRushesType.actions.delete,
            addRushImage:          userType.actions.rush.images.add,
            setStreak:             headerType.actions.streak.set,
            clearStreak:           headerType.actions.streak.clear,
            showDialog:            dialogType.actions.show,
        }),
        reset() {
            this.strip.type             = null
            this.strip.message          = ''
            this.strip.sent             = false
            this.strip.image            = null
            this.strip.image_id         = null
            this.strip.image_path       = null
        },
        setType(type) {
            if (this.strip.type != type) 
                this.reset()

            this.strip.type = type
            this.open       = true
        },
        setImage(image, fromUser = false) {
            this.open             = false
            this.strip.image_id   = fromUser ? null : image.id
            this.strip.image      = image.orig
            this.strip.image_path = image.image
        },
        uploadImage(event, type) {
            let v = this
            let files = event.target.files || event.dataTransfer.files

            if (!files.length) {
                return;
            }

            let reader = new FileReader
            reader.onload = (ev) => {
                let callback = () => {
                    const data = {image: files[0]}
                    const formData = v.getFormData(data)

                    return axios.post('/api/rush/upload/image', formData)
                        .then((response) => {
                            v.addRushImage(response.data.image)
                        })
                        .finally(() => {
                            event.target.value = null
                        })
                }

                v.imageIsLoading = true

                Promise.resolve(callback())
                       .then(() => {
                           v.imageIsLoading = false
                       })
            }

            reader.readAsDataURL(files[0])
        },
        nextStrip() {
            if (this.strip_id > 0){
                let v = this
                let next_strip = v.strips.filter(strip => strip.id > v.strip_id).shift()

                if (next_strip)
                    this.showStrip(next_strip)
                else {
                    this.strip_id = 0
                    this.reset()
                }
            }
        },
        previousStrip() {
            if (this.strip_id > 0 || this.strips.length){
                this.open = false

                let v = this
                let previous_strip = this.strips.filter(strip => v.strip_id ? strip.id < v.strip_id : true).pop()

                if (previous_strip)
                    this.showStrip(previous_strip)
            }
        },
        showStrip(strip) {
            this.open     = false
            this.strip_id = strip.id

            this.strip.type             = strip.type
            this.strip.message          = strip.message
            this.strip.image            = strip.image
            this.strip.image_id         = strip.image_id
            this.strip.image_path       = strip.image_path
            this.strip.profile_attached = strip.profile_attached
        },
        deleteStrip() {
            let strip_id = this.strip_id
            let message = this.strips.length > 1 ? 'You want to delete slide from this Strip. Are you sure?' : 'You want to delete this Strip. Are you sure?'

            let v = this
            this.showDialog({
                mode: 'confirm',
                message: message,
                callback: () => {
                    v.confirmDeleteStrip(strip_id)
                },
            })
        },
        confirmDeleteStrip(strip_id) {
            let v = this

            axios.post('/api/rush/delete', {
                strip_id: strip_id,
            }).then(({data}) => {
                if (data.success && data.rush) {
                    v.editMyRush(data.rush)
                    v.strips = v.strips.filter(item => item.id != data.id)
                } else if (data.success) {
                    v.deleteMyRush(data.rushId)
                    v.$router.push({name: 'rush'})
                } else {
                    this.showDialog({
                        mode: 'success',
                        message: data.message
                    })
                }

                if (data.success) {
                    this.showDialog({
                        mode: 'success',
                        message: data.rushDeleted ? 'Strip successfully deleted' : 'Slide successfully deleted',
                    })
                    v.refreshData()
                }

                if (data.id == v.strip_id) {
                    v.showStrip(v.strips[v.strips.length - 1])
                }
            })
            .catch((error) => {
                console.log(error)
            })
        },
        sendStrip() {
            let v = this

            if (v.strip_id == 0) {
                axios.post('/api/rush/create', {
                    rush_id: v.rush_id,
                    title: v.title,
                    type: v.strip.type,
                    message: v.strip.message,
                    image_id: v.strip.image_id,
                    image_path: v.strip.image_path,
                    profile_attached: v.strip.profile_attached,
                }).then(({data}) => {
                    if (data.success) {
                        v.rush_id  = data.rush.id
                        v.strip_id = data.rush.latest_strip.id

                        v.strips.push(data.rush.latest_strip)

                        if (data.newRush) {
                            v.addMyRushes(data.rush)
                            v.$router.push({name: 'rush.edit', params: {rushId: data.rush.id}})
                        } else {
                            v.editMyRush(data.rush)
                        }
                        v.refreshData()
                    }
                })
                .catch((error) => {
                    console.log(error)
                })
            } else {
                v.$router.push({name: 'rush'})
            }
        },
        getRush() {
            let v = this
            if (v.rush_id && !v.rushId) {
                v.strips     = []
                v.rush_id    = 0
                v.strip_id   = 0
                v.title      = ''

                v.reset()
            } else if (v.rushId){
                axios.get('/api/rush/edit/' + v.rushId)
                     .then(({data}) => {
                         if (data.success) {
                             v.rush_id = data.rush.id
                             v.title   = data.rush.title
                             v.strips  = data.rush.strips

                             let latest_strip = data.rush.strips.filter(strip => true).pop()
                             v.showStrip(latest_strip)
                             v.setStreak(data.rush.streak ? data.rush.streak : 0)
                         }
                     })
                     .catch((error) => {
                         console.log(error)
                     })
            }
        },
        toggleProfileAttached() {
            if (this.strip_id == 0) this.strip.profile_attached = !this.strip.profile_attached
        },
    },
    watch: {
        rushId(){
            this.clearStreak()
            this.getRush()
        },
    },
    mounted() {
        if (this.announce.latest == 'rush.welcome') {
            this.showAnnounce({
                type:   'create',
                latest: 'rush.create',
            })

            axios.post('/api/rush/announce', {
                type: 'rush.create',
            }).then(({data}) => {
                console.log(data)
            })
            .catch((error) => {
                console.log(error)
            })
        }

        $('html, body').scrollTop(0);

        this.getRush()
    },
    beforeDestroy() {
        this.clearStreak()
    },
}
</script>
