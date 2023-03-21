import Echo from 'laravel-echo';
import auth from '@general/lib/auth';

import newMessageReceivedListener from '@chat/lib/listeners/newMessageReceivedListener'
import newEventMessageReceivedListener from '@chat/lib/listeners/newEventMessageReceivedListener'
import updateMessageSentListener from '@chat/lib/listeners/updateMessageSentListener'
import updateMessageReceivedListener from '@chat/lib/listeners/updateMessageReceivedListener'
import CheckMessageListener from "@chat/lib/listeners/CheckMessageListener";
import UserBlockedListener from "@chat/lib/listeners/UserBlockedListener";

import newNotificationReceivedListener from '@notifications/lib/listeners/newNotificationReceivedListener'
import newVisitorReceivedListener from '@notifications/lib/listeners/newVisitorReceivedListener'
import eventMembershipUpdated from '@events/lib/listeners/eventMembershipUpdateListener'

import userEventListener from './listeners/userEventListener'
import videoProcessedListener from './listeners/videoProcessedListener'
import refreshDataRequestListener from './listeners/refreshDataRequestListener'
import showErrorNotificationListener from './listeners/showErrorNotificationListener'
import userSuspendedListener from './listeners/userSuspendedListener'

import eventsModule from '@events/module/store/type';
import videoDownloadPercentageListener from "./listeners/videoDownloadPercentageListener";

(function createEchoInstance() {
    let token = 'not_defined_yet';
    console.log('[Echo] createEchoInstance')

    if (
        typeof io !== 'undefined'
        &&
        typeof window.Echo == 'undefined'
    ) {
        window.Echo = new Echo({
            broadcaster: 'socket.io',
            host: window.CHAT_DOMAIN + ':' + window.NODEJS_PORT,
            auth: {headers: {Authorization: 'Bearer ' + token}}
        });
    }
})();

export const initEchoListeners = () => {
    console.log('[Echo] initEchoListeners')

    if(!auth.isAuthenticated()) {
        console.log("[Echo] Can't listen because I'm unauthenticated");
        return;
    }

    if(typeof window.Echo == 'undefined') {
        console.log("[Echo] Can't listen because Echo is not defined");
        return;
    }

    let userId = auth.getUserId();
    let token = auth.getToken();

    window.Echo.connector.options.auth.headers.Authorization = 'Bearer ' + token;

    console.log('[Echo] Listening to channel-' + userId);
    window.Echo
        .private('channel-' + userId)
        .listen('UpdateMessageSent', updateMessageSentListener)
        .listen('UpdateMessageReceived', updateMessageReceivedListener)
        .listen('NewMessageReceived', newMessageReceivedListener)
        .listen('NewEventMessageReceived', newEventMessageReceivedListener)
        .listen('NewNotificationReceived', newNotificationReceivedListener)
        .listen('UserEvent', userEventListener)
        .listen('UserSuspended', userSuspendedListener)
        .listen('VideoProcessed', videoProcessedListener)
        .listen('VideoDownloadPercentage', videoDownloadPercentageListener)
        .listen('ShowErrorNotification', showErrorNotificationListener)
        .listen('NewVisitorReceived', newVisitorReceivedListener)
        .listen('RefreshDataRequest', refreshDataRequestListener)
        .listen('EventMembershipUpdated', eventMembershipUpdated)
        .listen('CheckMessage', CheckMessageListener)
        .listen('UserBlocked', UserBlockedListener)

    console.log('[Echo] Listening to channel-event-membership');
    window.Echo
        .private('channel-event-membership')
        .listen('EventMembershipUpdated', eventMembershipUpdated)
};

export const stopListeningForMessages = () => {
    if(typeof window.Echo == 'undefined') {
        console.log("[Echo] Can't stop listening because Echo is not defined");
        return;
    }

    let userId = auth.getUserId();

    window.Echo.leave('channel-'+userId);
    console.log('[Echo] Leaved channel'+userId);

    window.Echo.leave('channel-event-membership');
    console.log('[Echo] Leaved channel channel-event-membership');

    store.dispatch(eventsModule.actions.membership.unsubscribeFromAll)
};
