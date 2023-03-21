<template>
    <div v-if="user">
        <div class="box">
            <div class="row main-pics">
                <div id="profile-main-pic" class="profile-main-pic" @click="openGallery('clear')">
                    <div class="img"
                        v-if="!preloaders.clear && !defaultAvatarBackground.match(/default_180x180/)"
                        :style="{'background': defaultAvatarBackground}"
                    >
                      <div v-if="avatars.default.pending || avatars.default.rejected"
                           :class="{'pending': avatars.default.pending, 'rejected': avatars.default.rejected}"
                      ></div>
                    </div>
                    <div v-if="!preloaders.clear && !defaultAvatarBackground.match(/default_180x180/)"
                        @click.stop="clearAvatar('clear')" class="close">
                    </div>
                    <img v-else-if="preloaders.clear" class="preloader"
                        src="/assets/img/preloader.svg"
                        alt="">
                    <div class="title" v-if="!isApp">{{ trans('preview') }}</div>
                </div>

                <div id="profile-main-pic-adult" class="profile-main-pic" v-if="!isApp" @click.self="openGallery('adult')">
                    <div class="img"
                        v-if="!preloaders.adult && !getAdultAvatarStyle.match(/default_180x180/)"
                        :style="{'background': getAdultAvatarStyle}">
                    </div>
                    <div v-if="!preloaders.adult && !getAdultAvatarStyle.match(/default_180x180/)"
                        @click.stop="clearAvatar('adult')" class="close">
                    </div>
                    <img v-else-if="preloaders.adult" class="preloader"
                        src="/assets/img/preloader.svg"
                        alt="">
                    <div class="title">{{ trans('alternative') }}</div>
                </div>
            </div>

            <div class="media-catalog">
                <PhotoSlider/>
                <VideoSlider/>
            </div>
        </div>
        <div class="box">
            <div class="name-edit notranslate">
                <input
                    v-model="user.name"
                    type="text"
                    name="name"
                    class="form-control"
                    v-bind:data-vv-as="trans('display_name')"
                    v-validate="{required: true, min: 3, max: 32}"
                    @focus="rememberProfileOldValue"
                    @change="saveProfileChange"
                >
            </div>
            <span class="form-error" :class="{'is-visible': errors.has('name')}">
              {{ errors.first('name') }}
            </span>
        </div>
        <div class="box">
            <div class="headline">{{ trans('about_me') }}</div>
            <div class="text" :style="{'padding-bottom': textarea.height ? 30 + 'px' : 0}">
                <textarea
                    v-model="user.about"
                    :rows="textarea.rows + 1"
                    name="about"
                    ref="textAreaAbout"
                    maxlength="300"
                    class="form-control no-swiping"
                    @change="saveProfileChange"
                    @input="resizeTextArea"
                    @blur="unfocusTextArea"
                    @key.up="() => {setTimeout(resizeTextArea, 100)}"
                    :placeholder="trans('main_profile_about')"></textarea>
            </div>
        </div>
        <div class="tags">
            <form @submit.prevent="onSubmit" class="tag edit notranslate">
                <input type="text" maxlength="50" v-model='newTag'
                    class="form-control no-swiping" @keyup.enter.prevent="addTag"
                    v-bind:placeholder="trans('main_add_tag')">
            </form>
            <div class="tag added" v-if="user.tags && user.tags.length"
                @click="deleteTag(tag)"
                v-for="tag in user.tags">
                <span class="notranslate">{{ tag.name }}</span>
            </div>
        </div>
        <div class="box">
            <div class="headline">{{ trans('main_statistics') }}</div>
            <div class="row">
                <div class="title">{{ trans('height') }}</div>
                <div class="field">
                    <select class="form-control no-swiping" name="height" v-model="user.height"
                            @change="saveProfileChange">
                        <option value="">---</option>
                        <option v-for="(value, key) in options.heights" :value="key">{{ value }}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="title">{{ trans('weight') }}</div>
                <div class="field">
                    <select class="form-control no-swiping" name="weight" v-model="user.weight"
                            @change="saveProfileChange">
                        <option value="">---</option>
                        <option v-for="(value, key) in options.weights" :value="key">{{ value }}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="title">{{ trans('body') }}</div>
                <div class="field">
                    <select class="form-control no-swiping" name="body" v-model="user.body"
                            @change="saveProfileChange">
                        <option value="">---</option>
                        <option v-for="(value, key) in options.bodyTypes" :value="key">{{ transBody(value) }}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="title">{{ trans('position') }}</div>
                <div class="field">
                    <select class="form-control no-swiping" name="position"
                            v-model="user.position" @change="saveProfileChange">
                        <option value="">---</option>
                        <option :value="key" v-for="(value, key) in options.positionTypes">{{ transPosition(value)
                            }}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="title">{{ trans('penis') }}</div>
                <div class="field">
                    <select class="form-control no-swiping" name="penis" v-model="user.penis"
                            @change="saveProfileChange">
                        <option value="">---</option>
                        <option v-for="(value, key) in options.penisSizes" :value="key">{{ value }}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="title">{{ trans('hiv') }}</div>
                <div class="field">
                    <select class="form-control no-swiping" name="hiv" v-model="user.hiv"
                            @change="saveProfileChange">
                        <option value="">---</option>
                        <option :value="key" v-for="(value, key) in options.hivTypes">{{ transHiv(value) }}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="title">{{ trans('drugs') }}</div>
                <div class="field">
                    <select class="form-control no-swiping" name="drugs" v-model="user.drugs"
                            @change="saveProfileChange">
                        <option value="">---</option>
                        <option :value="key" v-for="(value, key) in options.drugsTypes">{{ transDrugs(value) }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <PhotoGallery/>
        <VideoGallery/>
    </div>
</template>

<script>
    import {mapState} from 'vuex';

    import PhotoSlider from '@profile/views/widgets/form/media/PhotoSlider.vue'
    import VideoSlider from '@profile/views/widgets/form/media/VideoSlider.vue'
    import PhotoGallery from '@profile/views/widgets/form/media/PhotoGallery.vue'
    import VideoGallery from '@profile/views/widgets/form/media/VideoGallery.vue'
    import discoverModule from "../../../../discover/module/store/type";
    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            PhotoSlider,
            VideoSlider,
            PhotoGallery,
            VideoGallery,
        },
        data() {
            return {
                tags: [],
                newTag: '',
                preloaders: {
                    clear: false,
                    adult: false,
                },
                avatars: {
                    default: {
                        pending: false,
                        rejected: false
                    },
                    adult: {

                    }
                },
                textarea: {
                  rows: 1,
                  height: 45,
                  scrollHeight: null,
                  baseScrollHeight: 0,
                },
            }
        },
        computed: {
            ...mapState({
                user: 'profile',
                options: 'profileOptions'
            }),
            defaultAvatar(){
                let user = this.user
                return user.avatars && user.avatars.default
            },
            adultAvatar(){
                let user = this.user
                return user.avatars && user.avatars.adult
            },
            defaultAvatarBackground(){
                if (this.defaultAvatar) {
                    return `url(${this.defaultAvatar.photo_small}) center / cover`
                } else {
                    return 'url("/assets/img/default_180x180.jpg") center / cover'
                }
            },
            getAdultAvatarStyle(){
                if (this.adultAvatar) {
                    if (this.adultAvatar.rejected) {
                        return '#5a5a5a'
                    } else {
                        return `url(${this.adultAvatar.photo_small}) center / cover`
                    }
                } else {
                    return 'url("/assets/img/default_180x180.jpg") center / cover'
                }
            },
        },
        methods: {
            async clearAvatar(slot){
                let photo = slot === 'adult' ?
                    this.adultAvatar
                    :
                    this.defaultAvatar

                if (!photo) {
                    this.openGallery(slot)
                    return false
                }

                app.$emit('avatar-preloader', { slot, value: true })
                await this.makePhotoVisibleTo(photo.id, 'private')
                          .then(response => {
                            let defaultImg = '/assets/img/default_180x180.jpg'
                            let usersAround = this.$store.state.discoverModule.usersAround;
                            if (usersAround) {
                              let index = usersAround.findIndex(el => el.id === auth.getUserId());

                              if (index !== -1) {
                                  this.$store.state.discoverModule.usersAround[index].photo_small = defaultImg;
                                  let haveUserInfo = typeof this.$store.state.usersInfo[auth.getUserId()] != 'undefined';
                                  if (haveUserInfo) {
                                    this.$store.state.usersInfo[auth.getUserId()].photo_small = defaultImg;
                                    this.$store.state.usersInfo[auth.getUserId()].photo_orig = defaultImg;
                                  }
                              }
                            }
                          })
                await this.$store.dispatch('loadCurrentUserInfo')
                app.$emit('avatar-preloader', { slot, value: false })
            },
            openGallery(slot){
                app.$emit('show-photo-gallery', slot)
            },
            addTag() {
                let tagValue = _.trimStart(this.newTag, '# ');
                tagValue = _.trimEnd(tagValue, ' ');
                if (!tagValue) {
                    return;
                }

                this.newTag = '';
                axios.post('/api/tags/add', {name: tagValue})
                    .then(({data}) => {
                        if (data) {
                            this.user.tags.push(data);
                        }
                    })
            },
            deleteTag(tag) {
                const index = this.user.tags.indexOf(tag);
                const removedArray = this.user.tags.splice(index, 1);

                axios.post('/api/tags/delete', {id: tag.id})
                    .catch((error) => {
                        this.user.tags.splice(index, 0, removedArray[0]);
                    });
            },
            unfocusTextArea() {
               if (this.user.about === '') {
                  this.resetTextAreaSize();
               }
            },
            resetTextAreaSize(){
              this.textarea.rows   = 1
              this.textarea.height = 45
            },
            resizeTextArea() {

              let rowsCount = Math.ceil((this.$refs.textAreaAbout.scrollHeight - 12 * 2) / 21)

              this.textarea.rows = rowsCount + 1 > 6 ? 6 : rowsCount

              if (!this.isMobile) {
                this.textarea.height = this.textarea.rows * 21 + 12 * 2
              } else {
                if (rowsCount === 1) this.textarea.height = null
                else if (rowsCount === 2) this.textarea.height = 59
                else if (rowsCount === 3) this.textarea.height = 80
                else if (rowsCount === 4) this.textarea.height = 101
                else if (rowsCount === 5) this.textarea.height = 122
                else if (rowsCount >= 6) this.textarea.height = 143
              }
            },
            setAvatarPreloader(payload){
                this.preloaders[payload.slot] = payload.value
            }
        },
        watch: {
          defaultAvatar(value) {
            if (value) {
              this.avatars.default = {pending: value.pending, rejected: value.rejected}
            }
          }
        },
        activated() {
            this.$store.dispatch('loadCurrentUserInfo')
        },
        mounted() {
            let user = this.user;

            if (user) {
                let defaultAvatar = user.avatars?.default;
                this.avatars.default = {pending: defaultAvatar?.pending, rejected: defaultAvatar?.rejected}
            }

            app.$on('avatar-preloader', this.setAvatarPreloader)
        },
        destroyed() {
            app.$off('avatar-preloader')
        }
    }
</script>
