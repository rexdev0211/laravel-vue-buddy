@extends('layouts.adminLayout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

@include('admin.modals.videoModal')

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-body">
                    <form action="{{ route('admin.moderation.videos') }}" class="form-inline custom-inline">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="display: block">Show only:</label>
                                <select id="ratingType" name="rating" class="form-control" style="margin-bottom: 5px;">
                                    <option value="unrated">Unrated</option>
                                    <option value="safe" @if ($only == 'safe') selected @endif>Rated as Safe</option>
                                    <option value="not_safe" @if ($only == 'not_safe') selected @endif>Rated as Unsafe</option>
                                    <option value="prohibited" @if($only === 'prohibited') selected @endif>Rated as Prohibited</option>
                                    <option value="all" @if ($only === 'all') selected @endif>All Types</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Rate all videos as:</label>
                                <div class="custom-inline" id="rateAll">
                                    @if (!isset($only) || $only != 'safe')
                                        <button type="button" data-type="clear" class="btn btn-success">Clear</button>
                                    @endif
                                    @if (!isset($only) || $only != 'not_safe')
                                        <button type="button" data-type="adult" class="btn btn-danger">Hard</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            Rated Videos: <span id="counterRated">{{ $counters['rated'] }}</span><br />
                            Waiting For Rate: <span id="counterUnrated">{{ $counters['unrated'] }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                                <div class="form-group">
                                    <input name="id" class="form-control" type="text" placeholder="ID" value="{{ $userId ?? "" }}">
                                </div>
                                <div class="form-group">
                                    <input name="username" class="form-control" type="text" placeholder="Username" value="{{ $username ?? "" }}">
                                </div>
                                <div class="form-group">
                                    <input name="email" class="form-control" type="text" placeholder="Email" value="{{ $email ?? "" }}">
                                </div>
                                <div class="form-group">
                                    <input class="btn btn-primary btn-block" type="submit" value="Filter">
                                </div>
                                <div class="form-group">
                                    <a href="{{ route('admin.moderation.videos') }}?resetFilters" class="btn btn-default btn-block">Reset</a>
                                </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>

            <div class="box box-success">
                <div class="box-body {{ count($videos) ? 'no-padding' : '' }}">
                    @if (count($videos))
                    <div class="row no-margin">
                        @foreach ($videos as $video)
                        <div data-id="{{ $video->id }}" class="moderationVideo col-md-3 col-sm-6 col-xs-12 no-padding {{ in_array($video->manual_rating, $blockedRating) ? 'bg-danger' : 'bg-success' }}">
                            <a data-url="{{ $video->url_orig }}" class="moderationVideoImage" style="background-image: url('{{ $video->thumb_small }}'); cursor: pointer"></a>
                            <div data-filter="{{ $only }}" class="moderationVideoActions text-center">
                                <button type="button" data-rating="{{ $video->manual_rating }}" data-id="{{ $video->id }}" data-type="clear" class="bg-success">Clear</button>
                                <button type="button" data-rating="{{ $video->manual_rating }}" data-id="{{ $video->id }}" data-type="adult" class="bg-danger">Hard</button>
                                <button type="button" data-rating="{{ $video->manual_rating }}" data-id="{{ $video->id }}" data-type="prohibited" class="bg-default prohibited">Prohibited</button>
                                <a href="{{ route('admin.users.view', $video->user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $video->user->name }} [<strong>{{ $video->user->id }}</strong>]</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div>No videos were found</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection


@section('scripts_init')
<script>
window.Laravel = {!! json_encode([
    'route'   => route('admin.moderation.videos'),
    'rate'    => route('admin.moderation.videos.rate'),
    'rateAll' => route('admin.moderation.videos.rate.group'),
    'rated'   => $counters['rated'],
    'unrated' => $counters['unrated'],
]) !!};
</script>
<script src="{{ asset('backend/js/video_moderation.js') }}"></script>
@endsection
