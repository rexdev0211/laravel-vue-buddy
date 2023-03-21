<template>
    <div class="w-views">
        <div class="secondary-menu create-club">
            <div class="secondary-menu-header">
                <i class="back" @click="goTo('/clubs')"></i>
                <div class="title" style="color: #FAFCFA;">{{ trans('clubs.create') }}</div>
            </div>
            <div id="scroll-club" class="secondary-menu-body">
                <!-- <div class="tab-content-wrapper club-category-tabs"
                    :class="{'friends': type === 'friends', 'fun': type === 'fun', 'bang': type === 'bang'}">
                    <div class="tab-content-inner">
                        <div class="tab-content"> -->
                            <ClubCreate v-bind:vars="$data" />
                        <!-- </div>
                    </div>
                </div> -->
            </div>
        </div><!--secondary-menu-->
    </div><!--w-views-->
</template>

<script>
    import {mapGetters, mapState} from 'vuex';

    import ClubCreate from "@clubs/views/widgets/create/ClubCreate";

    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return this.defaultData();
        },
        computed: {
            ...mapState({
                // photos: 'profilePhotos',
                videos: 'profileVideos'
            }),
            ...mapGetters({
                publicPhotos: 'publicPhotos',
            }),
        },
        components: {
            ClubCreate
        },
        methods: {
            defaultData() {
                return {
                    title: '',
                    club_date: '', //this needs moment globally for vee-validate to work
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
                    //selected photos for club
                    selectedPhotos: [],
                    selectedVideos: [],
                    deletedPhotos: [],
                    deletedVideos: [],
                    defaultClubPhoto: 'url("/assets/img/default_180x180.jpg") center / cover',

                    //slim
                    croppers: [],
                    editPhotoId: null,
                    slimOptions: this.defaultSlimOptions(),
                    step4NextDisabled: true,
                    preview_photo: [],
                    textarea: {
                      rows: 3,
                      height: 45,
                      scrollHeight: null,
                      baseScrollHeight: 0,
                    },
                    customToolbar: [
                      ["bold", "italic", "underline"],
                      [{ list: "ordered" }, { list: "bullet" }]
                    ],
                    descriptionMaxLength: 3000
                }
            }
        },
    }
</script>
