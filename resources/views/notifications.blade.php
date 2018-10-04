@extends('layouts.app')

@section('title')
	{{ __('general.Notifications') }}
@endsection

@section('content')
	<div class="container-fluid charts">
		@foreach( $data AS $nInf)
			<div style="border: 1px solid #AAA; background: {{ $id == $nInf->id ? '#EEE' : '#FFF' }}; padding: 10px; margin-bottom: 2px;">
				<small style="color: #999;">{{ $nInf->time }}</small> {{ $nInf->title }} <br>
				{{ $nInf->text }}
				<div style="float: right; width: 30px;">
					<button type="button" class="btn btn-xs btn-{{ $nInf->status ? 'default' : 'info' }} chngStatus" data-id="{{ $nInf->id }}" data-status="{{ $nInf->status }}"><i class="fa fa-bell"></i></button>
				</div>
			</div>
		@endforeach
	</div>

	<script>
		$(".chngStatus").click(function()
		{
			var nid		=	$(this).attr('data-id'),
				status	=	$(this).attr('data-status')=='1' ? 0 : 1,
				btn		=	$(this);

			proApp.ajax('{{ url('ajax/notifications/status_change') }}' , {'id' : nid , 'status' : status} , function( result )
			{
				btn.attr('data-status' , status).removeClass('btn-default').removeClass('btn-info').addClass('btn-' + (status==1 ? 'default' : 'info'));
			});
		});
	</script>
@endsection
