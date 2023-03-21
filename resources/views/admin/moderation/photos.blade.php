@extends('layouts.adminLayout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Show only:</label>
                                <select id="ratingType" class="form-control">
                                    <option value="all">All Types</option>
                                    <option value="safe" @if ($only == 'safe') selected @endif>Safe</option>
                                    <option value="not_safe" @if ($only == 'not_safe') selected @endif>Not Safe</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            @if ($only)
                            <div class="form-group">
                                <label>Rate all images as:</label>
                                <div class="custom-inline" id="rateAll">
                                    @if ($only == 'safe')
                                    <button type="button" data-type="clear" class="btn btn-success">Clear</button>
                                    @else
                                    <button type="button" data-type="adult" class="btn btn-danger">Hard</button>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-4 text-right">
                            Rated Images: <span id="counterRated">{{ $counters['rated'] }}</span><br />
                            Waiting For Rate: <span id="counterUnrated">{{ $counters['unrated'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-success">
                <div class="box-body {{ count($photos) ? 'no-padding' : '' }}">
                    @if (count($photos))
                    <div class="row no-margin">
                        @foreach ($photos as $photo)
                        <div data-id="{{ $photo->id }}" class="moderationPhoto col-md-3 col-sm-6 col-xs-12 no-padding {{ $photo->nudity_rating > $startRating ? 'bg-danger' : 'bg-success' }}">
                            <a data-lity target="_blank" href="{{ $photo->url_orig }}" class="moderationPhotoImage" style="background-image: url('{{ $photo->url }}');"></a>
                            <div class="moderationPhotoActions text-center">
                                <button type="button" data-id="{{ $photo->id }}" data-type="clear" class="bg-success">Clear</button>
                                <button type="button" data-id="{{ $photo->id }}" data-type="soft" class="bg-warning">Soft</button>
                                <button type="button" data-id="{{ $photo->id }}" data-type="adult" class="bg-danger">Hard</button>
                                <button type="button" data-id="{{ $photo->id }}" data-type="prohibited" class="bg-default prohibited">Prohibited</button>
                                <a href="{{ route('admin.users.view', $photo->user->id) }}" data-toggle="modal" data-target="#modal-box-div">{{ $photo->user->name }} [<strong>{{ $photo->user->id }}</strong>]</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div>No photos were found</div>
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
    'route'   => route('admin.moderation.photos'),
    'rate'    => route('admin.moderation.photos.rate'),
    'rateAll' => route('admin.moderation.photos.rate.group'),
    'rated'   => $counters['rated'],
    'unrated' => $counters['unrated'],
]) !!};
</script>
<script src="{{ asset('backend/js/moderation/photos.js') }}"></script>
@endsection
