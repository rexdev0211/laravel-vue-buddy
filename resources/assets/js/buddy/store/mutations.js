import _ from "lodash";
import mixin from '@general/lib/mixin';
import { eventPhotosSort } from '@buddy/lib/helpers'
import { initialState } from '@buddy/store'

const mutations = {
    setHealthAlert(state, value){
        state.healthAlert = value
    },
    updateSidebarVisibility(state, { index, value }){
        state.sidebar[index].visible = value
    },
    updateDialog(state, payload){
        state.dialog = {...state.dialog, ...payload}
    },
    setModal(state, payload){
        state.modal = {...state.modal, ...payload}
    },
    setUser(state, profile) {
        state.profile = profile;
    },
    setSupportUser(state, user) {
        state.supportUser = user;
    },
    clearBlockedUsers(state) {
        state.profileBlockedUsers = [];
    },
    setBlockedUsers(state, blockedUsers) {
        state.profileBlockedUsers.push(...blockedUsers);
    },
    setHaveUnblockedUsers(state, value) {
        state.haveUnblockedUsers = value;
    },
    updateBlockedUsers(state, blockedUserId) {
        let index = state.profileBlockedUsers.findIndex((user) => {return user.id === blockedUserId})

        if (index !== -1) {
            Vue.delete(state.profileBlockedUsers, index);
        }
    },
    setPrepUser(state, user) {
        state.prepUser = user;
    },
    updateUser (state, object) {
        for (let key in object) {
            if (object.hasOwnProperty(key)) {
                Vue.set(state.profile, key, object[key])
            }
        }
    },
    setProfileOptions(state, options) {
        state.profileOptions = options;
    },
    setProfilePhotos(state, photos) {
        state.profilePhotos = photos;
    },
    setProfileVideos(state, videos) {
        state.profileVideos = videos;
    },
    setMyEvents(state, events) {
        state.myEvents = events
    },
    setInvitationsToBang(state, invitations) {
        state.invitationsToBang = invitations
    },
    setInvitationsToClub(state, invitations) {
        state.invitationsToClub = invitations
    },
    addMyEvents(state, events) {
        state.myEvents.push(...events)
    },
    removeFromMyEvents(state, eventId) {
        let eventIndex = state.myEvents.findIndex(el => el.id == eventId)
        if (eventIndex !== -1) {
            console.log('[removeFromMyEvents]', { eventIndex })
            Vue.delete(state.myEvents, eventIndex)
        }
    },
    removeFromMyClubs(state, clubId) {
        let clubIndex = state.myClubs.findIndex(el => el.id == clubId)
        if (clubIndex !== -1) {
            console.log('[removeFromMyClubs]', { clubIndex })
            Vue.delete(state.myClubs, clubIndex)
        }
    },
    updateMyEvent(state, eventData) {
        // My events
        let myEventsIndex = state.myEvents.findIndex(v => v.id == eventData.id)
        if (myEventsIndex !== -1) {
            Vue.set(
                state.myEvents,
                myEventsIndex,
                {...state.myEvents[myEventsIndex], ...eventData}
            )
        }
    },
    updateMyClub(state, clubData) {
        // My clubs
        let myClubsIndex = state.myClubs.findIndex(v => v.id == clubData.id)
        if (myClubsIndex !== -1) {
            Vue.set(
                state.myClubs,
                myClubsIndex,
                {...state.myClubs[myEventsIndex], ...clubData}
            )
        }
    },
    addPhoto(state, photo) {
        state.profilePhotos.unshift(photo);
    },
    removeVideo(state, video) {
        let videoIndex = state.profileVideos.findIndex(el => el.hash === video.hash)
        if (videoIndex !== -1) {
            Vue.delete(state.profileVideos, videoIndex)
        }
    },
    addVideo(state, video) {
        let videoIndex = state.profileVideos.findIndex(el => el.hash === video.hash)
        if (videoIndex !== -1) {
            Vue.set(state.profileVideos, videoIndex, video)
        } else {
            state.profileVideos.unshift(video)
        }
    },
    updateVideo(state, payload) {
        let index = state.profileVideos.findIndex(el => el.id === payload.newVideo.id);
        if (index !== -1) {
            Vue.set(state.profileVideos, index, payload.newVideo);
        }
    },
    updateVideoPercentage(state, payload) {
        let index = state.profileVideos.findIndex(el => el.hash === payload.hash);
        if (index !== -1) {
            Vue.set(state.profileVideos, index, payload);
        }
    },
    updatePhoto(state, { photoId, data }) {
        let photoIndex = state.profilePhotos.findIndex(el => el.id === photoId)
        if (photoIndex !== -1) {
            Vue.set(state.profilePhotos, photoIndex, data)
        }
    },
    logout(state) {
        state = _.cloneDeep(initialState)
    },
    setOnlineFavorites(state, favorites) {
        state.onlineFavorites = favorites;
    },
    setFavoritesCount(state, favoritesCount) {
        state.favoritesCount = favoritesCount
    },
    setBlockedCount(state, blockedCount) {
        state.blockedCount = blockedCount
    },
    setBlockedUsersIds(state, blockedUsersIds) {
        state.blockedUsersIds = blockedUsersIds
    },
    updateOnlineFavorites(state, favorites) {
        state.onlineFavorites.forEach(favUser => {
            const newUser = favorites.find(el => el.id == favUser.id);

            favUser.distanceMeters = newUser.distanceMeters ;
            //favUser.last_active = mixin.methods.convertUtcToLocal(newUser.last_active);
            favUser.isOnline = newUser.isOnline ;
            favUser.wasRecentlyOnline = newUser.wasRecentlyOnline;
        })
    },
    /**
     * set discreet mode status
     */
    setDiscreetMode(state, status) {
        state.discreetMode = status
    },
    /**
     * set user is pro status
     */
    setUserIsPro(state, status) {
        state.userIsPro = status
    },
    /**
     * set user latest used widget
     */
    setLatestWidget(state, status) {
        state.latestWidget = status
    },
    /**
     * open profile edit
     */
    openProfileEdit(state) {
        state.profileEditOpened         = true

        state.profileLocationOpened     = false
        state.profilePhotosOpened       = false
        state.profileVideosOpened       = false
        state.profileSettingsOpened     = false
        state.profileShareOpened        = false
        state.profileDeactivationOpened = false
        state.customizeSharingLinksOpened = false
        state.deleteAllSharingLinksOpened = false
        state.profileHelpOpened         = false
    },
    /**
     * close profile edit
     */
    closeProfileEdit(state) {
        state.profileEditOpened = false
    },
    /**
     * open location menu from profile sidebar
     */
    openProfileLocation(state) {
        state.profileLocationOpened     = true

        state.profileEditOpened         = false
        state.profilePhotosOpened       = false
        state.profileVideosOpened       = false
        state.profileSettingsOpened     = false
        state.profileShareOpened        = false
        state.profileDeactivationOpened = false
        state.customizeSharingLinksOpened = false
        state.deleteAllSharingLinksOpened = false
        state.profileHelpOpened         = false
    },
    /**
     * close location menu from profile sidebar
     */
    closeProfileLocation(state) {
        state.profileLocationOpened = false
    },
    /**
     * open photos menu from profile sidebar
     */
    openProfilePhotos(state) {
        state.profilePhotosOpened = true

        state.profileEditOpened         = false
        state.profileLocationOpened     = false
        state.profileVideosOpened       = false
        state.profileSettingsOpened     = false
        state.profileShareOpened        = false
        state.profileDeactivationOpened = false
        state.customizeSharingLinksOpened = false
        state.deleteAllSharingLinksOpened = false
        state.profileHelpOpened         = false
    },
    /**
     * close photos menu from profile sidebar
     */
    closeProfilePhotos(state) {
        state.profilePhotosOpened = false
    },
    /**
     * open videos menu from profile sidebar
     */
    openProfileVideos(state) {
        state.profileVideosOpened = true

        state.profileEditOpened         = false
        state.profileLocationOpened     = false
        state.profilePhotosOpened       = false
        state.profileSettingsOpened     = false
        state.profileShareOpened        = false
        state.profileDeactivationOpened = false
        state.customizeSharingLinksOpened = false
        state.deleteAllSharingLinksOpened = false
        state.profileHelpOpened         = false
    },
    /**
     * close videos menu from profile sidebar
     */
    closeProfileVideos(state) {
        state.profileVideosOpened = false
    },
    /**
     * open settings menu from profile sidebar
     */
    openProfileSettings(state) {
        state.profileSettingsOpened = true

        state.profileEditOpened         = false
        state.profileLocationOpened     = false
        state.profilePhotosOpened       = false
        state.profileVideosOpened       = false
        state.profileShareOpened        = false
        state.profileDeactivationOpened = false
        state.customizeSharingLinksOpened = false
        state.deleteAllSharingLinksOpened = false
        state.profileHelpOpened         = false
    },
    /**
     * close settings menu from profile sidebar
     */
    closeProfileSettings(state) {
        state.profileSettingsOpened = false
    },


    /**
     * open share menu from profile sidebar
     */
    openProfileShare(state) {
        state.profileShareOpened = true

        state.profileEditOpened         = false
        state.profileLocationOpened     = false
        state.profilePhotosOpened       = false
        state.profileSettingsOpened     = false
        state.profileDeactivationOpened = false
        state.customizeSharingLinksOpened = false
        state.deleteAllSharingLinksOpened = false
        state.profileHelpOpened         = false
    },
    /**
     * close settings menu from profile sidebar
     */
    closeProfileShare(state) {
        state.profileShareOpened = false
    },

    /**
     * open deactivation menu from profile settings
     */
    openProfileDeactivation(state) {
        state.profileDeactivationOpened = true
        state.deleteAllSharingLinksOpened = false
        state.customizeSharingLinksOpened = false
    },

    /**
     * open openCustomizeSharingLinks menu from profile settings
     */
    openCustomizeSharingLinks(state) {
        state.customizeSharingLinksOpened = true
        state.deleteAllSharingLinksOpened = false
    },

    /**
     * open delete all sharing links menu from profile settings
     */
    openDeleteAllSharingLinks(state) {
        state.deleteAllSharingLinksOpened = true
        state.profileDeactivationOpened = false
        state.customizeSharingLinksOpened = false
    },

    /**
     * close deactivation menu from profile settings
     */
    closeProfileDeactivation(state) {
        state.profileDeactivationOpened = false
    },

    /**
     * close delete all sharing links menu from profile settings
     */
    closeDeleteAllSharingLinks(state) {
        state.deleteAllSharingLinksOpened = false
    },

    /**
     * close openProfileDeactivation menu from profile settings
     */
    closeCustomizeSharingLinks(state) {
        state.customizeSharingLinksOpened = false
    },

    /**
     * open help menu from profile sidebar
     */
    openProfileHelp(state) {
        state.profileHelpOpened = true

        state.profileEditOpened         = false
        state.profileLocationOpened     = false
        state.profilePhotosOpened       = false
        state.profileVideosOpened       = false
        state.profileShareOpened        = false
        state.profileDeactivationOpened = false
        state.customizeSharingLinksOpened = false
        state.deleteAllSharingLinksOpened = false
        state.profileSettingsOpened     = false
    },
    /**
     * close help menu from profile sidebar
     */
    closeProfileHelp(state) {
        state.profileHelpOpened = false
    },
    closeAllProfilePages(state) {
        state.profileHelpOpened = false
        state.profileEditOpened = false
        state.profileLocationOpened = false
        state.profilePhotosOpened = false
        state.profileVideosOpened = false
        state.profileDeactivationOpened = false
        state.customizeSharingLinksOpened = false
        state.deleteAllSharingLinksOpened = false
        state.profileSettingsOpened = false
        state.profileShareOpened = false
    },
    /**
     * show Requirements Alert modal
     */
    showRequirementsAlert(state, type) {
        state.requirementsAlert.isShow      = true
        state.requirementsAlert.type        = type
        state.requirementsAlert.image       = '/images/splash/'+ type + '.svg'
        state.requirementsAlert.title       = app.trans('splash_' + type + '_title')
        state.requirementsAlert.description = app.trans('splash_' + type + '_description')
        state.requirementsAlert.button      = type == 'change_settings' ? app.trans('change_settings') : app.trans('upgrade_now')
        state.requirementsAlert.withProIcon = type == 'change_settings' ? false : true

        switch (type) {
            case 'change_settings':
                state.requirementsAlert.imageStyle = 'width: 50%'
                break;

            case 'media':
                state.requirementsAlert.imageStyle = 'margin: 30px auto; width: 75%'
                break;

            case 'deletemsg':
                state.requirementsAlert.imageStyle = 'margin: 0 auto 24px; width: 100%'
                break;

            case 'filters':
                state.requirementsAlert.imageStyle = 'margin: 0 auto; width: 71%'
                break;

            case 'favorites':
                state.requirementsAlert.imageStyle = 'margin: 40px auto 25px; width: 50%'
                break;

            case 'visitors':
                state.requirementsAlert.imageStyle = 'margin: 48px auto 25px; width: 60%'
                break;

            case 'events':
                state.requirementsAlert.imageStyle = 'margin: 35px auto 20px; width: 46%'
                break;

            case 'clubs':
                state.requirementsAlert.imageStyle = 'margin: 35px auto 20px; width: 46%'
                break;
    
            default:
                state.requirementsAlert.imageStyle = 'width: 44%'
                break;
        }
    },
    /**
     * hide Requirements Alert modal
     */
    hideRequirementsAlert(state) {
        state.requirementsAlert.isShow = false
    },
    /**
     * show Announce Alert modal
     */
    showAnnounceAlert(state, data) {
        state.announceAlert.isShow  = true
        state.announceAlert.message = data.message
    },
    /**
     * hide Announce Alert modal
     */
    hideAnnounceAlert(state) {
        state.announceAlert.isShow = false
    },
    setTapped(state) {
        state.isTapped = true
    },
    setMyTaps(state, taps) {
        taps.forEach(el => {
            el.idate = mixin.methods.convertUtcToLocal(el.idate);
        });
        state.myTaps = taps;
    },
	addMyTap(state, tap) {
        state.myTaps.push(tap)
    },
	setLocationUpdating(state, value) {
        state.locationUpdating = value
    },
    setSharingUrl(state, value) {
        state.sharingUrl = value;
    }
}

export default mutations
