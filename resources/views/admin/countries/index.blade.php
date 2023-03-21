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
                        <h3 class="box-title">Countries</h3>

                        <div class="box-tools">
                            {{--here go buttons--}}
                        </div>
                    </div>
                    <!-- /.box-header -->

                    @if (!count($countries))
                        <div class="box-body table-responsive">
                            <div>No countries were found</div>
                        </div>
                    @else
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-hover table-striped">
                                <tr>
                                    <th>ID</th>
                                    <th>Country name</th>
                                    <th>Country code</th>
                                    <th>wasRecentlyOnline time</th>
                                    <th>Changed date</th>
                                    <th>Actions</th>
                                </tr>
                                @foreach($countries as $country)
                                    <tr>
                                        <td>{{ $country->id }}</td>
                                        <td>{{ $country->name }}</td>
                                        <td>{{ $country->code }}</td>
                                        <td>{{ \Carbon\CarbonInterval::seconds($country->was_recently_online_time)->cascade()->forHumans() }}</td>
                                        <td>{{ Helper::formatDateMysql($country->changed_date, 'M j, Y') }}</td>
                                        <td>
                                            <a class="btn btn-success btn-xs" title="Edit" href="{{ route('admin.onlineCountries.edit', $country->id) }}"><i class="fa fa-pencil"></i> Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer clearfix">
                            @include('partials.pagination', ['model'=>$countries, 'url'=>route('admin.onlineCountries')])
                        </div>
                    @endif
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
    <!-- /.content -->
@endsection
