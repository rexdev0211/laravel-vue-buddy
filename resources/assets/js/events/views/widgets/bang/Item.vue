<template>
    <span>
        <div class="main-box" :id="`event-${bang.id}`">
            <div class="photo bang" :style="{'background': `url(${bang.photo_orig}) no-repeat center / cover`}"></div>
            <div class="main-box-footer">
                <div class="inner">
                    <div class="details bang">
                        <div class="row first">
                            <div class="detail date">{{ bang.event_date | formatDate('day-date') }}</div>
                            <div class="detail location notranslate">{{ bang.address_type == 'city_only' || bang.type == 'fun' || bang.type == 'bang' ? bang.locality : bang.address }}</div>
                        </div>
                        <div class="row second">
                            <div class="detail time">{{ bang.time }}</div>
                            <div class="detail group">{{ membersCount }}</div>
                        </div>
                        <div class="candy-yes" v-if="bang.chemsfriendly"></div>
                    </div>
                </div>

                <div class="favorites" v-if="members && membersCount">
                    <div class="faces">
                        <div class="face"
                             v-for="user in members"
                             v-if="user.status === 'requested' || user.id == bang.user_id"
                             @click="openUser(user)"
                             :class="{'host': ((bang.membership === 'host' && user.id === bang.user_id) || user.status === 'host'), 'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline, 'pending': user.status === 'requested'}">
                            <div class="img" 
                                :class="{'is-deleted': !!user.deleted_at}"
                                :style="!user.deleted_at && {'background': `url(${user.photo_small}) no-repeat center / cover`}">
                            </div>
                            <div class="membership-request" v-if="user.status === 'requested'"
                                 @click.stop="updateUserMembership(user, 'accept')">
                            </div>
                            <div class="close"
                                 v-if="bang.membership === 'host' && user.id !== bang.user_id && user.status === 'member'"
                                 @click.stop="updateUserMembership(user, 'remove')">
                            </div>
                            <div class="details">
                                <div class="name notranslate">{{ user.name }}</div>
                            </div>
                        </div>
                        <div class="add-private-member face" @click="openPrivateInvite()" v-if="bang.is_private && bang.membership === 'host'">
                          <img src="/images/bang-event/add.svg">
                        </div>
                        <div class="face"
                             v-for="user in members"
                             v-if="user.status !== 'requested' && user.id != bang.user_id"
                             @click="openUser(user)"
                             :class="{'host': ((bang.membership === 'host' && user.id === bang.user_id) || user.status === 'host'), 'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline, 'pending': user.status === 'requested'}">
                              <div class="img" 
                                  :class="{'is-deleted': !!user.deleted_at}"
                                  :style="!user.deleted_at && {'background': `url(${user.photo_small}) no-repeat center / cover`}">
                              </div>
                              <div class="membership-request" v-if="user.status === 'requested'"
                                   @click.stop="updateUserMembership(user, 'accept')">
                              </div>
                              <div class="close"
                                   v-if="bang.membership === 'host' && user.id !== bang.user_id && user.status === 'member'"
                                   @click.stop="updateUserMembership(user, 'remove')">
                              </div>
                              <div class="details">
                                  <div class="name notranslate">{{ user.name }}</div>
                              </div>
                          </div>
                    </div>
                </div>

                <transition name="slide-in-fwd-center">
                    <div id="event-location-map" class="event-location-map"
                        v-if="locationPreviewVisible"
                        v-on:hide-alert="locationPreviewVisible = false">
                        <Map
                            :lat="bang.lat"
                            :lng="bang.lng"
                            :zoom="15"
                            styles="height: 260px; width: 100%;"
                            :clickable="true"
                            :draggable="false"
                        />
                    </div>
                </transition>

                <div class="func-buttons bang">
                    <div id="show-map" class="map"
                        v-if="['host', 'member'].includes(bang.membership)"
                        @click="openMap">
                    </div>
                    <div class="send-message"
                        v-if="['host', 'member'].includes(bang.membership)"
                        v-on:click="startGroupConversation(bang.id)"
                        :class="{'notificated': bang.unreadMessagesCount}">
                    </div>
                    <div class="like notranslate"
                         v-if="!bang.is_private"
                        :class="{'liked': bang.isLiked}"
                        v-on:click="toggleEventLike(bang)">
                        <span v-if="bang.likes">{{ bang.likes }}</span>
                    </div>
                    <div v-if="bang.is_private && bang.membership != undefined" class="is-private-block">
                      <svg width="20" height="27" viewBox="0 0 20 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.5 9H16.25V6.5C16.25 3.05 13.45 0.25 10 0.25C6.55 0.25 3.75 3.05 3.75 6.5V9H2.5C1.125 9 0 10.125 0 11.5V24C0 25.375 1.125 26.5 2.5 26.5H17.5C18.875 26.5 20 25.375 20 24V11.5C20 10.125 18.875 9 17.5 9ZM10 20.25C8.625 20.25 7.5 19.125 7.5 17.75C7.5 16.375 8.625 15.25 10 15.25C11.375 15.25 12.5 16.375 12.5 17.75C12.5 19.125 11.375 20.25 10 20.25ZM6.25 9V6.5C6.25 4.425 7.925 2.75 10 2.75C12.075 2.75 13.75 4.425 13.75 6.5V9H6.25Z" fill="#2F7570"/>
                      </svg>
                    </div>
                    <strong v-if="bang.membership == undefined && bang.is_private" class="invited-text">You have been invited!</strong>
                    <div :class="{buttons:true, notranslate:true, twoButtons:(bang.membership == undefined && bang.is_private)}">
                      <button type="button"
                                class="btn"
                                v-if="!isMyEvent(bang) && (bang.membership != undefined || !bang.is_private)"
                                @click="updateMembership({ eventId: eventIdComputed })"
                                :disabled="membershipActionDisabled"
                      >{{ membershipActionText }}</button>
                      <button type="button"
                              class="btn darker"
                              v-if="bang.membership == undefined && bang.is_private"
                              @click="declineInvite({ eventId: eventIdComputed })"
                              :disabled="membershipActionDisabled"
                      >{{ trans('events.decline') }}</button>
                      <button type="button"
                              class="btn"
                              v-if="bang.membership == undefined && bang.is_private"
                              @click="acceptInvite({ eventId: eventIdComputed })"
                              :disabled="membershipActionDisabled"
                      >{{ trans('events.accept') }}</button>
                        <button type="button"
                            v-if="isMyEvent(bang)"
                            @click="editEvent(bang.id, 'bang')"
                            class="edit-button btn">
                            {{ trans('edit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

      <vue-custom-scrollbar :class="{'opened': inviteBlockOpened, 'favourites-list': true}">
        <div class="invite-menu">
          <div class="title">Invite favourite buddies</div>
          <div class="close" @click="closeInviteBlock"></div>
          <div class="wrapper">
            <div class="inner">
              <div class="sharing-block">
                <div class="faces">
                    <div class="face"
                        v-for="(user, index) in favourites"
                        v-if="user.status !== 'requested' && user.id != bang.user_id"
                        @click="selectUser(user.id, index, user.active)"
                        :class="{'selected': user.active}">
                        <div class="img" 
                            :class="{'is-deleted': !!user.deleted_at}"
                            :style="!user.deleted_at && {'background': `url(${user.photo_small}) no-repeat center / cover`}">
                        </div>
                        <div class="details">
                            <div class="name notranslate">{{ user.name }}</div>
                        </div>
                    </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </vue-custom-scrollbar>

      <div class="inviteButtons" v-if="selectedMembers.length > 0 && inviteBlockOpened" @click="handleInvites()">
        <button class="inviteButton btn" type="button">
          {{ trans('events.invite') }}
        </button>
      </div>

    </span>
</template>

<script>
    import eventsModule from '@events/module/store/type';
    import Map from '@buddy/views/widgets/Map.vue';
    import Alert from '@buddy/views/widgets/Alert.vue';
    import EditEvent from '@events/views/desktop/event/Edit.vue';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"
    import {mapActions, mapState} from "vuex";
    import axios from "axios";

    export default {
        mixins: [
	        require('@general/lib/mixin').default,
          require('@general/lib/mixin-events').default,
	        require('@general/lib/mixin-bang').default,
	        require('@general/lib/mixin-chat').default,
		],
        props: ['eventId'],
        components: {
            Alert,
            Map,
            EditEvent,
            vueCustomScrollbar
        },
        data: () => ({
            locationPreviewVisible: false,
            inviteBlockOpened: false,
            selectedMembers:[],
            favourites:[],
        }),
        computed: {
            ...mapState({
                blockedUsersIds: state => state.blockedUsersIds,
            }),
            eventIdComputed(){
                let eventId = this.eventId || this.bangData.eventId
                console.log('[Bang Item] eventIdComputed', { eventId })
                return eventId
            },
            bang(){
              return this.$store.getters.getEvent(this.eventIdComputed)
            },
            members() {
                return this.bang.members.filter(member => this.bang.membership !== 'host' || !this.blockedUsersIds.includes(member.id))
            },
            membersCount() {
                return this.members.length
            },
        },
      created() {
        app.$on('open-event', this.openEvent)
      },
      methods: {
            ...mapActions({
              inviteBuddies: 'inviteBuddies',
            }),
            declineInvite(data) {
              this.declineEventInvitation(data);
              this.showSuccessNotification('Invitation successfully declined')
              this.closeBang();
              this.$store.dispatch('loadCurrentUserInfo');
              setTimeout(function(){
                app.$emit('reload-events')
              }, 750)
            },
            acceptInvite(data) {
              this.acceptEventInvitation(data);
              this.showSuccessNotification('Invitation successfully accepted')
              this.$store.dispatch('loadCurrentUserInfo');
              setTimeout(function(){
                app.$emit('reload-events')
                app.$emit('open-event', data.eventId, 'bang')
              }, 750)
            },
            handleInvites() {
              let data = {
                selectedMembers : this.selectedMembers,
                eventIdComputed: this.eventIdComputed,
              }
              this.inviteBuddies(data);
              this.closeInviteBlock();
              this.selectedMembers = [];
              this.favourites.map(function (user) {
                user.active = false;
              })
              this.showSuccessNotification('Users successfully invited');
            },
            selectUser(userId, index, userActive) {
              if (!userId) {
                return;
              }

              let currentMember = this.favourites[index]
              let allMembers = this.favourites

              currentMember.active = !currentMember.active

              if (currentMember.active) {
                this.selectedMembers.push(userId)
              } else {
                this.selectedMembers.splice(_.findIndex(this.selectedMembers, userId), 1)
              }

              this.$set(allMembers, index, currentMember)
            },
            openPrivateInvite() {
              if (!this.favourites.length) {
                this.showErrorNotification(this.trans('events.please_add_favourite_buddies_first'));
              } else {
                this.inviteBlockOpened = true;
              }
            },
            closeInviteBlock() {
              this.inviteBlockOpened = false;
            },
            async openUser(user){
                let validUser = await this.$store.dispatch('loadUserInfo', user.id)
                if (!validUser) return

                if (user.deleted_at) {
                    this.showErrorNotification('profile_is_deleted')
                } else {
                    let userToken = user.link || user.id
                    if (this.isMobile) {
                        this.goTo('/user/' + userToken)
                        // this.openUserMobileModal(userToken);
                    } else {
                        this.openUserModal(userToken)
                    }
                }
            },
            openMap() {
              this.locationPreviewVisible = !this.locationPreviewVisible

              if (this.locationPreviewVisible) {
                  this.$nextTick(() => {
                    if (this.isMobile) {
                      document.getElementById('event-location-map').scrollIntoView();
                    } else {
                      const scrollDiv = document.getElementById('event-details');
                      const mapBox = document.getElementById('event-location-map');
                      const coordinates = scrollDiv.scrollHeight - mapBox.offsetHeight;

                      scrollDiv.scroll({
                        top: coordinates,
                      });
                    }
                  });
              }
            },
        },
        beforeMount() {
          axios.get(`/api/favourites`)
              .then((response) => {
                this.favourites = response.data;
              })
        },
        mounted() {
            console.log('[Bang Item] Mounted', {
                eventId: this.eventIdComputed,
                bang: this.bang
            })
            if (!!parseInt(this.eventIdComputed)){
                this.$store.dispatch(eventsModule.actions.membership.remove, this.eventIdComputed)
                this.$store.commit('updateUser', { has_event_notifications: false })
            }
        },
    }
</script>
