@extends('layouts.app')

@section('title')
	User details
@endsection

@section('content')
	<section class="row account-details">
		<div class="alerts"></div>

		<div class="col-xs-12 col-sm-12 col-md-3">
			<div class="box box-primary" style="background: #FFF; padding: 20px; border: 1px solid #DDD; border-radius: 5px;">
				<div class="box-body box-profile" style="text-align: center;">
					<img class="profile-user-img img-responsive img-circle" src="{{ !empty($info->fb_user_id) ? 'https://graph.facebook.com/'.$info->fb_user_id.'/picture?redirect=1&height=60&width=60&type=normal' : url('img/facebookUser.jpg') }}" style=" width: 150px; display: inline;margin: 0 0 20px 0; border: 1px solid #EEE;">
					<h3 class="profile-username text-center"> </h3>
					<button class="btn btn-primary editUserBtn btn-block" value="2">
						<i class="fa fa-fw fa-pencil" aria-hidden="true"></i>
						{{ __('accounts.Edit') }}
					</button>
					<button type="button" class="btn btn-{{ $info->status ? 'primary' : 'warning' }} btn-block userToggleAccountBtn" data-status="{{ $info->status ? 'on' : 'off' }}">
						<i class="fa fa-fw fa-toggle-on" aria-hidden="true"></i>
						{{ $info->status ? __('accounts.Disable Account') : __('accounts.Enable Account') }}
					</button>
					<button type="button" class="btn btn-primary btn-block" id="accessAcountBtn"><i class="fa fa-fw fa-sign-in" aria-hidden="true"></i> {{ __('accounts.Access Account') }}</button>
				</div>
			</div>
		</div>

		<div class="col-xs-12 col-sm-7 col-md-4">
			<div class="box box-primary">
				<div class="box-body">
					<ul class="list-group list-group-unbordered">
						<li class="list-group-item">
							<strong>{{ __('accounts.Username') }} :</strong>
							<span class="label label-primary">{{ $info->username }}</span>
						</li>
						<li class="list-group-item">
							<strong>{{ __('accounts.Role') }} :</strong>
							<span class="label label-primary">{{ @$info->User_role->name }}</span>
						</li>

						<li class="list-group-item">
							<strong>{{ __('accounts.E-mail') }} :</strong>
							<span class="label label-primary">{{ $info->email }}</span>
						</li>

						<li class="list-group-item">
							<strong>{{ __('accounts.Status') }} :</strong>
							<span class="label label-{{ !$info->status ? 'danger' : 'primary' }}">
								{{ $info->status ? __('accounts.Active') : __('accounts.Deactive') }}
							</span>
						</li>

						<li class="list-group-item">
							<strong>{{ __('accounts.Account expiry') }} :</strong>
							<span class="label label-{{ strtotime($info->expire_on) >= time() ? 'danger' : 'primary' }}">
								{{ strtotime($info->expire_on) >= time() ? __('accounts.Expired') : __('accounts.Active') }}
							</span>
						</li>

						<li class="list-group-item">
							<strong>{{ __('accounts.Account Expire on') }} :</strong>
							<span class="label label-primary">{{ date(dateFormat() , strtotime($info->expire_on)) }}</span>
						</li>

						<li class="list-group-item">
							<strong>{{ __('accounts.Group') }} :</strong>
							<span class="label label-primary">{{ @$info->User_role->name }}</span>
						</li>

						<li class="list-group-item">
							<strong>{{ __('accounts.Joined on') }} :</strong>
							<span class="label label-primary">{{ date(dateFormat() , strtotime($info->created_at)) }}</span>
						</li>

						<li class="list-group-item">
							<strong>{{ __('accounts.Last Login') }} :</strong>
							<span class="label label-primary">{{ !$info->last_login ? ' - ' : date(dateFormat().' H:i:s' , strtotime($info->last_login)) }}</span>
						</li>

						<li class="list-group-item">
							<strong>{{ __('accounts.Language') }} :</strong>
							<span class="label label-primary">{{ $info->Language->name }}</span>
						</li>

						<li class="list-group-item">
							<strong>{{ __('accounts.Timezone') }}:</strong>
							<span class="label label-primary">{{ $info->timezone }}</span>
						</li>

					</ul>
				</div>
			</div>
		</div>

		<div class="col-xs-12 col-sm-5 col-md-4">
			<div class="box box-primary">
				<div class="box-body">
					<ul class="list-group list-group-unbordered">
						<li class="list-group-item">
							<strong>{{ __('accounts.Number of facebook accounts') }} :</strong>
							<span class="label label-primary">{{ $accounts_count }}</span>
						</li>
						<li class="list-group-item">
							<strong>{{ __('accounts.Number of saved Posts') }} :</strong>
							<span class="label label-primary">{{ $saved_posts }}</span>
						</li>
						<li class="list-group-item">
							<strong>{{ __('accounts.Number of Schedules') }} :</strong>
							<span class="label label-primary">{{ $schedules }}</span>
						</li>
						<li class="list-group-item">
							<strong>{{ __('accounts.Total posts (success)') }} :</strong>
							<span class="label label-primary">{{ $posts_success }}</span>
						</li>
						<li class="list-group-item">
							<strong>{{ __('accounts.Total posts (fail)') }} :</strong>
							<span class="label label-primary">{{ $posts_fails }}</span>
						</li>
					</ul>
				</div>
			</div>
		</div>

	</section>

	<script>
		$(".editUserBtn").click(function()
		{
			proApp.loadModal('{{ url('accounts/addEditUser/' . $id) }}' , '...' , {});
		});
		$(".userToggleAccountBtn").click(function()
		{
			var tBtn	= $(this),
				status	= $(this).attr('data-status') == 'on' ? 'off' : 'on';

			proApp.ajax('{{ url('ajax/accounts/userStatusChange') }}' , {
				'id':		'{{ $id }}',
				'status':	status
			} , function( result )
			{
				if(status == 'off')
				{
					tBtn.removeClass('btn-primary').addClass('btn-warning').attr('data-status' , status).html('<i class="fa fa-fw fa-toggle-on" aria-hidden="true"></i> Enable account');
				}
				else
				{
					tBtn.removeClass('btn-warning').addClass('btn-primary').attr('data-status' , status).html('<i class="fa fa-fw fa-toggle-on" aria-hidden="true"></i> Disable account');
				}
			});
		});

		$("#accessAcountBtn").click(function()
		{
			proApp.ajax('{{ url('ajax/login_with_id') }}' , {'id' : '{{ $id }}'} , function(result)
			{
				location.href = '{{ url('home') }}';
			});
		});
	</script>
@endsection
