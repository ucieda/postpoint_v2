<style>
	.bootstrap-datetimepicker-widget
	{
		z-index: 9999999 !important;
		visibility: visible !important;
		opacity: 1 !important;
	}
</style>
<div id="newPost">
	<div class="modal-body">
		<div class="row left-side">
			<div class="col-md-12" id="left-content-2" style="left: 0 !important;">
				<div class="form-section">
					<div class="add-post-contents">
						<form>
							<div class="form-group">
								<label for="message">{{ __('home.schedule_post_interval') }}</label>
							</div>

							<div class="form-group pad-reset">
								<div class="col-md-8 reset-mar-pad">
									<div class="settings-option ">
										<div class="group checkbox-holder">
											<div class="custom-checkbox">
												<input id='id-1' type="radio" class="schedule_duration" name="schedule_duration" value="minutes"{{ $postInf['schedule_post_interval'] % 60 == 0 ? '' : ' checked' }}>
												<label for="id-1" class='checkbox'></label>
											</div>
											<label class="checkbox-title" for="id-1">{{ __('home.minutes') }}</label>
										</div>

										<div class="group checkbox-holder">
											<div class="custom-checkbox">
												<input id='id-2' type="radio" class="schedule_duration" name="schedule_duration" value="hours"{{ $postInf['schedule_post_interval'] % 60 == 0 ? ' checked' : '' }}>
												<label for="id-2" class='checkbox'></label>
											</div>
											<label class="checkbox-title" for="id-2">{{ __('home.hours') }}</label>
										</div>
									</div>
								</div>
								<div class="col-md-4 reset-mar-pad">
									<select class="schedule_duration_n">
										@for($h = (int)siteOption('minimum_schedule_post_interval'); $h <= (int)siteOption('minimum_schedule_post_interval')+90; $h++)
											<option value="{{ $h }}"{{ ($postInf['schedule_post_interval'] % 60 == 0 ? $postInf['schedule_post_interval']/60 : $postInf['schedule_post_interval']) == $h ? ' selected' : '' }}>{{ $h }}</option>
										@endfor
									</select>
								</div>

								<div class="col-md-6 reset-mar-pad mar-top-20 md-pad-r15 date-app-control">
									<label class="labelText" for="date">{{ __('home.schedule_post_start') }}</label>
									<input type="text" value="{{ empty($postInf['schedule_start']) ? date(dateFormat() . ' H:i') : date(dateFormat() . ' H:i' , strtotime($postInf['schedule_start'])) }}" class="form-control modalInputs dateInput schedule_start" autocomplete="off" aria-describedby="sizing-addon1">
									<div class="calendar"><img src="{{ url('img/calendar.png') }}"></div>
									<span class="help-block">{{ __('home.server_time') }} {{ date(dateFormat() . ' H:i') }}</span>
								</div>

								<div class="col-md-6 reset-mar-pad mar-top-20">
									<label class="labelText">{{ __('home.facebook_app') }}</label>
									<select class="schedule_fb_app">
										@foreach($fbApps AS $appInf)
											<option value="{{ $appInf['id'] }}"{{ $postInf['schedule_fb_app_id'] == $appInf['id'] ? ' selected' : '' }}>{{ $appInf['name'] }}</option>
										@endforeach
									</select>
								</div>

							</div>

							<div class="col-md-12  reset-mar-pad mar-top-20">
								<label class="labelText">{{ __('home.auto_pause_after') }}</label>
								<select class="schedule_auto_pause">
									<option value="0">{{ __('home.off') }}</option>
									@for($h = 1; $h <= 100; $h++)
										<option value="{{ $h }}"{{ $postInf['schedule_auto_pause'] == $h ? ' selected' : '' }}>{{ $h }}</option>
									@endfor
								</select>
							</div>

							<div class="mar-top-20">
								<div class="form-group">
									<label for="message">{{ __('home.auto_resume_after') }}</label>
								</div>
							</div>

							<div class="form-group pad-reset">
								<div class="col-md-8 reset-mar-pad">
									<div class="settings-option ">
										<div class="group checkbox-holder">
											<div class="custom-checkbox">
												<input id='id-3' type="radio" class="schedule_auto_resume" name="schedule_auto_resume" value="minutes"{{ $postInf['schedule_auto_resume'] % 60 == 0 ? '' : ' checked' }}>
												<label for="id-3" class='checkbox'></label>
											</div>
											<label class="checkbox-title" for="id-3">{{ __('home.minutes') }}</label>
										</div>

										<div class="group checkbox-holder">
											<div class="custom-checkbox">
												<input id='id-4' type="radio" class="schedule_auto_resume" name="schedule_auto_resume" value="hours"{{ $postInf['schedule_auto_resume'] % 60 == 0 ? ' checked' : '' }}>
												<label for="id-4" class='checkbox'></label>
											</div>
											<label class="checkbox-title" for="id-4">{{ __('home.hours') }}</label>
										</div>
									</div>
								</div>
								<div class="col-md-4 reset-mar-pad">
									<select class="bc-color-gray schedule_auto_resume_n">
										@for($h = 1; $h <= 90; $h++)
											<option value="{{ $h }}"{{ $postInf['schedule_auto_resume'] == $h ? ' selected' : '' }}>{{ $h }}</option>
										@endfor
									</select>
								</div>

								<div class="col-md-6 reset-mar-pad mar-top-20 md-pad-r15">
									<label class="labelText">{{ __('home.repeat_frequency') }}</label>
									<select class="schedule_frequency">
										<option value="0">{{ __('home.once') }}</option>
										<option value="1"{{ $postInf['schedule_frequency'] == 1 ? ' selected' : '' }}>{{ __('home.every_day') }}</option>
										@for($h = 2; $h <= 30; $h++)
											<option value="{{ $h }}"{{ $postInf['schedule_frequency'] == $h ? ' selected' : '' }}>{{ __('home.every_n_days' , ['days' => $h]) }}</option>
										@endfor
										@for($h = 3; $h <= 15; $h++)
											<option value="{{ $h*10 }}"{{ $postInf['schedule_frequency'] == $h ? ' selected' : '' }}>{{ __('home.every_n_days' , ['days' => $h*10]) }}</option>
										@endfor
									</select>
								</div>

								<div class="col-md-6 reset-mar-pad mar-top-20 date-app-control">
									<label class="labelText">{{ __('home.end_on') }}</label>
									<input value="{{ empty($postInf['schedule_end']) ? '' : date(dateFormat() . ' H:i' , strtotime($postInf['schedule_end'])) }}" type="text" class="form-control modalInputs dateInput schedule_end" autocomplete="off" aria-describedby="sizing-addon1">
									<div class="calendar"><img src="{{ url('img/calendar.png') }}"></div>
								</div>

							</div>
						</form>

					</div>

				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="orrange-btn-bc" style="margin-top: 0;" id="saveSchedule" type="button">
			Complete
		</button>
	</div>
