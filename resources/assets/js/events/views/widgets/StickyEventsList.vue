<template>
    <div>
        <div v-for="event in list">

            <BangListItem v-if="type === 'bang'" :bang="event"/>
            <EventListItem v-else :sticky="event"/>

        </div>
    </div>
</template>

<script>
    import EventListItem from '@events/views/widgets/event/ListItem.vue';
    import BangListItem from '@events/views/widgets/bang/ListItem.vue';

    export default {
        props: ['list', 'type'],
        components: {
            EventListItem,
            BangListItem
        },
    }
</script>
