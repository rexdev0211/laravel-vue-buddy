<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('page_title', ucwords(implode(' ', explode('_', snake_case(Request::segment(2, 'dashboard'))))) ) - {{ config('app.name') }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="{{ asset('backend/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('backend/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('backend/plugins/select2/select2.min.css') }}" />
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('backend/css/AdminLTE.min.css') }}">
    <!-- AdminLTE Skins. Choose a skin from the css/skins folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ asset('backend/css/skins/skin-green.min.css') }}">

    <!-- toastr -->
    <link rel="stylesheet" href="{{ asset('backend/plugins/toastr.js/toastr.min.css') }}">

    <!-- sweetalert2 -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/sweetalert2/6.6.2/sweetalert2.min.css">

    <!-- data lity -->
    <link href="{{ asset('backend/plugins/lity/lity.min.css') }}" rel="stylesheet" type="text/css"/>

    <link href="{{ asset('backend/css/custom_backend.css') }}" rel="stylesheet" type="text/css"/>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <meta name="csrf-token" content="{{ csrf_token() }}" />
</head>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

    <header class="main-header">
        <!-- Logo -->
        <a href="{{ route('admin.index') }}" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>Buddy</b></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>Buddy</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ \App\UserPhoto::getAdminPhotoUrl(Auth::user()->id, '90x90') }}" class="user-image" alt="User Image">
                            <span class="hidden-xs">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="{{ \App\UserPhoto::getAdminPhotoUrl(Auth::user()->id, '90x90') }}" class="img-circle" alt="User Image">
                                <p>
                                    {{ Auth::user()->name }} - Admin
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="{{ route('admin.profile') }}" class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="{{ route('logout') }}" class="btn btn-default btn-flat">Sign out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="{{ \App\UserPhoto::getAdminPhotoUrl(Auth::user()->id, '90x90') }}" class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p>{{ Auth::user()->name }}</p>
                    <a href="javascript:void(0)"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu" data-widget="tree" data-accordion="false">
                <li class="header">MAIN NAVIGATION</li>
                <li class="{{ Request::is('admin') ? 'active' : '' }}">
                  <a href="{{ route('admin.index') }}">
                    <i class="fa fa-home"></i> <span>Dashboard</span>
                  </a>
                </li>
                <li data-id="community" class="treeview{{ session()->get('adminMenu') && session()->get('adminMenu')['community'] ? ' menu-open' : '' }}{{ Request::is('admin/users*') || Request::is('admin/events') || Request::is('admin/photosModeration*') || Request::is('admin/rush*') ? ' active' : '' }}">
                  <a href="#">
                    <i class="fa fa-users"></i> <span>COMMUNITY</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu" style="{{ session()->get('adminMenu') && session()->get('adminMenu')['community'] ? 'display: block;' : '' }}">
                    <li class="{{ Request::is('admin/users*') ? 'active' : '' }}">
                      <a href="{{ route('admin.users') }}?page=1">
                        <i class="fa fa-user"></i> <span>Users</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/events') ? 'active' : '' }}">
                      <a href="{{ route('admin.events') }}">
                        <i class="fa fa-calendar"></i> <span>Events</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/photosModeration*') ? 'active' : '' }}">
                      <a href="{{ route('admin.photosModeration') }}">
                        <i class="fa fa-camera"></i> <span>Photos</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/community/videos*') ? 'active' : '' }}">
                      <a href="{{ route('admin.community.videos') }}">
                        <i class="fa fa-video-camera"></i> <span>Videos</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/rush*') ? 'active' : '' }}">
                      <a href="{{ route('admin.rush') }}">
                        <i class="fa fa-eye"></i> <span>Strips</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <li data-id="moderation" class="treeview{{ session()->get('adminMenu') && session()->get('adminMenu')['moderation'] ? ' menu-open' : '' }}{{ Request::is('admin/reports*') || Request::is('admin/blockedDomains*') || Request::is('admin/moderation/photos*') ? ' active' : '' }}">
                  <a href="#">
                    <i class="fa fa-pencil-square"></i> <span>MODERATION</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu" style="{{ session()->get('adminMenu') && session()->get('adminMenu')['moderation'] ? 'display: block;' : '' }}">
                    <li class="{{ Request::is('admin/reports*') ? 'active' : '' }}">
                      <a href="{{ route('admin.reports') }}">
                        <i class="fa fa-th-list"></i> <span>User Reporting</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/events/submissions*') ? 'active' : '' }}">
                       <a href="{{ route('admin.events.submissions') }}">
                           <i class="fa fa-th-list"></i> <span>Event Submissions</span>
                       </a>
                    </li>
                    <li class="{{ Request::is('admin/events/reports*') ? 'active' : '' }}">
                      <a href="{{ route('admin.reports.events') }}">
                        <i class="fa fa-th-list"></i> <span>Event Reporting</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/moderation/photos*') ? 'active' : '' }}">
                      <a href="{{ route('admin.moderation.photos') }}">
                        <i class="fa fa-camera"></i> <span>Photo Rating</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/moderation/videos*') ? 'active' : '' }}">
                       <a href="{{ route('admin.moderation.videos') }}">
                          <i class="fa fa-video-camera"></i> <span>Video Rating</span>
                       </a>
                    </li>
                    <li class="{{ Request::is('admin/blockedDomains*') ? 'active' : '' }}">
                        <a href="{{ route('admin.blockedDomains') }}">
                          <i class="fa fa-ban"></i> <span>Blocked domains</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('admin/moderation/word-filter*') ? 'active' : '' }}">
                      <a href="{{ route('admin.moderation.wordFilter') }}">
                        <i class="fa fa-commenting"></i> <span>Word Filter</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/moderation/word-search*') ? 'active' : '' }}">
                      <a href="{{ route('admin.moderation.wordSearch') }}">
                        <i class="fa fa-search-plus"></i> <span>Word Search</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <li data-id="admin" class="treeview{{ session()->get('adminMenu') && session()->get('adminMenu')['admin'] ? ' menu-open' : '' }}{{ Request::is('admin/newsletter*') || Request::is('admin/promo*') || Request::is('admin/pages*') || Request::is('admin/uploads*') || Request::is('admin/emailTemplates*') || Request::is('admin/videoServer*') || Request::is('admin/admins*') ? ' active' : '' }}">
                  <a href="#">
                    <i class="fa fa-gear"></i> <span>ADMIN</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu" style="{{ session()->get('adminMenu') && session()->get('adminMenu')['admin'] ? 'display: block;' : '' }}">
                    <li class="{{ Request::is('admin/internalMessage*') ? 'active' : '' }}">
                      <a href="{{ route('admin.internalMessage') }}">
                        <i class="fa fa-comments"></i> <span>Internal Message</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/newsletter*') ? 'active' : '' }}">
                      <a href="{{ route('admin.newsletter') }}">
                        <i class="fa fa-envelope-o"></i> <span>Newsletter</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/promo*') ? 'active' : '' }}">
                      <a href="{{ route('admin.promo') }}">
                        <i class="fa fa-percent"></i> <span>PROmo Codes</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/pro-users*') ? 'active' : '' }}">
                      <a href="{{ route('admin.proUsers') }}">
                        <i class="fa fa-id-card"></i> <span>PRO Users</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/pages*') ? 'active' : '' }}">
                      <a href="{{ route('admin.pages') }}">
                        <i class="fa fa-desktop"></i> <span>Static Pages</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/uploads*') ? 'active' : '' }}">
                      <a href="{{ route('admin.uploads') }}">
                        <i class="fa fa-cloud-upload"></i> <span>Uploads</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/emailTemplates*') ? 'active' : '' }}">
                      <a href="{{ route('admin.emailTemplates') }}">
                        <i class="fa fa-envelope"></i> <span>Email templates</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/buddyLinks*') ? 'active' : '' }}">
                      <a href="{{ route('admin.buddyLinks') }}">
                        <i class="fa fa-asterisk"></i> <span>Buddy Links</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/videoServer*') ? 'active' : '' }}">
                      <a href="{{ route('admin.videoServer') }}">
                        <i class="fa fa-video-camera"></i> <span>Video Server</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/admins*') ? 'active' : '' }}">
                      <a href="{{ route('admin.admins') }}">
                        <i class="fa fa-user-circle"></i> <span>Admins</span>
                      </a>
                    </li>
                    <li class="{{ Request::is('admin/onlineCountries*') ? 'active' : '' }}">
                      <a href="{{ route('admin.onlineCountries') }}">
                        <i class="fa fa-circle"></i> <span>Online by countries</span>
                      </a>
                    </li>
                  </ul>
                </li>
            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Main content -->

        @yield('content')

    </div>
    <!-- /.content-wrapper -->
