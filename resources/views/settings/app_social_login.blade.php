@extends('layouts.app')

@section('title')
	{{ __('settings.app.social_login.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'app'])
					<div class="resp-tabs-container">
						@include('settings.menu2' , ['menu2' => 'social_login'])
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
										{{ Form::open(array('url' => 'settings/app/social_login/save')) }}

										<div class="col-md-12">
											<div class="import">
												<label for="">{{ __('settings.app.social_login.Facebook app id') }}</label>
												{{ Form::text('fb_app_id' , siteOption('fb_app_id')) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.social_login.Facebook App secret') }}</label>
												{{ Form::text('fb_app_secret' , siteOption('fb_app_secret')) }}
											</div>

											<div class="button-div">
												<button class="button-save">{{ __('settings.app.social_login.Save changes') }}</button>
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
