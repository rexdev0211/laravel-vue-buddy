<template>
    <CustomReveal class="reveal-alert" revealId="delete-profile" :isVisible="deleteProfileFormVisible" v-on:close-reveal-delete-profile="closeDeleteProfileModal">
        <div class="box">
            <div class="headline">{{ trans('are_you_sure_delete_profile') }}</div>
        </div>
        <div class="options-box">
            <div class="options">
                <button id="delete_yes" class="btn" @click="deleteProfile">{{ trans('yes') }}</button>
                <button  data-close class="btn darker" @click="closeDeleteProfileModal">{{ trans('no') }}</button>
            </div>
        </div>
    </CustomReveal>
</template>

<script>
    import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                deleteProfileFormVisible: false
            }
        },
        components: {
            CustomReveal
        },
        mounted() {
            app.$on('show-delete-profile-form', this.showDeleteProfileForm);
        },
        beforeDestroy() {
            app.$off('show-delete-profile-form');
        },
        methods: {
            showDeleteProfileForm() {
                this.deleteProfileFormVisible = true;
                $('#reveal-overlay-delete-profile').css('display', 'flex');
                $('#reveal-overlay-delete-profile').css('z-index', 10000);
            },
            closeDeleteProfileModal() {
                this.deleteProfileFormVisible = false;
                $('#reveal-overlay-delete-profile').css('display', 'none');
                $('#reveal-overlay-delete-profile').css('z-index', 'auto');
            },
            deleteProfile() {
                let callback = () => {
                    return axios.post('/api/account/delete', {})
                        .then((response) => {
                            if(response.data == 'ok') {
                                this.closeDeleteProfileModal();
                                this.logout();
                                this.$store.commit('logout');
                            }
                        })
                };

                this.runLoadingFunction('#delete_yes', callback);
            },
        }
    }
</script>