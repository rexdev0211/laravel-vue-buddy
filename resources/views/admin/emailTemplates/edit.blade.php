@extends('layouts.adminLayout')

@section('scripts_init')
    <script type="text/javascript">
        jQuery(document).ready(function() {
            CKEDITOR.replace( 'body',{toolbar : 'Email',height:'300px'} );
        });
    </script>
@endsection

@section('content')

    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> Edit email template </h3>
                    </div>
                    <div class="box-body">

                        @include('partials.errors', ['errors'=>$errors])

                        {!! Form::model($emailTemplateLang, array('route' => ['admin.emailTemplates.update', $emailTemplate->id], 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}

                        <!-- BEGIN FORM-->

                            <div class="form-body">

                                <div class="form-group">
                                    <label class="control-label col-md-2">Template Name </label>
                                    <div class="col-md-9">
                                        <div style="padding-top: 7px">
                                            {{ $emailTemplate->name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-2">Template Language</label>
                                    <div class="col-md-9">
                                        {!! Form::text('lang', null, ['required', 'class'=>'form-control', 'readonly']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-2">Email Subject <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        {!! Form::text('subject', null, ['required', 'class'=>'form-control']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-2">Email Body <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        {!! Form::textarea('body', null, ['class'=>'form-control']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-2">Notes </label>
                                    <div class="col-md-9">
                                        {!! nl2br($emailTemplate->notes) !!}
                                    </div>
                                </div>

                            </div>
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-offset-2 col-md-9">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save</button>
                                        <a href="{{ route('admin.emailTemplates') }}" class="btn btn-default" data-dismiss="modal">Cancel</a>
                                    </div>
                                </div>
                            </div>


                            <!-- END FORM-->

                            {!! Form::close() !!}

                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection