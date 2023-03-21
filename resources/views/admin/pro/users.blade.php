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
                    {!! Form::open(array('route'=>'admin.proUsers', 'class'=>'form-inline custom-inline')) !!}

                        <input type="hidden" name="page" value="1" />

                        <div class="form-group">
                            {!! Form::select('filterStatus', ['pro' => 'PRO', 'ex-pro' => 'ex-PRO'], empty(Helper::getSessionUserPreference($sessionKey, 'filterStatus')) ? 'pro' : Helper::getSessionUserPreference($sessionKey, 'filterStatus'), array('class'=>'form-control', 'placeholder'=>'Status')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('filterName', Helper::getSessionUserPreference($sessionKey, 'filterName'), array('class'=>'form-control', 'placeholder'=>'Username')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('filterId', Helper::getSessionUserPreference($sessionKey, 'filterId'), array('class'=>'form-control', 'placeholder'=>'ID', 'size'=>'3')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('filterEmail', Helper::getSessionUserPreference($sessionKey, 'filterEmail'), array('class'=>'form-control', 'placeholder'=>'Email')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('filterTransactionId', Helper::getSessionUserPreference($sessionKey, 'filterTransactionId'), array('class'=>'form-control', 'placeholder'=>'Transaction ID')) !!}
                        </div>

                        {{--<br style="clear: both;" />--}}

                        <div class="form-group">
                            {!! Form::submit('Filter', ['class'=>'btn btn-primary btn-block']) !!}
                        </div>

                        <div class="form-group">
                            <a href="{{ route('admin.proUsers') }}?resetFilters" class="btn btn-default btn-block">Reset</a>
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
                    <h3 class="box-title">PRO Users</h3>
                </div>
                <!-- /.box-header -->

                @if (!count($users))
                <div class="box-body table-responsive">
                    <div>No PRO Users were found</div>
                </div>
                @else
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Issuer</th>
                                <th>Plan</th>
                                <th>Transaction ID</th>
                                <th>Email</th>
                                <th>Active Until</th>
                                <th>Next Rebill</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr class="{{ $user->isDeleted() ? 'bg-danger' : ($user->isSuspended() || $user->isGhosted() ? 'bg-gray' : '') }}">
                                <td>{{ $user->id }}</td>
                                <td>
                                    <a href="{{ route('admin.users.view', $user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $user->name }}</a>
                                </td>
                                <td>{{ $user->email ?: $user->email_orig }}</td>
                                <td>{{ $user->getIssuer() }}</td>
                                <td>{{ $user->getProPlan() }}</td>
                                <td>{{ $user->getProTransactionId() }}</td>
                                <td>{{ $user->getProTransactionEmail() }}</td>
                                <td>{{ $user->pro_expires_at }}</td>
                                <td>{{ $user->getProRebillDate() }}</td>
                                <td>{{ $user->isPro() ? 'active' : 'expired' }}</td>
                                <td>
                                    <a href="{{ route('admin.proUsers.transactions', $user->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-info"></i> History</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->

                <div class="box-footer clearfix">
                    @include('partials.pagination', ['model' => $users, 'url' => route('admin.proUsers')])
                </div>
                @endif
            </div>
            <!-- /.box -->
        </div>
    </div>
</section>
<!-- /.content -->
@endsection
