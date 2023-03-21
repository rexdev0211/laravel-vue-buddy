@extends('layouts.adminLayout')

@section('content')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN SAMPLE TABLE PORTLET-->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">PROmo Codes</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.promo.create') }}" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Add Code</a>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    @if ($list->total() < 1)
                        <div>No PROmo Codes added yet</div>
                    @else
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Code Name</th>
                                    <th>Creation Date</th>
                                    <th>Expiration Date</th>
                                    <th>PROmo Time</th>
                                    <th>Status</th>
                                    <th>Used/Limit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($list->items() as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ $item->created_at->format('d, M Y H:i') }}</td>
                                    <td>{{ $item->expiration_time->format('d, M Y') }}</td>
                                    <td>{{ $item->duration }}</td>
                                    <td>{{ $item->status == 0 ? 'Invalidated' : ($item->expiration_time->gt(\Carbon\Carbon::createFromTimestamp(strtotime('now'))) ? ($item->limit == 0 || $item->limit > $item->used_count ? 'Active' : 'Out of limit') : 'Expired') }}</td>
                                    <td>{{ $item->used_count }}/{!! $item->limit == 0 ? '&#8734;' : $item->limit !!}</td>

                                    <td class="actionCell">
                                        <a href="{{ route('admin.promo.edit', $item->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Edit</a>
                                        @if ($item->status > 0)
                                        <form method="POST" action="{{ route('admin.promo.invalidate', $item->id) }}" style="display: inline;">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-minus-circle"></i> Invalidate</button>
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
