import mixin from '@general/lib/mixin';

export default e => {
    let message = e.messageData.message;

    mixin.methods.showErrorNotification(message);
}
