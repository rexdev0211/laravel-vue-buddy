@extends('layouts.adminLayout')

@section('content')
<section class="content" id="app">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Video Server (<span id="totalVideos">{{ number_format($total, 0, '.', ' ') }}</span> videos)</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.videoServer') }}?remove-originals" class="btn btn-danger btn-sm">Remove original videos</a>
                    </div>
                </div>
                <!-- /.box-header -->

                <div class="box-body table-responsive">
                    <div><span id="freeSpace">{{ $freeSpace }}</span> Mb free space left on the server</div>
                    @if ($log)
                        <br>
                        <pre>{!! $log !!}</pre>
                    @endif
                </div>
            </div>
            <!-- /.box -->
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Deleted Users Videos (<span id="deletedVideos">{{ number_format($deletedUsers['videos'], 0, '.', ' ') }}</span> videos)</h3>
                    <div class="box-tools">
                        <i id="deleteSpinner" class="fa fa-spinner fa-pulse hidden"></i>
                        <button id="stopDelete" type="button" class="btn btn-success btn-sm hidden">Stop</button>
                        <button id="startDelete" type="button" class="btn btn-danger btn-sm">Remove deleted users videos</button>
                    </div>
                </div>
                <!-- /.box-header -->

                <div class="box-body table-responsive">
                    <div>Deleted users: {{ $deletedUsers['count'] }}</div>
                    <div id="deletedLog" class="hidden">
                        <button id="clearDelete" type="button" class="btn btn-warning btn-sm pull-right">Clear log</button>
                        <br>
                        <code></code>
                    </div>
                </div>
            </div>
            <!-- /.box -->
        </div>
    </div>
</section>
<!-- /.content -->
@endsection

@push('js')
<script>
$(function() {
    videoServer.init();
});

videoServer = {
    started: false,
    init: function() {
        $('#startDelete').unbind().bind('click', function(){
            if (!videoServer.started) videoServer.startClear();
        });

        $('#stopDelete').unbind().bind('click', function(){
            videoServer.started = false;
            $('#stopDelete').addClass('hidden');
        });

        $('#clearDelete').unbind().bind('click', function(){
            $('#deletedLog code').html('');
            $('#deletedLog').addClass('hidden');
        });
    },
    startClear: function() {
        videoServer.started = true;
        $('#deleteSpinner, #stopDelete').removeClass('hidden');

        makeAjaxRequest('{{ route('admin.videoServer.clearDeleted') }}', {}, 'POST').then(function(response) {
            videoServer.updateCounters(response);

            if (videoServer.started && response.deletedUsers.queued > 0) {
                videoServer.startClear();
            } else {
                videoServer.started = false;
                $('#deleteSpinner, #stopDelete').addClass('hidden');
            }
        });
    },
    updateCounters(response) {
        $('#deletedVideos').html(response.deletedUsers.videos);
        $('#totalVideos').html(response.total);
        $('#freeSpace').html(response.freeSpace);

        $('#deletedLog').removeClass('hidden');
        $('#deletedLog code').html($('#deletedLog code').html()+''+response.log);
    },
}
</script>
@endpush
