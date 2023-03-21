@extends('layouts.adminLayout')

@section('content')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @include('partials.errors', ['errors' => $errors])
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Reserved Buddy Names</h3>
                </div>

                {!! Form::open(array('route' => 'admin.buddyLinks.update', 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}
                    <div class="box-body">
                        <div class="form-group">
                            <label class="control-label col-md-2">Buddy Name (one per line)</label>
                            <div class="col-md-10">
                                {!! Form::textarea('buddy_links', $buddyLinks, ['class'=>'form-control', 'rows'=>'30']) !!}
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

@section('scripts_init')
<script>
</script>
@endsection
