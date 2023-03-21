<template>
    <div>
        <div class="box">
            <div class="headline">{{ page.title }}</div>
            <div class="text">
                <p class="cancel-sub-content" v-html="page.content"></p>
            </div>
        </div>
        <div v-if="profile.issuer === 'flexpay'">
            <div class="box">
                <div class="headline">Flexpay subscription</div>
            </div>
            <div class="options-box">
                <div class="options">
                    <button @click="showCancelSubscriptionForm" class="btn darker" v-html="trans('cancel_pro')"></button>
                    <button @click="goBack('/discover')" class="btn" v-html="trans('leave')"></button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        data: () => ({
            page: {
                title: null,
                content: null,
            }
        }),
        mixins: [
            require('@general/lib/mixin').default,
        ],
        computed: {
            ...mapState([
                'profile'
            ])
        },
        methods: {
            showCancelSubscriptionForm() {
                app.$emit('show-cancel-subscription-form');
            },
        },
        async created() {
            let response = await axios.get('/api/getStaticPage/' + app.lang + '/cancel')
            if (response.status === 200) {
                this.page = response.data
            } else {
                this.page = {
                    title: '404',
                    content: 'Page not found'
                }
            }
        }
    }
</script>