<template>
    <transition :name="'fade'" mode="out-in">
        <div v-if="bangData.visible">

            <CustomReveal class="event-modal"
                revealId="bang-profile-card"
                v-if="bangData.mode !== 'create'"
                :isVisible="bangData.visible"
                v-on:close-reveal-event-profile-card="closeBang">
                <div id="event-profile-card" class="event-card bang"
                    data-reveal2>
                    <ViewBang v-if="bangData.mode === 'view'"/>
                    <EditBang v-if="bangData.mode === 'edit'"/>
                </div>
            </CustomReveal>

        </div>
    </transition>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';

    import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';
    import ViewBang from '@events/views/desktop/bang/Item.vue';
    import EditBang from '@events/views/desktop/bang/Edit.vue';

    import eventsModule from '@events/module/store/type'

    export default {
        mixins: [
            require('@general/lib/mixin-events').default,
        ],
        components: {
            CustomReveal,
            ViewBang,
            EditBang
        },
        computed: {
            ...mapGetters({
                bangData: eventsModule.getters.bang
            }),
            bang() {
                return this.$store.getters.getEvent(this.eventId)
            }
        },
        methods: {
            ...mapActions({
                setBang: eventsModule.actions.setBang
            })
        }
    }
</script>
