@extends('layouts.adminLayout')

@section('scripts_init')
    <script type="text/javascript">
        jQuery(document).ready(function() {
            CKEDITOR.replace( 'body[en]',{toolbar : 'Email',height:'300px'} );
            CKEDITOR.replace( 'body[de]',{toolbar : 'Email',height:'300px'} );
            CKEDITOR.replace( 'body[fr]',{toolbar : 'Email',height:'300px'} );
        });

        function getFormData() {
            for (i in CKEDITOR.instances) {
                if (CKEDITOR.instances.hasOwnProperty(i)) {
                    CKEDITOR.instances[i].updateElement()
                }
            }

            return $('#newsletter-form').serialize();
        }

        function getNumberOfUsers() {
            makeAjaxRequest(`/admin/newsletter/send?getCount=1`, getFormData(), 'POST')
                .then(data => {
//                    $('#nr-users').html(`Email will be sent to ${data} users`)
                    $('#nr-users').html(data)
                })
        }

        function sendPreviewMail() {
            makeAjaxRequest(`/admin/newsletter/send?sendPreview=1`, getFormData(), 'POST')
                .then(data => {
//                    showNotification(`Preview mail was successfully sent to ${data}`)
                    showNotification(data)
                })
        }

        function sendMailToUsers() {
            $('#send-all').attr('disabled', 'disabled');

            makeAjaxRequest(`/admin/newsletter/send`, getFormData(), 'POST')
                .then(data => {
//                    showNotification(`Mail was successfully sent to ${data} users`)
                    showNotification(data)
                })
                .finally(() => {
                    $('#send-all').attr('disabled', false);
                })
        }
    </script>
@endsection

@section('content')

    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> Newsletter </h3>
                    </div>
                    <div class="box-body">

                    @include('partials.errors', ['errors'=>$errors])

                    {!! Form::open(array('route' => ['admin.newsletter.send'], 'enctype'=>"multipart/form-data", 'class'=>'form-horizontal form-bordered form-row-stripped', 'id'=>'newsletter-form')) !!}

                        <div class="form-body">
                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    <h3>Users</h3>
                                </div>
                            </div>

                            {{--<div class="form-group">--}}
                                {{--<label class="control-label col-md-2">Account status</label>--}}
                                {{--<div class="col-md-9">--}}
                                    {{--{!! Form::select('filterTrashed', $trashedOptions, '', array('class'=>'form-control')) !!}--}}
                                {{--</div>--}}
                            {{--</div>--}}

                            <div class="form-group">
                                <label class="control-label col-md-2">Language</label>
                                <div class="col-md-9">
                                    {!! Form::select('filterLanguage', $languages, '', array('class'=>'form-control')) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-2">Country</label>
                                <div class="col-md-9">
                                    {!! Form::select('filterCountry', $countries, '', array('class'=>'form-control')) !!}
                                </div>
                            </div>

                            {{--<div class="form-group">--}}
                                {{--<label class="control-label col-md-2">State</label>--}}
                                {{--<div class="col-md-9">--}}
                                    {{--{!! Form::text('filterState', '', array('class'=>'form-control', 'placeholder'=>'')) !!}--}}
                                {{--</div>--}}
                            {{--</div>--}}

                            {{--<div class="form-group">--}}
                                {{--<label class="control-label col-md-2">Locality</label>--}}
                                {{--<div class="col-md-9">--}}
                                    {{--{!! Form::text('filterLocality', '', array('class'=>'form-control', 'placeholder'=>'')) !!}--}}
                                {{--</div>--}}
                            {{--</div>--}}

                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    <button type="button" class="inline" onclick="getNumberOfUsers()">Calculate nr. of users</button>
                                    <div class="inline" style="padding-left: 15px;" id="nr-users"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    <h3>English mail content</h3>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-2">Subject</label>
                                <div class="col-md-9">
                                    {!! Form::text('subject[en]', null, ['required', 'class'=>'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-2">Body</label>
                                <div class="col-md-9">
                                    {!! Form::textarea('body[en]', null, ['class'=>'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    Variables: {FULL_NAME}, {EMAIL}, {UNSUBSCRIBE_LINK}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    <h3>German mail content</h3>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-2">Subject</label>
                                <div class="col-md-9">
                                    {!! Form::text('subject[de]', null, ['required', 'class'=>'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-2">Body</label>
                                <div class="col-md-9">
                                    {!! Form::textarea('body[de]', null, ['class'=>'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    Variables: {FULL_NAME}, {EMAIL}, {UNSUBSCRIBE_LINK}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    <h3><a href="javascript:void(0)" onclick="$('#fr_container').toggle()">French mail content</a></h3>
                                </div>
                            </div>

                            <div id="fr_container" style="display:none">
                                <div class="form-group">
                                    <label class="control-label col-md-2">Subject</label>
                                    <div class="col-md-9">
                                        {!! Form::text('subject[fr]', null, ['required', 'class'=>'form-control']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-2">Body</label>
                                    <div class="col-md-9">
                                        {!! Form::textarea('body[fr]', null, ['class'=>'form-control']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-9 col-md-offset-2">
                                        Variables: {FULL_NAME}, {EMAIL}, {UNSUBSCRIBE_LINK}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    <h3><a href="javascript:void(0)" onclick="$('#it_container').toggle()">Italian mail content</a></h3>
                                </div>
                            </div>

                            <div id="it_container" style="display:none">
                                <div class="form-group">
                                    <label class="control-label col-md-2">Subject</label>
                                    <div class="col-md-9">
                                        {!! Form::text('subject[it]', null, ['required', 'class'=>'form-control']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-2">Body</label>
                                    <div class="col-md-9">
                                        {!! Form::textarea('body[it]', null, ['class'=>'form-control']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-9 col-md-offset-2">
                                        Variables: {FULL_NAME}, {EMAIL}, {UNSUBSCRIBE_LINK}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    <h3><a href="javascript:void(0)" onclick="$('#nl_container').toggle()">Dutch mail content</a></h3>
                                </div>
                            </div>

                            <div id="nl_container" style="display:none">
                                <div class="form-group">
                                    <label class="control-label col-md-2">Subject</label>
                                    <div class="col-md-9">
                                        {!! Form::text('subject[nl]', null, ['required', 'class'=>'form-control']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-2">Body</label>
                                    <div class="col-md-9">
                                        {!! Form::textarea('body[nl]', null, ['class'=>'form-control']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-9 col-md-offset-2">
                                        Variables: {FULL_NAME}, {EMAIL}, {UNSUBSCRIBE_LINK}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    <h3>Send a preview mail</h3>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">
                                    <div style="padding-bottom: 10px;">Use a mail address that exists in the newsletter list</div>
                                    <input type="email" name="preview-mail" value="{{ Auth::user()->email }}" class="inline"/>
                                    <button class="inline" type="button" onclick="sendPreviewMail()">Send a preview mail</button>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <div class="row">
                                <div class="col-md-offset-2 col-md-9">
                                    <button type="button" id="send-all" onclick="sendMailToUsers()" class="btn btn-primary"><i class="fa fa-check"></i> Send mail to users</button>
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