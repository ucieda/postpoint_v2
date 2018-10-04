@extends('layouts.app')

@section('title')
	{{ __('settings.app.theme.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'app'])
					<div class="resp-tabs-container show">
						@include('settings.menu2' , ['menu2' => 'theme'])
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
										{{ Form::open(array('url' => 'settings/app/theme/save')) }}

										<div class="col-md-12">
											<div class="import" style="display: none;">
												<label for="">{{ __('settings.app.theme.Site logo (Recommended size 100x32px)') }}</label>
												{{ Form::text('site_logo_m' , siteOption('site_logo_m')) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.theme.Site logo (Recommended size 50x50 px )') }}</label>
												{{ Form::text('site_logo_xs' , siteOption('site_logo_xs')) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.theme.Site large logo (Recommended size 300x100 px )') }}</label>
												{{ Form::text('site_logo_l' , siteOption('site_logo_l')) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.theme.Site favicon') }}</label>
												{{ Form::text('site_favicon' , siteOption('site_favicon')) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.theme.Site meta description') }}</label>
												{{ Form::textarea('site_description' , siteOption('site_description'),['class'=>'form-control']) }}
											</div>
											<div class="import" style="display: none;">
												<label for="">{{ __('settings.app.theme.Theme color') }}</label>
												{{ Form::text('theme_color' , siteOption('theme_color')) }}
											</div>
											<div class="import" style="display: none;">
												<label for="">{{ __('settings.app.theme.Theme links color') }}</label>
												{{ Form::text('theme_links_color' , siteOption('theme_links_color')) }}
											</div>
											<div class="import" style="display: none;">
												<label for="">{{ __('settings.app.theme.Theme background image Public pages (Login page, Register page ..)') }}</label>
												{{ Form::text('theme_background_image' , siteOption('theme_background_image')) }}
											</div>
											<div class="import" style="display: none;">
												<label for="">{{ __('settings.app.theme.Theme background color Public pages (Login page, Register page ..)') }}</label>
												{{ Form::text('theme_background_color' , siteOption('theme_background_color')) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.theme.Custom CSS') }}</label>
												{{ Form::textarea('custom_css' , siteOption('custom_css'),['class'=>'form-control']) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.theme.Footer text (Support HTML tags)') }}</label>
												{{ Form::textarea('footer_text' , siteOption('footer_text'),['class'=>'form-control']) }}
											</div>

											<div class="button-div">
												<button class="button-save">{{ __('settings.app.theme.Save changes') }}</button>
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
