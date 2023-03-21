<template>
	<div class="w-root">
		<div class="w-views">
            <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">
                <div class="content-wrapper">
                    <div class="event-screen">
                        <div class="event-details event">
                            <div class="header">
                                <i class="back" @click="goBack()"></i>
                                <i class="dots" v-if="event && event.membership !== 'host'" v-show="!reportMenuVisible" v-on:click="showReportMenu"></i>
                            </div>

                            <div class="main-box" v-if="event" :id="`event-${event.id}`">
                                <div class="photo event"
                                    v-if="event.photo_small"
                                    @click="goToPhoto(defaultPhoto)"
                                    :style="{'background': `url(${event.photo_orig}) no-repeat center / cover`}"></div>
                                <div class="main-box-footer">
                                    <div class="inner">
                                        <div class="info" :class="{'was-online': event.wasRecentlyOnline && !event.isOnline, 'online': event.isOnline && event.type !== 'guide'}">
                                            <div class="name" v-if="!dataLoading && canViewEventDetails(event.user_id)">
                                                {{ event.title }}
                                            </div>
                                            <div class="venue">
                                                {{ event.venue }}
                                            </div>
                                            <div class="how-far" v-if="!isMyEvent(event) && event.type !== 'guide'">{{ getDistanceString(event) }}</div>
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
                                            <div class="host" v-if="event.is_profile_linked && !event.sticky" @click="!event.user.deleted_at && openUserModal(event.user_id)">
                                                <div class="img" :style="{'background': `url(${event.user.deleted_at ? trashIcon : event.user.photo_small}) no-repeat center / cover`}"></div>
                                                <div class="username">{{ event.user.name }}</div>
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
                                              v-for="photo in event.photos" @click="goToPhoto(photo)"
                                              :class="{'sensitive': user.view_sensitive_media === 'no'}">
                                              <div class="img" :style="{'background': `url(${photo.photo_orig}) no-repeat center / cover`}"></div>
                                          </div>
                                      </div>
                                      <div class="pics-catalog">
                                        <div class="pic video"
                                            v-for="video in event.videos" @click="goToVideo(video)"
                                            :class="{'sensitive': user.view_sensitive_media === 'no'}">
                                            <div class="img" :style="{'background': `url(${video.thumb_small}) no-repeat center / cover`}"></div>
                                        </div>
                                      </div>
                                    </div>

                                    <div v-else>
                                      <div class="pics-catalog">
                                        <div class="pic"
                                             v-for="photo in event.photos.slice(0, 4)" @click="goToPhoto(photo)"
                                             :class="{'sensitive': photo.manual_rating === 'prohibited'}">
                                          <div class="img" :style="{'background': `url(${photo.photo_orig}) no-repeat center / cover`}"></div>
                                        </div>
                                      </div>
                                      <div class="pics-catalog">
                                        <div class="pic video"
                                             v-for="video in event.videos.slice(0, 4)" @click="goToVideo(video)"
                                             :class="{'sensitive': !userIsPro}">
                                          <div class="img" :style="{'background': `url(${video.thumb_small}) no-repeat center / cover`}"></div>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="func-buttons event">
                                        <div v-if="!isMyEvent(event) && event.type !== 'guide'" class="send-message" v-on:click="chatEvent" :class="{'notificated': event.unreadMessagesCount}"></div>
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

                        </div>

                        <div class="mobileSidebarHolder positionRight"
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
                </div><!--content-wrapper-->
            </vue2-gesture>
		</div><!--w-views-->
	</div><!--w-root-->
</template>

<script>
    import {mapState, mapActions, mapGetters} from 'vuex';

    import eventsModule from '@events/module/store/type'
    import chatModule from '@chat/module/store/type'

    export default {
        data() {
			return {
                menuVisible: false,
                reportMenuVisible: false,
                reportData: [],
			}
		},
		props: ['eventId'],
	    mixins: [
	        require('@general/lib/mixin').default,
	        require('@general/lib/mixin-events').default,
		],
		methods: {
            ...mapActions({
                reportEvent: eventsModule.actions.events.report,
                toggleEventLike: eventsModule.actions.events.like
            }),
            openUserModal(userId) {
              this.goTo('/user/' + userId)
              //this.openUserMobileModal(userId)
            },
            handleGesture(str, e) {
                if (str == 'swipeRight')
                    this.goTo('/events')
            },
            showReportMenu() {
                this.reportMenuVisible = true;
            },
            hideReportMenu() {
                this.reportMenuVisible = false;
            },
            closeFilter() {
                this.hideReportMenu();
            },
            sendEventReport() {
                this.reportEvent({
                    reason: this.reportData,
                    id: this.event.id,
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
            getProfileSettingsUrl() {
                return window.APP_URL + '/profile/settings'
			},
            openProfileSettingsUrl() {
                window.open(this.getProfileSettingsUrl(), '_system')
			},
			async chatEvent() {
                if (!this.isMyEvent(this.event)) {
					this.goTo(`/chat/event-user/${this.eventId}/${this.event.user_id}`);
				} else {
					this.goTo('/chat');
				}
			},
            goToPhoto(photo) {
                /* too_hot */
                let isSafe = this.photoIsSafe(photo)

                if (!isSafe) {
                    return
                }

				if  (photo !== undefined) {
                    //TODO: save event profile in vuex as done for profile photos management, also pass photo details to the new component
                    this.$router.push(`/event/${this.eventId}/photo/${photo.id}`);
                }
            },
            goToVideo(video) {
                /* too_hot */
                let settingsSafe = this.userIsPro && this.$store.state.profile.view_sensitive_media == 'yes',
                    myEvent      = this.event.user_id == this.$store.state.profile.id && this.$store.state.profile.id

                if (this.isApp && !settingsSafe && !myEvent) {
                    if (!this.userIsPro) {
                        this.$store.dispatch('requirementsAlertShow', 'censored')
                    } else {
                        this.$store.dispatch('requirementsAlertShow', 'change_settings')
                    }

					return
                }

                if  (video !== undefined) {
                    //TODO: save event profile in vuex as done for profile photos management, also pass photo details to the new component
                    this.$router.push(`/event/${this.eventId}/video/${video.id}`);
                }
            },
		},
        computed: {
            ...mapState({
                user: 'profile',
                userIsPro: 'userIsPro',
            }),
            ...mapGetters({
                unreadEventMessagesCount: chatModule.getters.messages.count.unreadEvent
            }),
            getEventWebsite() {
                return this.event.website.startsWith('https://') || this.event.website.startsWith('http://') ? this.event.website : `//${this.event.website}`
            },
            heartFilledIn() {
                return this.event.isLiked
            },
            event() {
                return this.$store.getters.getEvent(this.eventId)
            },
            dataLoading() {
                return !this.event;
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
        mounted(){
            console.log('[Event Item mobile] Mounted')
            if (!!parseInt(this.eventId)){
                this.$store.dispatch(eventsModule.actions.events.loadInfo, this.eventId)
            }
            if (!!this.eventOriginal) {
                this.reset()
            }
        },
        activated(){
            console.log('[Event] Activated')
            // this.$store.dispatch('showHealthAlert')
        }
	}
</script>
