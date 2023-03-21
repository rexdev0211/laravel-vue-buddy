<template>
    <div>
        <div>
            <div v-if="type === 'discover'">
                <!-- <div class="timeline" v-if="dataData.date === 'invited' || dataData.date === 'accepted invitations'">{{ trans('clubs.invited')  }}</div>
                <div class="timeline" v-else>{{ dataData.date | formatDate('day-date') }}</div> -->

                <!-- NearBy Clubs -->
                <div class="timeline">{{ trans('clubs.nearby')  }}</div>
                <div class="events" v-for="club in clubs.clubs_nearby">
                    <ClubListItem :club="club" :invited="false" :accepted="false" />
                </div>

                <!-- More Clubs -->
                <div class="timeline">{{ trans('clubs.more')  }}</div>
                <div class="events" v-for="club in clubs.clubs_more">
                    <ClubListItem :club="club" :invited="false" :accepted="false" />
                </div>

                <!-- High range -->
                <!-- <div class="clubs" v-for="event in dataData.clubs_range_high">
                    <BangListItem :bang="event" :invited="dataData.date === 'invited'" :accepted="dataData.date === 'accepted invitations'" />
                </div> -->

                <div class="events" v-if="clubs.clubs_remained > 0">
                    <a v-if="!clubs.loading" class="btn load-more"
                        @click="loadMoreClubs()">
                        {{ trans('show_more') }}
                    </a>
                    <!-- Preloader -->
                    <div v-else class="b-event__preloader">
                        <img src="/assets/img/preloader.svg" alt="">
                    </div>
                </div>
            </div>
            <div v-else>
                <!-- Invited Clubs -->
                <div class="timeline">{{ trans('clubs.invited')  }}</div>
                <div class="events" v-for="club in clubs.clubs_invited">
                    <ClubListItem :club="club" :invited="true" :accepted="false" />
                </div>

                <!-- Admin Clubs -->
                <div class="timeline">{{ trans('clubs.admin')  }}</div>
                <div class="events" v-for="club in clubs.clubs_admin">
                    <ClubListItem :club="club" :invited="false" :accepted="false" />
                </div>

                <!-- Member Clubs -->
                <div class="timeline">{{ trans('member')  }}</div>
                <div class="events" v-for="club in clubs.clubs_member">
                    <ClubListItem :club="club" :invited="false" :accepted="false" />
                </div>

            </div>
        </div>

        <infinite-loading
            ref="infiniteLoadingClubs"
            @infinite="loadClubs"
            :force-use-infinite-wrapper="getBlockIdForPagination"
            spinner="bubbles">
            <div class="event no-events" slot="no-results" v-if="showCreateYourClub">
                <div class="inner">
                    <div class="title">{{ trans('no_clubs_around') }}</div>
                    <a id="add-event-link" @click="createClub()">
                        Create your club
                    </a>
                </div>
            </div>
            <div slot="no-results" v-else></div>
            <div class="event no-events" slot="no-more" v-if="showCreateYourClub">
                <div class="inner">
                    <div class="title">{{ trans('no_more_club_around') }}</div>
                    <a id="add-event-link" @click="createClub()">
                        Create your club
                    </a>
                </div>
            </div>
            <div slot="no-more" v-else></div>
        </infinite-loading>

    </div>
</template>

<script>
    import {mapState, mapActions, mapGetters} from 'vuex';
    import ClubListItem from '@clubs/views/widgets/ListItem.vue';
    // import BangListItem from '@clubs/views/widgets/bang/ListItem.vue';
    import clubsModule from '@clubs/module/store/type'

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-clubs').default,
        ],
        data() {
            return {
                showCreateYourClub: true
            }
        },
        computed: {
            ...mapState({
                myClubs: 'myClubs',
                userIsPro: 'userIsPro'
            }),
            ...mapGetters({
                type: clubsModule.getters.type,
                clubs: clubsModule.getters.clubs
            }),
            getBlockIdForPagination() {
                return app.isDesktop ? '#js-clubs-content' : '#application-wrapper'
            }
        },
        components: {
            ClubListItem,
            // BangListItem
        },
        methods: {
            ...mapActions({
                loadMoreClubs: clubsModule.actions.clubs.loadMoreClubs,
            }),
            // fDate(value) {
            //   var isDate = function(value) {
            //     return (new Date(value) !== "Invalid Date") && !isNaN(new Date(value));
            //   }
            //
            //   if (!isDate) {
            //     return value;
            //   }
            //
            //   return value | formatDate('day-date');
            // },
        },
        watch: {
            clubs: function (newVal) {
                console.log('Clubs mounted', newVal);
                let eventCounter = 0;

                if (newVal.clubs_nearby || newVal.clubs_more) {
                    eventCounter = newVal.clubs_nearby.length + newVal.clubs_more.length;
                }
                
                console.log('eventCounter', eventCounter);
                this.showCreateYourClub = !(eventCounter >= 0);
                console.log('showCreateYourClub', this.showCreateYourClub)
            }
        },
        created() {
            console.log('[Clubs] Created')
            app.$on('reload-clubs', this.resetClubsAround)
        },
        destroyed() {
            console.log('[Clubs] Destroyed')
            app.$off('reload-clubs', this.resetClubsAround)
        },
        activated(){
            console.log('[Clubs] Activated')
            // this.$store.dispatch('showHealthAlert')
            this.$store.commit('updateUser', {
                has_club_notifications: false
            })
        }
    }
</script>
