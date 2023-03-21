@extends('layouts.adminLayout')

@section('content')

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                    <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> Edit Page </h3>
                    </div>

                    @include('partials.errors', ['errors'=>$errors])

                    {!! Form::model($page, array('route' => ['admin.pages.update', $page->id], 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}
                        @include('admin.pages.form', ['type'=>'edit'])
                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </section>

@endsection