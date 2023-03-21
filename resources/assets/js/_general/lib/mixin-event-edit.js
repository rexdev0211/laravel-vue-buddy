import eventsModule from '@events/module/store/type'

import {mapState, mapActions} from 'vuex';

export default {
    data() {
        return {
            event: null,
            newTag: '',
            mapVisible: false,
            drawerForMainPhoto: false
        }
    },
    methods: {
        ...mapActions({
            setEvent: eventsModule.actions.setEvent,
            saveEvent: eventsModule.actions.events.submit,
            removeEvent: eventsModule.actions.events.remove,
        }),
        importPhoto(photo) {
            if (this.drawerForMainPhoto) {
                this.event.preview_photo = photo
                this.drawerForMainPhoto = false
            } else {
                this.event.photos.push(photo)
            }
            this.closeDrawer();
        },
        importVideo(video) {
            this.event.videos.push(video);
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
            let index = this.event.photos.findIndex(v => v.id == photo.id)
            this.event.photos.splice(index, 1);
        },
        deleteVideo(video) {
            let index = this.event.videos.findIndex(v => v.id == video.id)
            this.event.videos.splice(index, 1);
        },
        addTag() {
            if (this.newTag && !this.event.tags.includes(this.newTag)) {
                this.event.tags.push(this.newTag)
            }
            this.newTag = ''
        },
        deleteTag(tag) {
            const index = this.event.tags.findIndex(v => v == tag);
            this.event.tags.splice(index, 1);
        },
        deletePreviewPhoto() {
            this.event.preview_photo = {}
        },
        async handleMapMarkerDrag(event) {
            let newLat, newLng;

            if (this.mapProviderIsGmap) {
                newLat = event.latLng.lat();
                newLng = event.latLng.lng();
            } else {
                newLat = event.target.getLatLng().lat;
                newLng = event.target.getLatLng().lng;
            }

            try {
                let fullInfo = await this.getAddressForLatLng(newLat, newLng, true);
                this.event.lat = newLat;
                this.event.lng = newLng;
                this.event.address = fullInfo.formattedAddress;
                this.event.locality = fullInfo.locality;
                this.event.state = fullInfo.state;
                this.event.country = fullInfo.country;
                this.event.country_code = fullInfo.country_code;
            } catch (e) {
                this.showErrorNotification(e);
            }
        },
        handleAddressModification: _.debounce(async function (event) {
            let lat, lng;
            if (!event.target.value) {
                return;
            }

            try {
                //update map marker
                let fullInfo = await this.getLatLngForAddress(this.event.address, true);
                ({lat, lng} = fullInfo.point);
                this.event.lat = lat;
                this.event.lng = lng;
                this.event.locality = fullInfo.locality;
                this.event.state = fullInfo.state;
                this.event.country = fullInfo.country;
                this.event.country_code = fullInfo.country_code;
            } catch (e) {
                this.showErrorNotification(e);
            }
        }, 1000),
        async save() {
            let valid = await this.$validator.validateAll('form1')
            if (!valid) {
                console.log('Event is invalid', { errors: this.$validator.errors })
                return
            }

            let payload = {};
            for (let field of this.modifiableEventFields()) {
                if (this.event.hasOwnProperty(field)) {
                    payload[field] = this.event[field]
                }
            }

            payload.id = this.event.id
            payload.chemsfriendly = payload.chemsfriendly ? 1 : 0
            payload.videos = this.event.videos.map(v => v.id);
            payload.tags = this.event.tags;
            payload.photos = this.event.photos.map(v => v.id);
            payload.preview_photo = this.event.preview_photo

            console.log('[Edit event] payload', { payload })

            if (Object.keys(payload).length) {
                this.showLoadingButton('#update-event')
                await this.saveEvent(payload)
                this.restoreLoadingButton('#update-event')

                if (!payload.id) {
                    this.showSuccessNotification('event_successfully_added')
                }
            }
        },
        reset(){
            if (!this.isMyEvent(this.eventOriginal)) {
                if (this.isDesktop) {
                    this.setEvent({ mode: 'view' })
                } else {
                    this.goTo(`/event/${this.eventId}`)
                }
                return
            }

            this.event = _.cloneDeep(this.eventOriginal)

            let tags = _.cloneDeep(this.event.tags.map(tag => tag.name))
            console.log('tags', tags)
            Vue.set(this.event, 'tags', tags)

            let previewPhoto = this.event.photos.find(v => v.pivot.is_default === 'yes');
            Vue.set(this.event, 'preview_photo', previewPhoto || null)

        },
        setType(type) {
            this.event.type = type
            if (type !== 'fun') {
                this.event.chemsfriendly = false
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
            if (!this.event.photos) {
                return []
            }
            let currentIds = this.event.photos.map(v => v.id);
            return this.profilePhotos.filter(v => !currentIds.includes(v.id))
        },
        exclusiveVideos() {
            if (!this.event.videos) {
                return []
            }
            let currentIds = this.event.videos.map(v => v.id);
            return this.profileVideos.filter(v => !currentIds.includes(v.id))
        }
    },
    watch: {
        eventOriginal: {
            immediate: true,
            handler (value){
                console.log('[Edit Event] eventOriginal watcher', { value })
                if (value && value.id) {
                    this.reset()
                }
            }
        }
    }
}
