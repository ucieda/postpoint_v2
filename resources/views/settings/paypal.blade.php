@extends('layouts.app')

@section('title')
	{{ __('settings.paypal.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'paypal'])
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
										{{ Form::open(array('url' => 'settings/paypal/save')) }}

										<div class="col-lg-12 gen">
											<label for="">{{ __('settings.paypal.Environment') }}</label>
											{{ Form::select('mode' , ['sandbox'=>__('settings.paypal.Test mode (sandbox)') , 'live'=>__('settings.paypal.Live')] , siteOption('paypal_mode') , ['class' => 'select']) }}
											<label for="">{{ __('settings.paypal.PayPal Client ID') }}</label>
											{!! Form::text('client_id' , siteOption('paypal_client_id')) !!}
											<label for="">{{ __('settings.paypal.PayPal Client Secret') }}</label>
											{!! Form::text('client_secret' , siteOption('paypal_client_secret')) !!}
											<span class="help-block">IPN url: <b>{{ url('paypal_ipn') }}</b></span>
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
