@extends('layouts.app')

@section('title')
	{{ __('settings.app.mail.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'app'])
					<div class="resp-tabs-container">
						@include('settings.menu2' , ['menu2' => 'mail'])
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
										{{ Form::open(array('url' => 'settings/app/mail/save')) }}

										<div class="col-md-12">
											<div class="import">
												<label for="">{{ __('settings.app.mail.Mail protocol') }}</label>
												{{ Form::select('mail_protocol' , [
													'mail'	=>	__('settings.app.mail.Mail'),
													'smtp'	=>	__('settings.app.mail.SMTP')
												] , siteOption('mail_protocol') , ['class'=>'select mailProtocolSelect' , 'style' => 'width: 38%;']) }}
											</div>
											<div class="import fade_div_cls">
												<label for="">{{ __('settings.app.mail.SMTP host') }}</label>
												{{ Form::text('smtp_host' , siteOption('smtp_host') , ['class'=>'form-control']) }}
											</div>
											<div class="import fade_div_cls">
												<label for="">{{ __('settings.app.mail.SMTP user') }}</label>
												{{ Form::text('smtp_user' , siteOption('smtp_user') , ['class'=>'form-control']) }}
											</div>
											<div class="import fade_div_cls">
												<label for="">{{ __('settings.app.mail.SMTP pass') }}</label>
												{{ Form::text('smtp_pass' , siteOption('smtp_pass') , ['class'=>'form-control']) }}
											</div>
											<div class="import fade_div_cls">
												<label for="">{{ __('settings.app.mail.SMTP port') }}</label>
												{{ Form::text('smtp_port' , siteOption('smtp_port') , ['class'=>'form-control']) }}
											</div>
											<div class="import fade_div_cls">
												<label for="">{{ __('settings.app.mail.SMTP encryption') }}</label>
												{{ Form::text('smtp_encryption' , siteOption('smtp_encryption') , ['class'=>'form-control']) }}
											</div>

											<div class="button-div">
												<button class="button-save">{{ __('settings.app.mail.Save changes') }}</button>
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
