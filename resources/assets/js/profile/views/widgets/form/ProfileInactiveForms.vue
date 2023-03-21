<template>
    <div>
        <div class="box">
            <div class="text">
                <p v-html="trans('account_is_deactivated')"></p>
            </div>
        </div>
        <div class="options-box">
            <div class="options">
                <button @click="logout" class="btn darker" v-html="trans('leave')"></button>
                <button id="activate_yes" @click="activate" class="btn" v-html="trans('activate')"></button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mixins: [
            require('@general/lib/mixin').default,
        ],
        methods: {
            activate() {
                let callback = () => {
                    return axios.post('/api/account/activate', {})
                        .then((response) => {
                            if(response.data == 'active') {
                                this.$store.commit('updateUser', {status: 'active'});

                                this.goTo('/discover')
                            }
                        })
                };

                this.runLoadingFunction('#activate_yes', callback);
            },
        }
    }
</script>