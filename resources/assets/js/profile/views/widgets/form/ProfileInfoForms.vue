<template>
    <div class="small-12 columns profile-forms-container">



    </div><!--col-->
</template>

<script>
    import {mapState} from 'vuex';
    import DatePickerComponent from '@buddy/views/widgets/DatePickerComponent.vue';
    import Map from '@buddy/views/widgets/Map.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            DatePickerComponent,
            Map
        },
        computed: {
            ...mapState({
                user: 'profile',
            }),
            dataLoading() {
                return !this.user || !this.user.lng;
            }
        },
        methods: {
            isLocal() {
                return window.APP_ENV == 'local'
            },
            showPasswordForm() {
                app.$emit('show-password-form');
            },
            async loadProfileInfo() {
                store.dispatch('loadCurrentUserInfo', true)
            },
        }
    }
</script>
