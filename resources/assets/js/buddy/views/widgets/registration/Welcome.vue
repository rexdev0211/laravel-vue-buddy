<template>
    <div id="application-wrapper">
        <section class="onboarding finish" dusk="registration-completed" :style="[this.isDesktop ? {width: '100%', height: '100vh'} : '']">
            <div class="inner">
                <div class="section-body">
                    <div class="logo">
                        <img src="main/img/buddy-logo.svg" alt="Buddy | Buddies & Benefits" />
                    </div>
                    <div class="photo-box">
                        <div v-if="!photoUrl" class="photo"></div>
                        <div v-else class="photo uploaded" :style="{'background': `url(${photoUrl}) no-repeat center / cover`}"></div>
                        <a dusk="registration-photo-preview" href="/profile/edit"></a>
                        <span>{{ trans('complete_profile') }}</span>
                    </div>
                    <div class="buttons">
                        <div class="button discover">
                            <div class="icon"></div>
                            <a dusk="registration-go-to-discover" href="/discover"></a>
                            <div class="name">{{ trans('check_out_guys') }}</div>
                        </div>
                        <div class="button pro">
                            <div class="icon"></div>
                            <a dusk="registration-upgrade" href="/profile/pro"></a>
                            <div class="name">{{ trans('upgrade_to') }} <span class="pro"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        mixins: [require('@general/lib/mixin').default],
        computed: {
            ...mapState({
                photos: state => state.profilePhotos
            }),
            photoUrl(){
                if (!!this.photos[0]) {
                    return this.photos[0].photo_small
                }
                return null
            }
        },
        mounted() {
            fbq('track', 'CompleteRegistration');
        }
    }
</script>
