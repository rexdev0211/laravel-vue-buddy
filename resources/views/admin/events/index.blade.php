@extends('layouts.adminLayout')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

    <style type="text/css">
        .form-group {
            margin-bottom: 10px !important;
        }
    </style>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-body">
                        {!! Form::open(array('route'=>'admin.events', 'class'=>'form-inline custom-inline')) !!}

                        <input type="hidden" name="page" value="1" />

                        <div class="form-group">
                            {!! Form::text('filterTitle', Helper::getSessionUserPreference($sessionKey, 'filterTitle'), array('class'=>'form-control', 'placeholder'=>'Title')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('filterId', Helper::getSessionUserPreference($sessionKey, 'filterId'), array('class'=>'form-control', 'placeholder'=>'ID', 'size'=>'3')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('filterOwnerName', Helper::getSessionUserPreference($sessionKey, 'filterOwnerName'), array('class'=>'form-control', 'placeholder'=>'Owner Name', 'size'=>'20')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('filterOwnerId', Helper::getSessionUserPreference($sessionKey, 'filterOwnerId'), array('class'=>'form-control', 'placeholder'=>'Owner ID', 'size'=>'10')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::select('filterType', $typeOptions, Helper::getSessionUserPreference($sessionKey, 'filterType'), array('class'=>'form-control')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::select('filterStatus', $statusOptions, Helper::getSessionUserPreference($sessionKey, 'filterStatus'), array('class'=>'form-control')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::select('filterOccur', $occurOptions, Helper::getSessionUserPreference($sessionKey, 'filterOccur'), array('class'=>'form-control')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::submit('Filter', ['class'=>'btn btn-primary btn-block']) !!}
                        </div>

                        <div class="form-group">
                            <a href="{{ route('admin.events') }}?resetFilters" class="btn btn-default btn-block">Reset</a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Events</h3>

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
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Title</th>
                                    <th>Owner</th>
                                    <th>Address</th>
                                    <th>Location</th>
                                    <th>Created at</th>
                                    <th>Actions</th>
                                </tr>
                                @foreach($events as $event)
                                    <tr class="{{ $event->isSuspended() ? 'bg-gray' : '' }}">
                                        <td>{{ $event->id }}</td>
                                        <td>{{ Helper::formatDateMysql($event->event_date, 'M j, Y') }}</td>
                                        <td>{{ $event->typeCaption }}</td>
                                        <td>
                                            <a href="{{ route('admin.events.view', $event->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $event->title }}</a>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.view', $event->user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $event->user->name }} [<strong>{{ $event->user->id }}</strong>]</a>
                                        </td>
                                        <td>{{ $event->address }}</td>
                                        <td>{{ $event->location }}</td>
                                        <td>{{ Helper::formatDateTimeMysql($event->created_at, 'M j, Y') }}</td>
                                        <td>
                                            @if (in_array($event->status, ['active', 'approved']))
                                                <a class="btn btn-warning btn-xs" title="Suspend" href="{{ route('admin.events.suspend', $event->id) }}"><i class="fa fa-ban"></i> Suspend</a>
                                            @else
                                                <a class="btn btn-success btn-xs" title="Activate" href="{{ route('admin.events.activate', $event->id) }}"><i class="fa fa-check-circle"></i> Activate</a>
                                            @endif
                                            @include('partials.deleteButton', ['name' => $event->title, 'url' => route('admin.events.delete', $event->id), 'alert' => 'This action will delete selected event.'])
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer clearfix">
                            @include('partials.pagination', ['model'=>$events, 'url'=>route('admin.events')])
                        </div>
                    @endif
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
    <!-- /.content -->
@endsection
