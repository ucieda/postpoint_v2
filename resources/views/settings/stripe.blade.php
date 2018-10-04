@extends('layouts.app')

@section('title')
	{{ __('settings.stripe.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'stripe'])
					<div class="resp-tabs-container">
						<div>
							<section id="general_setings">
								<div class="container margin-normalizer">
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
										{{ Form::open(array('url' => 'settings/stripe/save')) }}

										<div class="col-lg-12 gen">
											<label for="">{{ __('settings.stripe.Environment') }}</label>
											{{ Form::select('mode' , ['sandbox'=>__('settings.stripe.Test mode (sandbox)') , 'live'=>__('settings.stripe.Live')] , siteOption('stripe_mode') , ['class' => 'select']) }}
											<label for="">{{ __('settings.stripe.Publishable Key') }}</label>
											{!! Form::text('publish_key' , siteOption('stripe_publish_key')) !!}
											<label for="">{{ __('settings.stripe.Secret Key') }}</label>
											{!! Form::text('secret_key' , siteOption('stripe_secret_key')) !!}

											<label for="">{{ __('settings.stripe.Signing Secret Key for Webhook') }}</label>
											{!! Form::text('webhook_secret' , siteOption('stripe_webhook_secret')) !!}
											<span class="help-block">Webhooh URL: <b>{{ url('stripe_webhook') }}</b></span>

											<div class="button-div">
												<button class="button-save"> {{ __('settings.general.Save changes') }}</button>
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
