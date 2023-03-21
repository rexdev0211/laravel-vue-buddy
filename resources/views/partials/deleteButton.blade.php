{{ Form::open(['method' => 'DELETE', 'url' => $url, 'class' => 'inline']) }}
    <a href="javascript:void(0)" class="btn btn-danger btn-xs" onclick="confirmDeletePost('{{ str_replace("'", "\'", $name) }}', this, '{{ $alert }}')"><i class="fa fa-trash"></i> {{ isset($title) ? $title : 'Delete' }} </a>
{{ Form::close() }}
