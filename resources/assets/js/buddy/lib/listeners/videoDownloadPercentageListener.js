import mixin from '@general/lib/mixin';

export default e => {
    let video = e;
    console.log('[Video Percentage]', { video })

    if (!('error' in video)) {
        store.commit('updateVideoPercentage', video)

    } else {
        store.commit('removeVideo', video)
        mixin.methods.showErrorNotification(video.error);
    }
}
