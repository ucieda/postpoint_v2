@extends('layouts.app')

@section('title')
	{{ __('publishing.saved.title') }}
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
					<li class="tab-acc block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/schedule_logs') }}';">{{ __('publishing.schedule_logs') }}</li>
					<li class="tab-acc resp-tab-active block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/saved_posts') }}';">{{ __('publishing.saved_posts') }}</li>
				</ul>

				<div class="resp-tabs-container">
					<section id="account">
						<div class="row">
							<div class="col-md-12">
								<div style="height: 50px; margin-top: 10px; background: #FFF; width: 100%; padding: 10px;">


									<span class="ch-1" style="">
										<input type="checkbox" id="selectAllChckbx" class="postCheckbox"/>
										<label for="selectAllChckbx" class="check-box"></label>
									</span>
									<span style="padding-right: 20px;">{{ __('publishing.saved.select_all') }}</span>


									<button type="button" class="btn btn-danger" id="deletePostBtn" style="margin-right: 10px;"><i class="fa fa-trash"></i> {{ __('publishing.saved.delete') }}</button>
									<button type="button" class="btn btn-green" onclick="location.href = '{{ url('home') }}';"><i class="fa fa-plus"></i> {{ __('publishing.saved.new_post') }}</button>

								</div>
							</div>

							<div class="account" id="savedPosts">

								@foreach($savedPosts AS $postInf)
									<div data-href="{{ url('home/' . $postInf->id) }}" class="col-lg-3 col-xl-3 col-sm-6 col-md-4 col-xs-12" style="text-decoration: none;">
										<div class="con">
											<div style="background-image: url('https://graph.facebook.com/{{ $postInf->schedule_fb_account_id > 0 ? @$postInf->Fb_account->fb_account_id : ( $fbAccInf['fb_account_id'] ?? '0' ) }}/picture?redirect=1&height=150&width=150&type=normal');" class=" y_b profil_photo"></div>
											<div class="description"><p>{{ textShorter($postInf->title , 100) }}</p></div>
											<div class="status"><p>{{ $postInf->post_type }}</p></div>
											<div class="date">
												<img src="{{ url('img/menu/calendar.png') }}">
												<p class="his">{{ date('d-m-Y H:i' , strtotime($postInf->created_at)) }}</p>
											</div>
											<div class="checkboxDiv ch-1" style="position: absolute; top: 30px; right: 30px; padding: 6px;">
												<input type="checkbox" data-id="{{ $postInf->id }}" id="cbtest{{ $postInf->id }}" class="postCheckbox"/>
												<label for="cbtest{{ $postInf->id }}" class="check-box"></label>
											</div>
										</div>
									</div>
								@endforeach

							</div>

							<div class="col-md-12">{{ $savedPosts->links() }}</div>
						</div>
					</section>
				</div>
			</div>
		</div>
	</div>

	<script>
		$("#selectAllChckbx").click(function()
		{
			if($(this).is(':checked'))
			{
				$(".postCheckbox:not(:checked)").prop('checked' , true);
			}
			else
			{
				$(".postCheckbox:checked").prop('checked' , false);
			}
		});

		$("#deletePostBtn").click(function()
		{
			var selectedPostsCount = $(".postCheckbox:checked").length;

			if( selectedPostsCount == 0 )
			{
				return;
			}

			proApp.confirm('{{ __('publishing.saved.delete_confirmation') }}' , '{{ __('publishing.saved.are_you_sure_to_delete') }}' , function()
			{
				var posts = [];

				$(".postCheckbox:checked").each(function()
				{
					posts.push( $(this).attr('data-id') );
				});

				proApp.ajax('{{ url('ajax/publishing/delete_saved_posts') }}' , {
					'posts' : posts
				} , function()
				{
					location.reload();
				});
			});
		});

		$(".checkboxDiv").click(function( event )
		{
			if( $(event.target).hasClass('checkboxDiv') )
			{
				$(this).children('input').prop('checked' , !$(this).children('input').is(':checked'));
			}
		});

		$("[data-href]").click(function(event)
		{
			if( !$(event.target).hasClass('checkboxDiv') && !$(event.target).hasClass('check-box') && !$(event.target).hasClass('postCheckbox') )
			{
				location.href = $(this).attr('data-href');
			}
		});

	</script>
@endsection
@section('style')
	<style>
		div[data-href]
		{
			cursor: pointer;
		}
		div[data-href]>.con:hover
		{
			box-shadow: 0px 0px 10px #ccc;
		}
	</style>
@endsection
