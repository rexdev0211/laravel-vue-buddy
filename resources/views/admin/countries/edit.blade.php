@extends('layouts.adminLayout')

@section('content')

    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> Edit Online by Country </h3>
                    </div>
                    <div class="box-body">

                        @include('partials.errors', ['errors' => $errors])

                        {!! Form::model($country, array('route' => ['admin.onlineCountries.update', $country->id], 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}

                        <!-- BEGIN FORM-->

                            <div class="form-body">

                                <div class="form-group">
                                    <label class="control-label col-md-2">Country Name </label>
                                    <div class="col-md-9">
                                        <div style="padding-top: 7px">
                                            {{ $country->name }}
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="box-header with-border">
                                <h3 class="box-title">wasRecentlyOnline time <span class="required">*</span></h3>
                            </div>
                            <div class="box-body">
                                <div class="form-group row">
                                    <label class="control-label col-md-2">Days</label>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" placeholder="Days" name="days" min="0" value="{{ old('days') ? old('days') : $days }}" />
                                    </div>
                                    <label class="control-label col-md-2">Hours</label>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" placeholder="Hours" name="hours" min="0" value="{{ old('hours') ? old('hours') : $hours }}" />
                                    </div>
                                    <label class="control-label col-md-2">Minutes</label>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" placeholder="Minutes" name="minutes" min="0" value="{{ old('minutes') ? old('minutes') : $minutes }}" />
                                    </div>
                                </div>
                            </div>

                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-offset-2 col-md-9">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save</button>
                                        <a href="{{ route('admin.onlineCountries') }}" class="btn btn-default" data-dismiss="modal">Cancel</a>
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