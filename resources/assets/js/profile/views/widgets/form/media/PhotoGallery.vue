<template>
    <transition :name="'slide-in-bottom'" mode="out-in">
        <div class="add-pics-box"
            v-if="visible"
            @click.self="hide"
            tabindex="0">
            <div class="wrapper">
                <div class="inner">
                    <div class="close" @click="hide"></div>
                    <div class="pics-catalog">
                        <div class="pic upload-photo"
                            id="addPhotoButton"
                            @click="choosePhoto">
                            <input type="file" id="newPhotoUpload" class="show-for-sr" name="photo" accept="image/*" ref="photo" v-on:change="uploadProfilePhoto($event, true)" :placeholder="trans('reg.add_photo')">
                        </div>
                        <div v-if="!photos || !photos.length" class="add-media-hover">
                            <span>{{ trans('reg.add_photo') }}</span>
                        </div>
                        <div class="pic"
                            v-for="(photo, index) in photos"
                             :class="{'selected': photo.active}"
                            @click="selectMedia(photo.id, index, photo.active, $event)">
                            <div class="img"
                                :id="`profile-photo-${photo.id}`"
                                :style="{'background': `url(${photo.photo_small}) center / cover`}">
                            </div>
                        </div>
                    </div>
                    <div class="float-button">
                        <button class="btn" type="submit" @click="submitPhotos($event)">{{ trans('ok') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import auth from '@general/lib/auth';
import discoverModule from "@discover/module/store/type";
import {mapActions, mapGetters, mapState} from 'vuex';

export default {
        mixins: [
            require('@general/lib/mixin').default
        ],
        data() {
            return {
                visible: false,
                slot: null,
                isSelected: false,
                selectedPhotos: [],
                photos: null,
                photosCount: 0
            }
        },
        mounted() {
            app.$on('show-photo-gallery', this.show);
        },
      beforeDestroy() {
            app.$off('show-photo-gallery');
        },
        methods: {
          ...mapActions([
            'requirementsAlertShow',
          ]),
            choosePhoto() {
                this.$refs.photo.click();
            },
            show(slot){
                app.$emit('show-scroll', false);

                this.visible = true
                this.slot = slot

                if (app.isDesktop) {
                  this.photos = this.privatePhotos;
                }
            },
            hide() {
                app.$emit('show-scroll', true);

                this.photosCount = 0;
                this.selectedPhotos = [];

                if (this.photos) {
                    this.photos.map(photo => {
                        photo.active = false;
                    });
                }

                this.visible = false
                this.slot = null
            },
            selectMedia(id, index, active) {
              if (this.slot) {
                  this.selectMainPhoto(index, active);
              } else {
                  this.selectProfilePhotos(index, active);
              }
            },
            selectMainPhoto(index, active) {

                let photos = this.photos;
                let photo = this.photos[index];

                if (this.selectedPhotos.length === 1 && !active) {
                    const deletePhotoId = this.selectedPhotos[0];

                    const changedPhoto = photos.filter((photo, index) => {
                        if (photo.id === deletePhotoId) {
                            photo.index = index;
                            photo.active = false;

                            return photo;
                        }
                    });

                    this.$set(photos, changedPhoto[0].index, changedPhoto[0]);

                    this.selectedPhotos.splice(0, 1);
                }

                photo.active = !photo.active;

                if (photo.active) {
                    this.selectedPhotos.push(photo.id);
                } else {
                    this.removeByValue(this.selectedPhotos, photo.id);
                }

                this.$set(photos, index, photo);
            },
            selectProfilePhotos(index, active) {

                if (!this.userIsPro && this.photosCount === 4 && !active) {
                    this.$store.dispatch('requirementsAlertShow', 'media');
                    return;
                }

                let photo = this.photos[index];
                let photos = this.photos;

                photo.active = !photo.active;

                if (photo.active) {
                    this.selectedPhotos.push(photo.id);
                    this.photosCount++;
                } else {
                    this.removeByValue(this.selectedPhotos, photo.id);
                    this.photosCount--;
                }

                this.$set(photos, index, photo);
            },
            removeByValue(arr) {
                let what, a = arguments, length = a.length, ax;
                while (length > 1 && arr.length) {
                  what = a[--length];
                  while ((ax = arr.indexOf(what)) !== -1) {
                    arr.splice(ax, 1);
                  }
                }
                return arr;
            },
            submitPhotos(event) {
                this.makePhotoPublic(this.selectedPhotos, event);
                this.photosCount = 0;
                this.selectedPhotos = [];
            },
            async makePhotoPublic(photo, event) {
                if (this.slot !== null) {
                    try {
                        let slot = this.slot
                        app.$emit('avatar-preloader', { slot, value: true })
                        await axios.get(`/api/photos/setAsDefault/${photo}/${slot}`)
                            .then(photo => {
                              let photoData = photo.data;

                              if (!photoData.rejected && !photoData.pending || slot === 'adult') {
                                let userAroundData = {
                                  photo_small: photoData.photo_small,
                                  photo_rating: photoData.slot
                                };
                                for (let key in userAroundData) {
                                  this.$store.dispatch(discoverModule.actions.users.update, {
                                    userId: auth.getUserId(),
                                    fields: {
                                      [key]: userAroundData[key]
                                    }
                                  })
                                }
                              }
                            })
                        this.hide()
                        await this.$store.dispatch('loadCurrentUserInfo')
                        app.$emit('avatar-preloader', { slot, value: false })
                    } catch (error) {
                        console.log('Set photo as default error', {error})
                    }
                    return
                }

                if (!this.userIsPro && !this.leftPublicPicturesCount) {
                    this.$store.dispatch('requirementsAlertShow', 'media')
                } else {
                    this.makePhotoVisibleTo(photo, 'public', event)
                        .then(response => {
                              let haveUserInfo = typeof this.$store.state.usersInfo[auth.getUserId()] != 'undefined';
                              if (haveUserInfo) {
                                  let userInfoPhotos = this.$store.state.usersInfo[auth.getUserId()].public_photos;
                                  this.$store.state.usersInfo[auth.getUserId()].public_photos = userInfoPhotos.concat(response.data);
                              }
                        })
                    this.hide()
                }
            }
        },
        computed: {
            ...mapState({
                userIsPro: 'userIsPro',
                profilePhotos: 'profilePhotos'
            }),
            ...mapGetters([
                'publicPhotosCount',
                'privatePhotos',
                'leftPublicPicturesCount'
            ]),
        },
        watch: {
            profilePhotos: function (newVal, oldVal) {
                if (this.slot) {
                    this.photos = newVal;
                }
            },
            privatePhotos: function (newVal, oldVal) {
              if (!this.slot) {
                    this.photos = newVal;
              }
            },
            visible: function (newVal) {
                if (newVal) {
                    this.photosCount = this.publicPhotosCount;
                }
            }
        }
    }
</script>