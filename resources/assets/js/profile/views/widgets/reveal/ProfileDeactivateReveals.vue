<template>
    <CustomReveal class="reveal-alert" revealId="deactivate-profile" :isVisible="deactivateProfileFormVisible" v-on:close-reveal-deactivate-profile="closeDeactivateProfileModal">
        <div class="box">
            <div class="headline">{{ trans('are_you_sure_deactivate_profile') }}</div>
        </div>
        <div class="options-box">
            <div class="options">
                <button id="deactivate_yes" class="btn" @click="deactivateProfile">{{ trans('yes') }}</button>
                <button  data-close class="btn darker" @click="closeDeactivateProfileModal">{{ trans('no') }}</button>
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
                deactivateProfileFormVisible: false
            }
        },
        components: {
            CustomReveal
        },
        mounted() {
            app.$on('show-deactivate-profile-form', this.showDeactivateProfileForm);
        },
        beforeDestroy() {
            app.$off('show-deactivate-profile-form');
        },
        methods: {
            showDeactivateProfileForm() {
                this.deactivateProfileFormVisible = true;
                $('#reveal-overlay-deactivate-profile').css('display', 'flex');
                $('#reveal-overlay-deactivate-profile').css('z-index', 9999);
            },
            closeDeactivateProfileModal() {
                this.deactivateProfileFormVisible = false;
                $('#reveal-overlay-deactivate-profile').css('display', 'none');
                $('#reveal-overlay-deactivate-profile').css('z-index', 'auto');
            },
            deactivateProfile() {
                let callback = () => {
                    return axios.post('/api/account/deactivate', {})
                        .then((response) => {
                            if(response.data == 'deactivated') {
                                this.$store.commit('updateUser', {status: 'deactivated'});

                                this.closeDeactivateProfileModal();

                                this.goTo('/profile/inactive')
                            }
                        })
                };

                this.runLoadingFunction('#deactivate_yes', callback);
            },
        }
    }
</script>