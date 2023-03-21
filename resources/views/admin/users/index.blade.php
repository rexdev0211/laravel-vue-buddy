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
                        {!! Form::open(array('route'=>'admin.users', 'class'=>'form-inline custom-inline')) !!}

                            <input type="hidden" name="page" value="1" />

                            <div class="form-group">
                                {!! Form::text('filterName', Helper::getSessionUserPreference($sessionKey, 'filterName'), array('class'=>'form-control', 'placeholder'=>'Username')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::text('filterBuddyLink', Helper::getSessionUserPreference($sessionKey, 'filterBuddyLink'), array('class'=>'form-control', 'placeholder'=>'Buddy Link')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::text('filterId', Helper::getSessionUserPreference($sessionKey, 'filterId'), array('class'=>'form-control', 'placeholder'=>'ID', 'size'=>'3')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::text('filterEmail', Helper::getSessionUserPreference($sessionKey, 'filterEmail'), array('class'=>'form-control', 'placeholder'=>'Email')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::select('filterActivity', $activityOptions, Helper::getSessionUserPreference($sessionKey, 'filterActivity'), array('class'=>'form-control')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::select('filterTrashed', $trashedOptions, Helper::getSessionUserPreference($sessionKey, 'filterTrashed'), array('class'=>'form-control')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::select('filterLanguage', $languages, Helper::getSessionUserPreference($sessionKey, 'filterLanguage'), array('class'=>'form-control')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::select('filterGroup', $groups, Helper::getSessionUserPreference($sessionKey, 'filterGroup'), array('class'=>'form-control')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::select('filterRegistration', $registrationOptions, Helper::getSessionUserPreference($sessionKey, 'filterRegistration'), array('class'=>'form-control')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::select('filterCountry', $countries, Helper::getSessionUserPreference($sessionKey, 'filterCountry'), array('class'=>'form-control')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::text('filterState', Helper::getSessionUserPreference($sessionKey, 'filterState'), array('class'=>'form-control', 'placeholder'=>'State name')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::text('filterLocality', Helper::getSessionUserPreference($sessionKey, 'filterLocality'), array('class'=>'form-control', 'placeholder'=>'Locality name')) !!}
                            </div>

                            {{--<br style="clear: both;" />--}}

                            <div class="form-group">
                                {!! Form::submit('Filter', ['class'=>'btn btn-primary btn-block']) !!}
                            </div>

                            <div class="form-group">
                                <a href="{{ route('admin.users') }}?resetFilters" class="btn btn-default btn-block">Reset</a>
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
                        <h3 class="box-title">Users</h3>

                        <div class="box-tools">
                            <a href="{{ route('admin.users.spammers') }}" class="btn btn-danger btn-sm">Spammers</a>
                        </div>
                    </div>
                    <!-- /.box-header -->

                    @if (!count($users))
                        <div class="box-body table-responsive">
                            <div>No users were found</div>
                        </div>
                    @else
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-hover table-striped">
                                <tr>
                                    <th></th>
                                    <th>{!! Helper::getOrderByLink(route('admin.users'), $sessionKey, 'ID', 'id') !!}</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.users'), $sessionKey, 'Username', 'name') !!}</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.users'), $sessionKey, 'Buddy Link', 'buddy_link') !!}</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.users'), $sessionKey, 'Email', 'email') !!}</th>
                                    <th>Group</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.users'), $sessionKey, 'Age', 'dob') !!}</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.users'), $sessionKey, 'Location', 'locality') !!}</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.users'), $sessionKey, 'Join date', 'created_at') !!}</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.users'), $sessionKey, 'Last active', 'last_active') !!}</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.users'), $sessionKey, 'Status', 'status') !!}</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.users'), $sessionKey, 'IP', 'ip') !!}</th>
                                </tr>
                                @foreach($users as $user)
                                    <tr class="{{ $user->isDeleted() ? 'bg-danger' : ($user->isSuspended() || $user->isGhosted() ? 'bg-gray' : '') }}">
                                        <td>
                                            <a data-lity style="float: left; margin-right:15px" target="_blank" href="{{ $user->getPhotoUrl('orig', true) }}">
                                                <img data-lity src="{{ $user->getPhotoUrl('180x180', true) }}" alt="" width="30" height="30" />
                                            </a>
                                        </td>
                                        <td>{{ $user->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.view', $user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $user->name }}</a>
                                        </td>
                                        <td>{{ $user->link }}</td>
                                        <td>{{ $user->email ?: $user->email_orig }}</td>
                                        <td>{{ $user->group }}</td>
                                        <td>{{ $user->dob->age }}</td>
                                        <td>{{ $user->locality }}, {{ $user->country }}</td>
                                        <td>{{ Helper::formatDate($user->created_at, 'M j, Y') }}</td>
                                        <td>{{ Helper::formatDate($user->last_active, 'M j, Y') }}<br/>{{ $user->activityStatus }}</td>
                                        <td>
                                            @if ($user->computedStatus == 'Deleted')
                                                <i class="fa fa-trash text-red" title="Deleted"></i>
                                            @elseif ($user->computedStatus == 'Suspended' || $user->computedStatus == 'Ghosted')
                                                <i class="fa fa-ban text-red" title="{{ $user->computedStatus }}"></i>
                                            @endif
                                            &nbsp;{{ $user->computedStatus }}
                                        </td>
                                        <td>{{ $user->ip ? $user->ip : '-' }}</td>
                                    </tr>
                                @endforeach
                            </table>

                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer clearfix">
                            @include('partials.pagination', ['model'=>$users, 'url'=>route('admin.users')])
                        </div>
                    @endif
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
    <!-- /.content -->
@endsection
