@extends('layouts.adminLayout')

@section('content')

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Event Submissions</h3>

                        <div class="box-tools">
                            {{--here go buttons--}}
                        </div>
                    </div>
                    <!-- /.box-header -->

                    @if (!count($events))
                        <div class="box-body table-responsive">
                            <div>No events were found</div>
                        </div>
                    @else
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-hover table-striped">
                                <tr>
                                    <th>ID</th>
                                    <th>Event Title</th>
                                    <th>Event owner</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                @foreach($events as $event)
                                    <tr>
                                        <td>{{ $event->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.events.view', $event->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $event->title }} [<b>{{ $event->id }}</b>]</a>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.view', $event->user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $event->user->name }} [<b>{{ $event->user->id }}</b>]</a>
                                        </td>
                                        <td>{{ Helper::formatEventDate($event->event_date, 'M j, Y') }}</td>
                                        <td>{{ $event->status }}</td>
                                        <td>
                                            <a class="btn btn-success btn-xs" title="APPROVE" href="{{ route('admin.events.approveGuide', $event->id) }}">APPROVE</a>
                                            <a class="btn btn-warning btn-xs" title="DECLINE" href="{{ route('admin.events.declineGuide', $event->id) }}">DECLINE</a>
                                            @include('partials.deleteButton', ['name' => $event->title, 'url' => route('admin.events.delete', $event->id), 'alert' => 'This action will delete selected event.'])
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer clearfix">
                            @include('partials.pagination', ['model' => $events, 'url' => route('admin.events.submissions')])
                        </div>
                    @endif
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
    <!-- /.content -->

@endsection
