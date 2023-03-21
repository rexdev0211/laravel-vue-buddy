<template>
    <div class="event-screen">
        <vue-custom-scrollbar ref="customScrollbar" class="event-details event">
            <div class="header">
                <i class="back" @click="closeEvent"></i>
                <i class="dots" v-if="event.user_id != authUserId && !event.sticky" v-show="!reportMenuVisible" v-on:click="showReportMenu"></i>
            </div>

            <div class="main-box" v-if="event" :id="`event-${event.id}`">
                <div class="photo event"
                    v-if="event.photo_small"
                    @click="displayEventPhoto(defaultPhoto)"
                    :style="{'background': `url(${event.photo_orig}) no-repeat center / cover`}">
                </div>
                <div class="main-box-footer">
                    <div class="inner">
                        <div class="info" :class="{'was-online': event.wasRecentlyOnline && !event.isOnline, 'online': event.isOnline && event.type !== 'guide'}">
                            <div class="name">
                                {{ event.title }}
                            </div>
                            <div class="venue">
                                {{ event.venue }}
                            </div>
                            <div class="how-far notranslate" v-if="!isMyEvent(event) && event.type !== 'guide'">{{ getDistanceString(event) }}</div>
                            <div class="candy-yes" v-if="event.chemsfriendly && event.type === 'fun'"></div>
                        </div>
                        <div class="details event">
                            <div class="time" v-if="!event.sticky">
                                {{ event.event_date | formatDate('day-date') }} - {{ event.time }}
                            </div>
                            <div class="location notranslate" v-if="event.address">{{ event.type == 'fun' || event.type == 'bang' ? event.locality : event.address }}</div>
                            <div class="location notranslate" v-else>BUDDYWOOD</div>
                            <div class="website" v-if="event.type === 'guide' && event.website">
                              <a :href="getEventWebsite" target="_blank">{{ event.website }}</a>
                            </div>
                            <div class="host" v-if="event.is_profile_linked && !event.sticky" @click="openUser(event.user_id)">
                                <div class="img" :style="{'background': `url(${event.user.deleted_at ? trashIcon : event.user.photo_small}) no-repeat center / cover`}"></div>
                                <div class="username notranslate">{{ event.user.name }}</div>
                            </div>
                            <div class="description" v-if="event.type !== 'guide'">{{ event.description }}</div>
                            <div class="description" v-else v-html="event.description"></div>
                            <div class="tags" v-if="event.tags && event.tags.length">
                                <div class="tag notranslate" v-for="tag in event.tags"><span>{{ tag.name }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div v-if="userIsPro">
                      <div class="pics-catalog">
                          <div class="pic"
                              v-for="photo in event.photos" @click="displayEventPhoto(photo)"
                              :class="{'sensitive': user.view_sensitive_media === 'no'}">
                              <div class="img" :style="{'background': `url(${photo.photo_orig}) no-repeat center / cover`}"></div>
                          </div>
                      </div>
                      <div class="pics-catalog">
                        <div class="pic video"
                            v-for="video in event.videos" @click="displayEventVideo(video)"
                            :class="{'sensitive': user.view_sensitive_media === 'no'}">
                            <div class="img" :style="{'background': `url(${video.thumb_small}) no-repeat center / cover`}"></div>
                        </div>
                    </div>
                    </div>

                    <div v-else>
                      <div class="pics-catalog">
                          <div class="pic"
                              v-for="photo in event.photos.slice(0, 4)" @click="displayEventPhoto(photo)"
                              :class="{'sensitive': photo.manual_rating === 'prohibited'}">
                              <div class="img" :style="{'background': `url(${photo.photo_orig}) no-repeat center / cover`}"></div>
                          </div>
                      </div>
                      <div class="pics-catalog">
                        <div class="pic video"
                             v-for="video in event.videos.slice(0, 4)" @click="displayEventVideo(video)"
                             :class="{'sensitive': !userIsPro}">
                          <div class="img" :style="{'background': `url(${video.thumb_small}) no-repeat center / cover`}"></div>
                        </div>
                      </div>
                    </div>


                    <div class="func-buttons event">
                        <div v-if="!isMyEvent(event) && event.type !== 'guide'" class="send-message" v-on:click="startEventConversation(event.user_id, event.id)" :class="{'notificated': unreadEventMessagesCount(event.id, null)}"></div>
                        <div class="like"
                            :class="{'liked': event.isLiked}"
                            v-on:click="toggleEventLike(event)">
                            <span v-if="event.likes">{{ event.likes }}</span>
                        </div>
                        <button type="button" v-if="isMyEvent(event)"
                            @click="editEvent(event.id, 'event')"
                            class="edit-button btn">
                            {{ trans('edit') }}
                        </button>
                    </div>
                </div>
            </div>
        </vue-custom-scrollbar>

        <div class="mobileSidebarHolder positionRight forDesktop"
            v-show="showReportMenu"
            :class="{'active': reportMenuVisible}">
            <div class="mobileSidebarHide" @click="hideReportMenu"></div>
            <div class="mobileSidebar">

                <div class="report-menu">
                    <div class="inner">
                        <div class="title">{{ trans('events.report.title') }}</div>
                        <div class="box">
                            <div class="checkbox-container">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="wrong_category" value="wrong_category" v-model="reportData">
                                    <span class="checkbox-custom"></span>
                                    <div class="input-title">{{ trans('events.report.wrong_type') }}</div>
                                </label>
                            </div>
                            <div class="checkbox-container">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="under_age" value="under_age" v-model="reportData">
                                    <span class="checkbox-custom"></span>
                                    <div class="input-title">{{ trans('report_under_age') }}</div>
                                </label>
                            </div>
                            <div class="checkbox-container">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="illegal" value="illegal" v-model="reportData">
                                    <span class="checkbox-custom"></span>
                                    <div class="input-title">{{ trans('events.report.illegal') }}</div>
                                </label>
                            </div>
                            <div class="checkbox-container">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="spam" value="spam" v-model="reportData">
                                    <span class="checkbox-custom"></span>
                                    <div class="input-title">{{ trans('report_spam') }}</div>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn" @click="sendEventReport">{{ trans('send') }}</button>
                    </div>
                </div><!--report-menu-->

            </div>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';
    import {mapState, mapActions, mapGetters} from 'vuex';
    import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';
    import UserPhotosReveals from '@buddy/views/widgets/UserPhotosReveals.vue';
    import UserVideosReveals from '@buddy/views/widgets/UserVideosReveals.vue';
    import EditEvent from '@events/views/desktop/event/Edit.vue';

    import chatModule from '@chat/module/store/type'
    import eventsModule from '@events/module/store/type'

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-chat').default,
            require('@general/lib/mixin-events').default,
        ],
        props: ['eventId'],
        data() {
            return {
                menuVisible: false,
                reportMenuVisible: false,
                reportData: [],
            }
        },
        components: {
            CustomReveal,
            UserPhotosReveals,
            UserVideosReveals,
            EditEvent,
            vueCustomScrollbar
        },
        computed: {
            ...mapState({
                user: 'profile',
                userIsPro: 'userIsPro',
            }),
            ...mapGetters({
                eventData: eventsModule.getters.event,
                unreadEventMessagesCount: chatModule.getters.messages.count.unreadEvent
            }),
            event(){
                return this.$store.getters.getEvent(this.eventData.eventId)
            },
            getEventWebsite() {
                return this.event.website.startsWith('https://') || this.event.website.startsWith('http://') ? this.event.website : `//${this.event.website}`
            },
            photos() {
                return this.event && this.event.photos && this.event.photos.filter(v => v.pivot.is_default == 'no');
            },
            videos() {
                return this.event && this.event.videos;
            },
            defaultPhoto() {
              return this.event.photos.find(v => v.pivot.is_default == 'yes');
            }
        },
        methods: {
            ...mapActions({
                reportEvent: eventsModule.actions.events.report,
                toggleEventLike: eventsModule.actions.events.like
            }),
            hideMenu(){
                this.hideReportMenu()
                this.menuVisible = false
            },
            showReportMenu() {
                this.reportMenuVisible = true
            },
            hideReportMenu() {
                this.reportMenuVisible = false
            },
            closeFilter() {
                this.hideReportMenu();
            },
            sendEventReport(reason) {
                this.reportEvent({
                    reason: this.reportData,
                    id:       this.event.id,
                    callback: this.eventReportResponse,
                })
            },
            eventReportResponse(response) {
                if (response.data.success) {
                    this.showSuccessNotification(response.data.trans)
                } else {
                    this.showErrorNotification(response.data.trans)
                }
                this.closeFilter()
            },
            displayEventPhoto(photo) {
                if (photo !== undefined) {
                    app.$emit('show-view-photo-reveal', photo.id);
                }
            },
            displayEventVideo(video) {
                if (video !== undefined) {
                    app.$emit('show-view-video-reveal', video.id);
                }
            },
            openUser(userId) {
                this.closeEvent()
                this.openUserModal(userId, 7)
            },
        },
        mounted() {
            if (this.$refs.customScrollbar) {
              this.$refs.customScrollbar.$forceUpdate()
            }
        }
    }
</script>
