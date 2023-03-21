@extends('layouts.app_google')

@section('content')
    <cookies-bar v-if="!isApp"></cookies-bar>
    <preloader v-show="loading"></preloader>
    <modal-dialog></modal-dialog>

    <transition :name="transitionName" mode="out-in">
        <keep-alive>
            <script>
                window.googleLanding = true;
            </script>
            <router-view :name="platform" class="view child-view" :key="$route.fullPath"></router-view>
        </keep-alive>
    </transition>
@endsection
