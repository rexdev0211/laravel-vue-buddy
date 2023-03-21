@extends('layouts.app')

@section('content')
    <div v-show="loading" class="w-root view loading-root">
        <div class="w-views">
            <div class="w-view">
                <div class="w-pages">
                    <div class="b-page">
                        <div class="loading-page w-page__content vertical-align2" :class="{'light-loader': isLightLoading}" @click="hideOnlyLightLoading">
                            <img src="/assets/img/icons/startup-logo.png" alt="" style="margin-bottom: 30px; width: 100px;" class="logo" />
                            <noscript>
                                <div style="margin-top: 30px">
                                    Please enable JavaScript to use this app.
                                </div>
                            </noscript>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <cookies-bar v-if="!isApp"></cookies-bar>
    <splash-screen v-if="0 && isMobile"></splash-screen>
    <offline-notification></offline-notification>

    <modal-dialog></modal-dialog>

    <modal-user-desktop-component v-if="isDesktop"></modal-user-desktop-component>
    <modal-user-mobile-component v-if="!isDesktop"></modal-user-mobile-component>
    <modal-event-desktop-component v-if="isDesktop"></modal-event-desktop-component>
    <modal-bang-desktop-component v-if="isDesktop"></modal-bang-desktop-component>
    <modal-chat-desktop-component v-if="isDesktop"></modal-chat-desktop-component>
    <modal-chat-mobile-component v-if="!isDesktop"></modal-chat-mobile-component>
    <modal-chat-event-desktop-component v-if="isDesktop"></modal-chat-event-desktop-component>
    <modal-chat-group-desktop-component v-if="isDesktop"></modal-chat-group-desktop-component>
    <modal-chat-preview v-if="isDesktop"></modal-chat-preview>

    <transition :name="transitionName" mode="out-in">
        <keep-alive>
            <router-view class="view child-view" :key="$route.fullPath"></router-view>
            <router-view v-if="isDesktop" name="desktop" class="view child-view" :key="$route.fullPath"></router-view>
            <router-view v-else-if="isApp" name="app" class="view child-view" :key="$route.fullPath"></router-view>
            <router-view v-else name="mobile" class="view child-view" :key="$route.fullPath"></router-view>
        </keep-alive>
    </transition>

    <health-alert></health-alert>
@endsection

{{--@push('scripts')--}}
	{{--<script>--}}
		{{--var user = {!! $user !!};--}}
	{{--</script>--}}
{{--@endpush--}}
