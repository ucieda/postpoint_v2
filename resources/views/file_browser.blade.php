<div class="modal-body">

	<link rel="stylesheet" type="text/css" href="{{ url('packages/barryvdh/elfinder/css/elfinder.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('packages/barryvdh/elfinder/css/theme.css') }}">

	<script src="{{ url('packages/barryvdh/elfinder/js/elfinder.min.js') }}"></script>

	<div id="elfinder"></div>

	<script>
		$(document).ready(function()
		{
			$('#elfinder').elfinder(
			{
				customData: {
					_token: '{{ csrf_token() }}'
				},
				getFileCallback: function(file) {
					clickedUploadBtn.parent('div').prev('input').val(file.url).trigger('keyup');
					$("#proModal{{ $_mn }}").modal('hide');
				},
				url : '{{ url('elfinder/connector') }}',
				soundPath: '{{ url('packages/barryvdh/elfinder/sounds') }}'
			});
			proApp.modalWidth('#proModal{{ $_mn }}' , '90');
		});
	</script>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">{{ __('general.Close') }}</button>
</div>
