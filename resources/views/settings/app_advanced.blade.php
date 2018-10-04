@extends('layouts.app')

@section('title')
	{{ __('settings.app.advanced.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'app'])
					<div class="resp-tabs-container">
						@include('settings.menu2' , ['menu2' => 'advanced'])
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
										{{ Form::open(array('url' => 'settings/app/advanced/save')) }}

										<div class="col-md-12">
											<div class="import">
												<label for="">{{ __('settings.app.advanced.Head javascript') }}</label>
												{{ Form::textarea('header_js' , siteOption('header_js') , ['class'=>'form-control']) }}
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.advanced.Footer javascript') }}</label>
												{{ Form::textarea('footer_js' , siteOption('footer_js') , ['class'=>'form-control']) }}
											</div>

											<div class="radio_button mar-top-20">
												<div class=" checkbox-holder" style="display: block;">
													{{ Form::checkbox('active_maintenance_mode', null , siteOption('active_maintenance_mode') , ['id' => 'active_maintenance_mode']) }}
													<label for="active_maintenance_mode" class="check-box"></label>
													<label class="text-label-txt" for="active_maintenance_mode">{{ __('settings.app.advanced.Active Maintenance Mode') }}</label>
												</div>
											</div>

											<div class="button-div">
												<button class="button-save">{{ __('settings.app.advanced.Save changes') }}</button>
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
