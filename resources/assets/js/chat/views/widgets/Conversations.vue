<template>
	<div>
		<div class="chat-buttons">
			<button class="bb-button-grey" @click="selectConversationsTab('all')" :class="{'active': activeConversationGroup === 'all'}">{{ trans('all') }}</button>
			<button class="bb-button-grey" @click="selectConversationsTab('unread')" :class="{'active': activeConversationGroup === 'unread'}">{{ trans('unread') }}</button>
			<button class="bb-button-grey" @click="selectConversationsTab('favorites')" :class="{'active': activeConversationGroup === 'favorites'}">{{ trans('favorites') }}</button>
		</div>
		
		<ul class="b-chat__list" id="b-chat__chat">
			<li v-for="conversation in conversations">
				<a v-if="conversation.chatType === 'user'" href="javascript:void(0)" @click="startUserConversation(conversation.interlocutor.id)" class="b-chat__link-item">
					<div class="item-media">
						<img :src="conversation.interlocutor.photo_small" alt="" class="">
						<div v-if="conversation.interlocutor.isFavorite" class="is-favorite"></div>
						<div v-bind:class="getStatusClasses(conversation.interlocutor, 'b-status__indicator', 's1')"></div>
					</div><!--item-media-->

					<div class="item-inner">
						<div :class="{'w-text': true, 'unread': conversation.unreadMessagesCount}">
							<div class="name">{{ conversation.interlocutor.name }}</div>
							<div class="body">
								<!--<i class="reply__icon"></i>-->
								<svg v-if="conversation.message.user_from === authUserId" class="icon icon-return"><use v-bind:xlink:href="symbolsSvgUrl('icon-return')"></use></svg>
								<template v-if="!conversation.message.cancelled">
									<span v-if="conversation.message.msg_type === 'text'">{{ conversation.message.message }}</span>
									<span v-if="conversation.message.msg_type === 'image'">{{ trans('photo_message') }}</span>
									<span v-if="conversation.message.msg_type === 'video'">{{ trans('video_message') }}</span>
									<span v-if="conversation.message.msg_type === 'location'">{{ trans('location_message') }}</span>
								</template>
								<span class="message__removed" v-else>{{ trans('removed') }}</span>
							</div>
						</div><!--w-text-->

						<div class="w-indicators">
							<div class="message__time">{{ timeAgo(conversation.message.idate) }}</div>
							<div class="message-container" v-if="conversation.unreadMessagesCount">
								<svg class="icon icon-message_bubble"><use v-bind:xlink:href="symbolsSvgUrl('icon-message_bubble')"></use></svg>
								<span>{{ conversation.unreadMessagesCount }}</span>
							</div>
						</div><!--w-indicators-->
					</div><!--item-inner-->
				</a>

				<a v-if="conversation.chatType === 'event'" href="javascript:void(0)"
					@click="startEventConversation(
						conversation.interlocutor.id,
						conversation.event.id
					)"
					class="b-chat__link-item"
				>
					<div class="item-media">
						<div>
							<div :class="getStatusClasses(conversation.interlocutor, 'b-status__indicator', 's2')"></div>
							<img :src="conversation.event.isMine ? conversation.interlocutor.photo_small : conversation.event.photo_small" alt="" class="">
						</div>
					</div><!--item-media-->

					<div class="item-inner">
						<div :class="{'w-text': true, 'unread': conversation.unreadMessagesCount, 'reply': conversation.message.user_from === authUserId, 'item-inner__user-event': conversation.event.isMine}">
							<div class="name">
								<svg class="icon icon-calendar_sub icon-chat-event-title"><use v-bind:xlink:href="symbolsSvgUrl('icon-calendar_sub')"></use></svg>
								{{ conversation.event.title }}
							</div>
							<div class="body">
								<div>{{ conversation.event.event_date | formatDate('day-date') }}</div>
							</div>
							<div v-if="conversation.event.isMine">{{ conversation.interlocutor.name }}</div>
						</div><!--w-text-->

						<div class="w-indicators">
							<div class="message__time">{{ timeAgo(conversation.message.idate) }}</div>

							<div class="elem-relative">
								<div class="message-container" v-if="conversation.unreadMessagesCount">
									<svg class="icon icon-message_bubble"><use v-bind:xlink:href="symbolsSvgUrl('icon-message_bubble')"></use></svg>
									<span>{{ conversation.unreadMessagesCount }}</span>
								</div>
							</div>

						</div><!--w-indicators-->
					</div><!--item-inner-->
				</a>

				<a v-if="conversation.chatType === 'group'" href="javascript:void(0)"
					@click="startGroupConversation(conversation.event.id)"
					class="b-chat__link-item"
				>
					<div class="item-media">
						<div>
							<div :class="getStatusClasses(conversation.event, 'b-status__indicator', 's2')"></div>
							<img :src="conversation.event.photo_small" alt="" class="">
						</div>
					</div><!--item-media-->

					<div class="item-inner">
						<div :class="{'w-text': true, 'unread': conversation.unreadMessagesCount, 'reply': conversation.message.user_from === authUserId, 'item-inner__user-event': true}">
							<div class="name">
								<svg class="icon icon-calendar_sub icon-chat-event-title"><use v-bind:xlink:href="symbolsSvgUrl('icon-calendar_sub')"></use></svg>
								{{ conversation.event.title }}
							</div>
							<div class="body">
								<div>{{ conversation.event.event_date | formatDate('day-date') }}</div>
							</div>
						</div><!--w-text-->

						<div class="w-indicators">
							<div class="message__time">{{ timeAgo(conversation.message.idate) }}</div>

							<div class="elem-relative">
								<div class="message-container" v-if="conversation.unreadMessagesCount">
									<svg class="icon icon-message_bubble"><use v-bind:xlink:href="symbolsSvgUrl('icon-message_bubble')"></use></svg>
									<span>{{ conversation.unreadMessagesCount }}</span>
								</div>
							</div>

						</div><!--w-indicators-->
					</div><!--item-inner-->
				</a>
			</li>
		</ul>

		<infinite-loading
			ref="infiniteLoadingChat"
			@infinite="getConversations"
			force-use-infinite-wrapper="#js-chat-widget"
			spinner="bubbles"
			direction="bottom"
		>
			<span slot="no-results">
			  {{ trans('no_chat_messages') }}
			</span>
				<span slot="no-more">
			  {{ trans('no_more_chat_messages') }}
			</span>
		</infinite-loading>
	</div>
