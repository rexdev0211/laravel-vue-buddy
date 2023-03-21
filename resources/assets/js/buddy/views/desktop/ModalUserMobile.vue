<template>
    <CustomReveal
        v-if="user !== null"
        :dusk="'user-modal-' + user.id"
        :isVisible="user !== null"
        revealId="card"
        class="user-modal"
        style="overflow: auto !important;"
        :class="{'report-menu-open': reportMenuVisible}"
        v-on:close-reveal-card="closeUserModal">

        <div class="secondary-menu profile">

            <div class="secondary-menu-body" id="card" data-reveal2>
                <div class="secondary-menu-header">
                    <i class="back mobileModalBack" @click="closeUserModal"></i>
                    <div class="info"
                        :class="{'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline}">
                        <div class="name notranslate">{{ user.name }}</div>
                        <div class="how-far notranslate" v-if="!isSelfUser">{{ getDistanceString(user) }}</div>
                    </div>
                    <i class="dots" v-if="!isSelfUser" @click="menuVisible = !menuVisible"></i>
                    <div class="block-report" :class="{'open': menuVisible}">
                        <div id="block-profile" class="option block"
                            v-show="!reportMenuVisible" v-on:click="blockUser(user.id)">
                            <span>{{ trans('block_user') }}</span>
                        </div>
                        <div id="report-profile" class="option report"
                            v-show="!reportMenuVisible" v-on:click="showReportMenu">
                            <span>{{ trans('report_user') }}</span>
                        </div>
                    </div>
                </div>

                <div class="main-box">
                    <div class="photo" :style="{'background': `url(${this.userPhoto}) no-repeat center / cover`}"
                        @click="displayUserPhoto(user.public_photos[0])">
                    </div>

                    <div class="main-box-footer" :class="{'justify-end': !isSelfUser}">
                        <!-- <router-link v-if="isSelfUser" class="edit-button btn" :to="`/profile/edit`">
                            <span>{{ 'Edit' || trans('edit_profile') }}</span>
                        </router-link> -->
                        <div class="func-buttons" v-if="!isSelfUser">
                            <div id="to-favorite" class="button to-favorite"
                                v-on:click="toggleFavorite(user)"
                                :class="{'selected': user.isFavorite}">
                            </div>
                            <div id="to-tap" class="button to-tap" :class="{'tapsForPro': userIsPro, 'open': toggleTapVisible}" v-on:click="toggleTapVisible">
                                <div v-if="waveIcon" class="tap-icon-given" :style="{'background-image': 'url('+ waveIcon +')'}"></div>
                                <div v-else class="to-tap-icon"></div>
                                <transition name="scale-in-center">
                                    <div class="taps" v-show="tapsVisible">
<!--                                        <div class="tap video" v-if="userIsPro"-->
<!--                                            v-on:click="waveToUser(userId, 'video', $event)">-->
<!--                                        </div>-->

                                      <div class="tap donut" v-if="userIsPro"
                                           v-on:click="waveToUser(userId, 'donut', $event)">
                                      </div>

                                        <div class="tap rocket" v-if="userIsPro"
                                            v-on:click="waveToUser(userId, 'rocket', $event)">
                                        </div>
                                        <div class="tap beer" v-if="userIsPro"
                                            v-on:click="waveToUser(userId, 'beer', $event)">
                                        </div>
<!--                                        <div class="tap ring" v-if="userIsPro"-->
<!--                                            v-on:click="waveToUser(userId, 'ring', $event)">-->
<!--                                        </div>-->
                                      <div class="tap nicebody" v-if="userIsPro"
                                           v-on:click="waveToUser(userId, 'nicebody', $event)">
                                      </div>
                                        <div class="tap now" v-on:click="waveToUser(userId, 'now', $event)"></div>
                                      <div class="tap pig" v-on:click="waveToUser(userId, 'pig', $event)"></div>
