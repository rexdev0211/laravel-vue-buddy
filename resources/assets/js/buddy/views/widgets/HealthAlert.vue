<template>
    <alert
        v-if="visible"
        :clear="true"
        @hide-alert="close"
    >
        <div class="health-banner">
            <div class="health-banner-header"></div>
            <div class="health-banner-body">
                <p>Please follow the rules of your local health departments.</p>
                <p>TAKE CARE OF YOUR BUDDIES!</p>
            </div>
        </div>
    </alert>
</template>

<script>
    import { mapState } from 'vuex';
    import Alert from '@buddy/views/widgets/Alert.vue';

    export default {
        methods: {
            close(){
                this.$store.commit('setHealthAlert', false)
            }
        },
        computed: {
            ...mapState({
                visible: state => state.healthAlert
            })
        },
        components: {
            Alert
        }
    }
</script>

<style scoped>

</style>