@extends('layouts.app')

@section('title')
	{{ __('settings.app.general.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'app'])
					<div class="resp-tabs-container">
						@include('settings.menu2' , ['menu2' => 'general'])
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
										{{ Form::open(array('url' => 'settings/app/save')) }}

										<div class="col-md-12">
											<div class="import">
												<label for="">{{ __('settings.app.general.Site name') }}</label>
												{{ Form::text('site_name' , siteOption('site_name')) }}
											</div>
											<div class="radio_button mar-top-20">
												<div class=" checkbox-holder" style="display: block;">
													{{ Form::checkbox('is_register_active', null , siteOption('is_register_active') , ['id' => 'is_register_active']) }}
													<label for="is_register_active" class="check-box"></label>
													<label class="text-label-txt" for="is_register_active">{{ __('settings.app.general.New users can register') }}</label>
												</div>
												<div class="sec checkbox-holder" style="display: block;">
													{{ Form::checkbox('confirm_with_email', null , siteOption('confirm_with_email') , ['id' => 'confirm_with_email']) }}
													<label for="confirm_with_email" class="check-box"></label>
													<label class="text-label-txt" for="confirm_with_email">{{ __('settings.app.general.New users must confirm their email address') }}</label>
												</div>
												<div class="sec checkbox-holder" style="display: block;">
													{{ Form::checkbox('new_users_must_activated_by_admin', null , siteOption('new_users_must_activated_by_admin') , ['id' => 'new_users_must_activated_by_admin']) }}
													<label for="new_users_must_activated_by_admin" class="check-box"></label>
													<label class="text-label-txt" for="new_users_must_activated_by_admin">{{ __('settings.app.general.New users accounts must be activated by an admin') }}</label>
												</div>
											</div>
											<div class="import">
												<label for="">{{ __('settings.app.general.New users Default role') }}</label>
												{{ Form::select('default_role_id' , \App\User_role::where('is_for_demo','1')->pluck('name','id') , siteOption('default_role_id') , ['class'=>'select' ] ) }}
												<label for="">{{ __('settings.app.general.New users Default timezone') }}</label>
												{{ Form::select('default_timezone' , \App\Http\Controllers\SettingsController::timezones() , siteOption('default_timezone') , ['class'=>'select'] ) }}
												<label for="">{{ __('settings.app.general.Default language') }}</label>
												{{ Form::select('default_lang_id' , \App\Language::pluck('name' , 'id') , siteOption('default_lang_id') , ['class'=>'select']) }}
												<label for="">{{ __('settings.app.general.Date format') }}</label>
												{{ Form::select('date_format' , [
													'YYYY-MM-DD' => 'YYYY-MM-DD',
													'MM/DD/YYYY' => 'MM/DD/YYYY',
													'DD-MM-YYYY' => 'DD-MM-YYYY'
												] , siteOption('date_format') , ['class'=>'select'] ) }}

											</div>
											<div class="radio_button mar-top-20">
												<div class=" checkbox-holder" style="display: block;">
													{{ Form::checkbox('lite_mode_nodes_table', null , siteOption('lite_mode_nodes_table') , ['id' => 'lite_mode_nodes_table']) }}
													<label for="lite_mode_nodes_table" class="check-box"></label>
													<label class="text-label-txt" for="lite_mode_nodes_table">{{ __('settings.app.general.Enable Lite mode for nodes table') }}</label>
												</div>
											</div>

											<div class="button-div">
												<button class="button-save">{{ __('settings.app.general.Save changes') }}</button>
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
