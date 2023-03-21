import mixin from '@general/lib/mixin'

export default e => {
    console.log('[Refresh listener] Signal received', e)
    store.dispatch('loadCurrentUserInfo', true)
}
