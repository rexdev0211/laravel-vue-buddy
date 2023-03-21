<template>
    <div class="event"
        :id="`event-${event.id}`"
        @click="openEvent(event.id, 'event')"
        :class="{
          'candy-yes': event.type === 'fun' && event.chemsfriendly,
          'online': event.isOnline && event.type !== 'guide',
          'was-online': event.wasRecentlyOnline && !event.isOnline && event.type !== 'guide',
          'featured': event.featured === 'yes'
        }">
        <div class="img" :style="{'background': backgroundEventImage}">
              <div class="guide-details" v-if="event.type === 'guide' && event.featured === 'yes'">
                  <div class="title">
                    {{ event.title }}
                  </div>
                  <div class="venue">
                      {{ event.venue }}
                  </div>
              </div>
        </div>
        <div class="details">
            <div class="event-title" v-if="event.featured === 'no'">{{ event.title }}</div>
            <div class="event-venue" v-if="event.type === 'guide' && event.featured === 'no'">{{ event.venue }}</div>
            <div class="small-details">
                <div>
                    <div class="time" v-if="!event.sticky">{{ event.time }}</div>
                    <div class="location" v-if="event.distanceMeters <= 50000">
                        <span>{{ getDistanceString(event) }}</span>
                    </div>
                    <div class="location notranslate" v-else><span>{{ event.locality }}</span></div>
                </div>
                <div>
                    <div class="messages" v-if="unreadEventMessagesCount(event.id, null)">
                        {{ unreadEventMessagesCount(event.id, null) }}
                    </div>
                    <div class="like notranslate" @click.stop="toggleEventLike(event)"
                        :class="{'liked': event.isLiked}">
                        <span v-if="event.likes">{{ event.likes }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';

    import chatModule from '@chat/module/store/type'
    import eventsModule from '@events/module/store/type'
    import Vue from "vue";

    export default {
      mixins: [
        require('@general/lib/mixin').default,
        require('@general/lib/mixin-events').default,
      ],
      props: ['ev', 'sticky', 'eventId'],
      created() {
        Vue.set(this.$store.state.eventsInfo, this.eventId, this.ev)
      },
      computed: {
        ...mapGetters({
          unreadEventMessagesCount: chatModule.getters.messages.count.unreadEvent,
        }),
        backgroundEventImage() {
          let photoUrl = this.event.type === 'guide' && this.event.featured === 'yes' ? this.event.photo_orig : this.event.photo_small;

          return `url(${photoUrl}) no-repeat center / cover`
        },
        event() {
          return this.sticky ? this.sticky : this.$store.getters.getEvent(this.eventId);
        }
      },
      methods: {
        ...mapActions({
          toggleEventLike: eventsModule.actions.events.like
        }),
      },
    }
</script>
