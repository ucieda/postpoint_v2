@extends('layouts.app')

@section('title')
	{{ __('publishing.all.title') }}
@endsection

@section('content')
	<style>
		@-webkit-keyframes fadeIn {
			from { opacity: 0; margin-right: 0px; }
			to { opacity: 1; margin-right: 5px; }
		}
		@keyframes fadeIn {
			from { opacity: 0; margin-right: 0px; }
			to { opacity: 1; margin-right: 5px; }
		}

		.resp-tabs-list li
		{
			float: none !important;
			color: #96abc0 !important;
		}
		.fc-day
		{
			position: relative;
		}
		.addNewPost
		{
			position: absolute;
			top: 0;
			right: 10px;
			display: none;
			float: right;
			margin: 5px;
			text-decoration: none !important;
			font-size: 13px;
			color: #02d9d6;
			z-index: 3 !important;
		}
		.addNewPost:hover
		{
			color: #02d9d6;
		}
		.fc-bg
		{
			z-index: 5 !important;
		}
		.fc-day:hover .addNewPost
		{
			display: block;
			-webkit-animation: fadeIn 1s;
			animation: fadeIn 1s;
		}
	</style>
	<div class="content-holder">
		<div id="main_section">
			<div id="horizontalTab">

				<ul class="resp-tabs-list clearfix show" style="height: auto;">
					<li class="tab-acc resp-tab-active visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing') }}';">{{ __('publishing.all_posts') }}</li>
					<li class="tab-acc block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/scheduled_posts') }}';">{{ __('publishing.scheduled_posts') }}</li>
					<li class="tab-acc block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/schedule_logs') }}';">{{ __('publishing.schedule_logs') }}</li>
					<li class="tab-acc block-xs visible-sm-inline-block visible-md-inline-block visible-lg-inline-block visible-xs-block" onclick="location.href = '{{ url('publishing/saved_posts') }}';">{{ __('publishing.saved_posts') }}</li>
				</ul>

				<div class="resp-tabs-container">
					<div id="calendar" class="fc fc-unthemed fc-ltr"></div>

					<section id="account-calendar">
						<div class="row">
							<div class="account">
								<div class="col-md-12">
									<div class="calendar-holder" id="open-calendar-btn">
										<p>{{ __('publishing.all.open_calendar') }}</p>
									</div>
								</div>

								@foreach($savedPosts AS $postInf)
									<div data-href="{{ url('home/' . $postInf->id) }}" class="col-lg-3 col-xl-3 col-sm-6 col-md-4 col-xs-12">
										<div class="con">
											<div style="background-image: url('https://graph.facebook.com/{{ $postInf->schedule_fb_account_id > 0 ? @$postInf->Fb_account->fb_account_id : ( $fbAccInf['fb_account_id'] ?? '0' ) }}/picture?redirect=1&height=150&width=150&type=normal');" class="profil_photo saved-post-bor"></div>
											<div class="description"><p>{{ textShorter($postInf->title , 100) }}</p></div>
											<div class="status"><p class="saved-post"> <span>{{ !$postInf->is_scheduled ? __('publishing.all.saved_post') : __('publishing.all.scheduled_post') }}</span></p></div>
											<div class="date">
												<img src="{{ url('img/menu/calendar.png') }}">
												<p class="his">{{ date('d-m-Y H:i' , strtotime($postInf->created_at)) }}</p>
											</div>
										</div>
									</div>
								@endforeach
								<div class="col-md-12">
									{{ $savedPosts->links() }}
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
		</div>
	</div>

	<script>
		$(document).ready(function() {

			$('#calendar').fullCalendar({
				contentHeight: 'auto',
				eventLimit: true, // allow "more" link when too many events
				events: function(start, end, timezone, callback)
				{
					proApp.ajax( '{{ url('ajax/publishing/load_calendar_days') }}', {
						'start_date':	start.format(),
						'end_date':		end.format()
					}, function( result )
					{
						callback(result['events']);
					});
				}
			});

			$('#calendar').on('mouseover' , '.fc-day.fc-future' , function()
			{
				if( $(this).children('.addNewPost').length == 0 )
				{
					$(this).append('<a href="{{ url('home?date=') }}' + $(this).attr('data-date') + '" class="addNewPost"><i class="fa fa-plus-circle"></i> Add new</a>');
				}
			});
			$('#calendar').on('mouseover' , '.fc-day.fc-today' , function()
			{
				if( $(this).children('.addNewPost').length == 0 )
				{
					$(this).append('<a href="{{ url('home?date=') }}' + $(this).attr('data-date') + '" class="addNewPost" style="color: #FFF !important;"><i class="fa fa-plus-circle"></i> Add new</a>');
				}
			});

			$('#calendar .fc-day.fc-today').prepend( $('#calendar .fc-day-top.fc-today')[0].outerHTML );
		});

		$("[data-href]").click(function(event)
		{
			if( !$(event.target).hasClass('checkboxDiv') && !$(event.target).hasClass('postCheckbox') )
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
	<link href='{{ url('css/fullcalendar.print.css') }}' rel='stylesheet' media='print' />
@endsection
@section('script')
	<script src='{{ url('js/moment.min.js') }}'></script>
	<script src='{{ url('js/fullcalendar.min.js') }}'></script>
@endsection