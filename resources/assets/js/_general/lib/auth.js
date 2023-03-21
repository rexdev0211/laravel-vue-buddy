import * as echoListener from '../../buddy/lib/echoListener';

export default {
    login(username, password, remember) {
        let data = {
            grant_type: 'password',
            client_id: 2,
            client_secret: 'nzVQUaVjdmYRVSbPRcosrv9emxvuxaEdQemoNZRi',
            username: username,
            password: password
        };

        return axios.post('/oauth/token', data)
            .then(response => {
                let data = response.data
                let token = data.access_token
                this._setAxiosAuthorizationHeader(token)

                //load profile on login
                return store.dispatch('loadCurrentUserInfo').then(() => {
                    let userId = store.state.profile.id
                    if(! _.isUndefined(userId)) {
                        this._rememberUser(token, userId, remember)
                        // console.log('[Poll] User logged in. Profile loaded. Start polling.')
                        // store.dispatch('enableUserOnlineStatusPolling')
                        return true
                    }
                    throw new Error('Something went wrong')
                })
            })
            .catch(error => {
                this.logout();
                let output;
                if (error.response && error.response.data && error.response.data.message) {
                    if (error.response.data.error && error.response.data.error == 'invalid_credentials') {
                        output = app.trans(error.response.data.error);
                    } else {
                        output = error.response.data.message;
                    }
                } else {
                    output = error.message;
                }
                return Promise.reject(output);
            });
    },
    logout() {
        //app.log('called logout!');
        this._forgetCurrentUser();
    },
    isAuthenticated() {
        return this.getToken() != '';
    },


    //TODO: perhaps store token in a safer place
    // https://stormpath.com/blog/where-to-store-your-jwts-cookies-vs-html5-web-storage
    // https://stormpath.com/blog/token-auth-spa
    // https://auth0.com/blog/ten-things-you-should-know-about-tokens-and-cookies/
    // https://github.com/IdentityServer/IdentityServer3/issues/2039
    // https://auth0.com/docs/security/store-tokens
    // https://auth0.com/blog/ten-things-you-should-know-about-tokens-and-cookies/

    _getStorage() {
        if(localStorage.getItem('remember_login') == '1') {
            return localStorage
        } else {
            return sessionStorage
        }
    },
    _rememberUser(token, userId, remember) {
        console.log('[_rememberUser]', { token, userId, remember })

        localStorage.setItem('remember_login', remember ? '1' : '0');
        this._cleanStorageTokenUserId();

        this._setAxiosAuthorizationHeader(token);
        this._getStorage().setItem('token', token);
        // this._getStorage().setItem('user', JSON.stringify(user));
        this._getStorage().setItem('userId', userId);

        echoListener.initEchoListeners();
    },
    _forgetCurrentUser() {
        echoListener.stopListeningForMessages();
        store.commit('logout');

        console.log('[Poll] User logged out. Stop polling.')
        store.dispatch('disableUserOnlineStatusPolling')

        this._removeAxiosAuthorizationHeader();
        this._cleanStorageTokenUserId();
    },
    _cleanStorageTokenUserId() {
        localStorage.removeItem('token');
        localStorage.removeItem('userId');
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('userId');
        sessionStorage.removeItem('loginAs');
    },
    getUserId() {
        console.log('[getUserId]', {
            storage: this._getStorage(),
            userId: this._getStorage().getItem('userId')
        })
        return parseInt(this._getStorage().getItem('userId'));
    },
    getToken() {
        return this._getStorage().getItem('token') ? this._getStorage().getItem('token') : '';
    },
    _setAxiosAuthorizationHeader(token) {
        if(!token) {
            token = this.getToken();
        }

        axios.defaults.headers.common['Authorization'] = 'Bearer ' + token

        let loginAsActivated = sessionStorage.getItem('loginAs')
        console.log('[_setAxiosAuthorizationHeader] ', { loginAsActivated })
        if (loginAsActivated === 'true') {
            console.log('[_setAxiosAuthorizationHeader] ', { loginAsActivated })
            axios.defaults.headers.common['X-Login-As'] = 'true';
        }
    },
    _removeAxiosAuthorizationHeader() {
        axios.defaults.headers.common['Authorization'] = '';
        axios.defaults.headers.common['X-Login-As'] = '';
    },
    _hasAxiosAuthorizationHeader() {
        return ! _.isEmpty(axios.defaults.headers.common.Authorization);
    }
}