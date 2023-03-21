@extends('layouts.adminLayout')

@section('content')

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN SAMPLE TABLE PORTLET-->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> Email Templates </h3>
                    </div>
                    <div class="box-body table-responsive">

                        @if(!$emailTemplates->count())

                            <div>No Email Templates were found</div>

                        @else

                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th>{!! Helper::getOrderByLink(route('admin.emailTemplates'), 'emailTemplates', 'Template Name', 'name') !!}</th>
                                        <th>Subject</th>
                                        <th>Body</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($emailTemplates as $emailTemplate)
                                        <tr>
                                            <td>{{ $emailTemplate->name }}</td>
                                            <td>
                                                @foreach ($langs as $lang => $langShow)
                                                    <div>
                                                        <b>{{ $lang }}</b>: {{ $emailTemplate->emailTemplateLangs->where('lang', $lang)->first() && $emailTemplate->emailTemplateLangs->where('lang', $lang)->first()['subject'] ? $emailTemplate->emailTemplateLangs->where('lang', $lang)->first()['subject'] : '---' }}
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach ($langs as $lang => $langShow)
                                                    <div>
                                                        <b>{{ $lang }}</b>: {!! str_limit(strip_tags($emailTemplate->emailTemplateLangs->where('lang', $lang)->first() && $emailTemplate->emailTemplateLangs->where('lang', $lang)->first()['body'] ? $emailTemplate->emailTemplateLangs->where('lang', $lang)->first()['body'] : '---'), 75) !!}
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td>{!! nl2br($emailTemplate->notes) !!}</td>
                                            <td>
                                                @foreach ($langs as $lang => $langShow)
                                                    <div>
                                                        <a href="{{ route('admin.emailTemplates.edit', $emailTemplate->id) }}?lang={{ $lang }}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> {{ $langShow }} </a>
                                                    </div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @include('partials.pagination', ['model'=>$emailTemplates, 'url'=>route('admin.emailTemplates')])

                        @endif

                    </div>
                </div>
                <!-- END SAMPLE TABLE PORTLET-->
            </div>
        </div>
    </section>

@endsection
