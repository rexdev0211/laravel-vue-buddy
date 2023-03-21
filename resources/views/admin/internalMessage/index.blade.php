@extends('layouts.adminLayout')

@section('scripts_init')
    <script src="{{ asset('backend/plugins/select2/select2.min.js') }}"></script>

    <script>
    $(function() {
        $('.select2').select2();
    });
    </script>
@endsection

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Internal Message</h3>
                    </div>
                    {!! Form::open(array('route' => ['admin.internalMessage.send'], 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped', 'id'=>'internalMessage-form')) !!}
                        <div class="box-body">
                            @include('partials.errors', ['errors' => $errors])

                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label col-md-2">Sender</label>
                                    <div class="col-md-9">
                                        {!! Form::select('sender_id', $senders, old('sender_id') ?? '', array('class'=>'form-control')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label col-md-2">Countries</label>
                                    <div class="col-md-9">
                                        {!! Form::select('countries', $countries, old('countries') ?? null, array('class'=>'form-control select2', 'multiple' => 'multiple', 'name'=>'countries[]', 'data-placeholder' => 'All Countries')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label col-md-2">Language</label>
                                    <div class="col-md-9">
                                        {!! Form::select('language', $languages, old('language') ?? '', array('class'=>'form-control')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label col-md-2">User Group</label>
                                    <div class="col-md-9">
                                        {!! Form::select('group', $groups, old('group') ?? '', array('class'=>'form-control')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label col-md-2">Message</label>
                                    <div class="col-md-9">
                                        <textarea name="message" class="form-control" rows="5">{{ old('message') ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            @if ($queueActive)
                            <div class="alert alert-warning">
                                Previous Internal Message is being processed.
                            </div>
                            @endif
                            <button type="submit" @if ($queueActive) disabled="disabled" @endif class="btn btn-primary">Send Message</button>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
@endsection
