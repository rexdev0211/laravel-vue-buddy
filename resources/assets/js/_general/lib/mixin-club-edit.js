import clubsModule from '@clubs/module/store/type'

import {mapState, mapActions} from 'vuex';

export default {
    data() {
        return {
            club: null,
            newTag: '',
            mapVisible: false,
            drawerForMainPhoto: false
        }
    },
    methods: {
        ...mapActions({
            setClub: clubsModule.actions.setClub,
            saveClub: clubsModule.actions.clubs.submit,
            removeClub: clubsModule.actions.clubs.remove,
        }),
        importPhoto(photo) {
            if (this.drawerForMainPhoto) {
                this.club.preview_photo = photo
                this.drawerForMainPhoto = false
            } else {
                this.club.photos.push(photo)
            }
            this.closeDrawer();
        },
        importVideo(video) {
            this.club.videos.push(video);
            this.closeVideoDrawer();
        },
        openDrawer(forMainPhoto) {
            this.drawerForMainPhoto = forMainPhoto
            $('#photos-drawer').addClass('photos-drawer--open');
        },
        closeDrawer() {
            $('#photos-drawer').removeClass('photos-drawer--open');
        },
        openVideoDrawer() {
            $('#videos-drawer').addClass('photos-drawer--open');
        },
        closeVideoDrawer() {
            $('#videos-drawer').removeClass('photos-drawer--open');
        },
        deletePhoto(photo) {
            let index = this.club.photos.findIndex(v => v.id == photo.id)
            this.club.photos.splice(index, 1);
        },
        deleteVideo(video) {
            let index = this.club.videos.findIndex(v => v.id == video.id)
            this.club.videos.splice(index, 1);
        },
        addTag() {
            if (this.newTag && !this.club.tags.includes(this.newTag)) {
                this.club.tags.push(this.newTag)
            }
            this.newTag = ''
        },
        deleteTag(tag) {
            const index = this.club.tags.findIndex(v => v == tag);
            this.club.tags.splice(index, 1);
        },
        deletePreviewPhoto() {
            this.club.preview_photo = {}
        },
        async handleMapMarkerDrag(club) {
            let newLat, newLng;

            if (this.mapProviderIsGmap) {
                newLat = club.latLng.lat();
                newLng = club.latLng.lng();
            } else {
                newLat = club.target.getLatLng().lat;
                newLng = club.target.getLatLng().lng;
            }

            try {
                let fullInfo = await this.getAddressForLatLng(newLat, newLng, true);
                this.club.lat = newLat;
                this.club.lng = newLng;
                this.club.address = fullInfo.formattedAddress;
                this.club.locality = fullInfo.locality;
                this.club.state = fullInfo.state;
                this.club.country = fullInfo.country;
                this.club.country_code = fullInfo.country_code;
            } catch (e) {
                this.showErrorNotification(e);
            }
        },
        handleAddressModification: _.debounce(async function (club) {
            let lat, lng;
            if (!club.target.value) {
                return;
            }

            try {
                //update map marker
                let fullInfo = await this.getLatLngForAddress(this.club.address, true);
                ({lat, lng} = fullInfo.point);
                this.club.lat = lat;
                this.club.lng = lng;
                this.club.locality = fullInfo.locality;
                this.club.state = fullInfo.state;
                this.club.country = fullInfo.country;
                this.club.country_code = fullInfo.country_code;
            } catch (e) {
                this.showErrorNotification(e);
            }
        }, 1000),
        async save() {
            let valid = await this.$validator.validateAll('form1')
            if (!valid) {
                console.log('Club is invalid', { errors: this.$validator.errors })
                return
            }

            let payload = {};
            for (let field of this.modifiableClubFields()) {
                if (this.club.hasOwnProperty(field)) {
                    payload[field] = this.club[field]
                }
            }

            payload.id = this.club.id
            payload.chemsfriendly = payload.chemsfriendly ? 1 : 0
            payload.videos = this.club.videos.map(v => v.id);
            payload.tags = this.club.tags;
            payload.photos = this.club.photos.map(v => v.id);
            payload.preview_photo = this.club.preview_photo

            console.log('[Edit club] payload', { payload })

            if (Object.keys(payload).length) {
                this.showLoadingButton('#update-club')
                await this.saveClub(payload)
                this.restoreLoadingButton('#update-club')

                if (!payload.id) {
                    this.showSuccessNotification('club_successfully_added')
                }
            }
        },
        reset(){
            if (!this.isMyClub(this.clubOriginal)) {
                if (this.isDesktop) {
                    this.setClub({ mode: 'view' })
                } else {
                    this.goTo(`/club/${this.clubId}`)
                }
                return
            }

            this.club = _.cloneDeep(this.clubOriginal)

            let tags = _.cloneDeep(this.club.tags.map(tag => tag.name))
            console.log('tags', tags)
            Vue.set(this.club, 'tags', tags)

            let previewPhoto = this.club.photos.find(v => v.pivot.is_default === 'yes');
            Vue.set(this.club, 'preview_photo', previewPhoto || null)

        },
        setType(type) {
            this.club.type = type
            if (type !== 'fun') {
                this.club.chemsfriendly = false
            }
        }
    },
    computed: {
        ...mapState({
            profilePhotos: 'profilePhotos',
            profileVideos: 'profileVideos',
            userIsPro: 'userIsPro',
        }),
        exclusivePhotos() {
            if (!this.club.photos) {
                return []
            }
            let currentIds = this.club.photos.map(v => v.id);
            return this.profilePhotos.filter(v => !currentIds.includes(v.id))
        },
        exclusiveVideos() {
            if (!this.club.videos) {
                return []
            }
            let currentIds = this.club.videos.map(v => v.id);
            return this.profileVideos.filter(v => !currentIds.includes(v.id))
        }
    },
    watch: {
        clubOriginal: {
            immediate: true,
            handler (value){
                console.log('[Edit Club] clubOriginal watcher', { value })
                if (value && value.id) {
                    this.reset()
                }
            }
        }
    }
}