</div>
<script>
	$("#proModal{{ $_mn }} .addAccount").removeClass('addAccount');
	$("#saveSchedule").click(function()
	{
		var schedule_duration_type		=	$(".schedule_duration:checked").val(),
			schedule_duration			=	$(".schedule_duration_n").val(),
			schedule_start				=	$(".schedule_start").val(),
			schedule_fb_app				=	$(".schedule_fb_app").val(),
			schedule_auto_pause			=	$(".schedule_auto_pause").val(),
			schedule_auto_resume_type	=	$(".schedule_auto_resume:checked").val(),
			schedule_auto_resume		=	$(".schedule_auto_resume_n").val(),
			schedule_frequency			=	$(".schedule_frequency").val(),
			schedule_end				=	$(".schedule_end").val();


		proApp.ajax('{{ url('ajax/home/schedulePost') }}' , {
			'post_id'					:	'{{ $postId }}',
			'schedule_duration'			:	schedule_duration * (schedule_duration_type == 'hours' ? 60 : 1),
			'schedule_start'			:	schedule_start,
			'schedule_fb_app'			:	schedule_fb_app,
			'schedule_auto_pause'		:	schedule_auto_pause,
			'schedule_auto_resume'		:	schedule_auto_resume * (schedule_auto_resume_type == 'hours' ? 60 : 1),
			'schedule_frequency'		:	schedule_frequency,
			'schedule_end'				:	schedule_end,
			'nodes'						:	[0]
		} , function( result )
		{
			location.reload();
		});
	});

	$(".schedule_auto_pause").change(function()
	{
		var val = $(this).val();
		if(val > 0)
		{
			$(".schedule_auto_resume_n").removeAttr('disabled').removeClass('bc-color-gray');
		}
		else
		{
			$(".schedule_auto_resume_n").attr('disabled' , 'disabled').addClass('bc-color-gray');
		}
	}).trigger('change');

	$(".schedule_frequency").change(function()
	{
		var val = $(this).val();
		if(val > 0)
		{
			$(".schedule_end").removeAttr('disabled').removeClass('bc-color-gray');
		}
		else
		{
			$(".schedule_end").attr('disabled' , 'disabled').addClass('bc-color-gray');
		}
	}).trigger('change');

	$(document).ready( function ()
	{
		$(".dateInput").datetimepicker({
			format: '{{ dateFormat(2) }} HH:mm'
		});
	});


</script>

<link rel="stylesheet" href="{{ url('plugin/datetimepicker/css/bootstrap-datetimepicker.min.css') }}" />
<script type="text/javascript" src="{{ url('plugin/moment/moment.js') }}"></script>
<script type="text/javascript" src="{{ url('plugin/datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>