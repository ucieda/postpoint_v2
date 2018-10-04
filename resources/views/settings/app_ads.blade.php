@extends('layouts.app')

@section('title')
	{{ __('settings.app.ads.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'app'])
					<div class="resp-tabs-container">
						@include('settings.menu2' , ['menu2' => 'ads'])
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
										{{ Form::open(array('url' => 'settings/app/ads/save')) }}

										<div class="col-md-12">
											<div class="import fade_div_cls">
												<label for="">{{ __('settings.app.ads.Ads banner ( Responsive ADS code )') }}</label>
												{{ Form::textarea('ads_banner' , siteOption('ads_banner') , ['class'=>'form-control']) }}
											</div>
											<div class="radio_button mar-top-20" style="display: none;">
												<div class=" checkbox-holder" style="display: block;">
													{{ Form::checkbox('display_ads_on_public', null , siteOption('display_ads_on_public') , ['id' => 'display_ads_on_public']) }}
													<label for="display_ads_on_public" class="check-box"></label>
													<label class="text-label-txt" for="display_ads_on_public">{{ __('settings.app.ads.Display Ads on public pages') }}</label>
												</div>
											</div>

											<div>{{ __('settings.app.ads.Show ADS to') }}:</div>

											<?php
											$show_ads_to = explode(',' , siteOption('show_ads_to'));
											?>
											@foreach( \App\User_role::get() AS $roleInf )
											<div class="radio_button mar-top-20">
												<div class=" checkbox-holder" style="display: block;">
													{{ Form::checkbox('show_ads_to[]', $roleInf->id , (in_array((string)$roleInf->id , $show_ads_to)) , ['id' => 'show_ads_to_'.$roleInf->id]) }}
													<label for="show_ads_to_{{ $roleInf->id }}" class="check-box"></label>
													<label class="text-label-txt" for="show_ads_to_{{ $roleInf->id }}">{{ $roleInf->name }}</label>
												</div>
											</div>
											@endforeach

											<div class="button-div">
												<button class="button-save">{{ __('settings.app.ads.Save changes') }}</button>
											</div>
										</div>

										{{ Form::token() }}
										{{ Form::close() }}
									</div>
								</div>
							</section>

							<script>
								$(".mailProtocolSelect").change(function()
								{
									if( $(this).val() == 'mail' )
									{
										$(".fade_div_cls").hide(400);
									}
									else
									{
										$(".fade_div_cls").show(400);
									}
								}).trigger('change');
							</script>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
