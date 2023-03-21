import auth from './auth';
import {mapGetters} from 'vuex';
import Slim from '@general/lib/slim/slim.module.js'
import PullToRefresh from 'pulltorefreshjs';

import chatModule from '@chat/module/store/type';
import eventsModule from '@events/module/store/type';
import notificationsModule from '@notifications/module/store/type';
import discoverModule from '@discover/module/store/type';
import VueGeolocation from 'vue-browser-geolocation';

import VModal from 'vue-js-modal'
import Vue from "vue";

export default {
    data() {
        return {
            transitionName: this.isMobile ? 'slide-left' : 'no-transition',
            maxPublicPicturesAmount: window.MAX_PUBLIC_PICTURES_AMOUNT,
            maxPublicVideosAmount: window.MAX_PUBLIC_VIDEOS_AMOUNT,
            swiper: null,
            trashIcon: '/assets/img/default_180x180.jpg',
        }
    },
    components: {
        VModal,
        VueGeolocation,
    },
    filters: {
        formatDate: function(date, dateFormat) {
            let returnFormat = dateFormat;

            if (dateFormat == 'mysql') {
                returnFormat = 'YYYY-MM-DD HH:mm:ss';
            }
            else if (dateFormat == 'hour') {
                returnFormat = 'HH:mm';
            }
            else if (dateFormat == 'date') {
                returnFormat = 'MMMM D, YYYY'
            }
            else if (dateFormat == 'day-date') {
                returnFormat = 'dddd, D MMMM'
            }
            else if (dateFormat == 'day-date-short') {
                returnFormat = 'dddd, D MMM'
            }
            else if (dateFormat == 'day-months-year') {
                returnFormat = 'DD.MM.YYYY'
            }

            //TODO: format for DE, maybe make it global, not just avail. here?
            return moment(date).format(returnFormat);
        },
        truncate: function(value, length = 20) {
            if (value.length > length) {
                value = value.substring(0, length-3) + '...';
            }
            return value
        }
    },
    computed: {
        ...mapGetters([
            'unitSystem',
            'profileOptions'
        ]),
        platform(){
            if (this.isApp) {
                return 'app'
            } else if (this.isMobile) {
                return 'mobile'
            } else {
                return 'desktop'
            }
        },
        isDesktop() {
            return this.$resize && this.$mq.above(800) && !this.isApp
        },
        isMobile() {
            return !this.isDesktop
        },
        isApp() {
            return location.hostname == window.APP_DOMAIN
        },
        appLanguage() {
            return app.lang;
        },
        userIsPro() {
            return this.$store.state.userIsPro
        },
        authUserId() {
            return auth.getUserId();
        },
        mapProviderIsGmap() {
            return window.MAP_PROVIDER === 'gmap'
        }
    },
    methods: {
        uploadVideo(event){
            let self = this
            let files = event.target.files || event.dataTransfer.files;
            if (!files.length) {
                console.log('[Video upload] Video file is empty. Abort.')
                return
            }

            let video = document.createElement('video')
            video.preload = 'metadata'

            console.log('[Video upload] Starting...')
            video.addEventListener('loadedmetadata', async function(event) {
                console.log('[Video upload] Metadata was loaded', { files, video, event })

                window.URL.revokeObjectURL(video.src)

                if (video.duration < 1) {
                    console.log('[Video upload] Duration is too low! Abort.')
                    self.showErrorNotification('uploadVideos.error.lowDuration')
                    !event.target || (event.target.value = null)
                    return
                }

                if (video.duration > 900) { // 15 minutes video file is max
                    console.log('[Video upload] Duration is too high! Abort.')
                    self.showErrorNotification('uploadVideos.error.highDuration')
                    !event.target || (event.target.value = null)
                    return
                }

                let newVideoTemporaryHash = Math.random().toString(36)
                self.$store.commit('addVideo', { hash: newVideoTemporaryHash })

                let formData = new FormData()
                formData.append('file', files[0])
                formData.append('hash', newVideoTemporaryHash)

                try {
                    let response = await axios.post('/api/videos/upload', formData, {
                        headers: {'Content-Type': 'multipart/form-data'}
                    })
                    if (response.status === 200) {
                        console.log('[Video upload] Video file was uploaded', response)
                        self.showSuccessNotification('video_uploaded_processing')
                        self.getVideoProcessPercentage(newVideoTemporaryHash)
                        self.$refs.video.value = null;
                    }
                } catch (response) {
                    console.log('[Video upload] Video file upload error', { response })
                    store.commit('removeVideo', { hash: newVideoTemporaryHash })
                }

                !event.target || (event.target.value = null)
            }, false)

            video.src = URL.createObjectURL(files[0])
        },
        async getVideoProcessPercentage(hash) {
            let videoProcessResponse = await axios.get(`/api/videos/getVideoProcess/${hash}`)
            if (videoProcessResponse.status === 200) {
                let video = videoProcessResponse.data
                console.log('[Processing Video]', video)
                if (video.status === 'waiting' || video.status === 'processing' || video.status === 'accessible') {
                    this.$store.commit('updateVideoPercentage', video)
                    setTimeout(async () => {
                        await this.getVideoProcessPercentage(hash);
                    }, 1000)
                }
            } else {
                console.log('[Processing Video Error]', videoProcessResponse)
            }
        },
        openVisitProfile(userToken, index) {
            if (!this.isVisitBlurry(index)) {
                this.openUserModal(userToken, 9)
            } else {
                this.$store.dispatch('requirementsAlertShow', 'visitors')
            }
        },
        openRegisterModal() {
            this.$store.commit('setModal', { register: true });
        },
        openRecoverPasswordModal() {
            this.$store.commit('setModal', { recoverPassword: true });
        },
        async openUserModal(userToken, track){
            $('.w-app').removeClass('overlay');

            let user = await this.$store.dispatch('loadUserInfo', userToken)
            if (!user)
                return

            if (user.deleted_at) {
                this.showErrorNotification('profile_is_deleted');
                return
            } else if (user.status == 'deactivated') {
                this.showErrorNotification('profile_is_deactivated');
                return
            }

            this.$store.commit('setModal', { user });
            this.$store.dispatch(notificationsModule.actions.addVisit, { userId: user.id });

            if ($('.w-chat__widget').css('display') == 'block') {
                let zIndex = parseInt($('.w-chat__widget').css('z-index'))

                setTimeout(function() { $('.reveal-overlay.user-modal').css('z-index', (zIndex ? zIndex : 2000) + 1) }, 100)
            }
        },
        async openUserMobileModal(userToken, track){
            $('.w-app').removeClass('overlay');

            if (this.isMobile) {
                setTimeout(function () {
                    $('footer').hide()
                }, 100)
            }

            let user = await this.$store.dispatch('loadUserInfo', userToken)
            if (!user)
                return

            if (user.deleted_at) {
                this.showErrorNotification('profile_is_deleted');
                return
            } else if (user.status == 'deactivated') {
                this.showErrorNotification('profile_is_deactivated');
                return
            }

            this.$store.commit('setModal', { user });
            this.$store.dispatch(notificationsModule.actions.addVisit, { userId: user.id });

            if ($('.w-chat__widget').css('display') == 'block') {
                let zIndex = parseInt($('.w-chat__widget').css('z-index'))

                setTimeout(function() { $('.reveal-overlay.user-modal').css('z-index', (zIndex ? zIndex : 2000) + 1) }, 100)
            }
        },
        closeUserModal() {
            if(this.isMobile) {
                setTimeout(function () {
                    $('footer').show()
                }, 100)
            }

            this.$store.commit('setModal', { user: null });
        },
        closeRegisterModal() {
            this.$store.commit('setModal', { register: false });
        },
        closeRecoverPasswordModal() {
            this.$store.commit('setModal', { recoverPassword: false });
        },
        openEventModal(event) {
            this.$store.commit('setModal', { event });
        },
        closeEventModal() {
            this.$store.commit('setModal', { event: null });
        },
        photoIsSafe(photo, silent = false) {
            if (photo && this.isApp) {
                let manuallySafe = photo.manual_rating == 'unrated' ? null : (photo.manual_rating == 'clear' ? true : false) // image set as clear by admin
                let defaultSafe  = photo.nudity_rating <= window.START_NUDITY_RATING // image set as clear by nudity rating system
                let settingsSafe = app.$store.state.userIsPro && app.$store.state.profile.view_sensitive_media == 'yes' // PRO user allowed sensitive media in settings
                let isSafe = (manuallySafe !== null && manuallySafe) || (manuallySafe === null && defaultSafe)

                if (!isSafe && !settingsSafe) {
                    let nAgt = navigator.userAgent
                    let os = '-'
                    let clientStrings = [
                        {s:'iOS', r:/(iPhone|iPad|iPod)/},
                    ]
                    for (let id in clientStrings) {
                        let cs = clientStrings[id]
                        if (cs.r.test(nAgt)) {
                            os = cs.s
                            break
                        }
                    }

                    if (os != 'iOS' && app.isApp && !app.$store.state.userIsPro) {
                        return false
                    }

                    if (!app.$store.state.userIsPro && !silent) {
                        app.$store.dispatch('requirementsAlertShow', 'censored')
                    } else if (!silent) {
                        app.$store.dispatch('requirementsAlertShow', 'change_settings')
                    }

                    return false
                }
            }

            return true
        },
        canViewEventDetails(event_user_id) {
            return !this.isApp || event_user_id == this.$store.state.profile.id || this.$store.state.profile.view_sensitive_events == 'yes'
        },
        attachPullToRefresh(elementSelector) {
            let self = this
            if (this.isMobile) {
                let refresh = PullToRefresh.init({
                    mainElement: elementSelector,
                    triggerElement: elementSelector,
                    onRefresh() {
                        self.pullRefresh()
                    },
                    iconArrow: ' '
                });

                return refresh
            }
        },
        verifyOneSignalPlayer() {
            if (this.isApp) {
                //verify one signal url
                const url = new URL(window.location.href);
                const oneSignalId = url.searchParams.get("onesignal_push_id");
                if (oneSignalId) {
                    if (!this.isAuthenticated()) {
                        localStorage.setItem('one-signal-player-id', oneSignalId)
                    } else {
                        this.assignOneSignalPlayerId(oneSignalId)
                    }
                }

                //verify one signal id in local storage
                if (localStorage.getItem('one-signal-player-id') && this.isAuthenticated()) {
                    this.assignOneSignalPlayerId(localStorage.getItem('one-signal-player-id'))
                }
            }
        },
        assignOneSignalPlayerId(playerId) {
            if (playerId) {
                return axios.get(`/api/user/assign-one-signal/${playerId}`)
                    .then(res => {
                        this.$store.commit('updateUser', {
                            'push_notifications': res.data
                        })

                        localStorage.removeItem('one-signal-player-id')
                    })
            }
        },
        symbolsSvgUrl(icon) {
            return '/assets/img/svg/symbols.svg#' + icon
        },
        slimInit (data, slim) {
            slim.edit();
        },
        slimModificationsConfirmed(data) {
            let formData = this.getFormData({
                photo_orig: data.input.file,
                actions: JSON.stringify(data.actions)
            });

            return axios.post(`/api/photos/update/${this.editPhotoId}`, formData)
                .then((response) => {
                    // debugger;
                    if (response.data.photo) {
                        let index = this.photos.findIndex(v => v.id == this.editPhotoId);

                        Vue.set(this.photos, index, response.data);
                    }
                })
        },
        slimCropPhoto(id) {
            this.editPhotoId = id;
            if (! this.croppers[`profile-photo-${id}`]) {
                this.croppers[`profile-photo-${id}`] = new Slim(document.getElementById('slim-elem'), this.slimOptions);
                app.showLightLoading(true);
                this.croppers[`profile-photo-${id}`]
                    .load(
                        document
                            .getElementById(`profile-photo-${id}`)
                            .getAttribute('src-big'),
                        this.slimOptions, (error, data) => {
                            app.showLightLoading(false);
                            this.croppers[`profile-photo-${id}`].edit();
                        }
                    );
            } else {
                this.croppers[`profile-photo-${id}`].edit();
            }
        },
        defaultSlimOptions() {
            return {
                didInit: this.slimInit,
                maxFileSize: 10,
                minSize: '400,400',
                didConfirm: this.slimModificationsConfirmed,
                rotateButton: true,
                edit: false,
            }
        },
        isMyEvent(event) {
            return event && event.user_id === auth.getUserId();
        },
        isMyClub(club) {
            return club && club.user_id === auth.getUserId();
        },
        isAuthenticated() {
            return auth.isAuthenticated();
        },
        authUser() {
            return this.$store.state.profile;
        },

        formatWeight(weightKg, showText = true) {
            if (this.unitSystem == 'metric') {
                if (showText) {
                    return weightKg + 'kg';
                } else {
                    return weightKg;
                }
            } else {
                //get value from parentheses
                let exec = /.+\((.+)\)/.exec(this.profileOptions.weights[weightKg]);

                if (exec === null) {
                    return ''
                } else {
                    if (showText) {
                        return exec[1]
                    } else {
                        let array = exec[1].split(" ")

                        return array[0] ? array[0] : ""
                    }
                }
            }
        },
        formatHeight(heightCm, showText = true) {
            if (this.unitSystem == 'metric') {
                if  (showText) {
                    return heightCm + 'cm';
                } else {
                    return heightCm;
                }
            } else {
                //get value from parentheses
                let exec = /.+\((.+)\)/.exec(this.profileOptions.heights[heightCm]);

                return exec === null ? '' : exec[1];
            }
        },
        formatDistance(distanceMeters) {
            if (this.unitSystem == 'metric') {
                if (distanceMeters < 1000) {
                    return Math.floor(distanceMeters) + 'm';
                } else {
                    return (distanceMeters/1000).toLocaleString('en-IN', {maximumFractionDigits: 1}) + 'km';
                }
            } else {
                return (distanceMeters/1000/1.609344).toLocaleString('en-IN', {maximumFractionDigits: 1}) + 'mi';
            }
        },
        getStatusClasses: (user, standardClass, tracker) => {
            let returnClass = [standardClass];
            if (user.isOnline) {
                returnClass.push('currently__online');
            } else if (user.wasRecentlyOnline) {
                returnClass.push('recently__online');
            } else {
                returnClass.push('offline');
            }
            return returnClass.join(' ');
        },
        uploadProfilePhoto(e, edit = false) {
            let files = e.target.files || e.dataTransfer.files;
            if (!files.length) {
                return;
            }

            let reader = new FileReader;
            reader.onload = (ev) => {
                let callback = () => {
                    const data = {photo: files[0]};
                    const formData = this.getFormData(data);
                    return axios.post('/api/photos/add', formData)
                        .then((response) => {
                            let photo = response.data;
                            this.$store.commit('addPhoto', photo);
                            if (edit) {
                                setTimeout(() => {
                                    this.slimCropPhoto(photo.id);
                                }, 0)
                            }
                        })
                        .finally(() => {
                            e.target.value = null
                        })
                };
                this.runLoadingFunction('#addPhotoButton', callback);
            };
            reader.readAsDataURL(files[0]);
        },
        loadScrollTopButton(containerId = "js-discover-content") {
            (function ($) {
                let scrollButton = $('#scroll__top');
                $(`#${containerId}`).scroll(function () {
                    if($(this).scrollTop() > 50) {
                        scrollButton.show();
                        scrollButton.addClass('active');
                    } else {
                        scrollButton.removeClass('active');
                        scrollButton.hide();
                    }
                });

                scrollButton.click(function (e) {
                    e.preventDefault();
                    $(`#${containerId}`).animate({scrollTop: 0}, 500);
                });
            }(jQuery));
        },
        scrollEventsPageTop(speed = 500) {
            if (this.isDesktop) {
                this.scrollElementTop('.bb-popup__content', speed)
            } else {
                this.scrollElementTop('.w-page__content', speed)
            }
        },
        scrollElementTop(selector, speed = 500) {
            $(selector).animate({scrollTop: 0}, speed);
        },
        loadSwiper(index = 0, callback = undefined) {
            const vm = this;
            (function($) {
                try {
                    let mySwiper = new Swiper('.swiper-container', {
                        init: true,
                        zoom: true,
                        passiveListeners: true,
                        preventClicks: true,
                        preventClicksPropagation: true,
                        initialSlide: index,
                        shortSwipes: true,
                        threshold: 10,
                        longSwipesRatio: 0.1,
                        longSwipesMs: 200,
                        longSwipes: true,
                        noSwiping: true,
                        noSwipingClass: 'no-swiping',
                        touchMoveStopPropagation: true,
                        loop: true,
                        toggle: true,
                        // Optional parameters

                        // If we need pagination
                        // pagination: '.swiper-pagination',

                        // Navigation arrows
                        // nextButton: '.swiper-button-next',
                        // prevButton: '.swiper-button-prev',

                        // And if we need scrollbar
                        // scrollbar: '.swiper-scrollbar',
                    });

                    if (
                        callback !== undefined
                        &&
                        mySwiper
                        &&
                        typeof mySwiper.on !== undefined
                    ) {
                        mySwiper.on('slideChange', el => {
                            callback(el.realIndex, false);
                        })

                        if (vm.isIos()) {
                            vm.swiper = mySwiper;
                        }
                    }

                    window.mySwiper = mySwiper;
                } catch (e) {
                    console.error('[SWIPER]', e);
                }
            }(jQuery));
        },
        loadSwiperPopup(id, index = 0, callback = undefined) {
            const vm = this;
            (function($) {
                let mySwiper = new Swiper('#'+id, {
                    zoom: true,
                    passiveListeners: vm.isIos(),
                    initialSlide: index,
                    shortSwipes: true,
                    threshold: 10,
                    longSwipesRatio: 0.1,
                    longSwipesMs: 200,
                    noSwiping: true,
                    noSwipingClass: 'no-swiping',
                    loop: true,
                    toggle: true,
                    // Optional parameters

                    // If we need pagination
                    // pagination: '.swiper-pagination',

                    // Navigation arrows
                    // nextButton: '.swiper-button-next',
                    // prevButton: '.swiper-button-prev',

                    // And if we need scrollbar
                    // scrollbar: '.swiper-scrollbar',
                });

                if (
                    callback !== undefined
                    &&
                    mySwiper
                    &&
                    typeof mySwiper.on !== undefined
                ) {
                    mySwiper.on('slideChange', el => {
                        if (vm.isIos()) {
                            vm.swiperZoomDisable();
                        }
                        callback(el.realIndex, false);
                    })

                    if (vm.isIos()) {
                        vm.swiper = mySwiper;
                    }
                }

                window.mySwiper = mySwiper;
            }(jQuery));
        },
        swiperZoomDisable() {
            if (this.isIos()) {
                let mySwiper = this.swiper;
                mySwiper.zoom.out();
            }
        },
        swiperZoomEnable() {
            let mySwiper = this.swiper;
            mySwiper.zoom.enable();
        },
        swiperZoomIn(callback = undefined) {
            this.swiperZoomEnable();
            let mySwiper = this.swiper
            mySwiper.zoom.in()
            callback(true)
        },
        swiperZoomOut(callback = undefined) {
            this.swiperZoomDisable();
            callback(false)
        },
        logout() {
            auth.logout();
            if (this.$route.name != 'login') {
                window.location = '/'
            }
        },
        clearFilter() {
            const filter = this.$store.getters[discoverModule.getters.filter.defaultFilter];
            const keys = _.keys(filter);

            keys.forEach(key => {
                let localFilter = `discover-${key}`;
                localStorage.removeItem(localFilter);
            });
        },
        goToVisitProfile(user, index) {
            if (!this.isVisitBlurry(index)) {
                this.$router.push({name: 'user', params: {userToken: user.link || user.id}})
            } else {
                this.$store.dispatch('requirementsAlertShow', 'visitors')
            }
        },
        isVisitBlurry(index) {
            return !this.userIsPro && index > 4
        },
        getCurrentPosition() {
            return new Promise((resolve, reject) => {
                Vue.use(VueGeolocation);
                VueGeolocation.getLocation({
                    enableHighAccuracy: true, //defaults to false
                    timeout: Infinity, //defaults to Infinity
                    maximumAge: 0 //defaults to 0
                })
                    .then(coordinates => {
                        let pos = {
                            lat: coordinates.lat,
                            lng: coordinates.lng
                        };
                        return resolve(pos);
                    })
                    .catch((e) => {
                        return reject("[Geo] Error! "+e);
                    });

                // let geolocationPromiseTimeout = setTimeout(() => {
                //     clearTimeout(geolocationPromiseTimeout)
                //     reject('[Geo] Error! The Geolocation service waiting too long... 10s.')
                // }, 10000)

                // if (navigator.geolocation) {
                //     navigator.geolocation.getCurrentPosition(function(position) {
                //         let pos = {
                //             lat: position.coords.latitude,
                //             lng: position.coords.longitude
                //         };
                //         alert(position.coords.latitude+'/'+position.coords.longitude);
                //         console.log('[Geo] Coordinates retrieved', pos)
                //         clearTimeout(geolocationPromiseTimeout)
                //         return resolve(pos);
                //     }, function(e) {
                //         clearTimeout(geolocationPromiseTimeout)
                //         return reject(JSON.stringify(e));
                //     });
                // } else {
                //     clearTimeout(geolocationPromiseTimeout)
                //     return reject("[Geo] Error! Your browser doesn't support geolocation.");
                // }
            });
        },
        //TODO: hardcoded lat/lng
        getAddressForLatLng(lat = 52.520389, lng = 13.40424, getFullInfo = false) {
            let result = null
            if (this.mapProviderIsGmap) {
                result = this.getAddressForLatLngGmap(lat, lng, getFullInfo);
            } else {
                result = this.getAddressForLatLngOsm(lat, lng, getFullInfo);
            }
            return result
        },
        getAddressForLatLngOsm(lat, lng, getFullInfo) {
            let requestUrl = `https://nominatim.openstreetmap.org/reverse?format=json&accept-language=en&lat=${lat}&lon=${lng}`;
            return new Promise((resolve, reject) => {
                let osmPromiseTimeout = setTimeout(() => {
                    clearTimeout(osmPromiseTimeout)
                    reject('Error: The OSM service waiting too long... 5s.')
                }, 5000)

                return axios.get(requestUrl)
                    .then(results => {
                        if (results.data.address !== undefined) {
                            //app.log('address for lat/lng', results);

                            let address = this.extractAddressComponents(results.data.address);

                            if (getFullInfo) {
                                clearTimeout(osmPromiseTimeout)
                                return resolve(address);
                            } else {
                                // return resolve(results.data.display_name);
                                clearTimeout(osmPromiseTimeout)
                                return resolve(address.formattedAddress);
                            }
                        } else {
                            clearTimeout(osmPromiseTimeout)
                            return reject('No results found');
                        }
                    })
                    .catch(e => {
                        clearTimeout(osmPromiseTimeout)
                        return reject('OSM geocoder failed.');
                    })
            });
        },
        getAddressForLatLngGmap(lat, lng, getFullInfo) {
            let geocoder = new google.maps.Geocoder();

            return new Promise((resolve, reject) => {
                return geocoder.geocode({
                    'location': {
                        lat: parseFloat(lat),
                        lng: parseFloat(lng)
                    },
                }, (results, status) => {
                    if (status === 'OK') {
                        if (results[0]) {
                            //app.log(`address for lat/lng - ${lat}/${lng}:`, results);

                            if (getFullInfo) {
                                //results[0] usually contains Koln, instead of Cologne; so we must search for key where types[] contains 'locality'
                                let bestResult = results.find(e => e.types.includes('locality'));
                                if (!bestResult) {
                                    bestResult = results.find(e => e.types.includes('administrative_area_level_1'));
                                }
                                if (!bestResult) {
                                    bestResult = results.find(e => e.types.includes('country'));
                                }

                                let country = bestResult.address_components.find(e => e.types.includes("country")),
                                    state = bestResult.address_components.find(e => e.types.includes("administrative_area_level_1")),
                                    locality = bestResult.address_components.find(e => e.types.includes("locality"));

                                let ret = {
                                    country: country ? country.long_name : '',
                                    country_code: country ? country.short_name : '',
                                    state: state ? state.long_name : '',
                                    locality: locality ? locality.long_name : (state ? state.long_name : ''),
                                    formattedAddress: results[0].formatted_address
                                };

                                return resolve(ret);
                            } else {
                                return resolve(results[0].formatted_address);
                            }
                        } else {
                            return reject('No results found');
                        }
                    } else {
                        return reject('Geocoder failed due to: ' + status);
                    }
                });
            });

        },
        async getCurrentPositionAndAddress(getFullInfo = false) {
            let lat, lng;
            try {
                ({lat, lng} = await this.getCurrentPosition());

                try {
                    let address = await this.getAddressForLatLng(lat, lng, getFullInfo);

                    return {lat, lng, address};
                } catch (e) {
                    return {lat, lng, address: ''};
                }
            } catch (e) {
                throw e;
            }
        },
        //TODO: hardcoded address
        getLatLngForAddress(address = 'Spandauer Str./Marienkirche (Berlin), 10178 Berlin, Germany', getFullInfo = false) {
            if (this.mapProviderIsGmap) {
                return this.getLatLngForAddressGmap(address, getFullInfo);
            } else {
                return this.getLatLngForAddressOsm(address, getFullInfo);
            }
        },
        extractAddressComponents(address) {
            let state = address.state ? address.state : (address.county ? address.county : '');
            let countryCode = (address.country_code ? address.country_code : '').toUpperCase();
            let country = address.country ? address.country : '';
            let locality = address.city ? address.city : (address.town ? address.town : (address.village ? address.village : state));
            let roadHouseNumber = ((address.road ? address.road : '') + ' ' + (address.house_number ? address.house_number : '')).trim();
            let formattedAddress = [];

            if (roadHouseNumber) {
                formattedAddress.push(roadHouseNumber)
            }
            if (locality) {
                formattedAddress.push(locality)
            }
            if (country) {
                formattedAddress.push(country)
            }

            formattedAddress = formattedAddress.join(', ');
            return {
                state,
                country_code: countryCode,
                country,
                locality,
                formattedAddress
            };
        },
        getLatLngForAddressOsm(address, getFullInfo) {
            let requestUrl = `https://nominatim.openstreetmap.org/search/?format=json&accept-language=en&addressdetails=1&limit=1&q=${encodeURIComponent(address)}`
            return new Promise((resolve, reject) => {
                return axios.get(requestUrl)
                    .then(results => {
                        // debugger;
                        if (results.data[0]) {
                            //app.log('lat/lng for address', results);

                            let res = results.data[0];

                            let point = {
                                lat: res.lat,
                                lng: res.lon
                            };

                            if (getFullInfo) {
                                let address = this.extractAddressComponents(res.address);

                                let ret = Object.assign(address, {point});

                                return resolve(ret);
                            } else {
                                return resolve(point);
                            }
                        } else {
                            return reject('No results found');
                        }
                    })
                    .catch(e => {
                        return reject('OSM geocoder request failed.');
                    });
            });
        },
        getLatLngForAddressGmap(address, getFullInfo) {
            let geocoder = new google.maps.Geocoder();

            return new Promise((resolve, reject) => {
                return geocoder.geocode({
                    'address': address
                }, (results, status) => {
                    if (status === 'OK') {
                        //app.log('lat/lng for address', results);

                        let point = {
                            lat: results[0].geometry.location.lat(),
                            lng: results[0].geometry.location.lng()
                        };

                        if (getFullInfo) {
                            let country = results[0].address_components.find(e => e.types.includes("country")),
                                state = results[0].address_components.find(e => e.types.includes("administrative_area_level_1")),
                                locality = results[0].address_components.find(e => e.types.includes("locality"));

                            let ret = {
                                country: country ? country.long_name : '',
                                country_code: country ? country.short_name : '',
                                state: state ? state.long_name : '',
                                locality: locality ? locality.long_name : (state ? state.long_name : ''),
                                point: point
                            };

                            return resolve(ret);
                        } else {
                            return resolve(point);
                        }
                    } else {
                        return reject('Geocode was not successful for the following reason: ' + status);
                    }
                });
            });
        },
        //https://www.geodatasource.com/developers/javascript
        //the unit you desire for results where: 'M' is statute miles (default), 'K' is kilometers, 'N' is nautical miles, 'm' is meters
        calculateDistanceBetween(lat1, lon1, lat2, lon2, unit) {
            let radlat1 = Math.PI * lat1/180;
            let radlat2 = Math.PI * lat2/180;
            let theta = lon1-lon2;
            let radtheta = Math.PI * theta/180;
            let dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);

            if (dist > 1) {
                dist = 1;
            }

            dist = Math.acos(dist);
            dist = dist * 180/Math.PI;
            dist = dist * 60 * 1.1515;

            if (unit=="K") { dist = dist * 1.609344 }
            if (unit=="N") { dist = dist * 0.8684 }

            if (unit=="m") { dist = dist * 1.609344 * 1000 }

            return dist;
        },
        getDistanceString(entity, showText = true) {
            if (entity.user_group == 'staff') {
                return 'BUDDYWOOD'
            }

            let me = this.$store.state.profile;
            let maxDistanceMeters = 50 * 1000; //50km

            if (entity.distanceMeters > maxDistanceMeters && entity.locality) {
                if (entity.country_code != me.country_code) {
                    return `${entity.locality}, ${entity.country}`;
                } else {
                    return entity.locality;
                }
            } else {
                if (showText) {
                    return this.trans('distance_away', {distance: this.formatDistance(entity.distanceMeters)});
                } else {
                    return this.formatDistance(entity.distanceMeters);
                }
            }
        },
        async toggleFavorite(toggleUser) {
            let newValue = !toggleUser.isFavorite;

            if (
                newValue
                &&
                !this.$store.state.userIsPro
                &&
                this.$store.state.favoritesCount >= window.FREE_FAVORITES_LIMIT
            ) {
                this.$store.dispatch('requirementsAlertShow', 'favorites')

            } else {
                toggleUser.isFavorite = newValue
                let newValueInt = newValue ? 1 : 0;
                let response = await axios.post(`/api/userFavorite/${toggleUser.id}/${newValueInt}`)
                try {
                    if (response.data.success) {
                        this.$store.dispatch(discoverModule.actions.users.update, {
                            userId: toggleUser.id,
                            fields: {
                                isFavorite: newValue
                            }
                        })

                        this.$store.dispatch(chatModule.actions.conversations.update, {
                            tracker: 'toggleFavorite',
                            userId: toggleUser.id,
                            interlocutor: {
                                isFavorite: newValue
                            },
                            // insertIfMissing: true
                        })

                        this.$store.commit('setFavoritesCount', newValue ?
                            this.$store.state.favoritesCount + 1
                            :
                            this.$store.state.favoritesCount - 1
                        )
                    } else {
                        toggleUser.isFavorite = !newValue

                        if (response.data.proRequired) {
                            this.$store.dispatch('requirementsAlertShow', 'favorites')
                        } else {
                            this.showErrorNotification('Failed to change favorite status')
                        }
                    }
                } catch (e) {
                    console.log('[User favourite] Error! ', {e})
                    toggleUser.isFavorite = !newValue
                }
            }
        },
        getWaveTapIcon(subType) {
            if (!subType) {
                return false;
            } else {
                var type = 'svg'

                return `/main/img/icons/taps/${subType}.${type}`;
            }
        },
        goBack(fallback, tag) {
            if (fallback && tag) {
                let goToBack = this.$route.meta.back.replace(/\?tag\=.+/, '');

                if (goToBack.match(/\/search/) || goToBack.match(/\/discover/)) {
                    goToBack = goToBack + '?tag='+tag;
                }

                this.$router.push(goToBack);
                return;
            }

            if (fallback && this.$route.meta && (_.isUndefined(this.$route.meta.back) || this.$route.meta.back == '/')) { //when there is no back then route is /
                //app.log('go back to fallback '+fallback);
                this.$router.push(fallback);
            } else {
                //app.log('go back to prev history');
                this.$router.back()
            }
        },
        goTo(url) {
            if (app.$router.currentRoute.path != url) {
                app.$router.push(url);
            }
        },
        goToOrBack(url) {
            if (url === app.routesHistory[app.routesHistory.length-1]) {
                this.$router.back()
            } else {
                this.goTo(url)
            }
        },
        capitalize(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },
        async updateUserLocation(payload) {
            this.$store.commit('updateUser', payload)
            await axios.post('/api/updateUser', payload)
            console.log(`[Geo] Location setting updated`, { payload })
        },
        showLoadingButton(buttonSelector) {
            let button = $(buttonSelector);
            let originalText = button.html();
            button.html(`<span class="hidden" style="display:none">${originalText}</span> <img class="preloader" src="/assets/img/preloader.svg" alt="">`);
            button.attr('disabled', 'disabled');
        },
        restoreLoadingButton(buttonSelector) {
            let button = $(buttonSelector);
            let span = button.find('span.hidden');
            button.html(span.html());
            button.removeAttr('disabled');
        },
        runLoadingFunction(buttonSelector, callback) {
            this.showLoadingButton(buttonSelector);
            return Promise.resolve(callback())
                .then(() => {
                    this.restoreLoadingButton(buttonSelector);
                });
        },
        modifiableEventFields() {
            return [
                'title', 'time', 'event_date',
                'type', 'chemsfriendly', 'address_type',
                'description', 'location',
                'lat', 'lng', 'address', 'locality', 'state',
                'is_profile_linked', 'country',
                'country_code'
            ];
        },
        //use as this: await this.sleep(1000);
        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },
        showSuccessNotification(message) {
            this.showNotification(message, 'success')
        },
        showErrorNotification(message) {
            this.showNotification(message, 'error')
        },
        showNotification(payload, mode) {
            app.$store.dispatch('hideDialog')

            // Message is suppressed
            if (payload === false) {
                return false
            }

            let message = null
            let defaultMessage = null
            if (mode === 'error') {
                defaultMessage = "An unexpected error has occurred. We'll fix it soon!"
            } else {
                defaultMessage = "Thank you!"
            }

            if (_.isString(payload)) {
                console.log('[showNotification] String', payload)
                message = this.trans(payload)
                console.log('[showNotification] Translated string', payload)
                app.$store.dispatch('showDialog', { mode, message: message || defaultMessage })

            } else if (_.isObject(payload)) {
                console.log('[showNotification] Object', payload)
                message = this.showNotification(
                    mode === 'error' ? (payload.error || payload.message) : payload.message,
                    mode
                )

                if (payload.error === this.trans('invalid_credentials')) {
                    setTimeout(() => {
                        app.$store.dispatch('hideDialog')
                    }, 2000)
                }

            } else if (_.isArray(payload)) {
                message = this.showNotification(_.head(payload), mode)
            }

            return message
        },
        rememberProfileOldValue(event) {
            let element = event.target;
            $(element).prop('oldvalue', element.value);
        },
        removePhotoFromEvents(photoId) {
            const removePhotoLocal = (event) => {
                if (this.$store.state.eventsInfo[event.id] && this.$store.state.eventsInfo[event.id].photos) {
                    const index = this.$store.state.eventsInfo[event.id].photos.findIndex(v => v.id == photoId);
                    if (index !== -1) {
                        //if this was default photo
                        if (this.$store.state.eventsInfo[event.id].photos[index].photo == event.photo) {
                            this.$store.state.eventsInfo[event.id].photo_orig = this.$store.state.profileOptions.defaultPhotos.photo_orig
                            this.$store.state.eventsInfo[event.id].photo_small = this.$store.state.profileOptions.defaultPhotos.photo_small
                        }

                        this.$store.state.eventsInfo[event.id].photos.splice(index, 1)
                    }
                }
            }

            //my events
            this.$store.state.myEvents.forEach(removePhotoLocal)
        },
        removeVideoFromEvents(videoId) {
            const removeVideoLocal = (event) => {
                if (this.$store.state.eventsInfo[event.id] && this.$store.state.eventsInfo[event.id].videos) {
                    const index = this.$store.state.eventsInfo[event.id].videos.findIndex(v => v.id == videoId);
                    if (index > -1) {
                        this.$store.state.eventsInfo[event.id].videos.splice(index, 1)
                    }
                }
            }

            //my events
            this.$store.state.myEvents.forEach(removeVideoLocal)
        },
        async makePhotoVisibleTo(photo, state, event) {
            if (event) {
                this.showLoadingButton(event.currentTarget)
            }

            try {
                let params;

                if (typeof photo === "number") {
                    params = {photoIds: [photo]};
                } else {
                    params = {photoIds: photo};
                }

                let response = await axios.get(`/api/photos/changeVisible/${state}`, { params });

                if (!response.data) {
                    if (event) {
                        this.restoreLoadingButton(event.currentTarget)
                    }
                    return
                }

                Object.keys(response.data).forEach(element => {
                    const photoData = response.data[element];

                    this.$store.commit('updatePhoto', {
                        photoId: photoData.id,
                        data: photoData
                    })
                });

                if (event) {
                    this.restoreLoadingButton(event.currentTarget)
                }

                return response;
            } catch (error) {
                console.log('[makePhotoVisibleTo] Error', { error })
            }

            if (event) {
                this.restoreLoadingButton(event.currentTarget)
            }
        },
        makeVideoVisibleTo(video, state, event) {
            let callback = () => {
                return axios.get(`/api/videos/changeVisible/${video}/${state}`)
                    .then(({data}) => {
                        this.$store.commit('updateVideo', {
                            oldVideo: video,
                            newVideo: data
                        });

                        this.$store.dispatch(discoverModule.actions.users.update, {
                            userId: auth.getUserId(),
                            fields: {
                                has_videos: !!this.$store.getters.publicVideos.length,
                            }
                        })
                        if (state === 'public') {
                            let userVideos = this.$store.state.usersInfo[auth.getUserId()].public_videos;
                            this.$store.state.usersInfo[auth.getUserId()].public_videos = userVideos.concat(data);
                        } else if (state === 'private') {
                            let deleteVideoId = data.id;
                            let userVideoInfo = this.$store.state.usersInfo[auth.getUserId()].public_videos;
                            if (userVideoInfo) {
                                let index = userVideoInfo.findIndex(el => el.id === deleteVideoId);
                                this.$store.state.usersInfo[auth.getUserId()].public_videos.splice(index, 1);
                            }
                        }

                    })
            };

            return this.runLoadingFunction(event.target, callback);
        },
        saveProfileChange(event, force = false) {
            let element = event.target;
            setTimeout(() => {
                if (!this.$validator.errors.has(element.name)) {
                    axios.post('/api/updateUser', {[element.name]: element.value})
                        .then(() => {
                            if (force) {
                                this.$store.dispatch('loadCurrentUserInfo', true)
                            } else {
                                this.$store.dispatch(discoverModule.actions.users.update, {
                                    userId: auth.getUserId(),
                                    fields: {
                                        [element.name]: element.value
                                    }
                                })
                                let userInfo = this.$store.state.usersInfo[auth.getUserId()];
                                let usersInfo = this.$store.state.usersInfo;

                                if (userInfo) {
                                    userInfo[element.name] = element.value;
                                    Vue.set(usersInfo, auth.getUserId(), userInfo);
                                }
                            }
                        })
                        .catch((error) => {
                            let oldValue = $(element).prop('oldvalue');
                            if (typeof oldValue != 'undefined') {
                                this.user[element.name] = oldValue;
                            }
                        });
                } else {
                    console.error(`BB custom error: input validation for "${element.name}" not passed`);
                }
            }, 0);
        },
        getFormData(obj) {
            const fd = new FormData();
            Object.keys(obj).forEach(key => {
                if (obj[key] && obj[key].constructor === Array) {
                    obj[key].forEach(value => {
                        fd.append(`${key}[]`, value)
                    })
                } else {
                    fd.append(key, obj[key]);
                }
            });

            return fd;
        },
        trans(word, args) {
            return app.trans(word, args);
        },
        transBody(word) {
            if (word)
                return this.trans(`options.body.${word}`);
        },
        transPosition(word, lowercase = false) {
            if (word) {
                let out = this.trans(`options.position.${word}`);
                if (lowercase) {
                    out = out.toLowerCase();
                }
                return out;
            }
        },
        transHiv(word) {
            if (word)
                return this.trans(`options.hiv.${word}`);
        },
        transDrugs(word) {
            if (word)
                return this.trans(`options.drugs.${word}`);
        },
        setAppLanguage(lang) {
            app.setLanguage(lang);
        },
        maxAdultDate() {
            return moment().format('YYYY-MM-DD');
        },
        todayDate() {
            return moment().format('YYYY-MM-DD');
        },
        yesterdayDate() {
            return moment().subtract(1, 'days').format('YYYY-MM-DD');
        },
        inAYearDate() {
            return moment().add(1, 'years').format('YYYY-MM-DD');
        },
        registerDate() {
            return moment().subtract(18, 'years').format('YYYY-MM-DD');
        },
        timeAgo(date) {
            //TODO: 'ago' for DE
            return moment(date).fromNow();
        },
        convertUtcToLocal(idate) {
            return moment.utc(idate).local().format('YYYY-MM-DD HH:mm:ss');
        },
        isSafari() {
            var isSafari = !!navigator.userAgent.match(/Version\/[\d\.]+.*Safari/);
            return isSafari;
        },
        isIos() {
            return /iPhone|iPad|iPod/.test(navigator.userAgent);
        },
        iosVersion() {
            if (/iP(hone|od|ad)/.test(navigator.platform)) {
                // supports iOS 2.0 and later: <http://bit.ly/TJjs1V>
                let v = (navigator.appVersion).match(/OS (\d+)_(\d+)_?(\d+)?/);
                let versionArr = [parseInt(v[1], 10), parseInt(v[2], 10), parseInt(v[3] || 0, 10)];
                return parseFloat(versionArr.join('.'))
            }
        },
        isTouch() {
            let isTouch = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
            return isTouch;
        },
        async blockUserById(userId) {
            // Remove user conversations
            await this.$store.dispatch(chatModule.actions.conversations.removeAllUserBlockedChats, {userId})
            // Remove user from usersAround list
            let entryRemovedFromUsersAround = await this.$store.dispatch(discoverModule.actions.users.remove, { userId })
            console.log('[Block] Blocking user is found', entryRemovedFromUsersAround)
            // Remove user from Favorites list
            const favoritesIndex = this.$store.state.onlineFavorites.findIndex(e => e.id == userId);
            if (favoritesIndex > -1) {
                this.$store.state.onlineFavorites.splice(favoritesIndex, 1);
            }

            // Make block request
            axios.post(`/api/blockUser/${userId}`)
                .then((response) => {
                    if (response.data.success) {
                        this.showSuccessNotification('user_blocked_confirmation')
                        this.$store.commit(notificationsModule.mutations.visitors.set, [])
                        this.$store.commit(notificationsModule.mutations.visited.set, [])
                        this.$store.commit(notificationsModule.mutations.notifications.set, [])
                        this.$store.commit(chatModule.mutations.conversations.clearGroup, 'all')
                        this.$store.commit(chatModule.mutations.conversations.clearGroup, 'unread')
                        this.$store.commit(chatModule.mutations.conversations.clearGroup, 'favorites')
                        this.$store.commit(eventsModule.mutations.resetPagination)
                        this.$store.commit(eventsModule.mutations.events.set, [])

                        this.$nextTick(async () => {
                            await this.$store.dispatch(notificationsModule.actions.visitors.load)
                            await this.$store.dispatch(notificationsModule.actions.visited.load)
                            await this.$store.dispatch(notificationsModule.actions.notifications.load)
                            await this.$store.dispatch(chatModule.actions.conversations.loadGroup, {
                                page: 0,
                                limit: window.LOAD_CHAT_WINDOWS_LIMIT,
                                group: 'all'
                            })
                            await this.$store.dispatch(eventsModule.actions.events.load)
                            await this.$store.dispatch('loadCurrentUserInfo')
                        })
                    } else {
                        if (response.data.proRequired) {
                            this.$store.dispatch('requirementsAlertShow', 'blocks')
                        } else {
                            this.showErrorNotification('fail_block_user')
                        }

                        if (!!entryRemovedFromUsersAround) {
                            this.$store.dispatch(discoverModule.actions.users.push, [entryRemovedFromUsersAround])
                        }
                    }
                })
                .catch(e => {
                    //restore deleted user in case request fails, but only if it existed before
                    if (!!entryRemovedFromUsersAround) {
                        this.$store.dispatch(discoverModule.actions.users.push, [entryRemovedFromUsersAround])
                    }
                });
        }
    },
    created() {
        Vue.use(VModal);
    },
    mounted() {
        $(this.$el).foundation();
        $(document).on('contextmenu', 'img', function() {
            return false;
        })
        $(document).on('contextmenu', 'video', function() {
            return false;
        })
    },
};
