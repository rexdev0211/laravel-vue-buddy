<template>
    <div :class="[
        'dialog-overlay', 
        dialog.visible ? 'visible' : null,
        dialog.visible ? 'dialog-mode-' + dialog.mode : null,
    ]">
        <div :class="['dialog', dialog.mode]">
            <a class="dialog-close" @click="hide(1000)" v-if="dialog.mode !== 'success'">
                <svg height="20px" viewBox="0 0 329.26933 329" width="20px" xmlns="http://www.w3.org/2000/svg"><path fill="white" d="m194.800781 164.769531 128.210938-128.214843c8.34375-8.339844 8.34375-21.824219 0-30.164063-8.339844-8.339844-21.824219-8.339844-30.164063 0l-128.214844 128.214844-128.210937-128.214844c-8.34375-8.339844-21.824219-8.339844-30.164063 0-8.34375 8.339844-8.34375 21.824219 0 30.164063l128.210938 128.214843-128.210938 128.214844c-8.34375 8.339844-8.34375 21.824219 0 30.164063 4.15625 4.160156 9.621094 6.25 15.082032 6.25 5.460937 0 10.921875-2.089844 15.082031-6.25l128.210937-128.214844 128.214844 128.214844c4.160156 4.160156 9.621094 6.25 15.082032 6.25 5.460937 0 10.921874-2.089844 15.082031-6.25 8.34375-8.339844 8.34375-21.824219 0-30.164063zm0 0"/></svg>
            </a>
            <p>{{ dialog.message }}</p>
            <div class="dialog-actions" v-if="dialog.mode === 'confirm'">
                <a class="dialog-btn dialog-btn-cancel" @click="hide">{{ trans('no') }}</a>
                <a class="dialog-btn dialog-btn-confirm" @click="confirm">{{ trans('yes') }}</a>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapGetters } from 'vuex';

    import {
        _type as dialogType
    } from '@rush/modules/dialog'

    export default {
        mixins: [require('@rush/lib/mixin').default],
        data: () => ({
            jQueryDialog: null,
            animation: null,
            animationInprocess: false
        }),
        computed: {
            ...mapGetters({
                dialog: dialogType.getters.dialog
            })
        },
        methods: {
            confirm(){
                if (this.animationInprocess){
                    return
                }
                this.dialog.callback()
                this.hide(1000)
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
                        self.$store.dispatch(dialogType.actions.hide)
                        self.animationInprocess = false
                    })
            },
            resetPosition(){
                return this.jQueryDialog
                    // Initial position
                    .css({
                        'margin-top': 0,
                        display: 'block',
                        opacity: 0
                    })
            }
        },
        watch: {
            'dialog.visible': {
                immediate: true,
                handler: function(newValue, oldValue) {
                    let self = this
                    if (newValue === true && this.dialog.mode === 'success') {
                        this.animationInprocess = true
                        // Initial position
                        this.resetPosition()
                            // Fade in + move from top
                            .animate({
                                opacity: 1,
                                'margin-top': "7%"
                            }, 1500, function() {
                                self.animationInprocess = false
                                // Fade out after 2 secons
                                self.animation = setTimeout(function(){
                                    self.hide(1500)
                                }, 2000)
                            });

                    } else if (newValue === true && this.dialog.mode === 'error') {
                        this.animationInprocess = true
                        // Initial position
                        this.resetPosition()
                            // Fade in + move from top
                            .animate({
                                opacity: 1,
                                'margin-top': "7%"
                            }, 1500, function(){
                                self.animationInprocess = false
                            });

                    } else if (newValue === true && this.dialog.mode === 'confirm') {
                        this.animationInprocess = true
                        // Initial position
                        this.resetPosition()
                            .css('margin-top', '7%')
                            // Fade in + move from top
                            .animate({
                                opacity: 1
                            }, 1000, function(){
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
        top: 0;
        font-size: 18px;
        font-family: "Raleway-Light";
    }
    .dialog-overlay.visible {
        display: flex;
    }
    .dialog {
        margin-top: 7%;
        min-width: 300px;
        width: 30%;
        background: rgba(0,0,0,0.75);
        border-radius: 10px;
        padding: 40px 40px 30px 40px;
        position: relative;
        height: fit-content;
        display: none;
    }
    .dialog.confirm {
        padding-bottom: 70px;
    }
    .dialog p {
        color: white;
        font-size: 20px;
        text-align: center;
    }
    .dialog-close {
        width: 25px;
        height: 25px;
        position: absolute;
        top: 20px;
        right: 20px;
        opacity: 0.8;
        display: block;
    }
    .dialog-actions {
        position: absolute;
        width: 100%;
        left: 0;
        bottom: 0;
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        border-radius: 0px 0px 10px 10px;
        -moz-border-radius: 0px 0px 10px 10px;
        -webkit-border-radius: 0px 0px 10px 10px;
        border: 0px solid #000000;
        overflow: hidden;
    }
    .dialog-actions > .dialog-btn {
        width: 50%;
        padding: 15px 30px;
        text-align: center;
        display: block;
        color: white;
    }
    .dialog-actions .dialog-btn-cancel {
        background: #666666;
    }
    .dialog-actions .dialog-btn-confirm {
        background: #00CC3D;
    }
    .dialog-overlay.dialog-mode-success {
        overflow: visible;
        height: 0;
    }
</style>

