@extends('layouts.adminLayout')

@section('content')

    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="box box-primary ">
                    <div class="box-header with-border">
                        <h3 class="box-title"> My Profile </h3>
                    </div>
                    <div class="box-body form2">

                        @include('partials.errors', ['errors'=>$errors])

                        {!! Form::model($user, array('route' => ['admin.profile.update'], 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}

                        <div class="form-body">

                            <div class="form-group">
                                <label class="control-label col-md-3">Email address <span class="required">*</span></label>
                                <div class="col-md-9">
                                    {!! Form::email('email', null, ['required', 'class'=>'form-control']) !!}
                                </div>
                            </div>

                            <div id="password-tr" class="form-group">
                                <label class="control-label col-md-3">New Password</label>
                                <div class="col-md-9">
                                    {!! Form::password('password', ['pattern'=>'.{6,}', 'class'=>'form-control',
                                    'title'=>'Minimum 6 chars required', 'onchange'=>'form.password2.pattern = this.value',
                                    'placeholder'=>'Leave empty to ignore']) !!}
                                </div>
                            </div>

                            <div id="password2-tr" class="form-group">
                                <label class="control-label col-md-3">Confirm Password</label>
                                <div class="col-md-9">
                                    {!! Form::password('password2', ['title'=>'Password and confirmation must match',
                                    'class'=>'form-control', 'placeholder'=>'Leave empty to ignore']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3">Full Name <span class="required">*</span></label>
                                <div class="col-md-9">
                                    {!! Form::text('name', null, ['required', 'class'=>'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3">My photo </label>
                                <div class="col-md-9">
                                    <a data-lity style="float: left; margin-right:15px" target="_blank" href="{{ \App\UserPhoto::getAdminPhotoUrl($user->id, 'orig') }}">
                                        <img data-lity src="{{ \App\UserPhoto::getAdminPhotoUrl($user->id, '45x45') }}" alt="" />
                                    </a>

                                    <input type="file" name="photo" size="20" >
                                    <p class="help-block">Image files must be gif, jpeg, png or jpg.</p>
                                </div>
                            </div>

                        </div>


                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save</button>
                                </div>
                            </div>
                        </div>

                        {!! Form::close() !!}

                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection