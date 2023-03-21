@extends('layouts.adminLayout')

@section('content')

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @include('partials.errors', ['errors'=>$errors])
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> Blocked domains </h3>
                    </div>

                    {!! Form::open(array('route' => 'admin.blockedDomains.updateDomains', 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}

                    <div class="box-body">

                        <div class="form-group">
                            <label class="control-label col-md-2">Domains (one per line) </label>
                            <div class="col-md-10">
                                {!! Form::textarea('domains', $domains, ['class'=>'form-control', 'rows'=>'30']) !!}
                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save</button>
                            </div>
                        </div>
                    </div>

                    {!! Form::close() !!}

                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> Blocked shorteners </h3>
                    </div>

                    {!! Form::open(array('route' => 'admin.blockedDomains.updateShorteners', 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}

                    <div class="box-body">

                        <div class="form-group">
                            <label class="control-label col-md-2">Domains (one per line) </label>
                            <div class="col-md-10">
                                {!! Form::textarea('shorteners', $shorteners, ['class'=>'form-control', 'rows'=>'30']) !!}
                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save</button>
                            </div>
                        </div>
                    </div>

                    {!! Form::close() !!}

                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> Blocked IPs </h3>
                    </div>

                    {!! Form::open(array('route' => 'admin.blockedDomains.updateIPs', 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}

                    <div class="box-body">

                        <div class="form-group">
                            <label class="control-label col-md-2">IP (one per line) </label>
                            <div class="col-md-10">
                                {!! Form::textarea('ips', $ips, ['class'=>'form-control', 'rows'=>'30']) !!}
                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save</button>
                            </div>
                        </div>
                    </div>

                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </section>

@endsection
