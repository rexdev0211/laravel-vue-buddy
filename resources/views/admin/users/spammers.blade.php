@extends('layouts.adminLayout')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Possible Spammers</h3>

                        <div class="box-tools">
                            {{--here go buttons--}}
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
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Language</th>
                                    <th>Age</th>
                                    <th>Location</th>
                                    <th>Join date</th>
                                    <th>Last active</th>
                                </tr>
                                @foreach($users as $user)
                                    <tr class="{{ $user->isDeleted() ? 'bg-danger' : ($user->isSuspended() ? 'bg-gray' : '') }}">
                                        <td>
                                            <a data-lity style="float: left; margin-right:15px" target="_blank" href="{{ $user->getPhotoUrl() }}">
                                                <img data-lity src="{{ $user->getPhotoUrl('30x30') }}" alt="" />
                                            </a>
                                        </td>
                                        <td>{{ $user->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.view', $user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $user->name }}</a>
                                        </td>
                                        <td>{{ $user->email ?: $user->email_orig }}</td>
                                        <td>{{ $user->language }}</td>
                                        <td>{{ $user->dob->age }}</td>
                                        <td>{{ $user->locality }}, {{ $user->country }}</td>
                                        <td>{{ Helper::formatDate($user->created_at, 'M j, Y') }}</td>
                                        <td>{{ Helper::formatDate($user->last_active, 'M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </table>

                        </div>
                        <!-- /.box-body -->
                    @endif
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
    <!-- /.content -->
@endsection