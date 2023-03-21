@extends('layouts.app_new')

@section('content')

    <cookies-bar v-if="!isApp"></cookies-bar>
    <preloader v-show="loading"></preloader>
    <modal-dialog></modal-dialog>

    <modal-register-desktop-component v-if="isDesktop"></modal-register-desktop-component>
    <modal-recover-password-desktop-component v-if="isDesktop"></modal-recover-password-desktop-component>

    <transition :name="transitionName" mode="out-in">
        <keep-alive>
            <router-view class="view child-view" :key="$route.fullPath"></router-view>
            <router-view :name="platform" class="view child-view" :key="$route.fullPath"></router-view>
        </keep-alive>
    </transition>
@endsection
