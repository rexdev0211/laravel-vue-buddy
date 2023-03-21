import mixin from '@general/lib/mixin';

export default e => {
    let video = e.videoData;
    console.log('[Video listener] Signal received', { e })

    if (!('error' in video)) {
        if (video.status === 'processed') {
            store.commit('addVideo', video)
            mixin.methods.showSuccessNotification('video_processed');
        }
        
        // video.error could be "false"!
        // It is still an error without a message
    } else {
        if (!!video.error) {
            store.commit('removeVideo', video)
        }
    }
}
