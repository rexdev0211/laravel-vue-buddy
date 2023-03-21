<template>
    <div class="proPageSlider">
        <vue2-gesture :type="'swipeLeft'" :call="handleGesture.bind(this, 'swipeLeft')">
            <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">
                <div v-for="(slide, index) in slides"
                                 class="proPageSlide"
                                :style="'background-image: url(\'' + slide.image + '\');'"
                                :class="{'isActive': active == index}">
                    <div class="inner">
                        <div class="title" v-html="slide.title"></div>
                        <div class="descr" v-html="slide.description"></div>
                    </div>
                </div>
            </vue2-gesture>
        </vue2-gesture>
        <div class="proPageSliderDots">
            <div v-for="(slide, index) in slides" @click="goToSlide(index)" class="proPageSliderDot" :class="{'isActive': active == index}"></div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                timeout: null,
                active: 0,
                slides: [
                    {
                        image:       '/images/pro/slide_1.jpg',
                        title:       app.trans('pro_slides_1_title'),
                        description: app.trans('pro_slides_1_description'),
                    },
                    {
                        image:       '/images/pro/slide_2.jpg',
                        title:       app.trans('pro_slides_2_title'),
                        description: app.trans('pro_slides_2_description'),
                    },
                    {
                        image:       '/images/pro/slide_3.jpg',
                        title:       app.trans('pro_slides_3_title'),
                        description: '',
                    },
                    {
                        image:       '/images/pro/slide_4.jpg',
                        title:       app.trans('pro_slides_4_title'),
                        description: app.trans('pro_slides_4_description'),
                    },
                    {
                        image:       '/images/pro/slide_5.jpg',
                        title:       app.trans('pro_slides_5_title'),
                        description: app.trans('pro_slides_5_description'),
                    },
                ],
            }
        },
        computed: {
        },
        methods: {
            sliderTimeout() {
                clearTimeout(this.timeout)

                let v = this
                this.timeout = setTimeout(function() {
                    v.goToNextSlide()
                }, 5000)
            },
            goToSlide(index) {
                this.active = index
                this.sliderTimeout()
            },
            goToNextSlide() {
                this.active = this.active + 1 >= this.slides.length ? 0 : this.active + 1
                this.sliderTimeout()
            },
            goToPreviousSlide() {
                this.active = this.active - 1 < 0 ? this.slides.length - 1 : this.active - 1
                this.sliderTimeout()
            },
            handleGesture(str, e) {
                if (str == 'swipeLeft') this.goToNextSlide()
                if (str == 'swipeRight') this.goToPreviousSlide()
            },
        },
        mounted() {
            this.sliderTimeout()
        },
    }
</script>