</div>
<!-- ./wrapper -->

<div class="modal fade" tabindex="-1" role="dialog" id="modal-box-div">
    <div class="modal-dialog modal-lg" id="air-modal">
        <div class="modal-content">
            <img src="{{ asset('backend/img/ajax-modal-loading.gif') }}" alt=""/>
        </div>
    </div>
</div>

<!-- jQuery 3 -->
<script src="{{ asset('backend/bower_components/jquery/dist/jquery.min.js') }}"></script>
<!-- Bootstrap 3.3.7 -->
<script src="{{ asset('backend/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<!-- Slimscroll -->
<script src="{{ asset('backend/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<!-- FastClick -->
<script src="{{ asset('backend/bower_components/fastclick/lib/fastclick.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('backend/js/adminlte.min.js') }}"></script>

<script src="{{ asset('backend/plugins/underscore/underscore-min.js') }}"></script>

<!-- CK Editor -->
<script src="{{ asset('backend/plugins/ckeditor/ckeditor.js') }}"></script>

<!-- toastr -->
<script src="{{ asset('backend/plugins/toastr.js/toastr.min.js') }}" type="text/javascript"></script>

<!-- sweetalert2 -->
<script src="//cdn.jsdelivr.net/sweetalert2/6.6.2/sweetalert2.min.js" type="text/javascript"></script>

<!-- data lity -->
<script src="{{ asset('backend/plugins/lity/lity.min.js') }}" type="text/javascript"></script>

@yield('scripts_init')

<script src="{{ asset('backend/js/custom.js') }}"></script>

<script type="text/javascript">
    @if (Session::get('showNotification', false))
        showNotification("{{ Session::get('showNotification') }}");
        <?php Session::forget('showNotification'); ?>
    @elseif(!empty($showNotification))
        showNotification("{{ $showNotification }}");
    @endif

    @if (Session::get('showErrorNotification', false))
        showErrorNotification("{{ Session::get('showErrorNotification') }}");
        <?php Session::forget('showErrorNotification'); ?>
    @elseif(!empty($showErrorNotification))
        showErrorNotification("{{ $showErrorNotification }}");
    @endif
</script>

@stack('js')

</body>
</html>
