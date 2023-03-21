<template>
    <div dusk="registration-step-2" class="step">
        <div dusk="registration-photo-load" v-show="!avatarPhoto">
            <div class="headline" v-html="trans('reg.add_photo_text')"></div>

            <label for="exampleFileUpload" class="photo-upload">
                <a class="photo"></a>
            </label>
        </div>

        <div v-show="avatarPhoto">
            <div class="headline" v-html="trans('edit_your_photo')"></div>

            <slim :options="slimOptions">
                <img :src="avatarPreview" alt="">
                <input dusk="registration-photo-file" type="file" id="exampleFileUpload" class="show-for-sr" name="photo" accept="image/*" ref="photo" v-on:change="choosePhoto">
            </slim>

            <div class="reg-btn-skip-photo">
                <a href="javascript:void(0)" @click="tryAnotherPhoto" class="btn darker">{{ trans('reg.try_another_photo') }}</a>
            </div>
        </div>

        <div class="btns">
            <a dusk="registration-photo-skip" href="javascript:void(0)" @click="submitForm('skip')" class="btn darker">{{ trans('reg.skip') }}</a>
            <button @click="submitForm()" id="add-photo" class="btn green">
                {{ trans('reg.next') }}
            </button>
        </div>
    </div>
</template>

<script>
    import slim from '@general/lib/slim/slim.vue';

    // called when slim has initialized
    function slimInit (data, slim) {
        this.slimObj = slim;
    }

    function imageRemoved(data) {
        this.avatarPhoto = null;
        this.avatarPreview = null;
    }

    function modificationsConfirmed(data) {
        //app.log('confirmed', data, this.slimObj);
        this.avatarActions = data.actions;
    }

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['vars'],
        data() {
            return this.vars;
        },
        components: {
            slim
        },
        computed: {
            slimOptions() {
                let options = {
                    didInit: slimInit.bind(this),
                    maxFileSize: 10,
                    minSize: '400,400',
                    maxSize: '4096,4096',
                    didConfirm: modificationsConfirmed.bind(this),
                    rotateButton: true,
                    initialImage: null,
                    didRemove: imageRemoved.bind(this)
                }

                if (this.avatarActions.rotation) {
                    options['rotation'] = this.avatarActions.rotation;
                }

                if (this.avatarActions.crop) {
                    options['crop'] = this.avatarActions.crop;
                }

                return options;
            }
        },
        methods: {
            tryAnotherPhoto() {
                this.deletePhoto();

                this.$refs.photo.click();
            },
            deletePhoto() {
                this.slimObj.remove();
            },
            goToStep2() {
                this.step = 2;
            },
            async submitForm(submitType = 'form') {
                this.showLoadingButton('#add-photo')
                if (submitType !== 'skip' && !this.avatarPhoto) {
                    this.showErrorNotification('reg.add_photo_text')
                    return
                }
                this.step = 3
                this.restoreLoadingButton('#add-photo')
            },
            choosePhoto(e) {
                let files = e.target.files || e.dataTransfer.files;

                if(!files.length) {
                    return;
                }

                let reader = new FileReader;
                reader.onload = (ev) => {
                    this.avatarPreview = ev.target.result;
                    this.avatarPhoto = this.$refs.photo.files[0];
                    this.restoreLoadingButton('#add-photo');
                };

                reader.readAsDataURL(files[0]);
                this.showLoadingButton('#add-photo');
            }
        }
    }
</script>
