<template>
  <div class="w-root">
    <div class="w-views">
      <div class="secondary-menu share-menu">

        <div class="secondary-menu-header">
          <i class="back" @click="goTo('/discover')"></i>
          <div class="title">{{ trans('share') }}</div>
        </div>

        <div class="secondary-menu-body">
          <div class="shareBlock">
            <ShareNetwork
                v-for="network in networks"
                :network="network.network"
                :key="network.network"
                :style="{backgroundColor: network.color}"
                :url="sharing.url"
                :title="sharing.title"
                :description="sharing.description"
                :quote="sharing.quote"
                :hashtags="sharing.hashtags"
                :twitterUser="sharing.twitterUser"
            >
              <img :src="'/main/img/share/'+network.network+'.png'" align="center"> {{ network.name }}
            </ShareNetwork>
          </div>
          <div style="clear:both;"></div>
        </div>
      </div>
    </div><!--w-views-->
  </div><!--w-root-->
</template>

<style>
.shareBlock {
  padding:30px;
}
.shareBlock a {
  text-decoration: none;
  display: block;
  width:45%;
  padding:5px;
  background:#004646;
  font-weight: bold;
  color:#fff;
  border-radius: 10px;
  margin:5px;
  float:left;
  cursor: pointer;
  opacity: 0.8;
}
.shareBlock a:hover {
  text-decoration: none;
  opacity: 1;
}
.shareBlock img {
  max-width: 32px;
  max-height: 32px;
  margin-right: 10px;
}
</style>

<script>
import VueSocialSharing from 'vue-social-sharing'

export default {
  data () {
    return {
      sharing: {
        url: 'https://'+window.location.hostname,
        title: 'Buddy.net',
        description: 'Buddy.net - Buddies & Benefits',
        quote: 'Buddy.net - Buddies & Benefits',
        hashtags: 'buddy,benefits,buddies',
        twitterUser: ''
      },
      networks: [
        { network: 'whatsapp', name: 'Whatsapp', icon: 'fab fah fa-lg fa-whatsapp', color: '#25d366' },
        { network: 'telegram', name: 'Telegram', icon: 'fab fah fa-lg fa-telegram-plane', color: '#0088cc' },
        { network: 'twitter', name: 'Twitter', icon: 'fab fah fa-lg fa-twitter', color: '#1da1f2' },
        { network: 'messenger', name: 'Messenger', icon: 'fab fah fa-lg fa-facebook-messenger', color: '#0084ff' },
        { network: 'facebook', name: 'Facebook', icon: 'fab fah fa-lg fa-facebook-f', color: '#1877f2' },
        { network: 'skype', name: 'Skype', icon: 'fab fah fa-lg fa-skype', color: '#00aff0' },
        { network: 'sms', name: 'SMS', icon: 'far fah fa-lg fa-comment-dots', color: '#333333' },
        { network: 'email', name: 'Email', icon: 'far fah fa-lg fa-envelope', color: '#333333' },
        // { network: 'baidu', name: 'Baidu', icon: 'fas fah fa-lg fa-paw', color: '#2529d8' },
        // { network: 'buffer', name: 'Buffer', icon: 'fab fah fa-lg fa-buffer', color: '#323b43' },
        // { network: 'evernote', name: 'Evernote', icon: 'fab fah fa-lg fa-evernote', color: '#2dbe60' },
        // { network: 'flipboard', name: 'Flipboard', icon: 'fab fah fa-lg fa-flipboard', color: '#e12828' },
        // { network: 'hackernews', name: 'HackerNews', icon: 'fab fah fa-lg fa-hacker-news', color: '#ff4000' },
        // { network: 'instapaper', name: 'Instapaper', icon: 'fas fah fa-lg fa-italic', color: '#428bca' },
        // { network: 'line', name: 'Line', icon: 'fab fah fa-lg fa-line', color: '#00c300' },
        // { network: 'linkedin', name: 'LinkedIn', icon: 'fab fah fa-lg fa-linkedin', color: '#007bb5' },
        // { network: 'pinterest', name: 'Pinterest', icon: 'fab fah fa-lg fa-pinterest', color: '#bd081c' },
        // { network: 'pocket', name: 'Pocket', icon: 'fab fah fa-lg fa-get-pocket', color: '#ef4056' },
        // { network: 'quora', name: 'Quora', icon: 'fab fah fa-lg fa-quora', color: '#a82400' },
        // { network: 'reddit', name: 'Reddit', icon: 'fab fah fa-lg fa-reddit-alien', color: '#ff4500' },
        // { network: 'stumbleupon', name: 'StumbleUpon', icon: 'fab fah fa-lg fa-stumbleupon', color: '#eb4924' },
        // { network: 'tumblr', name: 'Tumblr', icon: 'fab fah fa-lg fa-tumblr', color: '#35465c' },
        // { network: 'viber', name: 'Viber', icon: 'fab fah fa-lg fa-viber', color: '#59267c' },
        // { network: 'weibo', name: 'Weibo', icon: 'fab fah fa-lg fa-weibo', color: '#e9152d' },
        // { network: 'wordpress', name: 'Wordpress', icon: 'fab fah fa-lg fa-wordpress', color: '#21759b' },
        // { network: 'xing', name: 'Xing', icon: 'fab fah fa-lg fa-xing', color: '#026466' },
        // { network: 'yammer', name: 'Yammer', icon: 'fab fah fa-lg fa-yammer', color: '#0072c6' },
      ]
    }
  },
  mixins: [
    require('@general/lib/mixin').default
  ],
  components: {
    VueSocialSharing,
  },
  created(){
    Vue.use(VueSocialSharing);
  },
}
</script>

