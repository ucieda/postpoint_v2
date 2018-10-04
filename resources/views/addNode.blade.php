<div class="modal-body">
	<div class="row">
		<div class="col-md-12">
			<select class="form-control category" placeholder="Categories">
				@foreach($categories AS $categoryInf)
					<option value="{{ $categoryInf->id }}">{{ $categoryInf->name }}</option>
				@endforeach
			</select>
		</div>
	</div>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-primary addBtn">{{ __('general.Add') }}</button>
	<button type="button" class="btn btn-default" data-dismiss="modal">{{ __('general.Close') }}</button>
</div>
<script>
	$("#proModal{{ $_mn }} .addBtn").click(function()
	{
		var category = $("#proModal{{ $_mn }} .category").val(),
			nodes	 = [];

		$("#nodesList .nodeCheckbox:checked").each(function()
		{
			nodes.push( $(this).closest('tr').attr('data-id') );
		});

		proApp.ajax('{{ url('ajax/addNode/save') }}' , {
			'category'	: category,
			'nodes'		: nodes
		} , function( result )
		{
			$("#proModal{{ $_mn }}").modal('hide');
			location.reload();
		});
	});
</script>