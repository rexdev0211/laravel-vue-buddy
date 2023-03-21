@extends('layouts.adminLayout')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

    <style type="text/css">
        .form-group {
            margin-bottom: 10px !important;
        }
    </style>

    <section class="content">
        @if (count($top))
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Top 10 Reports</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Reported Event [ID]</th>
                                    <th>Event owner [ID]</th>
                                    <th>Reports</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($top as $report)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.events.view', $report->event->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $report->event->title }} [<b>{{ $report->event->id }}</b>]</a>
                                    </td>
                                    <td>
                                        @if(!empty($report->owner))
                                            <a href="{{ route('admin.users.view', $report->owner->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $report->owner->name }} [<b>{{ $report->owner->id }}</b>]</a>
                                        @else
                                            Unknown reporter [<b>{{ $report->user_id }}</b>]
                                        @endif
                                    </td>
                                    <td>{{ $report->counted }}</td>
                                    <td>{{ $report->reason }}</td>
                                    <td>
                                        @if ($report->event->status == 'active')
                                            <a class="btn btn-warning btn-xs" title="Suspend" href="{{ route('admin.events.suspend', $report->event->id) }}"><i class="fa fa-ban"></i> Suspend</a>
                                        @else
                                            <a class="btn btn-success btn-xs" title="Activate" href="{{ route('admin.events.activate', $report->event->id) }}"><i class="fa fa-check-circle"></i> Activate</a>
                                        @endif
                                        @include('partials.deleteButton', ['title' => 'Delete Event', 'name' => $report->event->title, 'url' => route('admin.events.delete', $report->event->id), 'alert' => 'This action will delete selected event.'])
                                        @include('partials.deleteButton', ['title' => 'Delete Reports', 'name' => $report->event->title .' Reports', 'url' => route('admin.reports.events.clear', $report->event->id), 'alert' => 'This action will clear reports for this event.'])
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.box -->
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Reported events</h3>

                        <div class="box-tools">
                            {{--here go buttons--}}
                        </div>
                    </div>
                    <!-- /.box-header -->

                    @if (!count($list))
                        <div class="box-body table-responsive">
                            <div>No reports were found</div>
                        </div>
                    @else
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-hover table-striped">
                                <tr>
                                    <th>ID</th>
                                    <th>Reporter</th>
                                    <th>Category</th>
                                    <th>Reported event</th>
                                    <th>Event Owner</th>
                                    <th>Reason</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.reports.events'), $sessionKey, 'Date', 'idate') !!}</th>
                                    <th>Actions</th>
                                </tr>
                                @foreach($list as $report)
                                    <tr>
                                        <td>{{ $report->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.view', $report->reporter->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $report->reporter->name }} [<b>{{ $report->reporter->id }}</b>]</a>
                                        </td>
                                        <td>{{ $report->event->typeCaption }}</td>
                                        <td>
                                            <a href="{{ route('admin.events.view', $report->event->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $report->event->title }} [<b>{{ $report->event->id }}</b>]</a>
                                        </td>
                                        <td>
                                            @if(!empty($report->event->user))
                                                <a href="{{ route('admin.users.view', $report->event->user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $report->event->user->name }} [<b>{{ $report->event->user->id }}</b>]</a>
                                            @else
                                                Deleted user [<b>{{ $report->event->user_id }}</b>]
                                            @endif
                                        </td>
                                        <td>{{ $report->reason }}</td>
                                        <td>{{ $report->created_at }}</td>
                                        <td>
                                            @if ($report->event->status == 'active')
                                                <a class="btn btn-warning btn-xs" title="Suspend" href="{{ route('admin.events.suspend', $report->event->id) }}"><i class="fa fa-ban"></i> Suspend</a>
                                            @else
                                                <a class="btn btn-success btn-xs" title="Activate" href="{{ route('admin.events.activate', $report->event->id) }}"><i class="fa fa-check-circle"></i> Activate</a>
                                            @endif
                                            @include('partials.deleteButton', ['title' => 'Delete Event', 'name' => $report->event->title, 'url' => route('admin.events.delete', $report->event->id), 'alert' => 'This action will delete selected event.'])
                                            @include('partials.deleteButton', ['title' => 'Delete Report', 'name' => $report->event->title.' - '.$report->reason, 'url' => route('admin.reports.events.delete', $report->id), 'alert' => 'This action will delete selected report.'])
                                            <a class="btn btn-info btn-xs" target="_blank" title="Login as {{ $report->event->user->name }}" href="{{ route('admin.loginAsUser', $report->event->user->id) }}"><i class="fa fa-user"></i> {{ $report->event->user->name }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer clearfix">
                            @include('partials.pagination', ['model' => $list, 'url' => route('admin.reports.events')])
                        </div>
                    @endif
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
    <!-- /.content -->
@endsection
