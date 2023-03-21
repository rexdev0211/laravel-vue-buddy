@extends('layouts.adminLayout')

@section('content')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @include('partials.errors', ['errors' => $errors])
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Restricted</h3>
                </div>

                {!! Form::open(array('route' => 'admin.moderation.wordFilter', 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}
                    <input type="hidden" name="type" value="restricted" />

                    <div class="box-body">
                        <div class="form-group">
                            <label class="control-label col-md-2">Words or Phrases (one per line)</label>

                            <div class="col-md-10">
                                {!! Form::textarea('words', $restricted, ['class'=>'form-control', 'rows'=>'30']) !!}
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

        <div class="col-md-6">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Prohibited</h3>
                </div>

                {!! Form::open(array('route' => 'admin.moderation.wordFilter', 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped')) !!}
                    <input type="hidden" name="type" value="prohibited" />

                    <div class="box-body">
                        <div class="form-group">
                            <label class="control-label col-md-2">Words or Phrases (one per line)</label>

                            <div class="col-md-10">
                                {!! Form::textarea('words', $prohibited, ['class'=>'form-control', 'rows'=>'30']) !!}
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
