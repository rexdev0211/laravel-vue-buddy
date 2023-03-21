@extends('layouts.adminLayout')

@section('page_title')
    Videos Management
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

<script type="text/javascript">
    function deleteVideo(videoId) {
        $('#deleteVideoConfirmation').unbind().bind('click', function(){
            $('#alertDeleteVideo').modal('hide');

            window.location.href = `/admin/community/videos/${videoId}/delete`;
        });

        $('#alertDeleteVideo').modal('show');
    }
</script>

<section class="content" id="app">
    <div class="row">
        <div class="col-md-12">
            @include('partials.errors', ['errors' => $errors])
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-body">
                    {!! Form::open(array('route'=>'admin.community.videos', 'class'=>'form-inline custom-inline')) !!}
                        <input type="hidden" name="page" value="1" />

                        <div class="form-group">
                            {!! Form::select('filterVisible', $visibleOptions, Helper::getSessionUserPreference($sessionKey, 'filterVisible'), array('class'=>'form-control')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::select('filterManualNude', $manualNudeOptions, Helper::getSessionUserPreference($sessionKey, 'filterManualNude'), array('class'=>'form-control')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('filterOwnerName', Helper::getSessionUserPreference($sessionKey, 'filterOwnerName'), array('class'=>'form-control', 'placeholder'=>'Owner name')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('filterOwnerId', Helper::getSessionUserPreference($sessionKey, 'filterOwnerId'), array('class'=>'form-control', 'placeholder'=>'Owner ID', 'size'=>'10')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::submit('Filter', ['class'=>'btn btn-primary btn-block']) !!}
                        </div>

                        <div class="form-group">
                            <a href="{{ route('admin.community.videos') }}?resetFilters" class="btn btn-default btn-block">Reset</a>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Videos Management</h3>
                </div>
                <!-- /.box-header -->

                @if (!count($videos))
                <div class="box-body table-responsive">
                    <div>No videos were found</div>
                </div>
                @else
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover table-striped">
                        <tr>
                            <th>Details</th>
                            <th>Video</th>
                            <th>Actions</th>

                            <th>Details</th>
                            <th>Video</th>
                            <th>Actions</th>

                            <th>Details</th>
                            <th>Video</th>
                            <th>Actions</th>
                        </tr>
                        @foreach($videos as $key => $video)
                            <?php
                                $videoClass = !$video->manual_rating ? '' : (in_array($video->manual_rating, ['unrated', 'adult', 'prohibited']) ? 'bg-danger' : 'bg-success');
                            ?>
                            @if ($key%3 == 0)
                                <tr>
                            @endif
                                <td class="{{ $videoClass }}">
                                    <div>ID: {{ $video->id }}</div>
                                    <div>Owner: <a href="{{ route('admin.users.view', $video->user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $video->user->name }} [<strong>{{ $video->user->id }}</strong>]</a></div>
                                    <div>Visibility: {{ $video->visible_to }}</div>
                                    <div>Rating: {{ $video->manual_rating }}</div>
                                </td>
                                <td class="{{ $videoClass }}">
                                    <a data-lity style="float: left; margin-right:15px" target="_blank" href="{{ $video->getVideoUrl() }}">
                                        <img data-lity src="{{ $video->getThumbnailUrl() }}" alt="" style="max-width: 100px; max-height: 100px"/>
                                    </a>
                                </td>
                                <td class="{{ $videoClass }} moderationPhoto smallBox" style="border-right: 1px solid darkgray">
                                    <div class="moderationPhotoActions text-center smallButtons" style="width: 100%;">
                                        <a href="{{ route('admin.community.videos.rate', ['videoId' => $video->id, 'type' => 'clear']) }}" class="bg-success">Clear</a>
                                        <a href="{{ route('admin.community.videos.rate', ['videoId' => $video->id, 'type' => 'soft']) }}" class="bg-warning">Soft</a>
                                        <a href="{{ route('admin.community.videos.rate', ['videoId' => $video->id, 'type' => 'adult']) }}" class="bg-danger">Hard</a>
                                        <a href="{{ route('admin.community.videos.rate', ['videoId' => $video->id, 'type' => 'prohibited']) }}" class="bg-default prohibited">Prohibited</a>
                                        <button type="button" onclick="deleteVideo({{ $video->id }})" class="btn btn-danger">Delete</button>
                                    </div>
                                </td>
                            @if ($key % 3 == 2)
                                </tr>
                            @endif
                        @endforeach
                    </table>
                </div>
                <!-- /.box-body -->

                <div class="box-footer clearfix">
                    @include('partials.pagination', ['model' => $videos, 'url' => route('admin.community.videos')])
                </div>
                @endif
            </div>
            <!-- /.box -->
        </div>
    </div>
</section>

<div class="modal fade" tabindex="-1" role="dialog" id="alertDeleteVideo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Are you sure?</h4>
            </div>
            <div class="modal-body">
                <p>You want to delete users uploaded video.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteVideoConfirmation">Delete video</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- /.content -->
@endsection
