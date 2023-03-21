@section('scripts_init')
    <script type="text/javascript">
        jQuery(document).ready(function() {

            CKEDITOR.replace(
                'the-page-content',
                {
                    toolbar : 'Full',
                    height:'500px',
                    width:'975px',
                    contentsCss: [

                    ]
                }
            );

        });
    </script>
@endsection



<!-- BEGIN FORM-->

    <div class="box-body">

        <div class="form-group">
            <label class="control-label col-md-3">Title <span class="text-red">*</span></label>
            <div class="col-md-9">
                {!! Form::text('title', null, ['required', 'class'=>'form-control', 'placeholder'=>'Page title']) !!}
            </div>
        </div>

        @if($type == 'edit' && $page->is_required == 'yes')
            <div class="form-group">
                <label class="control-label col-md-3">Url <span class="text-red">*</span></label>
                <div class="col-md-9">
                    <div style="padding-top: 7px">
                        {{ $page->url }}
                    </div>
                </div>
            </div>
        @else
            <div class="form-group">
                <label class="control-label col-md-3">Url <span class="text-red">*</span></label>
                <div class="col-md-9">
                    {!! Form::text('url', null, ['required', 'class'=>'form-control', 'placeholder'=>'page-url.html']) !!}
                </div>
            </div>
        @endif

        <div class="form-group">
            <label class="control-label col-md-3">Language <span class="text-red">*</span> </label>
            <div class="col-md-9">
                {!! Form::select('lang', $languages, null, ['class'=>'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-3">Meta Keywords </label>
            <div class="col-md-9">
                {!! Form::text('meta_keywords', null, ['class'=>'form-control', 'placeholder'=>'keyword 1, keyword 2']) !!}
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-3">Meta Description </label>
            <div class="col-md-9">
                {!! Form::textarea('meta_description', null, ['class'=>'form-control', 'placeholder'=>'Page Description', 'rows'=>'3']) !!}
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-3">Page Content <span class="text-red">*</span></label>
            <div class="col-md-9">
                {!! Form::textarea('content', isset($pageContent) ? $pageContent : null, ['class'=>'form-control', 'id'=>'the-page-content', 'rows'=>'20']) !!}
            </div>
        </div>

    </div>

    <div class="box-footer">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save</button>
                <a href="{{ route('admin.pages') }}" class="btn btn-default" data-dismiss="modal">Cancel</a>
            </div>
        </div>
    </div>


<!-- END FORM-->

