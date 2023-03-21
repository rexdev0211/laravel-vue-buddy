<template>
    <div class="secondary-menu-header">
        <i class="back" @click="goTo('/discover')"></i>
        <div class="title">Settings</div>
    </div>
</template>

<script>
    export default {
        mixins: [
            require('@general/lib/mixin').default
        ]
    }
</script>
