<template>
    <div>
        <div class="box">
            <div class="headline">{{ trans('taking_break_buddy') }}</div>
            <div class="text">
                <p v-html="trans('deactivate_text_description')"></p>

                <p v-html="trans('delete_profile_text_first')"></p>

                <p v-html="trans('delete_profile_text_second')"></p>
            </div>
        </div>
        <div class="options-box">
            <div class="options">
                <button id="delete" class="btn darker" @click="showDeleteProfileForm">{{ trans('delete') }}</button>
                <button id="deactivate" class="btn" @click="showDeactivateProfileForm">{{ trans('deactivate') }}</button>
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
            showDeleteProfileForm() {
                app.$emit('show-delete-profile-form');
            },
            showDeactivateProfileForm() {
                app.$emit('show-deactivate-profile-form');
            },
        }
    }
</script>