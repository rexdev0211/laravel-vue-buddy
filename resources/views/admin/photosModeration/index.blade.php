@extends('layouts.adminLayout')

@section('page_title')
    Photos Management
@endsection

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

    <style type="text/css">
        .form-group {
            margin-bottom: 10px !important;
        }
    </style>

    <script type="text/javascript">
        function markSafe(photoId) {
            window.location.href = `/admin/photosModeration/changeRating/${photoId}/safe`
        }

        function markUnsafe(photoId) {
            window.location.href = `/admin/photosModeration/changeRating/${photoId}/unsafe`
        }

        function markClear(photoId) {
            window.location.href = `/admin/moderation/photos/${photoId}/rate/clear`;
        }

        function markSoft(photoId) {
            window.location.href = `/admin/moderation/photos/${photoId}/rate/soft`;
        }

        function markAdult(photoId) {
            window.location.href = `/admin/moderation/photos/${photoId}/rate/adult`;
        }

        function markProhibited(photoId) {
            window.location.href = `/admin/moderation/photos/${photoId}/rate/prohibited`;
        }

        function deleteImage(photoId) {
            $('#deleteImageConfirmation').unbind().bind('click', function(){
                $('#alertDeleteImage').modal('hide');

                window.location.href = `/admin/moderation/photos/${photoId}/delete`;
            });

            $('#alertDeleteImage').modal('show');
        }
    </script>

    <section class="content" id="app">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-body">
                        {!! Form::open(array('route'=>'admin.photosModeration', 'class'=>'form-inline custom-inline')) !!}

                        <input type="hidden" name="page" value="1" />

                        <div class="form-group">
                            {!! Form::select('filterDefault', $defaultOptions, Helper::getSessionUserPreference($sessionKey, 'filterDefault'), array('class'=>'form-control')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::select('filterVisible', $visibleOptions, Helper::getSessionUserPreference($sessionKey, 'filterVisible'), array('class'=>'form-control')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::select('filterNude', $nudeOptions, Helper::getSessionUserPreference($sessionKey, 'filterNude'), array('class'=>'form-control')) !!}
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
                            <a href="{{ route('admin.photosModeration') }}?resetFilters" class="btn btn-default btn-block">Reset</a>
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
                        <h3 class="box-title">Photos Management</h3>
                    </div>
                    <!-- /.box-header -->

                    @if (!count($photos))
                        <div class="box-body table-responsive">
                            <div>No photos were found</div>
                        </div>
                    @else
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-hover table-striped">
                                <tr>
                                    <th>Details</th>
                                    <th>Photo</th>
                                    <th>Actions</th>

                                    <th>Details</th>
                                    <th>Photo</th>
                                    <th>Actions</th>

                                    <th>Details</th>
                                    <th>Photo</th>
                                    <th>Actions</th>
                                </tr>
                                @foreach($photos as $key => $photo)
                                    <?php
                                        $photoClass = !$photo->nudity_rating ? '' : ($photo->nudity_rating > $startRating ? 'bg-danger' : 'bg-success');
                                    ?>
                                    @if ($key%3 == 0)
                                        <tr>
                                    @endif
                                        <td class="{{$photoClass}}">
                                            <div> ID: {{ $photo->id }} </div>
                                            <div> Owner: <a href="{{ route('admin.users.view', $photo->user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $photo->user->name }} [<strong>{{ $photo->user->id }}</strong>]</a></div>
                                            @if ($photo->nudity_rating)
                                                <div style="font-weight: 700;"> Rating: {{ $photo->nudity_rating }} </div>
                                            @endif
                                            <div>Rating: {{ $photo->manual_rating }}</div>
                                        </td>
                                        <td class="{{$photoClass}}">
                                            <a data-lity style="float: left; margin-right:15px" target="_blank" href="{{ $photo->getUrl('orig', true) }}">
                                                <img data-lity src="{{ $photo->getUrl('180x180', true) }}" alt="" style="height: 100px;"/>
                                            </a>
                                        </td>
                                        <td class="{{$photoClass}} moderationPhoto smallBox" style="border-right: 1px solid darkgray">
                                            <div class="moderationPhotoActions text-center smallButtons" style="width: 100%;">
                                                <button type="button" onclick="markClear({{ $photo->id }})" class="bg-success">Clear</button>
                                                <button type="button" onclick="markSoft({{ $photo->id }})" class="bg-warning">Soft</button>
                                                <button type="button" onclick="markAdult({{ $photo->id }})" class="bg-danger">Hard</button>
                                                <button type="button" onclick="markProhibited({{ $photo->id }})" class="bg-default prohibited">Prohibited</button>
                                                <button type="button" onclick="deleteImage({{ $photo->id }})" class="btn btn-danger">Delete</button>
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
                            @include('partials.pagination', ['model'=>$photos, 'url'=>route('admin.photosModeration')])
                        </div>
                    @endif
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>

    <div class="modal fade" tabindex="-1" role="dialog" id="alertDeleteImage">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Are you sure?</h4>
                </div>
                <div class="modal-body">
                    <p>You want to delete users uploaded photo.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="deleteImageConfirmation">Delete image</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- /.content -->
@endsection
