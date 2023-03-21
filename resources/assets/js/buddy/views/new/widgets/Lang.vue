<template>
    <div class="languages">
        <div class="chosen-language">{{ getLangCaption(languages[currentLang]) }}</div>
        <ul id="languages">
            <template v-for="(language, code) in languages">
                <li v-if="code != currentLang">
                    <a @click="setAppLanguage(code)">{{ getLangCaption(language) }}</a>
                </li>
            </template>
        </ul>
    </div>
</template>

<script>
    export default {
        data: () => ({
            languages: {
                en: 'english',
                de: 'german',
                fr: 'french',
                it: 'italian',
                nl: 'dutch',
                es: 'spanish',
                pt: 'portuguese'
            }
        }),
        mixins: [require('@general/lib/mixin').default],
        computed: {
            currentLang(){
                return app.lang
            }
        },
        methods: {
            getLangCaption(lang) {
                if (this.isMobile) {
                    return this.trans(`${lang}_short`)
                } else {
                    return this.trans(`${lang}`)
                }
            }
        }
    }
</script>

<style scoped>

</style>