import notificationsModule from '@notifications/module/store/type';

const getters = {
    userProfile: state => {
        return state.profile
    },
    publicPhotos: state => {
        return state.profilePhotos.filter(p => p.visible_to === 'public' && p.is_default === 'no');
    },
    privatePhotos: state => {
        return state.profilePhotos.filter(p => p.visible_to === 'private');
    },
    publicVideos: state => {
        return state.profileVideos.filter(p => p.visible_to === 'public');
    },
    privateVideos: state => {
        return state.profileVideos.filter(p => p.visible_to === 'private' || !p.id);
    },
    publicPhotosCount: (state, getters) => {
        return getters.publicPhotos.length;
    },
    publicVideosCount: (state, getters) => {
        return getters.publicVideos.length;
    },
    defaultPhoto: (state, getters) => {
        return getters.publicPhotos.find(v => v.is_default == 'yes');
    },
    leftPublicPicturesCount: (state, getters) => {
        const maxAmount = app.maxPublicPicturesAmount;
        const publicPhotos = getters.publicPhotos;
        return (publicPhotos.length >= maxAmount) ? 0 : (maxAmount-publicPhotos.length);
    },
    leftPublicVideosCount: (state, getters) => {
        const maxAmount = app.maxPublicVideosAmount;
        const publicVideos = getters.publicVideos;
        return (publicVideos.length >= maxAmount) ? 0 : (maxAmount-publicVideos.length);
    },
    unitSystem: state => {
        return state.profile.unit_system;
    },
    profileOptions: state => {
        return state.profileOptions;
    },
    sortedProfilePhotos: state => {
        let arr = state.profilePhotos.slice();
        console.log('[Getter] sortedProfilePhotos', { arr })
        return arr.sort((a, b) => {
            if (a.is_default == 'yes') {
                return -1;
            }
            if (b.is_default == 'yes') {
                return 1;
            }
            return b.id - a.id;
        });
    },
    sortedProfileVideos: state => {
        let arr = state.profileVideos.slice();
        console.log('[Getter] sortedProfileVideos', { arr })
        return arr.sort((a, b) => {
            return b.id - a.id;
        });
    },
    discreetModeEnabled(state) {
        return state.discreetMode
    },
    getUser: state => userId => {
        const userById = state.usersInfo[userId]
        if (userById) {
            return userById
        }

        for (let key in state.usersInfo) {
            if (
                state.usersInfo.hasOwnProperty(key)
                &&
                state.usersInfo[key].link === userId
            ) {
                return state.usersInfo[key]
            }
        }

        return null
    },
    getEvent: state => eventId => {
        return state.eventsInfo[eventId] || null
    },
    getInvitationsToBang: state => {
        return state.invitationsToBang || null
    },
    getClub: state => clubId => {
        return state.clubsInfo[clubId] || null
    },
    getInvitationsToClub: state => {
        return state.invitationsToClub || null
    },
    userHasNewMessages: state => {
        return state.profile.has_new_messages
    },
    userHasNotifications: (state) => {
        return state.profile.has_notifications
    },
    userHasEventNotifications: (state) => {
        return state.profile.has_event_notifications
    },
    userHasClubNotifications: (state) => {
        return state.profile.has_club_notifications
    },
    userHasNewVisitors: state => {
        return state.profile.has_new_visitors
    },
    userHasNewNotifications: state => {
        return state.profile.has_new_notifications
    },
    profileEditOpened: state => {
        return state.profileEditOpened
    },
    profileLocationOpened: state => {
        return state.profileLocationOpened
    },
    profilePhotosOpened: state => {
        return state.profilePhotosOpened
    },
    profileVideosOpened: state => {
        return state.profileVideosOpened
    },
    profileSettingsOpened: state => {
        return state.profileSettingsOpened
    },
    profileShareOpened: state => {
        return state.profileShareOpened
    },
    profileDeactivationOpened: state => {
        return state.profileDeactivationOpened
    },
    customizeSharingLinksOpened: state => {
        return state.customizeSharingLinksOpened
    },
    deleteAllSharingLinksOpened: state => {
        return state.deleteAllSharingLinksOpened
    },
    profileHelpOpened: state => {
        return state.profileHelpOpened
    },
    sharingUrl: state => {
        return state.sharingUrl
    }
}

export default getters