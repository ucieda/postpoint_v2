@extends('layouts.app')

@section('title')
	{{ __('publishing.logs.title') }}
@endsection

@section('content')
	<style>
		.resp-tabs-list li
		{
			float: none !important;
			color: #96abc0 !important;
		}
	</style>
	<div class="content-holder">
		<div id="main_section">
			<div id="horizontalTab">

				<ul class="resp-tabs-list clearfix show" style="height: auto;">
					<li class="tab-acc visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing') }}';">{{ __('publishing.all_posts') }}</li>
					<li class="tab-acc block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/scheduled_posts') }}';">{{ __('publishing.scheduled_posts') }}</li>
					<li class="tab-acc resp-tab-active block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/schedule_logs') }}';">{{ __('publishing.schedule_logs') }}</li>
					<li class="tab-acc block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/saved_posts') }}';">{{ __('publishing.saved_posts') }}</li>
				</ul>

				<div class="resp-tabs-container">

					<div>
						<!--  ===============section 2  ===============  -->
						<div class="mar-top-20" style="padding: 10px;">

							<div style="padding: 10px 0px;">
								<button class="btn btn-danger" type="button" id="clearLogsBtn"><i class="fa fa-trash"></i> {{ __('publishing.logs.clear_log') }}</button>
							</div>

							<div class="table-responsive schedule-logs" style="border: none;">
								<table class="table-1 main-style table table-bordered">
									<thead class="grey white">
										<tr>
											<th>{{ __('publishing.logs.published_on') }}</th>
											<th>{{ __('publishing.logs.node') }}</th>
											<th>{{ __('publishing.logs.node_type') }}</th>
											<th>{{ __('publishing.logs.post_details') }}</th>
										</tr>
									</thead>
									<tbody>
										@forelse($logs AS $logInfo)
										<tr data-id="{{ $logInfo->id }}">
											<td>{{ date('Y-m-d H:i' , strtotime($logInfo->time)) }}</td>
											<td>
												@if( $logInfo->node_id > 0 )
												<a href="https://fb.com/{{ @$logInfo->Fb_account_node->node_id }}" target="_blank">{{ @$logInfo->Fb_account_node->name }}</a>
												@else
												<a href="https://fb.com/{{ @$logInfo->Fb_account->fb_account_id }}" target="_blank">{{ @$logInfo->Fb_account->name }}</a>
												@endif
											</td>
											<td>{{ ucfirst($logInfo->node_id > 0 ? @$logInfo->Fb_account_node->node_type : 'Fb user') }}</td>
											<td>
												@if($logInfo->status == 'ok')
												<a href="https://fb.com/{{ $logInfo->fb_feed_id }}" class="btn btn-default btn-view" target="_blank"><i class="fa fa-eye"></i> {{ __('publishing.logs.view_post') }}</a>
												<button type="button" class="btn btn-primary reportBtn" target="_blank"><i class="fa fa-bar-chart"></i></button>
												@else
												<i class="fa fa-info-circle"></i> {{ $logInfo->error_message }}
												@endif
											</td>
										</tr>
										@empty
											<tr><td colspan="100%">{{ __('publishing.logs.no_records_available') }}</td></tr>
										@endforelse

									</tbody>
								</table>
							</div>

							<div>
								{{ $logs->links() }}
							</div>
						</div>
						<!-- ============= end section 2   ================== -->

					</div>

				</div>
			</div>
		</div>
	</div>
	<script>

		$(".reportBtn").click(function()
		{
			var id = $(this).closest('tr').attr('data-id');

			proApp.loadModal('{{ url('ajax/publishing/postReport/') }}/' + id , '...' , {});
		});

		$("#clearLogsBtn").click(function()
		{
			proApp.confirm('{{ __('publishing.logs.delete_confirmation') }}' , '{{ __('publishing.logs.are_you_sure_to_delete') }}' , function()
			{

				proApp.ajax('{{ url('ajax/publishing/clear_logs') }}' , {
					'clear'		:	'1',
					'post_id'	:	'{{ $postId }}'
				} , function()
				{
					location.reload();
				});
			});
		});
	</script>
@endsection