<template>
    <div dusk="discover-filter-modal" class="bb-popup" @click.self="hideDesktopFilter" tabindex="0">
        <div class="bb-popup__container bb-popup__event">
            <div dusk="discover-filter-modal-close" class="bb-popup__close">
                <svg @click="hideDesktopFilter" class="icon icon-close"><use v-bind:xlink:href="symbolsSvgUrl('icon-close')"></use></svg>
            </div>

            <div class="bb-popup__content">
                <div class="row">
                    <div class="small-12 columns">
                        <FiltersForms></FiltersForms>
                    </div><!--col-->
                </div><!--row-->
            </div>
        </div>
    </div>
</template>

<style scoped>
    .w-navbar {
        background-color: var(--bb-darkgrey);
        height: 50px;
    }
    .b-navbar {
        /*top: 5px;*/
    }
    .icon-arrow_back-navbar {
        fill: #aaaaaa !important;
        height: 24px !important;
        width: 24px !important;
        margin-left: 3px !important;
    }
    .b-steps li {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--bb-midgrey);
        margin: 0 1px;
    }
    .b-steps li.is-active {
        background: var(--bb-green);
    }
    .bb-popup__content {
        padding-top: 15px;
    }
</style>

<script>
    import FiltersForms from '@discover/views/widgets/FiltersForms.vue';
    import discoverModule from '@discover/module/store/type';

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            FiltersForms
        },
        methods: {
            hideDesktopFilter() {
                this.$emit('closeFiltersReveal')

                console.log('[Filter] Reset discover')
                app.$emit('reload-discover')
            }
        }
    }
</script>