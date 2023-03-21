@extends('layouts.adminLayout')

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Admins</h3>

                        <div class="box-tools">
                            {{--here go buttons--}}
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover table-striped">
                            <tr>
                                <th></th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Created date</th>
                            </tr>
                            @foreach($admins as $admin)
                                <tr>
                                    <td>
                                        <a href="{{ \App\UserPhoto::getAdminPhotoUrl($admin->id, 'orig') }}" data-lity>
                                            <img src="{{ \App\UserPhoto::getAdminPhotoUrl($admin->id, '30x30') }}" alt="">
                                        </a>
                                    </td>
                                    <td>{{ $admin->id }}</td>
                                    <td>{{ $admin->name }}</td>
                                    <td>{{ $admin->email }}</td>
                                    <td>{{ Helper::formatDate($admin->created_at, 'M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
    <!-- /.content -->
@endsection