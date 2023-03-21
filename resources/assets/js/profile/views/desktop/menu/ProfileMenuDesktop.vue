<template>
    <aside class="w-sidenav">
        <div class="b-sidenav b-sidenav--profile">
            <ProfileMenu :tab="tab"/>
        </div><!--b-sidenav-->
    </aside><!--w-sidenav-->
</template>

<script>
    import ProfileMenu from "@profile/views/widgets/ProfileMenu.vue";

    export default {
        props: ['tab'],
        components: {
            ProfileMenu
        }
    }
</script>