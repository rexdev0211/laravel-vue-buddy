<template v-if="entitiesLoaded">
    <div style="height: 100%; min-height: 100%;" class="wrap">
        <div id="js-chat-group-cmp" class="messages-box"
            :style="{'padding-bottom': (textarea.height + 30 * 2) > 108 ? (textarea.height + 30 * 2)+'px' : '108px'}"
            @click="hideAttachMenu">
            <slot></slot>

            <svg class="svg" width="0" height="0">
              <clipPath id="media-clip-incomming" clipPathUnits="objectBoundingBox">
                    <path d="M0.113,0 C0.113,0,0.113,0,0.113,0 H0.068 H0.055 H0.01 C0.004,0,0,0.005,0,0.01 C0,0.012,0.001,0.014,0.002,0.016 L0.046,0.084 V0.952 C0.046,0.979,0.066,1,0.092,1 H0.954 C0.979,1,1,0.979,1,0.952 V0.048 C1,0.021,0.979,0,0.954,0 H0.113"></path>
                </clipPath>
            </svg>
            <svg class="svg" width="0" height="0">
                <clipPath id="media-clip-outcomming" clipPathUnits="objectBoundingBox">
                    <path d="M0.887,1 h0.103 a0.01,0.01,0,0,0,0.01,-0.01 c0,-0.002,-0.001,-0.004,-0.002,-0.006 L0.954,0.916 V0.048 c0,-0.027,-0.021,-0.048,-0.046,-0.048 H0.046 C0.021,0,0,0.021,0,0.048 v0.904 c0,0.027,0.021,0.048,0.046,0.048 h0.841"></path>
                </clipPath>
            </svg>

            <infinite-loading
                ref="infiniteLoadingChat"
                @infinite="getMessages"
                spinner="bubbles"
                direction="top">
                <span slot="no-results">{{ trans('no_chat_messages') }}</span>
                <span slot="no-more" v-if="false">{{ trans('no_more_chat_messages') }}</span>
            </infinite-loading>

            <div class="fixed-event-box" v-if="chatMode === 'event' && isMyEvent(event)">
                <div class="img" :style="{'background': `url(${event.photo_small}) no-repeat center / cover`}"></div>
                <div class="col">
                    <div class="event-title">{{ event.title }}</div>
                    <div class="date">{{ event.event_date | formatDate('day-date-short') }}</div>
                </div>
            </div>

            <div v-for="(msg, index) in messages" class="message-box" :id="`msg-${msg.id}`">
                <div class="chat-updates"
                    v-if="['joined', 'left'].includes(msg.msg_type)"
                    @click="openUser(msg.user.link || msg.user.id)">
                    <div class="img" 
                        :class="{'is-deleted': !!msg.user.deleted_at}"
                        :style="!msg.user.deleted_at && {'background': `url(${msg.user.photo_small}) no-repeat center / cover`}">
                    </div>
                    <div class="col">
                        <div class="username">{{ msg.user.name }}</div>
                        <div class="descr">has {{ msg.msg_type }}</div>
                    </div>
                    <div class="time">{{ msg.idate | formatDate('hour') }}</div>
                </div>

                <div v-if="isNewDay(index)" class="timeline">{{ msg.idate | formatDate('day-date')}}</div>
                <div v-if="msg.cancelled" class="message removed" v-bind:class="{'outcomming': msg.user_from === currentUser.id, 'incomming': msg.user_from !== currentUser.id}">
                    {{ trans('removed') }}
                </div>

                <div v-else-if="(msg.msg_type === 'image') && msg.image_id === null && !msg.cancelled" class="message photo removed" v-bind:class="{'outcomming': msg.user_from === currentUser.id, 'incomming': msg.user_from !== currentUser.id}">
                    <span></span>
                </div>
                <div v-else-if="(msg.msg_type === 'video') && msg.video_id === null && !msg.cancelled" class="message video removed" v-bind:class="{'outcomming': msg.user_to == userId, 'incomming': msg.user_to != userId}">
                    <span></span>
                </div>

                <div v-else-if="!msg.cancelled && !['joined', 'left'].includes(msg.msg_type)" class="message"
                    :class="{'last': isMessageLast(index) || !!msg.failed,
                        'outcomming': msg.user_from === currentUser.id,
                        'incomming': msg.user_from !== currentUser.id,
                        'msg location': msg.msg_type === 'location',
                        'msg photo': msg.msg_type === 'image',
                        'msg video': msg.msg_type === 'video',
                    }">
                    <div class="close" @click="tryDelete(msg.id)"
                        v-bind:class="{'no-pro': !userIsPro}"
                        v-if="!['joined', 'left'].includes(msg.msg_type) && msg.user_from == currentUser.id">
                    </div>

                    <div class="img"
                        v-if="msg.user_from !== currentUser.id"
                        @click="openUser(msg.user_from)"
                        :class="{'is-deleted': !!msg.user.deleted_at}"
                        :style="!msg.user.deleted_at && {'background': `url(${msg.user.photo_small}) no-repeat center / cover`}">
                    </div>

                    <div class="msg" v-if="msg.msg_type === 'text'">
                        <div class="username"
                            v-if="msg.user_from !== currentUser.id && msg.msg_type === 'text'">
                            {{ msg.user.name }}
                        </div>
                        <div class="message-text" style="word-break: break-word;" @click="msg.failed && editMessageForNotDelivered(msg)">
                            <div v-for="msgText in msg.message.split('\n')" class="notranslate">
                                {{ msgText || "&nbsp;" }}
                            </div>
                        </div>
                    </div>

                    <div class="message-photo" v-if="msg.msg_type === 'image'"  @click.stop="msg.failed ? editMessageForNotDelivered(msg) : viewPhoto(msg.id)">
                        <div class="img" :style="{'background-image': 'url(' + getImagePath(msg) + ')' }"></div>
                    </div>

                    <div class="message-video" v-if="msg.msg_type === 'video'" @click.stop="msg.failed ? editMessageForNotDelivered(msg) : viewVideo(msg.id)">
                        <div class="img" :style="{'background': 'url(' + (msg.thumbnail_gif || msg.thumbnail_img) + ') center / cover' }"></div>
                    </div>

                    <div class="message-location" v-if="msg.msg_type === 'location'" @click.stop="msg.failed ? editMessageForNotDelivered(msg) : viewLocation(msg)"></div>

                    <div class="message-not-delivered" v-if="msg.failed"></div>
                    
                    <div v-if="msg.failed" class="message-not-delivered-text" @click.stop="editMessageForNotDelivered(msg)">
                        {{ trans('message_not_delivered') }}
                    </div>

                    <div v-if="!msg.failed && isMessageLast(index)" class="time">
                        <span v-if="msg.user_to == currentUser.id && userIsPro" v-bind:class="{'is-read': msg.is_read === 'yes'}">
                            <span class="message-info delivered" v-if="msg.is_read !== 'yes'"></span>
                            <span class="message-info read" v-else></span>
                        </span>
                        {{ msg.idate | formatDate('hour') }}
                    </div>
                </div>
            </div>
        </div>

        <transition name="fade" v-if="isMobile">
            <div v-show="showScrollBottom" class="scroll_bottom-button"
                style="margin-bottom: 10px;"
                @click="scrollToBottom()" :title="trans('arrow_scroll_top')">
                <svg class="icon icon-arrow_down"><use v-bind:xlink:href="symbolsSvgUrl('icon-arrow_down')"></use></svg>
            </div>
        </transition>

        <div class="footer" :class="{'attach-menu-open': attachMenuVisible, 'send-media-open': tab === 'photo' || tab === 'video', 'photo': tab === 'photo', 'video': tab === 'video', 'send-location-open': tab === 'location'}"
            @click.self="showTab()"
            :style="{'padding-bottom': textarea.height ? 30 + 'px' : 0}">
            <div class="inner">
                <div id="attach" class="attach" @click="attachMenuVisible = !attachMenuVisible || showTab()"></div>
                <textarea
                    v-if="isMobile"
                    autofocus
                    id="text__field"
                    :rows="textarea.rows"
                    :placeholder="trans('type_something')"
                    v-model="textarea.message"
                    ref="chatMessageTextarea"
                    @focus="focusTextArea"
                    @blur="unfocusTextArea"
                    @input="resizeTextArea"
                    @keydown.delete="deleteTextMessage"
                    @keyup.enter.exact="resizeTextAreaDebounce"
                    @keydown.enter.shift.exact.prevent
                    @keydown.enter.shift.exact="sendMessage"
                    tabindex="1"
                ></textarea>
                <textarea
                    v-if="!isMobile"
                    autofocus
                    id="text__field"
                    :rows="textarea.rows"
                    :placeholder="trans('type_something')"
                    v-model="textarea.message"
                    ref="chatMessageTextarea"
                    @focus="focusTextArea"
                    @blur="unfocusTextArea"
                    @input="resizeTextArea"
                    @keyup.enter.exact.prevent
                    @keydown.enter.exact.prevent
                    @keydown.enter.exact="sendMessage" 
                    @keydown.enter.shift.exact="resizeTextAreaDebounce"
                    @keydown.delete="deleteTextMessage"
                    tabindex="1"
                ></textarea>
                <div class="send" @click="sendMessage" tabindex="2">
                    <i class="send"></i>
                </div>
            </div>
            <div class="attach-menu" v-show="attachMenuVisible">
                <div class="attach-menu-inner">
                    <div id="send-photo" class="media-link photo" @click="showTab('photo')">
                        <i class="photo"></i>
                    </div>
                    <div id="send-video" class="media-link video" @click="showTab('video')">
                        <i class="video"></i>
                    </div>
                    <div id="send-location" class="media-link location" @click="showTab('location')">
                        <i class="location"></i>
                    </div>
                </div>
            </div>

            <div class="send-media-box" v-show="tab !== null" :class="{'photo': tab === 'photo', 'video': tab === 'video'}">
                <div class="send-media-wrapper" v-show="tab === 'photo'">
                    <div class="close" @click="showTab()"></div>
                    <vue-custom-scrollbar v-if="isDesktop" class="send-media-inner">
                        <div class="pics-catalog">
                            <div id="addPhotoButton" class="pic upload-photo" @click="choosePhoto">
                                <input type="file" id="newPhotoUpload" class="show-for-sr" name="photo" accept="image/*" ref="photo" v-on:change="uploadProfilePhoto($event, true)">
                            </div>
                            <div class="pic" v-for="(photo, index) in photos" @click="selectMedia(photo.id, index, 'photo')"
                                :class="{'selected': photo.active}">
                                <img class="img" :src="photo.photo_small" :src-big="photo.photo_orig" :id="`profile-photo-${photo.id}`" alt="" />
                            </div>
                        </div>
                        <div class="float-button">
                            <button class="btn" @click="sendPhotos">{{ trans('send_photo') }}</button>
                        </div>
                    </vue-custom-scrollbar>
                    <div v-else class="send-media-inner">
                        <div class="pics-catalog">
                            <div id="addPhotoButton" class="pic upload-photo" @click="choosePhoto">
                            <input type="file" id="newPhotoUpload" class="show-for-sr" name="photo" accept="image/*" ref="photo" v-on:change="uploadProfilePhoto($event, true)">
                        </div>
                        <div class="pic" v-for="(photo, index) in photos" @click="selectMedia(photo.id, index, 'photo')"
                             :class="{'selected': photo.active}">
                            <img class="img" :src="photo.photo_small" :src-big="photo.photo_orig" :id="`profile-photo-${photo.id}`" alt="" />
                        </div>
                        </div>
                        <div class="float-button">
                            <button class="btn" @click="sendPhotos">{{ trans('send_photo') }}</button>
                        </div>
                    </div>
                </div>

                <div class="send-media-wrapper" v-show="tab === 'video'">
                    <div class="close" @click="showTab()"></div>
                    <vue-custom-scrollbar v-if="isDesktop" class="send-media-inner">
                        <div class="pics-catalog">
                            <div id="addVideoButton" class="pic upload-photo" @click="chooseVideo">
                                <input type="file" id="newVideoUpload" class="show-for-sr" name="video" accept="video/*" ref="video" v-on:change="uploadVideo($event)">
                            </div>
                            <div class="pic" v-for="(video, index) in videos" @click="selectMedia(video.id, index, 'video')"
                                v-if="!!video.id"
                                :class="{'selected': video.active}">
                                <span class="preloader"
                                    :style="{'background': !video.id ? 'url(/assets/img/preloader.svg) center no-repeat' : 'none'}">
                                </span>
                                <div class="img" v-if="!!video.id" :id="`profile-video-${video.id}`"
                                    :style="{'background': `url(${video.thumb_small}) center / cover`}">
                                </div>
                            </div>
                        </div>
                        <div class="float-button">
                            <button class="btn" @click="sendVideos">{{ trans('send') }}</button>
                        </div>
                    </vue-custom-scrollbar>
                    <div v-else class="send-media-inner">
                      <div class="pics-catalog">
                        <div id="addVideoButton" class="pic upload-photo" @click="chooseVideo">
                          <input type="file" id="newVideoUpload" class="show-for-sr" name="video" accept="video/*" ref="video" v-on:change="uploadVideo($event)">
                        </div>
                        <div class="pic" v-for="(video, index) in videos" @click="selectMedia(video.id, index, 'video')"
                             v-if="!!video.id"
                             :class="{'selected': video.active}">
                                  <span class="preloader"
                                        :style="{'background': !video.id ? 'url(/assets/img/preloader.svg) center no-repeat' : 'none'}">
                                  </span>
                          <div class="img" v-if="!!video.id" :id="`profile-video-${video.id}`"
                               :style="{'background': `url(${video.thumb_small}) center / cover`}">
                          </div>
                        </div>
                      </div>
                      <div class="float-button">
                        <button class="btn" @click="sendVideos">{{ trans('send') }}</button>
                      </div>
                    </div>
                </div>
            </div>

            <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this,'swipeRight')">
                <div class="send-location-box" id="location__tab" v-show="tab === 'location'">
                    <div class="send-location-inner">
                        <div id="map" class="pick-location no-swiping" v-if="!userLoading">
                            <Map
                                :lat="lat"
                                :lng="lng"
                                :zoom="mapZoom"
                                styles="height: 250px"
                                :clickable="true"
                                :draggable="true"
                                @dragEnd="handleMapMarkerDrag"
                                @zoomChanged="handleZoomChanged"
                                ref="geoMap"
                            />
                        </div>
                        <div class="float-button">
                            <button class="btn" @click="sendLocation">{{ trans('send_location') }}</button>
                        </div>
                    </div>
                </div>
            </vue2-gesture>
        </div>

        <alert v-if="deleteAlertVisible" v-on:hide-alert="hideDeleteAlert">
            <p>{{ trans('delete_this_message') }}</p>
            <div class="dialog-actions">
                <button type="button" class="btn darker" @click="hideDeleteAlert">{{ trans('cancel') }}</button>
                <button type="button" class="btn" @click="confirmDelete">{{ trans('delete') }}</button>
            </div>
        </alert>

        <alert v-if="locationPreviewVisible" v-on:hide-alert="hideLocationPreview">
            <Map
                :lat="JSON.parse(locationPreview.message).lat"
                :lng="JSON.parse(locationPreview.message).lng"
                :zoom="JSON.parse(locationPreview.message).zoom"
                :styles="mapStyles"
                :clickable="true"
                :draggable="false"
            />
        </alert>
    </div>
