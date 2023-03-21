<div class="vertical-align">
    <div class="col-md-3 col-xs-12">
        Displaying {{ $model->firstItem() }} to {{ $model->lastItem() }} of  {{ $model->total()}} entries
    </div>
    <div class="pagination-container col-md-6 col-xs-12 text-center">
        <div class="text-center"> {!! $model->render() !!} </div>
    </div>
    <div class="col-md-3 col-xs-12 text-right">
        Page Size:
        @foreach (\Helper::getPerPageArray() as $index => $perPage)
            @if ($index != 0)
                |
            @endif
            @if ($model->perPage() == $perPage)
                {{ $perPage }}
            @else
                <a href="{{ $url }}?perPage={{ $perPage }}&page=1">{{ $perPage }}</a>
            @endif

        @endforeach
    </div>
    <div style="clear:both"></div>
</div>