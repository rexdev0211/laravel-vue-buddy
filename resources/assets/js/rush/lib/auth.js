export default {
    login() {
        if (!this.isAuthenticated()) {
            this.logout()
            //window.location = '/'
        } else {
            let token = this.getToken()

            this._setAxiosAuthorizationHeader(token)
        }
    },
    logout() {
        this._forgetCurrentUser()
    },
    isAuthenticated() {
        return this.getToken() != ''
    },
    _getStorage() {
        if (localStorage.getItem('remember_login') == '1') return localStorage
        else return sessionStorage
    },
    _forgetCurrentUser() {
        this._removeAxiosAuthorizationHeader()
        this._cleanStorageTokenUserId()
    },
    _cleanStorageTokenUserId() {
        localStorage.removeItem('token')
        localStorage.removeItem('userId')
        sessionStorage.removeItem('token')
        sessionStorage.removeItem('userId')
    },
    getUserId() {
        return this._getStorage().getItem('userId')
    },
    getToken() {
        return this._getStorage().getItem('token') ? this._getStorage().getItem('token') : ''
    },
    _setAxiosAuthorizationHeader(token) {
        if(!token) {
            token = this.getToken()
        }

        axios.defaults.headers.common['Authorization'] = 'Bearer ' + token
    },
    _removeAxiosAuthorizationHeader() {
        axios.defaults.headers.common['Authorization'] = ''
    },
}
