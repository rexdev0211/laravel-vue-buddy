<template>
    <div class="secondary-menu create-event" v-if="event.visible" @click.self="closeEvent" tabindex="0">
        <div class="secondary-menu-header">
            <i class="back" @click="closeEvent"></i>
            <div class="title">{{ trans('create_new_event') }}</div>
        </div>
        <vue-custom-scrollbar ref="vueCustomScrollbar" :settings="scrollBarSettings" id="scroll-event" class="secondary-menu-body">
            <div class="tab-content-wrapper event-category-tabs"
                :class="{'friends': type === 'friends', 'fun': type === 'fun', 'bang': type === 'bang'}">
                <div class="tab-content-inner">
                    <div class="tab-content">
                        <EventCreate v-bind:vars="$data" :aboutEvents="aboutEvents"/>
                    </div>
                </div>
            </div>
        </vue-custom-scrollbar>
    </div>
</template>

<script>
    import {mapGetters, mapState, mapActions} from 'vuex';
    import eventsModule from '@events/module/store/type'

    import EventCreate from "@events/views/widgets/event/create/EventCreate";

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-events').default,
        ],
        data() {
           return this.defaultData()
        },
        components: {
            EventCreate,
            vueCustomScrollbar
        },
        computed: {
            ...mapGetters({
                event: eventsModule.getters.event,
                publicPhotos: 'publicPhotos',
                publicVideos: 'publicVideos'
            }),
            aboutEvents() {
              return [
                  {
                    title: 'Guide',
                    description: this.trans('events.about_guide')
                  },
                  {
                    title: this.trans('events.type.fun'),
                    description: this.trans('events.about_fun')
                  },
                  {
                    title: this.trans('events.type.bang'),
                    description: this.trans('events.about_bang')
                  }
              ]
            },
            selectedPhotosArray() {
                let photosIds = this.publicPhotos.map(function (e) {
                    return e.id;
                }, []);

                return this.selectedPhotos = photosIds
            },
            selectedVideosArray() {
                let videoIds = this.publicVideos.map(function (e) {
                    return e.id;
                }, []);
                return this.selectedVideos = videoIds;
            }
        },
        mounted () {
          app.$on('show-scroll', this.showScroll);
        },
        methods: {
          showScroll(slot) {
            this.scrollBarSettings.suppressScrollY = !slot;
          },
          defaultData() {
            return {
                title: '',
                event_date: '', //this needs moment globally for vee-validate to work
                time: '',
                description: ``,
                location: '',
                address_type: 'full_address',
                address: '',
                addressShow: '',
                addressError: false,
                tags: [],
                newTag: "",
                type: '',
                chemsfriendly: false,
                is_profile_linked: false,
                date: '',

                website: '',
                venue: '',
                name: '',
                contact: '',
                note: '',

                lat: 52.520389,
                lng: 13.40424,

                locality: 'Berlin',
                state: 'Berlin',
                country: 'Germany',
                country_code: 'DE',

                //inner components variables
                mapVisible: null,
                mapFirstTime: true,
                //selected photos for event
                selectedPhotos: [],
                selectedVideos: [],
                defaultEventPhoto: 'url("/assets/img/default_180x180.jpg") center / cover',
                deletedPhotos: [],
                deletedVideos: [],
                photos: [],

                //slim
                croppers: [],
                editPhotoId: null,
                slimOptions: this.defaultSlimOptions(),
                step4NextDisabled: true,
                preview_photo: [],
                textarea: {
                  rows: 1,
                  height: 45,
                  scrollHeight: null,
                  baseScrollHeight: 0,
                },
                customToolbar: [
                  ["bold", "italic", "underline"],
                  [{ list: "ordered" }, { list: "bullet" }]
                ],
                descriptionMaxLength: 3000,

                scrollBarSettings: {
                  suppressScrollY: false,
                  suppressScrollX: true
                }
            }
          }
        }
    }
</script>
