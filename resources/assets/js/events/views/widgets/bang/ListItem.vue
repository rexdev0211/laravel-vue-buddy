<template>
    <div class="event"
        :id="`event-${bang.id}`"
        @click="openEvent(bang.id, 'bang')"
        :class="{'candy-yes': bang.type == 'bang' && bang.chemsfriendly, 'online': bang.membership === 'host' && bang.isOnline, 'was-online': bang.membership === 'host' && bang.wasRecentlyOnline == true}">
        <div class="img" :style="{'background': `url(${bang.photo_orig}) no-repeat center / cover`}"></div>
        <div class="details">
            <div class="participants">
                <div class="participant"
                    v-for="member in members.slice(0, 2)"
                    :class="{'is-deleted': !!member.deleted_at}"
                    :style="!member.deleted_at && {'background': prepareMember(member)}">
                </div>
                <div class="participant" v-if="members.length > 2"><span>+{{ membersCount }}</span></div>
            </div>
            <div class="small-details">
                <div class="location notranslate" style="width:auto;" v-if="bang.distanceMeters <= 50000">
                    <span>{{ getDistanceString(bang) }}</span>
                </div>
                <div class="location notranslate" style="width:auto;" v-else><span>{{ bang.locality }}</span></div>
                <div class="like notranslate"
                     v-if="!bang.is_private && !invited && !accepted"
                     @click.stop="toggleEventLike(bangInfo)"
                    :class="{'liked': bang.isLiked}">
                    <span v-if="bang.likes">{{ bang.likes }}</span>
                </div>
                <svg style="margin-left:20px;" v-if="bang.is_private || invited || accepted" width="20" height="27" viewBox="0 0 20 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M17.5 9H16.25V6.5C16.25 3.05 13.45 0.25 10 0.25C6.55 0.25 3.75 3.05 3.75 6.5V9H2.5C1.125 9 0 10.125 0 11.5V24C0 25.375 1.125 26.5 2.5 26.5H17.5C18.875 26.5 20 25.375 20 24V11.5C20 10.125 18.875 9 17.5 9ZM10 20.25C8.625 20.25 7.5 19.125 7.5 17.75C7.5 16.375 8.625 15.25 10 15.25C11.375 15.25 12.5 16.375 12.5 17.75C12.5 19.125 11.375 20.25 10 20.25ZM6.25 9V6.5C6.25 4.425 7.925 2.75 10 2.75C12.075 2.75 13.75 4.425 13.75 6.5V9H6.25Z" fill="#2F7570"/>
                </svg>
            </div>
        </div>
      <button
          v-if="!invited"
          type="button"
          class="btn notranslate"
          :class="membershipActionBtnClass"
          @click.stop="updateMembership({ eventId: bang.id })"
          :disabled="membershipActionDisabled">
        {{ membershipActionText }}
      </button>
      <button
          v-if="invited && !accepted"
          type="button"
          class="btn darker notranslate"
          style="right:130px;"
          @click.stop="declineInvite({ eventId: bang.id })">
        {{ trans('events.decline') }}
      </button>
      <button
          v-if="invited && !accepted"
          type="button"
          class="btn notranslate"
          :class="membershipActionBtnClass"
          @click.stop="acceptInvite({ eventId: bang.id })">
        {{ trans('events.accept') }}
      </button>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';

    import chatModule from '@chat/module/store/type';
    import eventsModule from '@events/module/store/type';

    export default {
        mixins: [
	        require('@general/lib/mixin').default,
	        require('@general/lib/mixin-events').default,
	        require('@general/lib/mixin-bang').default,
		    ],
        props: ['bang', 'invited', 'accepted'],
        created() {
          Vue.set(this.$store.state.eventsInfo, this.bang.id, this.bang)
        },
        computed: {
            members() {
                return this.bang.members.filter(member => !member.blocked);
            },
            membersCount() {
                return this.isMobile ? this.members.length - 2 : this.members.length - 3;
            },
            bangInfo() {
              return this.$store.state.eventsInfo[this.bang.id] ?? this.bang;
            }
        },
        methods: {
            declineInvite(data) {
              this.declineEventInvitation(data);
              this.showSuccessNotification('Invitation successfully declined')
              // this.openEvent(data.eventId, 'bang');
              this.$store.dispatch('loadCurrentUserInfo');
              setTimeout(function(){
                app.$emit('reload-events')
              }, 750)
            },
            acceptInvite(data) {
              this.acceptEventInvitation(data);
              this.showSuccessNotification('Invitation successfully accepted')
              // this.openEvent(data.eventId, 'bang');
              this.$store.dispatch('loadCurrentUserInfo');
              setTimeout(function(){
                app.$emit('reload-events')
              }, 750)
            },
            prepareMember(member) {
                const photo = member.photo_small;
                if (photo != null) {
                    return `url(${photo}) no-repeat center / cover`;
                } else {
                  return 'url("/assets/img/default_180x180.jpg") center / cover';
                }
            }
        }
    }
</script>