<!--                                        <div class="tap sweat" v-on:click="waveToUser(userId, 'sweat', $event)"></div>-->
                                        <div class="tap devil" v-on:click="waveToUser(userId, 'devil', $event)"></div>
                                        <div class="tap apple" v-on:click="waveToUser(userId, 'apple', $event)"></div>
                                        <div class="tap love" v-on:click="waveToUser(userId, 'love', $event)"></div>
                                        <div class="tap banana" v-on:click="waveToUser(userId, 'banana', $event)"></div>
                                        <div class="tap fire" v-on:click="waveToUser(userId, 'fire', $event)"></div>
                                        <div class="tap hand" v-on:click="waveToUser(userId, 'hand', $event)"></div>
                                    </div>
                                </transition>
                            </div>
                            <div id="to-chat" class="button to-chat"
                                v-on:click="startUserConversation(user.id, true)"
                                :class="{'notificated': unreadMessagesCount(user.id)}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile-footer" v-if="user.about || user.height || user.weight || user.body || user.position || user.penis || user.hiv || user.drugs || notDefaultPublicPhotos && notDefaultPublicPhotos.length || user && user.public_videos.length">
                    <div class="box">
                        <div class="about" v-if="user.about">
                            <p>{{ user.about }}</p>
                        </div>
                        <div class="tags" v-if="!isApp && user.tags && user.tags.length">
                          <div class="tag notranslate" v-for="tag in user.tags"><a @click.prevent="goSearchTag(tag);" :href="'/search?q='+encodeURIComponent('#'+tag.name)">{{ tag.name }}</a></div>
                        </div>
                        <div class="parameters-box" v-if="user.age || user.height || user.weight || user.body || user.position || user.penis || user.hiv || user.drugs">
                            <div class="parameters" v-if="user.age || user.height || user.weight || user.body">
                                <span v-if="user.age">{{ user.age }}</span>
                                <span v-if="user.height">{{ formatHeight(user.height) }}</span>
                                <span v-if="user.weight">{{ formatWeight(user.weight) }}</span>
                                <span v-if="user.body">{{ transBody(user.body).toLowerCase() }}</span>
                            </div>
                            <div class="parameters" v-if="user.position || user.penis">
                                <span v-if="user.position">{{ transPosition(user.position).toLowerCase() }}</span>
                                <span v-if="user.penis">{{ user.penis }}</span>
                            </div>
                            <div class="parameters" v-if="user.hiv">
                                <span>{{ trans('hiv') }}: {{ transHiv(user.hiv).toLowerCase() }}</span>
                            </div>
                            <div class="parameters" v-if="user.drugs">
                                <span>{{ trans('drugs') }}: {{ transDrugs(user.drugs).toLowerCase() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pics-catalog mobile-catalog" v-if="onlyPublicPhotos && onlyPublicPhotos.length">
                  <div class="pic" v-for="photo in notDefaultPublicPhotos"
                       :class="{'sensitive': photo.manual_rating === 'prohibited'}"
                       :id="`photo${photo.id}`"
                       @click="displayUserPhoto(photo)">
                    <div class="img" :id="'photo' + photo.id"
                         :style="{'background': `url(${photo.photo_small}) no-repeat center / cover`}">
                    </div>
                  </div>
                </div>
                <div class="pics-catalog mobile-catalog" v-if="videos && videos.length">
                  <div class="pic video" v-for="video in videos"
                       :id="`video${video.id}`"
                       @click="displayUserVideo(video)">
                    <div class="img" :id="'video' + video.id"
                         :style="{'background': `url(${video.thumb_small}) center / cover`}">
                    </div>
                  </div>
                </div>
            </div>
        </div>

        <div class="reveals-popup">
          <UserPhotosReveals v-if="user" :userToken="user.id" :photos="user.public_photos"></UserPhotosReveals>
          <UserVideosReveals v-if="user" :userToken="user.id" :videos="user.public_videos"></UserVideosReveals>
        </div>

        <div class="mobileSidebarHolder positionRight forDesktop"
             v-show="showReportMenu"
             :class="{'active': reportMenuVisible}">
            <div class="mobileSidebarHide" @click="hideReportMenu"></div>
            <div class="mobileSidebar">

                <div class="report-menu">
                    <div class="inner">
                        <i class="back" @click="hideReportMenu"></i>
                        <div class="title">{{ trans('report_user') }}</div>
                        <div class="box">
                            <div class="checkbox-container">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="spam" value="spam" v-model="reportData">
                                    <span class="checkbox-custom"></span>
                                    <div class="input-title">{{ trans('report_spam') }}</div>
                                </label>
                            </div>
                            <div class="checkbox-container">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="fake" value="fake" v-model="reportData">
                                    <span class="checkbox-custom"></span>
                                    <div class="input-title">{{ trans('report_fake') }}</div>
                                </label>
                            </div>
                            <div class="checkbox-container">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="harassment" value="harassment" v-model="reportData">
                                    <span class="checkbox-custom"></span>
                                    <div class="input-title">{{ trans('report_harassment') }}</div>
                                </label>
                            </div>
                            <div class="checkbox-container">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="under-age" value="under_age" v-model="reportData">
                                    <span class="checkbox-custom"></span>
                                    <div class="input-title">{{ trans('report_under_age') }}</div>
                                </label>
                            </div>
                            <div class="checkbox-container">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="other" value="other" v-model="reportData">
                                    <span class="checkbox-custom"></span>
                                    <div class="input-title">{{ trans('report_other') }}</div>
                                </label>
                            </div>
                        </div>
                        <button type="send" class="btn" @click="reportUser">{{ trans('send') }}</button>
                    </div>
                </div><!--report-menu-->

            </div>
        </div>

    </CustomReveal>
</template>

<style>
.reveals-popup .secondary-menu-header {
  position: absolute !important;
}
.mobile-catalog {
  background:#003C3C;
}
</style>

<script>
    import {mapState, mapGetters} from 'vuex';

    import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';
    import UserPhotosReveals from '@buddy/views/widgets/MobileUserPhotosReveals.vue';
    import UserVideosReveals from '@buddy/views/widgets/MobileUserVideosReveals.vue';

    import chatModule from '@chat/module/store/type'
    import discoverModule from "@discover/module/store/type";

    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-tap').default,
            require('@general/lib/mixin-chat').default,
        ],
        data() {
            return {
                userPhoto: '',
                tapsVisible: false,

                menuVisible: false,
                reportMenuVisible: false,
                reportData: [],
            }
        },
        components: {
            CustomReveal,
            UserPhotosReveals,
            UserVideosReveals,
            vueCustomScrollbar
        },
        computed: {
            ...mapState({
                user: state => state.modal.user,
                userIsPro: 'userIsPro',
                blockedUsersIds: 'blockedUsersIds',
            }),
            ...mapGetters({
                unreadMessagesCount: chatModule.getters.messages.count.unread,
            }),
            userId() {
                return this.user.id;
            },
            photos() {
              return this.user && this.user.public_photos;
            },
            videos() {
              return this.user && this.user.public_videos;
            },
            photo() {
                return this.publicPhotos && this.publicPhotos.find(el => el.id == this.pid);
            },
            onlyPublicPhotos() {
                return this.photos.filter(v => v.is_default == 'no');
            },
            notDefaultPublicPhotos() {
                return this.user && this.user.public_photos.filter(v => v.photo_orig != this.user.photo_orig)
            }
        },
        methods: {
          goToPhoto(photo) { // TODO: tmp method, change with popup
            /* too_hot */
            let isSafe = this.photoIsSafe(photo)
            if (!isSafe) {
              return
            }

            if (photo !== undefined) {
              let userToken = this.user.link || this.user.id
              this.$router.push(`/user/${userToken}/photo/${photo.id}`)
            }

            this.$nextTick(() => {
              this.closeUserModal();
            });
          },
          goToVideo(video) { // TODO: tmp method, change with popup
            /* too_hot */
            if (video && this.isApp && (!app.$store.state.userIsPro || app.$store.state.profile.view_sensitive_media == 'no')) {
              if (!app.$store.state.userIsPro) app.$store.dispatch('requirementsAlertShow', 'censored')
              else app.$store.dispatch('requirementsAlertShow', 'change_settings')
            } else if (video !== undefined) {
              let userToken = this.user.link || this.user.id
              this.$router.push(`/user/${userToken}/video/${video.id}`)
            }

            this.$nextTick(() => {
              this.closeUserModal();
            });
          },

            goSearchTag(tag) {
              this.closeUserModal();
              this.$router.replace({ name: 'search', query: { q: '#'+tag.name  }, })
            },
            displayUserPhoto(photo) {
                if (photo !== undefined) {
                    app.$emit('show-view-photo-reveal', photo.id);
                }
            },
            displayUserVideo(video) {
                if (video !== undefined) {
                    app.$emit('show-view-video-reveal', video.id);
                }
            },
            showReportMenu() {
                this.reportMenuVisible = true
            },
            hideReportMenu() {
                this.reportMenuVisible = false
            },
            hideMenu() {
                this.hideReportMenu()
                this.menuVisible = false
            },
            reportUser(userId, type) {
                axios.post(`/api/reportUser/${this.userId}?type=${this.reportData}`)
                    .then(() => {
                        this.showSuccessNotification('user_reported_confirmation');
                        this.hideMenu();
                    })
            },
            async blockUser(userId) {
                if (!this.$store.state.userIsPro && this.$store.state.blockedCount >= window.FREE_BLOCKS_LIMIT) {
                    await this.$store.dispatch('requirementsAlertShow', 'blocks')
                } else {
                    //close user menu
                    this.hideMenu();

                    // Close user profile
                    if (this.user && userId == this.user.id) {
                        this.closeUserModal();
                    }

                    // Close user chat
                    if (this.chatUser && userId == this.chatUser.id) {
                        this.finishConversation();
                    }
                    // Make block request
                    await this.blockUserById(userId)
                }
            },
        },
        watch: {
            user(newValue, oldValue) {
                if (newValue) {
                    this.userPhoto = this.user.photo_small;
                    setTimeout(() => {
                        this.userPhoto = this.user.photo_orig;
                    }, 0);
                } else {
                    this.hideMenu();
                    this.hideTaps();
                }
            },
            blockedUsersIds: function(blockedUsersIds) {
                if (blockedUsersIds.includes(this.user?.id)) {
                    this.closeUserModal()
                }
            }
        },

      created() {
        document.onkeydown = evt => {
          evt = evt || window.event;
          if (evt.keyCode == 27) {
            this.closeUserModal()
          }
        }

      }
    }
</script>