</template>

<script>
    import {mapState, mapActions, mapGetters} from 'vuex';
    import chatModule from '@chat/module/store/type'

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-chat').default,
        ],
        data() {
            return {
                pageCounters: {
                    all: 0,
                    unread: 0,
                    favorites: 0,
                },
                activeConversationGroup: 'all'
            }
        },
        methods: {
            selectConversationsTab(tab) {
                this.activeConversationGroup = tab;
                if (!this.conversations.length) {
					this.reset()
				}
            },
			async getConversations(infiniteScroll) {
				if (this.pageNumber === 0) {
					this.$store.dispatch(chatModule.actions.conversations.clearGroup, this.activeConversationGroup)
				}

      await this.$store.dispatch(chatModule.actions.conversations.loadGroup, {
        page: this.pageNumber,
        limit: this.conversationsLimit,
        group: this.activeConversationGroup
      })

				if (this.conversations.length) {
					infiniteScroll.loaded()
				}

				if (this.conversations.length < window.LOAD_CHAT_WINDOWS_LIMIT * (this.pageNumber + 1)) {
					infiniteScroll.complete()
				}

				this.pageCounters[this.activeConversationGroup]++;
			},
			reset(){
				console.log('[Widget Conversations] Reset', {activeConversationGroup: this.activeConversationGroup})
				this.pageCounters[this.activeConversationGroup] = 0
				this.$store.dispatch(chatModule.actions.conversations.clearGroup, this.activeConversationGroup)
				this.$nextTick(function(){
					if (this.$refs.infiniteLoadingChat) {
						this.$refs.infiniteLoadingChat.stateChanger.reset()
					}
				});
			}
        },
        computed: {
            ...mapGetters({
                conversationsAll: chatModule.getters.conversations.all,
                conversationsUnread: chatModule.getters.conversations.unread,
                conversationsFavorites: chatModule.getters.conversations.favorites,
            }),
			pageNumber(){
				return this.pageCounters[this.activeConversationGroup];
			},
            conversations() {
				let conversations = []
				if (this.activeConversationGroup === 'unread') {
					conversations = this.conversationsUnread
				} else if (this.activeConversationGroup === 'favorites') {
					conversations = this.conversationsFavorites
				} else {
					conversations = this.conversationsAll
				}
				return conversations
            },
        },
		created(){
			console.log('[Conversations] Created')
			this.reset()
		},
		mounted() {
			console.log('[Conversations] Mounted')
            app.$on('reload-conversations', this.reset);
        },
		destroyed() {
			app.$off('reload-conversations');
		}
	}
</script>
