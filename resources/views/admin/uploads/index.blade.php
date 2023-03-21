@extends('layouts.adminLayout')

@section('content')

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN SAMPLE TABLE PORTLET-->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> File uploads </h3>
                        <div class="box-tools">
                            {!! Form::open(array('route' => 'admin.uploads.add', 'id'=>"file-form", 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}
                                <input type="file" name="file" id="file" onchange="$('#file-form').submit()" style="display:none" />
                                <a onclick="$('#file').click()" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Upload</a>
                            {!! Form::close() !!}
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        @if(!count($files))
                            <div>No files were found</div>
                        @else
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Preview</th>
                                    <th>Name</th>
                                    <th>URL</th>
                                    <th class="actionCell">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($files as $file)
                                    <tr>
                                        <td>
                                            @if ($file['image'])
                                                <a data-lity href="{{ $file['path']}}">
                                                    <img src="{{ $file['path']}}" height="100" alt="" />
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $file['name']}}</td>
                                        <td>{{ $file['path']}}</td>
                                        <td class="actionCell">
                                            @include('partials.deleteButton', ['name' => $file['name'], 'url' => route('admin.uploads.delete', $file['name']), 'alert' => 'This action will delete selected file.'])
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
                <!-- END SAMPLE TABLE PORTLET-->
            </div>
        </div>
    </section>

@endsection
