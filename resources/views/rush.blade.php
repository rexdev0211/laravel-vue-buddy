@extends('layouts.rush')

@section('content')
<rush-header :is-header-allowed="headerAllowed"></rush-header>
<rush-sidebar :is-sidebar-active="isSidebarActive" :my-rushes="myRushes" @click="hideWidget"></rush-sidebar>
<div id="rushBody" @click="closeOverlays">
    <router-view></router-view>
</div>
<announce></announce>
<modal-dialog></modal-dialog>
<requirement></requirement>
@endsection
