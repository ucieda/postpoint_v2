@extends('layouts.app')

@section('title')
	{{ __('settings.app.publish.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'app'])
					<div class="resp-tabs-container">
						@include('settings.menu2' , ['menu2' => 'publish'])
						<div class="resp-tab-content resp-tab-content-active">

							<section id="facebook_login">
								<div class="container-fluid">
									<div class="row">
										<div class="col-md-10" style="margin-top: 10px;">
											@if ($errors->any())
												<div class="alert alert-danger">
													{{ $errors->first('error_msg') == '' ? __('settings.form_error') : $errors->first('error_msg') }}
												</div>
											@endif
											@if (\Session::has('success'))
												<div class="alert alert-success">
													{{ __('settings.saved_successfull') }}
												</div>
											@endif
										</div>
										{{ Form::open(array('url' => 'settings/app/publish/save')) }}

										<div class="col-md-12">
											<div class="import">
												<label for="">{{ __('settings.app.publish.Minimum interval for immediate posting (In seconds)') }}</label>
												{{ Form::number('minimum_immediate_post_interval' , siteOption('minimum_immediate_post_interval')) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.publish.Minimum interval on schedule post (in minutes)') }}</label>
												{{ Form::number('minimum_schedule_post_interval' , siteOption('minimum_schedule_post_interval')) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.publish.Enable Schedule Random interval') }}</label>
												{{ Form::select('schedule_random_interval' , [
													0	=>	__('settings.app.publish.Off'),
													1	=>	'1 ' . __('settings.app.publish.Minute'),
													2	=>	'2 ' . __('settings.app.publish.Minute'),
													3	=>	'3 ' . __('settings.app.publish.Minute'),
													4	=>	'4 ' . __('settings.app.publish.Minute'),
													5	=>	'5 ' . __('settings.app.publish.Minute'),
													6	=>	'6 ' . __('settings.app.publish.Minute'),
													7	=>	'7 ' . __('settings.app.publish.Minute'),
													8	=>	'8 ' . __('settings.app.publish.Minute'),
													9	=>	'9 ' . __('settings.app.publish.Minute'),
													10	=>	'10 ' . __('settings.app.publish.Minute'),
													11	=>	'11 ' . __('settings.app.publish.Minute'),
													12	=>	'12 ' . __('settings.app.publish.Minute'),
													13	=>	'13 ' . __('settings.app.publish.Minute'),
													14	=>	'14 ' . __('settings.app.publish.Minute'),
													15	=>	'15 ' . __('settings.app.publish.Minute')
												] , siteOption('schedule_random_interval') , ['class'=>'select'] ) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.publish.Instant post Random interval') }}</label>
												{{ Form::select('instant_random_interval' , [
													0	=>	__('settings.app.publish.Off'),
													10	=>	'10 ' . __('settings.app.publish.Sec'),
													20	=>	'20 ' . __('settings.app.publish.Sec'),
													30	=>	'30 ' . __('settings.app.publish.Sec'),
													40	=>	'40 ' . __('settings.app.publish.Sec'),
													50	=>	'50 ' . __('settings.app.publish.Sec'),
													60	=>	'60 ' . __('settings.app.publish.Sec'),
													70	=>	'70 ' . __('settings.app.publish.Sec'),
													80	=>	'80 ' . __('settings.app.publish.Sec'),
													90	=>	'90 ' . __('settings.app.publish.Sec'),
													100	=>	'100 ' . __('settings.app.publish.Sec'),
													110	=>	'110 ' . __('settings.app.publish.Sec'),
													120	=>	'120 ' . __('settings.app.publish.Sec'),
													130	=>	'130 ' . __('settings.app.publish.Sec'),
													140	=>	'140 ' . __('settings.app.publish.Sec'),
													150	=>	'150 ' . __('settings.app.publish.Sec'),
													160	=>	'160 ' . __('settings.app.publish.Sec'),
													170	=>	'170 ' . __('settings.app.publish.Sec'),
													180	=>	'180 ' . __('settings.app.publish.Sec'),
													190	=>	'190 ' . __('settings.app.publish.Sec'),
													200	=>	'200 ' . __('settings.app.publish.Sec'),
													210	=>	'210 ' . __('settings.app.publish.Sec'),
													220	=>	'220 ' . __('settings.app.publish.Sec'),
													230	=>	'230 ' . __('settings.app.publish.Sec'),
													240	=>	'240 ' . __('settings.app.publish.Sec'),
													250	=>	'250 ' . __('settings.app.publish.Sec'),
													260	=>	'260 ' . __('settings.app.publish.Sec'),
													270	=>	'270 ' . __('settings.app.publish.Sec'),
													280	=>	'280 ' . __('settings.app.publish.Sec'),
													290	=>	'290 ' . __('settings.app.publish.Sec'),
													300	=>	'300 ' . __('settings.app.publish.Sec'),
													310	=>	'310 ' . __('settings.app.publish.Sec'),
													320	=>	'320 ' . __('settings.app.publish.Sec'),
													330	=>	'330 ' . __('settings.app.publish.Sec'),
													340	=>	'340 ' . __('settings.app.publish.Sec'),
													350	=>	'350 ' . __('settings.app.publish.Sec'),
													360	=>	'360 ' . __('settings.app.publish.Sec'),
													370	=>	'370 ' . __('settings.app.publish.Sec'),
													380	=>	'380 ' . __('settings.app.publish.Sec'),
													390	=>	'390 ' . __('settings.app.publish.Sec'),
													400	=>	'400 ' . __('settings.app.publish.Sec'),
													410	=>	'410 ' . __('settings.app.publish.Sec'),
													420	=>	'420 ' . __('settings.app.publish.Sec'),
													430	=>	'430 ' . __('settings.app.publish.Sec'),
													440	=>	'440 ' . __('settings.app.publish.Sec'),
													450	=>	'450 ' . __('settings.app.publish.Sec'),
													460	=>	'460 ' . __('settings.app.publish.Sec'),
													470	=>	'470 ' . __('settings.app.publish.Sec'),
													480	=>	'480 ' . __('settings.app.publish.Sec'),
													490	=>	'490 ' . __('settings.app.publish.Sec'),
													500	=>	'500 ' . __('settings.app.publish.Sec'),
												] , siteOption('instant_random_interval') , ['class'=>'select'] ) }}
											</div>

											<div class="radio_button mar-top-20">
												<div class=" checkbox-holder" style="display: block;">
													{{ Form::checkbox('enable_instant_post', null , siteOption('enable_instant_post') , ['id' => 'enable_instant_post']) }}
													<label for="enable_instant_post" class="check-box"></label>
													<label class="text-label-txt" for="enable_instant_post">{{ __('settings.app.publish.Enable instant post') }}</label>
												</div>
												<div class="sec checkbox-holder" style="display: block;">
													{{ Form::checkbox('enable_sale_post', null , siteOption('enable_sale_post') , ['id' => 'enable_sale_post']) }}
													<label for="enable_sale_post" class="check-box"></label>
													<label class="text-label-txt" for="enable_sale_post">{{ __('settings.app.publish.Enable sale post type') }}</label>
												</div>
												<div class="sec checkbox-holder" style="display: block;">
													{{ Form::checkbox('enable_link_customization', null , siteOption('enable_link_customization') , ['id' => 'enable_link_customization']) }}
													<label for="enable_link_customization" class="check-box"></label>
													<label class="text-label-txt" for="enable_link_customization">{{ __('settings.app.publish.Enable link customization') }}</label>
												</div>
											</div>

											<div class="button-div">
												<button class="button-save">{{ __('settings.app.publish.Save changes') }}</button>
											</div>

										</div>

										{{ Form::token() }}
										{{ Form::close() }}
									</div>
								</div>
							</section>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
