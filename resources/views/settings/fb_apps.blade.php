@extends('layouts.app')

@section('title')
	{{ __('settings.fb_apps.title') }}
@endsection

@section('content')
    <div class="tab-white">
        <div >
            <div id="main_section" class="fb-apps">
                <div id="horizontalTab" style="display: block; width: 100%; margin: 0px;">
					@include('settings.menu1' , ['menu1' => 'fb_apps'])
                    <div class="resp-tabs-container">
                        <div class="resp-tab-content resp-tab-content-active">

                            <div class="container fbApps addAccount" style="width: 100% !important;">
                                <div class="row borderBottom">

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
                                    <div class="col-md-8 col-sm-12 col-xs-12 reset-mar-pad">
                                        {{ Form::open(array('url' => 'settings/fb_apps/save')) }}
                                            <div class="form-group row">
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <label class="labelText mar-bot-10" for="inpt-1">{{ __('settings.fb_apps.Facebook app ID') }}:</label>
                                                    {{ Form::text('fb_app_id' , '' , ['class' => 'form-control modalInputs']) }}
                                                </div>
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <label class="labelText mar-bot-10" for="inpt-2">{{ __('settings.fb_apps.Facebook app Secret') }}:</label>
                                                    {{ Form::text('fb_app_secret' , '' , ['class' => 'form-control modalInputs']) }}
                                                </div>
                                            </div>
                                            <div class="form-group row pad-top-20">
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <label class="labelText mar-bot-10" for="inpt-1">{{ __('settings.fb_apps.Facebook app authenticate link') }}:</label>
                                                    {{ Form::text('fb_app_authenticate_link' , '' , ['class' => 'form-control modalInputs']) }}
                                                </div>
												@if(Auth::user()->is_admin)
                                                <div class="checkApp col-md-6 col-sm-6 col-xs-12">
                                                    {{ Form::checkbox('fb_public_app' , null , null , ['id' => 'fb_public_app']) }}
                                                    <label for="fb_public_app" class="check-box"></label>
                                                    <label class="" for="fb_public_app" style="margin-left: 10px">{{ __('settings.fb_apps.Make this app public') }}</label>
                                                </div>
												@endif
                                            </div>
                                            <div class="row">
                                                <button type="submit" class="btn btn-default btn-block addAccountBtn fbAdd mar-left-30 button-normalizer">{{ __('settings.fb_apps.ADD') }}</button>
                                            </div>
                                            {{ Form::token() }}
                                        {{ Form::close() }}
                                    </div>
                                </div>
                                <div class="col-md-5 hidden-sm"></div>

								@if( !empty($fbAccountName) )
                                <div class="alert alert-warning" style="margin-top:20px;">
                                    <i class="fa fa-exclamation-triangle"></i> {{ __('settings.fb_apps.warning1' , ['name' => $fbAccountName]) }}
                                </div>
								@endif

                                <div>
                                    <div class="table-holder">
                                        <table class="table-1 main-style table table-bordered">
                                            <thead class="grey white">
                                            <tr>
                                                <th>{{ __('settings.fb_apps.App name') }}</th>
                                                <th>{{ __('settings.fb_apps.Expires on') }}</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($apps AS $appInf)
                                            <tr data-id="{{ $appInf->id }}" data-auth-id="{{ $appInf->auth ? $appInf->auth->id : 0 }}" data-type="{{ empty($appInf->fb_app_authenticate_link) ? 'auth' : 'link' }}">
                                                <td>{{ $appInf->name }}</td>
												<td>{{ $appInf->auth ? ($appInf->auth->expires_on ? $appInf->auth->expires_on : __('settings.fb_apps.Never')) : __('settings.fb_apps.---------') }}</td>
												<td style="width: 250px;">
													@if( !$appInf->is_standart )
                                                    <button type="button" class="btn btn-danger btn-md redBtn btn-table deleteAppBtn">{{ __('settings.fb_apps.Delete') }}</button>
													@endif

													@if( $appInf->auth )
                                                    <button type="button" class="btn btn-danger btn-md btn-table deauthenticateBtn">{{ __('settings.fb_apps.Deauthenticate') }}</button>
													@else
													<button type="button" class="btn btn-primary btn-md btn-table authenticateBtn">{{ __('settings.fb_apps.Authenticate') }}</button>
													@endif

                                                </td>
                                            </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

	<script>
		$(".deauthenticateBtn").click(function()
		{
			var t = $(this);

			proApp.confirm('{{ __('settings.fb_apps.DeauthenticateConfirmation') }}' , '{{ __('settings.fb_apps.DeauthenticateConfirmation2') }}' , function()
			{
				var dataId	= t.closest('tr').attr('data-auth-id');

				proApp.ajax('{{ url('settings/fb_apps/deauthenticate') }}' , {
					'id':	dataId
				} , function( result )
				{
					location.reload();
				} , true);
			});

		});

		$(".authenticateBtn").click(function()
		{
			var dataId = $(this).closest('tr').attr('data-id'),
				dataType	=	$(this).closest('tr').attr('data-type');

			if( dataId <= 2 )
			{
				proApp.loadModal('{{ url('settings/fb_apps/authenticate/') }}/' + dataId);
			}
			else if( dataType == 'link' )
			{
				proApp.loadModal('{{ url('settings/fb_apps/authenticate/') }}/' + dataId);
			}
			else
			{
				window.open('{{ url('fb/login/') }}/' + dataId , 'DescriptiveWindowName', 'resizable,scrollbars,status,width=800,height=700');
			}
		});

		$(".deleteAppBtn").click(function()
		{
			var dataId		=	$(this).closest('tr').attr('data-id');

			proApp.confirm('{{ __('settings.fb_apps.Delete confirmation') }}' , '{{ __('settings.fb_apps.Are you sure you want to delete the app?') }}' , function()
			{
				proApp.ajax('{{ url('settings/fb_apps/delete') }}' , {
					'id':	dataId
				} , function( result )
				{
					location.reload();
				} , true);
			});
		});
	</script>
@endsection
