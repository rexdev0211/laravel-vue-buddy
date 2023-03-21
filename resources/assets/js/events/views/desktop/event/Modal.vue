<template>
    <transition :name="'fade'" mode="out-in">
        <div v-if="eventData.visible">

            <CustomReveal class="event-modal"
                revealId="event-profile-card"
                v-if="eventData.mode !== 'create'"
                :isVisible="eventData.visible"
                v-on:close-reveal-event-profile-card="closeEvent">
                <div id="event-profile-card" class="event-card"
                    data-reveal2>
                    <ViewEvent v-if="eventData.mode === 'view'"/>
                    <EditEvent v-if="eventData.mode === 'edit'"/>
                </div>

                <UserPhotosReveals v-if="event" :photos="event.photos"></UserPhotosReveals>
                <UserVideosReveals v-if="event" :videos="event.videos"></UserVideosReveals>
            </CustomReveal>

            <CreateEvent v-if="eventData.mode === 'create'"/>

        </div>
    </transition>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';
    import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';
    import UserPhotosReveals from '@buddy/views/widgets/UserPhotosReveals.vue';
    import UserVideosReveals from '@buddy/views/widgets/UserVideosReveals.vue';

    import ViewEvent from '@events/views/desktop/event/Item.vue';
    import EditEvent from '@events/views/desktop/event/Edit.vue';
    import CreateEvent from '@events/views/desktop/event/Create.vue';

    import eventsModule from '@events/module/store/type'

    export default {
        mixins: [
            require('@general/lib/mixin-events').default,
        ],
        components: {
            CustomReveal,
            UserPhotosReveals,
            UserVideosReveals,
            EditEvent,
            ViewEvent,
            CreateEvent
        },
        computed: {
            ...mapGetters({
                eventData: eventsModule.getters.event
            }),
            event(){
                return this.$store.getters.getEvent(this.eventData.eventId)
            },
        },
        methods: {
            ...mapActions({
                setEvent: eventsModule.actions.setEvent
            })
        },
    }
</script>