</template>

<style lang="css" scoped>
    @import "../../../_general/lib/slim/slim.min.css";

    .form-error {
        margin-top: 0;
    }
</style>

<script>
    import { mapState, mapGetters } from 'vuex';

    import InfiniteLoading from 'vue-infinite-loading';
    import { scrollToNewMessages } from '@chat/lib/helpers';
    import chatModule from '@chat/module/store/type'
    import auth from '@general/lib/auth';
    import Alert from '@buddy/views/widgets/Alert.vue';
    import Map from '@buddy/views/widgets/Map.vue';
    import $ from "jquery";

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"
    import _ from "lodash";

    export default {
        mixins: [
            require('@general/lib/mixin').default
        ],
        props: ['chatMode', 'userId', 'eventId'],
        components: {
            Alert,
            InfiniteLoading,
            Map,
            vueCustomScrollbar
        },
        data(){
            return {
                refreshQueued: false,
                chatAllowed: false,
                tab: null,

                selectedPhotos: [],
                selectedVideos: [],

                deleteAlertVisible: false,
                deleteMessageId: 0,

                croppers: [],
                editPhotoId: null,
                slimOptions: this.defaultSlimOptions(),

                textarea: {
                    rows: 1,
                    height: 45,
                    scrollHeight: null,
                    baseScrollHeight: 0,
                    message: ''
                },

                attachMenuVisible: false,

                mask: {
                    initialWidth: 400,
                    initialHeight: 400,
                    widthModifier: 1,
                    heightModifier: 1,
                    width: 0,
                    height: 0,
                },

                locationPreview: null,
                locationPreviewVisible: false,
                lat: 52.520389,
                lng: 13.40424,
                mapZoom: 15,
                showScrollBottom: false
            }
        },
        methods: {
            async openUser(userId){
                let user = await this.$store.dispatch('loadUserInfo', userId)
                if (!user)
                    return

                if (user.deleted_at) {
                    this.showErrorNotification('profile_is_deleted')
                    return
                }

                if (this.isMobile) {
                    this.goTo('/user/' + userId)
                } else {
                    this.openUserModal(userId)
                }
            },

            // ###############################################
            // General methods
            // ###############################################
            selectMedia(id, index, type) {

              let data, arr;

              if (type === 'photo') {
                data = this.photos[index];
                arr = this.photos;
              } else {
                data = this.videos[index];
                arr = this.videos;
              }

              data.active = !data.active;

              if (!data.active) {
                this.removeByValue(this.selectedPhotos, data.id)
              } else {
                type === 'photo' ? this.selectedPhotos.push(data.id) : this.selectedVideos.push(data.id);
              }

              this.$set(arr, index, data);
            },
            removeByValue(arr) {
              let what, a = arguments, length = a.length, ax;
              while (length > 1 && arr.length) {
                what = a[--length];
                while ((ax = arr.indexOf(what)) !== -1) {
                  arr.splice(ax, 1);
                }
              }
              return arr;
            },
            showAttachMenu() {
                this.attachMenuVisible = true;
            },
            hideAttachMenu() {
                this.tab = false;
                this.attachMenuVisible = false;
                this.changeScrollBarState(true)
            },
            init(){
                let v = this

                $('.media-link').on('click', function (e) {
                    e.preventDefault();
                    $('.w-messagebar').addClass('active');
                    $('.w-page__content').on('click', function (e) {
                        $('.w-messagebar').removeClass('active');
                    });
                    $('#text__field').on('click', function (e) {
                        $('.w-messagebar').removeClass('active');
                    });
                });
                $('#keyboard__link').on('click', function (e) {
                    $('#text__field').click().focus();
                });

                let storageId = 'remember-chat-group-' + this.eventId
                if (storageId) {
                    let storedMessage = localStorage.getItem(storageId)
                    if (storedMessage)
                        this.textarea.message = storedMessage
                }

                this.initTextAreaValues();
            },
            async getMessages(infiniteScroll) {
                console.log('getMessages', {
                    userId: this.userId,
                    eventId: this.eventId,
                    chatMode: this.chatMode
                })

                let messages = await this.$store.dispatch(chatModule.actions.messages.loadGroup, {
                    eventId: this.eventId
                })

                if (messages.length) {
                    infiniteScroll.loaded()
                }
                if (messages.length < window.LOAD_CHAT_MESSAGES_LIMIT) {
                    infiniteScroll.complete()
                }

                this.initTextAreaValues()

                this.scrollToBottom()
            },
            scrollToBottom() {
                if (this.isMobile) {
                    const scrollElement = this.$el.querySelector('#js-chat-group-cmp').lastElementChild;

                    this.$nextTick(() => {
                        scrollElement.scrollIntoView({block: "center"})
                    });
                } else {
                    const scrollDiv = document.getElementById('conversation-group-scroll');

                    const lastMessageElem = this.$el.querySelector('#js-chat-group-cmp').lastElementChild;
                    const coordinates = scrollDiv.scrollHeight - lastMessageElem.offsetHeight;

                    this.$nextTick(() => {
                        scrollDiv.scroll({
                            top: coordinates,
                        })
                    })
                }
            },
            isMessageLast(index) {
                const msg = this.messages[index];
                const nextMsg = this.messages[index+1];

                return !nextMsg || msg.user_from != nextMsg.user_from || msg.idate.substr(0, 16) != nextMsg.idate.substr(0, 16);
            },
            getNewMessagePayload(messageFields){
                let event = this.event
                let payload = {
                    eventId: this.eventId,
                    conversation: {
                        chatType: 'group',
                        message: {
                            hash: null,
                            id: null,
                            idate: moment().utc(),
                            image_id: null,
                            video_id: null,
                            cancelled: false,
                            message: null,
                            msg_type: null,
                            user_from: auth.getUserId(),
                            channel: 'group',
                            ...messageFields
                        },
                        unreadMessagesCount: 0
                    },
                    isNewUnreadMessage: false,
                    insertIfMissing: true
                }

                payload.conversation.event = {
                    id:          event.id,
                    date:        event.event_date,
                    photo_small: event.photo_small,
                    title:       event.title
                }
                payload.conversation.message.event_id = event.id

                return payload
            },

            async sendMessage(e) {
                if (e) {
                    e.preventDefault();
                }
                
                if (this.userIsInactive) {
                    return;
                }

                let message = this.textarea.message.replace(/^\s+|\s+$/g, ''); //trim whitespace from the beginning and ending of string

                if (message) {
                    this.textarea.message = ''

                    let conversationUpdatePayload = this.getNewMessagePayload({
                        msg_type: 'text',
                        message:  message
                    })

                    this.$store.dispatch(chatModule.actions.conversations.update, conversationUpdatePayload)

                    // We need temporary hash to match and update this message later
                    let messageHash = Math.random().toString(36)

                    this.$store.commit(chatModule.mutations.message.push, {
                        eventId: this.eventId,
                        message: {
                            ...conversationUpdatePayload.conversation.message,
                            hash: messageHash
                        }
                    })

                    this.$nextTick(function(){
                        this.scrollToBottom();
                    })
                    try {
                        let response = await axios.post('/api/sendMessage', {
                            eventId: this.eventId,
                            message: message,
                            msgType: 'text',
                            channel: 'group',
                            hash:    messageHash
                        })

                        if (response.status === 200) {
                            let index,
                                messages = this.messages,
                                data = response.data

                            index = _.findIndex(messages, (e) => {
                                return e.id === data.conversation.message.id;
                            })

                            if (index === -1) {
                                this.$store.dispatch(chatModule.actions.conversations.update, {
                                    eventId: this.eventId,
                                    conversation: data.conversation,
                                    isNewUnreadMessage: false,
                                    insertIfMissing: true
                                })

                                let message = {...data.conversation.message, hash: messageHash }
                                this.$store.commit(chatModule.mutations.message.update, {
                                    eventId: this.eventId,
                                    message
                                })

                                this.$nextTick(function(){
                                    this.scrollToBottom();
                                })
                            }
                        }
                    } catch (e) {
                        console.error('error', e)
                        this.$store.commit(chatModule.mutations.message.update, {
                            eventId: this.eventId,
                            message: {
                                ...conversationUpdatePayload.conversation.message,
                                hash: messageHash,
                                id: messageHash,
                                failed: true,
                            }
                        })

                        this.$nextTick(function(){
                            this.scrollToBottom();
                        })
                    }
                    
                }

                let storageId = 'remember-chat-group-' + this.eventId
                if (storageId) {
                    localStorage.setItem(storageId, '')
                }

                this.$nextTick(function(){
                    this.afterSend()
                })
            },
            
            editMessageForNotDelivered(failedMessage) {
                this.$store.commit(chatModule.mutations.messages.set, {
                    userId: this.userId,
                    eventId: this.eventId,
                    messages: this.messages.filter(message => message.id !== failedMessage.id)
                })

                switch (failedMessage.msg_type) {
                    case 'text':
                        this.textarea.message = failedMessage.message
                        this.$nextTick(function(){
                            this.focusMessageField()
                            if (app.isDesktop) {
                                this.changeScrollStyle(false, true)
                                this.changeScrollStyle(true, false)
                            }
                        })
                        break;
                    case 'image':
                        this.showAttachMenu()
                        this.showTab('photo')
                        let photo = this.photos.find((photo) => photo.id == failedMessage.image_id)
                        photo.active = true
                        this.selectedPhotos = [photo.id]
                        break;
                    case 'video':
                        this.showAttachMenu()
                        this.showTab('video')
                        let video = this.videos.find((video) => video.id == failedMessage.video_id)
                        video.active = true
                        this.selectedVideos = [video.id]
                        break;
                    case 'location':
                        this.showAttachMenu()
                        this.showTab('location')
                        break;
                    default:
                        break;
                }
                
            },

            // ###############################################
            // Ancillary methods
            // ###############################################
            handleGesture(str, e) {
                if (str === 'swipeRight') {
                    // Suppress swipe right, do nothing
                    this.$store.dispatch(chatModule.actions.toggleSwipe, false)
                }
            },
            changeScrollBarState(value) {
                this.$emit('changeScrollBarState', value);
            },
            showTab(tab){
                this.tab = tab
                if (tab === 'location') {
                    this.locationTabClick()
                }

                if (typeof tab === 'undefined') {
                  this.changeScrollBarState(true)
                } else {
                  this.changeScrollBarState(false)
                }
            },
            resizeClippingMask(){
                // 100% height and 100% width of parent
                this.mask.widthModifier = $('.w-messages').width() * 0.7 / this.mask.initialWidth
                this.mask.heightModifier = 200 / this.mask.initialHeight
                this.mask.width = this.mask.initialWidth * this.mask.widthModifier
                this.mask.height = this.mask.initialHeight * this.mask.heightModifier
            },
            goToPrevChatPosition(position, iteration = 1) {
                if (iteration++ > 10) {
                    return;
                }

                const hash = document.querySelector('#js-chat-group-cmp');
                if (null === hash) {
                    return
                }

                hash.scrollTop = position;
                setTimeout(() => {
                    this.goToPrevChatPosition(position, iteration);
                }, 50);
            },
            getImagePath(message) {
                let imagePath = message.photo_orig
                let path = null
                if (
                    imagePath.substr(0, 11) === '/assets/img'
                    ||
                    imagePath.substr(0, 4) === 'http'
                ){
                    path = imagePath
                } else {
                    path = `/uploads/users/${imagePath}_orig.jpg`
                }
                return path
            },
            isNewDay(index) {
                const msg = this.messages[index];
                const prevMsg = this.messages[index-1];

                return !prevMsg || msg.idate.substr(0, 10) !== prevMsg.idate.substr(0, 10);
            },

            // ###############################################
            // Photo methods
            // ###############################################
            viewPhoto(msgId) { //event
                if (this.isMobile) {
                    this.goTo(`/chat-group-photo/${this.eventId}/${msgId}`);
                } else {
                    app.$emit('show-view-group-chat-photo-reveal', this.eventId, msgId)
                }
            },
            choosePhoto() {
                this.$refs.photo.click();
            },
            async sendPhotos() {
                this.tab         = null;

                const photosList = _.pickBy(this.selectedPhotos, value => value); //select photos with value === true
                const photosIds  = _.values(photosList);

                if (photosIds.length) {
                    $('.w-messages').click();

                    let messageHashes             = {},
                        conversationUpdatePayload = this.getNewMessagePayload({msg_type: 'image'})

                    this.$store.dispatch(chatModule.actions.conversations.update, conversationUpdatePayload)

                    photosIds.forEach(function(photoId){
                        // We need temporary hash to match and update this message later
                        let hash  = Math.random().toString(36),
                            photo = this.photos.find((photo) => photo.id == photoId)

                        this.$store.commit(chatModule.mutations.message.push, {
                            eventId: this.eventId,
                            message: _.cloneDeep({
                                ...conversationUpdatePayload.conversation.message,
                                image_id:    parseInt(photoId),
                                photo_small: photo && photo.photo_small,
                                photo_orig:  photo && photo.photo_orig,
                                hash:        hash
                            })
                        })

                        messageHashes[photoId] = hash
                    }, this)

                    this.$nextTick(function(){
                        this.scrollToBottom()
                    })

                    try {
                        let response = await axios.post('/api/sendMessages', {
                            eventId: this.eventId,
                            msgType: 'photo',
                            channel: 'group',
                            hashes:  messageHashes,
                            photosIds
                        })

                        if (response.status === 200) {
                            let index,
                                messages = this.messages,
                                data     = response.data

                            index = _.findIndex(messages, (e) => {
                                return e.id === data.conversation.message.id;
                            })

                            if (index === -1) {
                                this.$store.dispatch(chatModule.actions.conversations.update, {
                                    eventId:            this.eventId,
                                    conversation:       data.conversation,
                                    isNewUnreadMessage: false,
                                    insertIfMissing:    true
                                });

                                if (data.messages.length) {
                                    data.messages.forEach((responseMessage) => {
                                        let message = {
                                            ...responseMessage,
                                            hash: messageHashes[responseMessage.image_id]
                                        }

                                        this.$store.commit(chatModule.mutations.message.update, {
                                            eventId: this.eventId,
                                            message
                                        })

                                        this.$nextTick(function(){
                                            this.scrollToBottom()
                                        })
                                    }, this)
                                }

                                //remove cached chat images from memory
                                this.$store.commit(chatModule.mutations.messages.deleteImages, {
                                    eventId: this.eventId
                                })
                            }
                        }
                    } catch (e) {
                        console.error('error', e)
                        photosIds.forEach(function(photoId) {
                            let photo = this.photos.find((photo) => photo.id == photoId)

                            this.$store.commit(chatModule.mutations.message.update, {
                                eventId: this.eventId,
                                message: _.cloneDeep({
                                    ...conversationUpdatePayload.conversation.message,
                                    image_id:    parseInt(photoId),
                                    photo_small: photo && photo.photo_small,
                                    photo_orig:  photo && photo.photo_orig,
                                    hash:        messageHashes[photoId],
                                    id:          messageHashes[photoId],
                                    failed:      true,
                                })
                            })
                        }, this)

                        this.$nextTick(function(){
                            this.scrollToBottom()
                        })
                    }
                    
                }

                this.selectedPhotos = []

                this.photos.map(photo => {
                    photo.active = false;
                });

                this.$nextTick(function(){
                    this.afterSend()
                })
            },

            // ###############################################
            // Video methods
            // ###############################################
            chooseVideo() {
                this.$refs.video.click();
            },
            viewVideo(msgId) {
                if (this.isMobile) {
                    this.goTo(`/chat-group-video/${this.eventId}/${msgId}`);
                } else {
                    app.$emit('show-view-group-chat-video-reveal', this.eventId, msgId)
                }
            },
            async sendVideos() {
                this.tab = null;
                const videosList = _.pickBy(this.selectedVideos, value => value); //select videos with value === true
                const videosIds = _.values(videosList);

                if (videosIds.length) {
                    $('.w-messages').click();

                    let messageHashes             = {},
                        conversationUpdatePayload = this.getNewMessagePayload({msg_type: 'video'})

                    this.$store.dispatch(chatModule.actions.conversations.update, conversationUpdatePayload)

                    videosIds.forEach(function(videoId){
                        // We need temporary hash to match and update this message later
                        let hash  = Math.random().toString(36),
                            video = this.videos.find((video) => video.id == videoId)

                        this.$store.commit(chatModule.mutations.message.push, {
                            eventId: this.eventId,
                            message: _.cloneDeep({
                                ...conversationUpdatePayload.conversation.message,
                                video_id:      parseInt(videoId),
                                message:       video && video.video_name,
                                hash:          hash,
                                thumbnail_img: (video && video.thumbnail_type === 'img' && video.thumb_small) || null,
                                thumbnail_gif: (video && video.thumbnail_type === 'gif' && video.thumb_small) || null
                            })
                        })

                        messageHashes[videoId] = hash
                    }, this)

                    this.$nextTick(function(){
                        this.scrollToBottom()
                    })
                    try {
                        let response = await axios.post('/api/sendMessages', {
                            eventId: this.eventId,
                            msgType: 'video',
                            channel: 'group',
                            hashes:  messageHashes,
                            videosIds
                        })

                        if (response.status === 200) {
                            let index,
                                messages = this.messages,
                                data     = response.data

                            index = _.findIndex(messages, (e) => {
                                return e.id === data.conversation.message.id;
                            })

                            if (index === -1) {
                                this.$store.dispatch(chatModule.actions.conversations.update, {
                                    eventId:            this.eventId,
                                    conversation:       data.conversation,
                                    isNewUnreadMessage: false,
                                    insertIfMissing:    true
                                })

                                if (data.messages.length) {
                                    data.messages.forEach((responseMessage) => {
                                        let message = {
                                            ...responseMessage,
                                            hash: messageHashes[responseMessage.video_id]
                                        }

                                        this.$store.commit(chatModule.mutations.message.update, {
                                            eventId: this.eventId,
                                            message
                                        })

                                        this.$nextTick(function(){
                                            this.scrollToBottom()
                                        })
                                    }, this)
                                }

                                //remove cached chat videos from memory
                                this.$store.commit(chatModule.mutations.messages.deleteVideos, {
                                    eventId: this.eventId
                                })
                            }
                        }
                    } catch (e) {
                        console.error('error', e)
                        videosIds.forEach(function(videoId){
                            let video = this.videos.find((video) => video.id == videoId)

                            this.$store.commit(chatModule.mutations.message.update, {
                                eventId: this.eventId,
                                message: _.cloneDeep({
                                    ...conversationUpdatePayload.conversation.message,
                                    video_id:      parseInt(videoId),
                                    message:       video && video.video_name,
                                    thumbnail_img: (video && video.thumbnail_type === 'img' && video.thumb_small) || null,
                                    thumbnail_gif: (video && video.thumbnail_type === 'gif' && video.thumb_small) || null,
                                    hash:          messageHashes[videoId],
                                    id:            messageHashes[videoId],
                                    failed:        true,
                                })
                            })
                        }, this)

                        this.$nextTick(function(){
                            this.scrollToBottom()
                        })
                    }
                    
                }

                this.selectedVideos = []

                const videos = this.videos;

                videos.forEach(function (elem, index) {
                    elem.active ? elem.active = !elem.active : elem.active;

                    this.$set(videos, index, elem);
                }, this);

                this.$nextTick(function(){
                    this.afterSend();
                })
            },

            // ###############################################
            // Location methods
            // ###############################################
            handleMapMarkerDrag(event) {
                let newLat, newLng;

                if (this.mapProviderIsGmap) {
                    newLat = event.latLng.lat();
                    newLng = event.latLng.lng();
                } else {
                    newLat = event.target.getLatLng().lat;
                    newLng = event.target.getLatLng().lng;
                }

                this.lat = newLat;
                this.lng = newLng;
            },
            handleZoomChanged(event) {
                if (this.mapProviderIsGmap) {
                    this.mapZoom = event;
                } else {
                    this.mapZoom = event.target.getZoom();
                }
            },
            viewLocation(msg) {
                this.locationPreview = msg
                this.locationPreviewVisible = true
            },
            hideLocationPreview() {
                this.locationPreviewVisible = false
                this.locationPreview = null
            },
            locationTabClick() {
                let self = this

                Vue.nextTick(function () {
                    if (self.mapProviderIsGmap) {
                        Vue.$gmapDefaultResizeBus.$emit('resize');
                    } else {
                        self.$refs.geoMap.$refs.osmMap.mapObject.invalidateSize();
                    }
                })

                this.getCurrentPosition()
                    .then(function(pos) {
                        self.lat = pos.lat
                        self.lng = pos.lng
                    });
            },
            async sendLocation() {
                this.tab = null;
                let location = {
                    zoom: this.mapZoom,
                    lat: this.lat,
                    lng: this.lng,
                };

                let conversationUpdatePayload = this.getNewMessagePayload({
                    msg_type: 'location',
                    message:  JSON.stringify(location)
                })

                this.$store.dispatch(chatModule.actions.conversations.update, conversationUpdatePayload)

                // We need temporary hash to match and update this message later
                let messageHash = Math.random().toString(36)
                this.$store.commit(chatModule.mutations.message.push, {
                    eventId: this.eventId,
                    message: {
                        ...conversationUpdatePayload.conversation.message,
                        hash: messageHash
                    }
                })

                this.$nextTick(function(){
                    this.scrollToBottom()
                })

                try {
                    let response = await axios.post('/api/sendMessage', {
                        eventId: this.eventId,
                        msgType: 'location',
                        channel: 'group',
                        message: JSON.stringify(location),
                        hash:    messageHash
                    })

                    if (response.status === 200) {
                        let data = response.data

                        this.$store.dispatch(chatModule.actions.conversations.update, {
                            eventId:            this.eventId,
                            conversation:       data.conversation,
                            isNewUnreadMessage: false,
                            insertIfMissing:    true
                        });

                        let message = {...data.conversation.message, hash: messageHash}
                        this.$store.commit(chatModule.mutations.message.update, {
                            eventId: this.eventId,
                            message
                        });

                        this.$nextTick(function(){
                            this.scrollToBottom()
                        })
                    }
                } catch (e) {
                    console.error('error', e)
                    this.$store.commit(chatModule.mutations.message.update, {
                        eventId: this.eventId,
                        message: {
                            ...conversationUpdatePayload.conversation.message,
                            hash: messageHash,
                            id: messageHash,
                            failed: true,
                        }
                    })

                    this.$nextTick(function(){
                        this.scrollToBottom()
                    })
                }
                

                $('.messages-box').click();

                this.$nextTick(function(){
                    this.afterSend();
                })
            },

            // ###############################################
            // Delete methods
            // ###############################################
            tryDelete(messageId) {
                if (this.userIsPro) {
                    this.deleteMessageId = messageId
                    this.showDeleteAlert()
                } else {
                    this.$store.dispatch('requirementsAlertShow', 'deletemsg')
                }
            },
            showDeleteAlert() {
                this.deleteAlertVisible = true
            },
            hideDeleteAlert() {
                this.deleteAlertVisible = false
            },
            confirmDelete() {
                this.$store.dispatch(chatModule.actions.messages.delete, {
                    eventId: this.eventId,
                    messageId: this.deleteMessageId
                })
                this.hideDeleteAlert()
            },

            // ###############################################
            // Textarea methods
            // ###############################################
            initTextAreaValues(){
                let messageTmp = this.textarea.message
                this.textarea.message = ''
                this.textarea.baseScrollHeight = this.$refs.chatMessageTextarea.scrollHeight
                this.textarea.message = messageTmp
            },
            resetTextAreaSize(){
                this.textarea.rows = 1
                this.textarea.height = 45
            },
            focusTextArea(){
                this.attachMenuVisible = false
                this.resizeTextArea()
            },
            unfocusTextArea(){
                if (this.textarea.message == '') {
                    this.resetTextAreaSize()
                }
            },
          deleteTextMessage() {
            let storageId = this.chatMode === 'user' ?
                'remember-chat-' + this.userId
                :
                'remember-chat-event-' + this.eventId

            if (storageId) {

              var app = this
              setTimeout(function () {
                var message = app.textarea.message
                localStorage.setItem(storageId, message)
              }, 10)

            }
          },
            resizeTextAreaDebounce: _.debounce(function () {
                this.resizeTextArea();
            }, 100),
            resizeTextArea() {
                let rowsCount = Math.ceil((this.$refs.chatMessageTextarea.scrollHeight - 12 * 2) / 21)
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

                let storageId = this.chatMode === 'user' ?
                    'remember-chat-' + this.userId
                    :
                    'remember-chat-event-' + this.eventId

                if (storageId) {
                  var message = this.textarea.message
                  if(localStorage.getItem(storageId) && !message) {
                    message = localStorage.getItem(storageId)
                  }
                  localStorage.setItem(storageId, message)
                }
            },
            afterSend(){
                this.focusMessageField()

                if (app.isDesktop) {
                    this.changeScrollStyle(false, true);
                }

                this.resetTextAreaSize()

                if (app.isDesktop) {
                    this.changeScrollStyle(true, false);
                }

                this.changeScrollBarState(true)
            },
            changeScrollStyle(activeScrollSidebar, disableScroll) {
                this.$emit('changeScrollStyle', activeScrollSidebar, disableScroll);
            },
            focusMessageField(){
                this.$refs.chatMessageTextarea.focus();
            },
            reset(){
                this.$store.commit(chatModule.mutations.messages.set, {
                    eventId: this.eventId,
                    messages: []
                })

                if (this.$refs.infiniteLoadingChat)
                    this.$refs.infiniteLoadingChat.stateChanger.reset()
            },
            markConversationAsRead(){
                this.$store.dispatch(chatModule.actions.conversations.markAsRead, {
                    userId: this.userId,
                    eventId: this.eventId,
                    chatType: 'group',
                    sync: true
                })
            },

            // ###############################################
            // Cache methods
            // ###############################################
            softReload(source){
                if (this.refreshQueued) {
                    this.reset()
                    this.refreshQueued = false
                }
            },
            invalidate(){
                this.refreshQueued = true
                this.page = 0
                this.softReload()
            },
            checkScroll(event) {
                let eventTarget = event.target;
                let currentScroll = eventTarget.scrollTop;
                let scrollHeight = eventTarget.scrollHeight;
                let clientHeight = eventTarget.clientHeight;

                if (app.isDesktop) {
                  this.$emit('showScrollBottomButton', currentScroll + clientHeight <= scrollHeight - 700);
                } else {
                  this.showScrollBottom = currentScroll + clientHeight <= scrollHeight - 700;
                }
            },
            textAreaFocus() {
                document.getElementById('text__field').focus();
            }
        },
        watch: {
            entitiesLoaded: {
                immediate: true,
                handler: function(newValue, oldValue){
                    if (newValue && newValue !== oldValue) {
                        this.$nextTick(function(){
                            this.init()
                        })
                    }
                }
            },
            refreshQueued: {
                immediate: true,
                handler(value) {
                    console.log('[GroupChatComponent] Watcher refreshQueued', { value })
                    if (value && !this._inactive) {
                        this.reset()
                        this.refreshQueued = false
                    }
                }
            }
        },
        computed: {
            ...mapGetters([
                'userHasNewMessages'
            ]),
            mapStyles() {
              if (app.isDesktop) {
                return "width: 100%;";
              } else {
                return "width: 100%; min-width: 70vw";
              }
            },
            entitiesLoaded(){
                /*console.log('entitiesLoaded', {
                    chatMode: this.chatMode,
                    user: this.user,
                    event: this.event,
                })*/

                if (
                    !!this.eventId
                    &&
                    this.event !== null
                ) {
                    return true
                }

                return false
            },
            event(){
                if (!this.eventId || !this.chatMode)
                    return null
                return this.$store.getters.getEvent(this.eventId)
            },
            ...mapState({
                messages: function(state) {
                    return state.chatModule.chat.event.messages[this.eventId] || [];
                },
                currentUser: 'profile',
                photos: 'profilePhotos',
                videos: 'profileVideos',
            }),
            userLoading() {
                let loading = !this.currentUser || !this.currentUser.lng;
                //on user info loaded -> update marker
                if(!loading) {
                    this.lat = parseFloat(this.currentUser.lat);
                    this.lng = parseFloat(this.currentUser.lng);
                }

                return loading;
            },
        },
        mounted() {
            console.log('[GroupChatComponent] mounted', {
                userId: this.userId,
                eventId: this.eventId,
                chatMode: this.chatMode,
            })

            this.$store.commit(chatModule.mutations.messages.set, {
                userId: this.userId,
                eventId: this.eventId,
                messages: []
            })

            let container;

            if (app.isMobile) {
                container = document.getElementById('application-wrapper');
            } else {
                container = document.getElementById(`conversation-group-scroll`);
            }

            container.addEventListener('scroll', this.checkScroll, true);

            this.$refs.infiniteLoadingChat.stateChanger.reset();
            
            this.textAreaFocus();

            if (!this.entitiesLoaded) {
                return
            }

            this.markConversationAsRead()

            app.$on('invalidate-messages', this.invalidate)
        },
        activated() {
            console.log('[GroupChatComponent] Activated')

            let container;
            this.$nextTick(() => {
                if (app.isMobile) {
                    container = document.getElementById('application-wrapper');
                } else {
                    container = document.getElementById('conversation-group-scroll');
                }

                container.addEventListener('scroll', this.checkScroll, true);
            });

            this.$refs.infiniteLoadingChat.stateChanger.reset();

            this.textAreaFocus();
            this.markConversationAsRead();

            this.softReload('Chat')
            app.$on('invalidate-messages', this.invalidate)
        },
        deactivated() {
          this.selectedPhotos = [];
          this.photos.map(photo => {
            photo.active = false;
          });
        },
        created() {
            if (this.$refs.infiniteLoadingChat) {
                this.$refs.infiniteLoadingChat.stateChanger.reset();
            }
        },
        destroyed() {
            app.$off('invalidate-messages')
        }
    }
</script>
<style scoped>
@media only screen and (min-width: 1200px) {
  .scroll_bottom-button {
    right: calc(50% - 125px);
    bottom: 125px;
  }
}
</style>