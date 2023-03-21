<template>
    <div class="edit-bang-page w-views">
        <div class="w-view">

            <div class="w-pages">
                <div class="b-page">
                        <div class="row">
                            <div class="small-12">
                                <div class="w-navbar">
                                    <div class="b-navbar centered">
                                        <div class="navbar-left">
                                            <button class="b-btn__icon" type="button" @click="goTo('/events')">
                                                <svg class="icon icon-arrow_back-navbar"><use v-bind:xlink:href="symbolsSvgUrl('icon-arrow_back')"></use></svg>
                                            </button>
                                        </div>
                                        <div class="navbar-right"></div>
                                    </div><!--b-navbar-->
                                </div><!--w-navbar-->

                                <BangForm/>
                            </div><!--col-->
                        </div><!--row-->
                </div><!--b-page-->
            </div><!--w-pages-->

        </div><!--w-view-->
    </div><!--w-views-->
</template>

<script>
    import BangForm from "@events/views/widgets/bang/Form.vue";

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            BangForm
        }
    }
</script>
