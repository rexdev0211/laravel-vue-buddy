<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Event photo</h3>
                        </div>
                        <div class="box-body text-center">
                            <a data-lity href="{{ $event->getPhotoUrl('orig', true) }}">
                                <img src="{{ $event->getPhotoUrl('300x260', true) }}" alt="">
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Event gallery: {{ count($event->photos) }} photos</h3>
                        </div>
                        <div class="box-body">
                            @foreach($event->photos as $photo)
                                <a data-lity href="{{ $photo->getUrl('orig', true) }}">
                                    <img src="{{ $photo->getUrl('65x65', true) }}" style="margin: 0 5px 5px 0">
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Event videos: {{ count($event->videos) }} videos</h3>
                        </div>
                        <div class="box-body">
                            @foreach($event->videos as $video)
                                <a data-lity href="{{ $video->video_url['mp4'] }}">
                                    <img src="{{ $video->thumb_small }}" style="margin: 0 5px 5px 0">
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($event->type !== 'guide')
                    <div class="col-md-12">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Category: {{ $event->typeCaption }}</h3>
                            </div>
                            <div class="box-body">
                                @if ($event->type == 'fun')
                                    <a class="btn btn-success btn-xs" title="Change to Friends" href="{{ route('admin.events.type', ['id' => $event->id, 'type' => 'friends']) }}">Change to Friends</a>
                                    @if ($event->chemsfriendly)
                                        <a class="btn btn-warning btn-xs" title="Change to Fun" href="{{ route('admin.events.type', ['id' => $event->id, 'type' => 'fun']) }}">Change to Fun</a>
                                    @else
                                        <a class="btn btn-warning btn-xs" title="Change to Fun (cf)" href="{{ route('admin.events.type', ['id' => $event->id, 'type' => 'fun-cf']) }}">Change to Fun (cf)</a>
                                    @endif
                                @else
                                    <a class="btn btn-warning btn-xs" title="Change to Fun" href="{{ route('admin.events.type', ['id' => $event->id, 'type' => 'fun']) }}">Change to Fun</a>
                                    <a class="btn btn-warning btn-xs" title="Change to Fun (cf)" href="{{ route('admin.events.type', ['id' => $event->id, 'type' => 'fun-cf']) }}">Change to Fun (cf)</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Actions</h3>
                        </div>
                        <div class="box-body">
                            @if ($event->type !== 'guide')
                                @if ($event->status == 'active')
                                    <a class="btn btn-warning btn-xs" title="Suspend" href="{{ route('admin.events.suspend', $event->id) }}"><i class="fa fa-ban"></i> Suspend</a>
                                @else
                                    <a class="btn btn-success btn-xs" title="Activate" href="{{ route('admin.events.activate', $event->id) }}"><i class="fa fa-check-circle"></i> Activate</a>
                                @endif
                            @else
                                <a class="btn btn-success btn-xs" title="APPROVE" href="{{ route('admin.events.approveGuide', $event->id) }}">APPROVE</a>
                                <a class="btn btn-warning btn-xs" title="DECLINE" href="{{ route('admin.events.declineGuide', $event->id) }}">DECLINE</a>
                                @if ($event->featured === 'no')
                                    <a class="btn btn-info btn-xs" title="FEATURE" href="{{ route('admin.events.featureGuide', [$event->id, 'yes']) }}">FEATURE</a>
                                @else
                                    <a class="btn btn-info btn-xs" title="UNFEATURE" href="{{ route('admin.events.featureGuide', [$event->id, 'no']) }}">UNFEATURE</a>
                                @endif
                            @endif
                            @include('partials.deleteButton', ['name' => $event->title, 'url' => route('admin.events.delete', $event->id), 'alert' => 'This action will delete selected event.'])
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Event information</h3>
                        </div>

                        <div class="box-body">
                            <div class="form-group @if ($event->type === 'guide') col-md-6 @else col-md-12 @endif">
                                <label>Category</label>
                                <div>{{ $event->typeCaption }}</div>
                            </div>
                            @if ($event->type === 'guide')
                                <div class="form-group col-md-6">
                                    <label>Status</label>
                                    @if ($event->featured === 'yes')
                                        <div>Featured</div>
                                    @else
                                        <div>{{ $event->status }}</div>
                                    @endif
                                </div>
                            @endif
                            <div class="form-group col-md-6">
                                <label>Event Title</label>
                                <div>{{ $event->title }}</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Event Id</label>
                                <div>{{ $event->id }}</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Owner Id</label>
                                <div>{{ $event->user->id }}</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Owner Name</label>
                                <div>{{ $event->user->name }}</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Address</label>
                                <div>{{ $event->address }}</div>
                            </div>
                            @if ($event->type === 'guide')
                                <div class="form-group col-md-6">
                                    <label>Venue</label>
                                    <div>{{ $event->venue }}</div>
                                </div>
                            @else
                                <div class="form-group col-md-6">
                                    <label>Location</label>
                                    <div>{{ $event->location }}</div>
                                </div>
                            @endif
                            <div class="form-group col-md-6">
                                <label>Date</label>
                                <div>{{ $event->event_date }}</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Time</label>
                                <div>{{ $event->time }}</div>
                            </div>
                            @if ($event->type === 'guide')
                                <div class="form-group col-md-6" style="float: right; word-break: break-all">
                                    <label>URL</label>
                                    <div>
                                        <a href="//{{ $event->website }}" target="_blank">{{ $event->website }}</a>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group col-md-12" style="word-break: break-all">
                                <label>Description</label>
                                <div>{!! $event->description !!}</div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        @if ($event->type === 'guide')
                            <div class="box-footer">
                                <div class="col-md-12">
                                    <label>Name</label>
                                    <div>{{ $event->name }}</div>
                                </div>
                                <div class="col-md-12" style="margin-top: 15px">
                                    <label>Contact</label>
                                    <div>{{ $event->contact }}</div>
                                </div>
                                <div class="col-md-12" style="margin-top: 15px">
                                    <label>Note</label>
                                    <div>{{ $event->note }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
