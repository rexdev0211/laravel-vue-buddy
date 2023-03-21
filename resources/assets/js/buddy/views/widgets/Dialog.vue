<template>
    <div :class="[
        'dialog-overlay',
        dialog.visible ? 'visible' : null,
        dialog.visible ? 'dialog-mode-' + dialog.mode : null,
    ]">
        <div :class="['dialog', dialog.mode]">
            <div class="close" @click="hide(500)" v-if="dialog.mode !== 'success'"></div>
            <p v-html="dialog.message"></p>
            <div class="dialog-actions" v-if="dialog.mode === 'confirm'">
                <a class="btn darker" @click="reject">{{ trans(dialog.rejectText || 'no') }}</a>
                <a class="btn" @click="confirm">{{ trans(dialog.submitText || 'yes') }}</a>
            </div>
            <div class="dialog-actions confirm-or-view-profile" v-else-if="dialog.mode === 'confirm-or-view-profile'">
                <a class="btn darker" @click="openUser">{{ trans('view_profile') }}</a>
                <a class="btn darker" @click="reject">{{ trans(dialog.rejectText || 'no') }}</a>
                <a class="btn" @click="confirm">{{ trans(dialog.submitText || 'yes') }}</a>
            </div>
            <div class="dialog-actions confirm-or-view-profile-club" v-else-if="dialog.mode === 'confirm-or-view-profile-club'">
                <a class="btn darker" @click="openUser">{{ trans('view_profile') }}</a>
                <a class="btn accept-all" @click="acceptAllRequestsForClub">{{ trans('clubs.accept_all') }}</a>
                <a class="btn darker" @click="reject">{{ trans(dialog.rejectText || 'no') }}</a>
                <a class="btn" @click="confirm">{{ trans(dialog.submitText || 'yes') }}</a>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex';

    export default {
        mixins: [require('@general/lib/mixin').default],
        data: () => ({
            jQueryDialog: null,
            animation: null,
            animationInprocess: false
        }),
        computed: {
            ...mapState({
                dialog: state => state.dialog
            })
        },
        methods: {
            confirm(){
                if (this.animationInprocess){
                    return
                }
                if (this.dialog.callback) {
                    this.dialog.callback()
                }
                this.hide(500)
            },
            reject(){
                if (this.dialog.callbackNegative) {
                    this.dialog.callbackNegative()
                }
                this.hide()
            },
            openUser() {
                let userToken = this.dialog.user.id

                if (this.isMobile) {
                    this.goTo('/user/' + userToken)
                    //this.openUserMobileModal(userToken)
                } else {
                    this.openUserModal(userToken)
                }
                this.hide(500)
            },
            hide(speed){
                if (this.animationInprocess){
                    return
                }

                let self = this
                this.animationInprocess = true
                this.jQueryDialog
                    .fadeOut(speed)
                    .promise()
                    .done(function(){
                        self.$store.dispatch('hideDialog')
                        self.animationInprocess = false
                    })
            },
            resetPosition(){
                return this.jQueryDialog
                    // Initial position
                    .css({
                        'margin-top': 0,
                        display: 'flex',
                        opacity: 0
                    })
            },
            acceptAllRequestsForClub() {
                console.log('animationInprocess', this.animationInprocess);
                if (this.animationInprocess){
                    return
                }
                if (this.dialog.callback) {
                    this.dialog.callback('accept_all_for_clubs')
                }
                this.hide(500)
            }
        },
        watch: {
            'dialog.visible': {
                immediate: true,
                handler: function(newValue, oldValue) {
                    let self = this
                    if (newValue === true && this.dialog.mode === 'success') {
                        this.jQueryDialog.stop()
                        this.animationInprocess = true
                        if (this.animation){
                            clearTimeout(this.animation)
                        }

                        // Initial position
                        this.resetPosition()
                            // Fade in + move from top
                            .animate({
                                opacity: 1,
                                'margin-top': "7%"
                            }, 500, function() {
                                self.animationInprocess = false
                                // Fade out after 2,5 seconds
                                self.animation = setTimeout(function(){
                                    self.hide(500)
                                }, 2500)
                            });

                    } else if (newValue === true && this.dialog.mode === 'error') {
                        this.jQueryDialog.stop()
                        this.animationInprocess = true
                        if (this.animation){
                            clearTimeout(this.animation)
                        }

                        // Initial position
                        this.resetPosition()
                            // Fade in + move from top
                            .animate({
                                opacity: 1,
                                'margin-top': "7%"
                            }, 500, function(){
                                self.animationInprocess = false
                            });

                    } else if (newValue === true && this.dialog.mode === 'confirm' || newValue === true && this.dialog.mode === 'confirm-or-view-profile' || newValue === true && this.dialog.mode === 'confirm-or-view-profile-club') {
                        this.jQueryDialog.stop()
                        this.animationInprocess = true
                        if (this.animation){
                            clearTimeout(this.animation)
                        }

                        // Initial position
                        this.resetPosition()
                            .css('margin-top', '7%')
                            // Fade in + move from top
                            .animate({
                                opacity: 1
                            }, 500, function(){
                                self.animationInprocess = false
                            });
                    }
                }
            }
        },
        mounted(){
            this.jQueryDialog = $('.dialog-overlay .dialog')
        }
    }
</script>

<style scoped>

    .dialog-overlay {
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        z-index: 30000;
        flex-direction: row;
        justify-content: center;
        overflow: hidden;
        font-size: 18px;
        font-family: 'RobotoCondensed-Regular', sans-serif;
    }
    .dialog-overlay.visible {
        display: flex;
    }
    .dialog {
        margin-top: 7%;
        background: rgba(0, 0, 0, 0.595908);
        backdrop-filter: blur(27.1828px);
        border-radius: 10px;
        width: 80vw;
        max-width: 350px;
        padding: 45px 20px 40px;
        box-sizing: border-box;
        position: relative;
        height: fit-content;
        display: none;
        flex-flow: column nowrap;
        justify-content: center;
    }

    .dialog p {
        color: white;
        font-size: 20px;
        text-align: center;
    }
    .dialog-actions {
        position: static;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        width: 70%;
        margin: 0 auto;
        overflow: hidden;
    }
    .dialog.confirm .dialog-actions {
        width: 64%;
    }
    .dialog-overlay.dialog-mode-success {
        overflow: visible;
    }
</style>

