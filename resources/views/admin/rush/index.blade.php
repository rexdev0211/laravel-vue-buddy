@extends('layouts.adminLayout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN SAMPLE TABLE PORTLET-->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Strips</h3>
                </div>
                <div class="box-body table-responsive">
                    @if ($list->total() < 1)
                        <div>No Strips added yet</div>
                    @else
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Owner</th>
                                    <th>Days (streak)</th>
                                    <th>Favorites</th>
                                    <th>Applause</th>
                                    <th>Views</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($list->items() as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->title }}</td>
                                    <td><a href="{{ route('admin.users.view', $item->author->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $item->author->name }} [<strong>{{ $item->author->id }}</strong>]</a></td>
                                    <td>{{ $item->streak }}</td>
                                    <td>{{ $item->favorites_count }}</td>
                                    <td>{{ $item->applauses_count }}</td>
                                    <td>{{ $item->views_count }}</td>

                                    <td class="actionCell">
                                        <a href="/rush/{{ $item->id }}" target="_blank" class="btn btn-xs btn-info">
                                            <i class="fa fa-eye"></i> Check Strip
                                        </a>
                                        @if ($item->status == 'active')
                                        <form method="POST" action="{{ route('admin.rush.suspend', $item->id) }}" style="display: inline;">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-xs btn-warning"><i class="fa fa-ban"></i> Suspend</button>
                                        </form>
                                        @else
                                        <form method="POST" action="{{ route('admin.rush.activate', $item->id) }}" style="display: inline;">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check-circle"></i> Activate</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <div class="box-footer clearfix">
                    @if ($list->total() > 0)
                        {{ $list->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
