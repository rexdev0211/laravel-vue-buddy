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
        <div class="col-xs-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Reported users</h3>

                    <div class="box-tools">
                        {{--here go buttons--}}
                    </div>
                </div>
                <!-- /.box-header -->

                @if (!count($reports))
                <div class="box-body table-responsive">
                    <div>No reports were found</div>
                </div>
                @else
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover table-striped">
                        <tr>
                            <th>ID</th>
                            <th>Reporter</th>
                            <th>Reported user</th>
                            <th>Reason</th>
                            <th>{!! Helper::getOrderByLink(route('admin.reports'), $sessionKey, 'Reports (same reason)', 'reports_same_count') !!}</th>
                            <th>{!! Helper::getOrderByLink(route('admin.reports'), $sessionKey, 'Reports (total)', 'reports_total_count') !!}</th>
                            <th>{!! Helper::getOrderByLink(route('admin.reports'), $sessionKey, 'Date', 'idate') !!}</th>
                            <th>Actions</th>
                        </tr>
                        @foreach($reports as $report)
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>
                                    <a href="{{ route('admin.users.view', $report->reporter->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $report->reporter->name }} [<b>{{ $report->reporter->id }}</b>]</a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.view', $report->reported->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $report->reported->name }} [<b>{{ $report->reported->id }}</b>]</a>
                                </td>
                                <td>{{ $report->report_type }}</td>
                                <td>{{ $report->reports_same_count }}</td>
                                <td>{{ $report->reports_total_count }}</td>
                                <td>
                                    {{ Helper::formatDateTimeMysql($report->idate, 'M j, Y') }}
                                </td>
                                <td>
                                    @include('partials.deleteButton', ['name' => $report->reported->name.' - '.$report->report_type, 'url' => route('admin.reports.delete', $report->id), 'alert' => 'This action will delete selected report.'])
                                    <a class="btn btn-info btn-xs" target="_blank" title="Login as {{ $report->reported->name }}" href="{{ route('admin.loginAsUser', $report->reported->id) }}"><i class="fa fa-user"></i> {{ $report->reported->name }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <!-- /.box-body -->

                <div class="box-footer clearfix">
                    @include('partials.pagination', ['model' => $reports, 'url' => route('admin.reports')])
                </div>
                @endif
            </div>
            <!-- /.box -->
        </div>
    </div>
</section>
<!-- /.content -->
@endsection
