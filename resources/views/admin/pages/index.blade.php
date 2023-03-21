@extends('layouts.adminLayout')

@section('content')

    <section class="content">
        {{--<div class="row">--}}
        {{--<div class="col-md-12">--}}
        {{--<div class="box box-primary">--}}
        {{--<div class="box-header with-border">--}}
        {{--{!! Form::open(array('route'=>'admin.pages', 'class'=>'form-inline')) !!}--}}
        {{--{!! Form::text('filterTitle', Session::get('pages.filterTitle'), array('placeholder'=>'Page Title', 'class'=>'form-control')) !!}--}}
        {{--{!! Form::text('filterUrl', Session::get('pages.filterUrl'), array('placeholder'=>'Page Url', 'class'=>'form-control')) !!}--}}
        {{--{!! Form::submit('Search', ['class'=>'btn btn-primary']) !!}--}}
        {{--{!! Form::submit('Show all', ['name'=>'resetFilters', 'class'=>'btn btn-default']) !!}--}}
        {{--{!! Form::close() !!}--}}
        {{--</div>--}}
        {{--</div>--}}
        {{--</div>--}}
        {{--</div>--}}

        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN SAMPLE TABLE PORTLET-->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> Pages Management </h3>
                        <div class="box-tools">
                            <a href="{{ route('admin.pages.add') }}" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Add Page</a>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        @if(!$pages->count())
                            <div>No pages were found</div>
                        @else
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>{!! Helper::getOrderByLink(route('admin.pages'), $sessionKey, 'Title', 'title') !!}</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.pages'), $sessionKey, 'Url', 'url') !!}</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.pages'), $sessionKey, 'Language', 'lang') !!}</th>
                                    <th>Meta Keywords</th>
                                    <th>Meta Description</th>
                                    <th>{!! Helper::getOrderByLink(route('admin.pages'), $sessionKey, 'Updated', 'updated_at') !!}</th>
                                    <th class="actionCell">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($pages as $page)
                                    <tr class="{{ \Carbon\Carbon::now()->diffInMinutes($page->updated_at) < 5 ? 'text-success' : ''}}">
                                        <td>{{ str_limit($page->title, 75) }}</td>
                                        <td>{{ $page->url }}</td>
                                        <td>{{ $page->lang }}</td>
                                        <td>{{ str_limit($page->meta_keywords, 50) }}</td>
                                        <td>{{ str_limit($page->meta_description, 50) }}</td>
                                        <td>{{ Helper::formatDate($page->updated_at) }}</td>
                                        <td class="actionCell">
                                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Edit</a>
{{--                                            <a href="{{ route('admin.pages.editContent', $page->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Preview Mode</a>--}}
                                            @if($page->is_required != 'yes')
                                                {{--<a href="javascript:void(0)" class="btn btn-danger btn-xs" onclick="confirmDelete('{{$page->title}}', '{{ route('admin.pages.delete', $page->id) }}')"><i class="fa fa-trash"></i> Delete </a>--}}
                                                @include('partials.deleteButton', ['name' => $page->title, 'url' => route('admin.pages.delete', $page->id), 'alert' => 'This action will delete selected page.'])
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>

                    <div class="box-footer clearfix">
                        @if($pages->count())
                            @include('partials.pagination', ['model'=>$pages, 'url'=>route('admin.pages')])
                        @endif
                    </div>
                </div>
                <!-- END SAMPLE TABLE PORTLET-->
            </div>
        </div>
    </section>

@endsection
