<div class="modal-body">
	<div class="row">
		<div class="col-md-12">
			<input type="text" class="form-control categoryName" placeholder="{{ __('general.Name') }}">
		</div>
	</div>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-primary createBtn">{{ __('general.Create') }}</button>
	<button type="button" class="btn btn-default" data-dismiss="modal">{{ __('general.Close') }}</button>
</div>
<script>
	$("#proModal{{ $_mn }} .createBtn").click(function()
	{
		var name = $("#proModal{{ $_mn }} .categoryName").val();

		proApp.ajax('{{ url('ajax/addNodeCategory/save') }}' , {'name': name} , function( result )
		{
			$("#proModal{{ $_mn }}").modal('hide');

			$("#nodeCategoriesSelect").append('<option value="' + parseInt(result['id']) + '">' + proApp.htmlspecialchars_decode(name) + '</option>');
		});
	});
</script>