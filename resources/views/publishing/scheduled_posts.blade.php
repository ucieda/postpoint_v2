@extends('layouts.app')

@section('title')
	{{ __('publishing.scheduled.title') }}
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
					<li class="tab-acc resp-tab-active block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/scheduled_posts') }}';">{{ __('publishing.scheduled_posts') }}</li>
					<li class="tab-acc block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/schedule_logs') }}';">{{ __('publishing.schedule_logs') }}</li>
					<li class="tab-acc block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/saved_posts') }}';">{{ __('publishing.saved_posts') }}</li>
				</ul>

				<div class="resp-tabs-container">

					<div>
						<!--  ===============section 2  ===============  -->
						<div class="mar-top-20" style="padding: 10px;">

							<div style="padding: 10px 20px 20px 15px;">
								<button class="btn btn-danger" type="button" id="deletePostBtn"><i class="fa fa-trash"></i> {{ __('publishing.scheduled.delete') }}</button>
							</div>

							<div class="table-responsive scheduled-list" style="border: none;">
								<table class="table-1 main-style table table-bordered" style="border: none;">
									<thead class="grey white">
									<tr>
										<th class="hiddenTh"></th>
										<th>{{ __('publishing.scheduled.next_posting_time') }}</th>
										<th>{{ __('publishing.scheduled.post_interval') }}</th>
										<th>{{ __('publishing.scheduled.post') }}</th>
										<th>{{ __('publishing.scheduled.fb_app') }}</th>
										<th>{{ __('publishing.scheduled.fb_account') }}</th>
										<th>{{ __('publishing.scheduled.status') }}</th>
										<th></th>
									</tr>
									</thead>
									<tbody>
									@foreach($scheduledPosts AS $postInfo)
										<tr data-id="{{ $postInfo['id'] }}">
											<td class="borderNone">
												<div class="ch-1">
													<input type="checkbox" id="cbtest{{ $postInfo->id }}" class="postCheckbox"/>
													<label for="cbtest{{ $postInfo->id }}" class="check-box red"></label>
												</div>
											</td>
											<td>{{ $postInfo['next_posting_time'] }}</td>
											<td>{{ $postInfo['schedule_post_interval'] % 60 == 0 ? ($postInfo['schedule_post_interval'] / 60) . ' '.__('publishing.scheduled.hours') : $postInfo['schedule_post_interval'] . ' '.__('publishing.scheduled.minutes') }}</td>
											<td><a href="{{ url('home/' . $postInfo['id']) }}">{{ $postInfo['title'] }}</a></td>
											<td>{{ @$postInfo->Fb_account_access_token->Fb_app->name }}</td>
											<td><img class="img-circle " src="https://graph.facebook.com/{{ @$postInfo->Fb_account->fb_account_id }}/picture?redirect=1&amp;height=25&amp;width=25&amp;type=normal" width="30px" height="30px"> {{ @$postInfo->Fb_account->name }}</td>
											<td>
												@if($postInfo['status'] == 1)
												<button type="button" class="btn btn-success">
													{{ __('publishing.scheduled.completed') }} ( {{ $postInfo['schedule_nodes_count'] }} / {{ $postInfo['schedule_nodes_count'] }} )
												</button>
												@elseif($postInfo['schedule_is_paused'] == 1)
												<button type="button" class="btn btn-warning">
													{{ __('publishing.scheduled.paused') }} ( {{  $postInfo['schedule_nodes_count']- $postInfo['remaining'] }} / {{ $postInfo['schedule_nodes_count'] }} )
												</button>
												@else
												<button type="button" class="btn btn-info">
													{{ __('publishing.scheduled.in_progress') }} ( {{  $postInfo['schedule_nodes_count']- $postInfo['remaining'] }} / {{ $postInfo['schedule_nodes_count'] }} )
												</button>
												@endif
												@if( $postInfo['schedule_auto_pause'] > 0 )
													<br>
														{{ __('publishing.scheduled.auto_pause' , ['count' => $postInfo['schedule_auto_pause'] ]) }}<br>
														{{ __('publishing.scheduled.resume_after') }} {{ $postInfo['schedule_auto_resume'] % 60 == 0 ? ($postInfo['schedule_auto_resume'] / 60) . ' '.__('publishing.scheduled.hours') : $postInfo['schedule_auto_resume'] . ' '.__('publishing.scheduled.minutes') }}
													@if(!empty( $postInfo['schedule_auto_resume_time'] ))
															<br> <small>({{ __('publishing.scheduled.continued') }} {{ date('Y-m-d H:i' , strtotime($postInfo['schedule_auto_resume_time'])) }})</small>
													@endif
												@endif
											</td>
											<td>
												@if( $postInfo->status == 0 )

												<button type="button" class="btn btn-primary editSchedule" title="{{ __('publishing.scheduled.edit') }}">
													<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
												</button>

												@if( $postInfo->schedule_is_paused == 0 )
												<button type="button" class="btn btn-primary pauseBtn"><i class="fa fa-pause" aria-hidden="true"></i></button>
												@else
												<button type="button" class="btn btn-warning resumeBtn"><i class="fa fa-play" aria-hidden="true"></i></button>
												@endif

												@endif
												<a href="{{ url('publishing/schedule_logs/' . $postInfo->id) }}" title="{{ __('publishing.scheduled.view_log') }}" class="btn btn-default btn-log">
													<i class="fa fa-file" aria-hidden="true"></i>
												</a>
											</td>
										</tr>
									@endforeach
									</tbody>
								</table>
							</div>

							<div style="padding: 10px;">{{ $scheduledPosts->links() }}</div>
						</div>
						<!-- ============= end section 2   ================== -->
					</div>

				</div>
			</div>
		</div>
	</div>
	<script>
		$(".editSchedule").click(function()
		{
			var id = $(this).closest('tr').attr('data-id');

			proApp.loadModal('{{ url('ajax/publishing/scheduled_post_edit') }}/' + id , '...' , {});
		});

		$("#deletePostBtn").click(function()
		{
			var selectedPostsCount = $(".postCheckbox:checked").length;

			if( selectedPostsCount == 0 )
			{
				return;
			}

			proApp.confirm('{{ __('publishing.scheduled.delete_confirmation') }}' , '{{ __('publishing.scheduled.are_you_sure_to_delete') }}' , function()
			{
				var posts = [];

				$(".postCheckbox:checked").each(function()
				{
					posts.push( $(this).closest('tr').attr('data-id') );
				});

				proApp.ajax('{{ url('ajax/publishing/delete_scheduled_posts') }}' , {
					'posts' : posts
				} , function()
				{
					location.reload();
				});
			});
		});

		$(".pauseBtn").click(function()
		{
			var id = $(this).closest('tr').attr('data-id');

			proApp.ajax('{{ url('ajax/publishing/scheduled_posts/pause') }}' , { 'id' : id } , function()
			{
				location.reload();
			});
		});

		$(".resumeBtn").click(function()
		{
			var id = $(this).closest('tr').attr('data-id');

			proApp.ajax('{{ url('ajax/publishing/scheduled_posts/resume') }}' , { 'id' : id } , function()
			{
				location.reload();
			});
		});

	</script>
@endsection