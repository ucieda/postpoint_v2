@extends('layouts.app')

@section('title')
	{{ __('settings.profile.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'profile'])
					<div class="resp-tabs-container">
						<div>
							<section id="my_profile">
								<div class="container margin-normalizer">
									<div class="row">
										<div class="col-md-10" style="margin-top: 10px;">
											@if ($errors->any())
												<div class="alert alert-danger">
													{{ __('settings.form_error') }}
												</div>
											@endif
											@if (\Session::has('success'))
												<div class="alert alert-success">
													{{ __('settings.saved_successfull') }}
												</div>
											@endif
										</div>
										{{ Form::open(array('url' => 'settings/profile/save')) }}
											<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 reset-mar-pad flex flex-wrap-reverse">
												<div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 left first_position">
													<div class="form-element">
														<label for="">{{ __('settings.profile.Username') }}:</label>
														<div class="input-wrapper">
															{!! Form::text('username' , $info->username , ['class'=>'admin' , 'disabled']) !!}
														</div>
													</div>
													<div class="form-element">
														<label for="">{{ __('settings.profile.First name') }}:</label>
														<div class="input-wrapper">
															{!! Form::text('name' , $info->name) !!}
														</div>
													</div>
													<div class="form-element">
														<label for="">{{ __('settings.profile.E-mail') }}</label>
														<div class="input-wrapper">
															{!! Form::text('email' , $info->email) !!}
														</div>
													</div>
													{!! Form::submit('Save changes' , ['class'=>'button-save']) !!}
												</div>
												<div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 right first_position">
													<div class="form-element">
														<label for="">{{ __('settings.profile.Do you want to change password?') }}</label>
														<div class="input-wrapper">
															<input type="password" value="*****" disabled class="password">
															<button class="change_btn" type="button" onclick="proApp.loadModal('{{ url('settings/chngPass') }}' , '{{ __('settings.profile.Change password') }}')">{{ __('settings.profile.Change password') }}</button>
														</div>
													</div>
													<div class="form-element">
														<label for="">{{ __('settings.profile.Last name') }}:</label>
														<div class="input-wrapper">
															{!! Form::text('surname' , $info->surname) !!}
														</div>
													</div>
													<div class="form-element">
														<label for="">{{ __('settings.profile.Facebook user id') }}:</label>
														<div class="input-wrapper">
															{!! Form::text('fb_user_id' , $info->fb_user_id) !!}
														</div>
													</div>
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
