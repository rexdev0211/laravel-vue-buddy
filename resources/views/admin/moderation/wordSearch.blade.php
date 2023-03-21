@extends('layouts.adminLayout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-body">
                    {!! Form::open(['route' => 'admin.moderation.wordSearch', 'class' => 'form-inline custom-inline']) !!}
                        <input type="hidden" name="page" value="1" />

                        <div class="form-group">
                            {!! Form::select('type', ['user' => 'User', 'event' => 'Event'], Helper::getSessionUserPreference($sessionKey, 'type'), ['class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('search', Helper::getSessionUserPreference($sessionKey, 'search'), ['class' => 'form-control', 'placeholder' => 'Search phrase']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::submit('Search', ['class' => 'btn btn-primary btn-block']) !!}
                        </div>

                        <div class="form-group">
                            <a href="{{ route('admin.moderation.wordSearch') }}?resetFilters" class="btn btn-default btn-block">Reset</a>
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
                    <h3 class="box-title">Matches</h3>
                </div>
                <!-- /.box-header -->

                @if (!count($matches))
                <div class="box-body table-responsive">
                    <div>No matches were found</div>
                </div>
                @else
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>ID</th>
                                <th>Details</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matches as $item)
                            <tr>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->id }}</td>
                                <td>{!! $item->where !!}</td>
                                <td>{!! $item->user !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
                <!-- /.box-body -->

                <div class="box-footer clearfix">
                    @include('partials.pagination', ['model' => $matches, 'url' => route('admin.moderation.wordSearch')])
                </div>
                @endif
            </div>
            <!-- /.box -->
        </div>
    </div>
</section>
@endsection

@section('scripts_init')
<script>
</script>
@endsection
