@extends('layouts.adminLayout')

@section('content')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ isset($item) ? 'Edit PROmo Code' : 'Add PROmo Code' }}</h3>
                </div>

                {!! Form::open(array('route' => 'admin.promo.save', 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}
                <input type="hidden" name="id" value="{{ old('id') ? old('id') : (isset($item) ? $item->id : 0) }}" />
                <div class="box-body">
                    @include('partials.errors', ['errors' => $errors])
                    <div class="form-group row">
                        <label class="control-label col-md-3">Code Name</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="code" value="{{ old('code') ? old('code') : (isset($item) ? $item->title : '') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3">Expiry Date</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control datepicker" name="expiration_time" value="{{ old('expiration_time') ? old('expiration_time') : (isset($item) ? $item->expiration_time->format('d.m.Y') : '') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3">Limit</label>
                        <div class="col-md-9">
                            <input type="number" min="0" class="form-control" name="limit" value="{{ old('limit') ? old('limit') : (isset($item) ? $item->limit : 1) }}" />
                        </div>
                    </div>
                </div>
                <div class="box-header with-border">
                    <h3 class="box-title">PROmo Time</h3>
                </div>
                <div class="box-body">
                    <div class="form-group row">
                        <label class="control-label col-md-2">Months</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control" placeholder="Months" name="months" value="{{ old('months') ? old('months') : (isset($item) ? $item->months : 0) }}" />
                        </div>
                        <label class="control-label col-md-2">Weeks</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control" placeholder="Weeks" name="weeks" value="{{ old('weeks') ? old('weeks') : (isset($item) ? $item->weeks : 0) }}" />
                        </div>
                        <label class="control-label col-md-2">Days</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control" placeholder="Days" name="days" value="{{ old('days') ? old('days') : (isset($item) ? $item->days : 0) }}" />
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save</button>
                            <a href="{{ route('admin.promo') }}" class="btn btn-default" data-dismiss="modal">Cancel</a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts_init')
<link rel="stylesheet" href="{{ asset('backend/plugins/datepicker/bootstrap-datepicker.min.css') }}">
<script src="{{ asset('backend/plugins/datepicker/bootstrap-datepicker.min.js') }}"></script>
<script>
$(function(){
    $('.datepicker').datepicker({
        format: 'dd.mm.yyyy',
    });
});
</script>
@endsection
