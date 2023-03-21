<template>
    <div class="announce" v-if="isShown">
        <div class="announce-background" @click="closeAnnounce"></div>
        <div class="announce-body">
            <a class="dialog-close" @click="closeAnnounce">
                <svg height="20px" viewBox="0 0 329.26933 329" width="20px" xmlns="http://www.w3.org/2000/svg">
                <path fill="white" d="m194.800781 164.769531 128.210938-128.214843c8.34375-8.339844 8.34375-21.824219 0-30.164063-8.339844-8.339844-21.824219-8.339844-30.164063 0l-128.214844 128.214844-128.210937-128.214844c-8.34375-8.339844-21.824219-8.339844-30.164063 0-8.34375 8.339844-8.34375 21.824219 0 30.164063l128.210938 128.214843-128.210938 128.214844c-8.34375 8.339844-8.34375 21.824219 0 30.164063 4.15625 4.160156 9.621094 6.25 15.082032 6.25 5.460937 0 10.921875-2.089844 15.082031-6.25l128.210937-128.214844 128.214844 128.214844c4.160156 4.160156 9.621094 6.25 15.082032 6.25 5.460937 0 10.921874-2.089844 15.082031-6.25 8.34375-8.339844 8.34375-21.824219 0-30.164063zm0 0"/></svg>
            </a>
            <div class="announce-welcome-back" v-if="type == 'welcome-back'">
                <div class="announce-scrollable" v-if="currentLang == 'de'">
                    <p>
                        <b>Willkommen zurück Buddy!</b><br />
                        <br />
                        Der grüne Datingspass startet endlich wieder durch.<br />
                        Wie gewohnt ohne Werbung, mit heißen Videos und den besten Events & Bangs.
                    </p>
                    <p>
                        <b>Wichtiger Hinweis!</b><br />
                        <br />
                        Beim Brand unseres Cloudserver Anbieters wurden auch die Backups zerstört.<br />
                        Gemeinsam haben wir nichts unversucht gelassen alle Daten zu retten. <br />
                        Nichts zu machen … wir beginnen von Neuem.
                    </p>
                    <p>
                        <b><u>Bitte erstellt euch aus diesem Grund ein neues Profil!</u></b><br />
                        <br />
                        Wählt einen neuen Nick und neue Bilder. <br />
                        Wir entschuldigen uns bei euch mit einem Gratismonat PRO Mitgliedschaft. <br />
                        (Alle Promitglieder schreiben wir gesondert an).
                    </p>
                    <p>
                        Gemeinsam machen wir Buddy wieder zur heissesten Plattform für Dating & Fun.
                    </p>
                    <p>
                        Eure Buddybuilder
                    </p>
                </div>
                <div class="announce-scrollable" v-else>
                    <p>
                        <b>Welcome back Buddy!</b><br />
                        <br />
                        The green dating site is back again.<br />
                        As always, without annoying ads, with hot videos and the best events nearby.
                    </p>
                    <p>
                        <b>Important notice!</b><br />
                        <br />
                        In the fire at our cloud server host, our backups got destroyed, too.<br />
                        We have done everything we could to rescue the data.<br />
                        Not possible ... so we'll make a fresh start.
                    </p>
                    <p>
                        <b><u>Therefore, please create a new profile!</u></b><br />
                        <br />
                        Choose a new nick and new pics.<br />
                        We apologize for this with a free month of PRO membership.<br />
                        (current PRO members will get an email from us).
                    </p>
                    <p>
                        Together, we'll turn BUDDY again into the hottest platform for dating & fun.
                    </p>
                    <p>
                        Your Buddy builders
                    </p>
                </div>
                <a @click="closeAnnounce" href="/register" class="btn green">{{ trans('register_new') }}</a>
            </div>
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                isOpen: false,
                forceClosed: false,
            }
        },
        mixins: [require('@general/lib/mixin').default],
        props: ['type'],
        computed: {
            isShown() {
                if (this.type == 'welcome-back') {
                    /* Disable Welcome Back announce */
                    return false;

                    let alreadySeen = localStorage.getItem('announce-welcome-back-'+this.currentLang)

                    if (alreadySeen === 'true') {
                        return false
                    }

                    return !this.forceClosed
                }
                return this.isOpen
            },
            currentLang(){
                return app.lang
            }
        },
        methods: {
            closeAnnounce() {
                this.isOpen      = false
                this.forceClosed = true

                if (this.type == 'welcome-back') {
                    localStorage.setItem('announce-welcome-back-'+this.currentLang, true)
                }
            }
        },
    }
</script>
