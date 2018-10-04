@extends('layouts.app')

@section('title')
	{{ __('accounts.title') }}
@endsection

@section('content')
	<div class="tab-white" style="padding: 15px 10px;">
		<div class="row">
			<div class="col-md-12">
				<button type="button" class="btn btn-green" id="addNewUserBtn"><i class="fa fa-plus"></i> {{ __('accounts.add_new_user') }}</button>
				<button type="button" class="btn btn-green" id="exportUserEmails"><i class="fa fa-file-o"></i> {{ __('accounts.export_emails') }}</button>
				<button type="button" class="btn btn-danger" id="deleteUserBtn"><i class="fa fa-trash"></i> {{ __('accounts.delete') }}</button>
			</div>
			<div class="col-md-12" style="margin-top: 10px;">
				<div class="table-responsive" style="border: none;">
					<table class="table table-bordered" id="usersTable">
						<thead>
						<tr>
							<th class="ch-1" style="text-align: center; width: 40px;">
								<input type="checkbox" id="selectAllChckbx">
								<label for="selectAllChckbx" class="check-box red"></label>
							</th>
							<th>{{ __('accounts.Username') }}</th>
							<th class="hidden-xs hidden-sm">{{ __('accounts.E-mail') }}</th>
							<th class="hidden-xs hidden-sm">{{ __('accounts.Joined on') }}</th>
							<th>{{ __('accounts.Facebook profile') }}</th>
							<th class="hidden-xs hidden-sm">{{ __('accounts.Role') }}</th>
							<th class="hidden-xs hidden-sm">{{ __('accounts.Status') }}</th>
							<th></th>
						</tr>
						</thead>
						<tbody>
						@foreach($users AS $userInf)
							<tr data-id="{{ $userInf->id }}">
								<td style="text-align: center;"><input style="transform: scale(1.4);" type="checkbox" class="userCheckbox"></td>
								<td>{{ $userInf->username }}</td>
								<td class="hidden-xs hidden-sm">{{ $userInf->email }}</td>
								<td class="hidden-xs hidden-sm">{{ date('d-m-Y' , strtotime($userInf->created_at)) }}</td>
								<td><a href="https://fb.com/{{ $userInf->fb_user_id }}" target="_blank">{{ __('accounts.Facebook link') }}</a></td>
								<td class="hidden-xs hidden-sm">{{ @$userInf->user_role->name }}</td>
								<td class="hidden-xs hidden-sm" style="text-align: center;">
									@if($userInf->status == 1)
									<button type="button" class="btn btn-sm btn-success switchStatus" data-status="on"><i class="fa fa-fw fa-toggle-on"></i></button>
									@else
									<button type="button" class="btn btn-sm btn-default switchStatus" data-status="off"><i class="fa fa-fw fa-toggle-on"></i></button>
									@endif
								</td>
								<td>
									<button type="button" class="btn btn-primary btn-sm editUserBtn"><i class="fa fa-edit"></i> {{ __('accounts.Edit') }}</button>
									<button type="button" onclick="location.href = '{{ url('accounts/' . $userInf->id) }}';" class="btn btn-primary btn-sm"><i class="fa fa-info-circle"></i> {{ __('accounts.Details') }}</button>
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<div class="col-md-12">
				{{ $users->links() }}
			</div>
		</div>
	</div>

	<script>
		$("#addNewUserBtn").click(function()
		{
			proApp.loadModal('{{ url('accounts/addEditUser/0') }}' , '' , {});
		});

		$(".editUserBtn").click(function()
		{
			proApp.loadModal('{{ url('accounts/addEditUser/') }}/' + $(this).closest('tr').attr('data-id') , '' , {});
		});

		$("#usersTable").on('click' , '.switchStatus' , function()
		{
			var tBtn	= $(this),
				userId	= $(this).closest('tr').attr('data-id'),
				status	= $(this).attr('data-status') == 'on' ? 'off' : 'on';

			proApp.ajax('{{ url('ajax/accounts/userStatusChange') }}' , {
				'id':		userId,
				'status':	status
			} , function( result )
			{
				if(status == 'off')
				{
					tBtn.removeClass('btn-success').addClass('btn-default').attr('data-status' , status);
				}
				else
				{
					tBtn.removeClass('btn-default').addClass('btn-success').attr('data-status' , status);
				}
			});
		});

		$("#selectAllChckbx").click(function()
		{
			if($(this).is(':checked'))
			{
				$(".userCheckbox:not(:checked)").prop('checked' , true);
			}
			else
			{
				$(".userCheckbox:checked").prop('checked' , false);
			}
		});

		$("#deleteUserBtn").click(function()
		{
			var selectedUsersCount = $("#usersTable .userCheckbox:checked").length;

			if( selectedUsersCount == 0 )
			{
				return;
			}

			proApp.confirm('{{ __('accounts.delete_confirmation') }}' , '{{ __('accounts.are_you_sure_to_delete') }}' , function()
			{
				var users = [];

				$("#usersTable>tbody>tr").each(function()
				{
					if( $(this).find('.userCheckbox').is(':checked') )
					{
						users.push( $(this).attr('data-id') );
					}
				});

				proApp.ajax('{{ url('ajax/accounts/deleteUsers') }}' , {
					'users' : users
				} , function()
				{
					location.reload();
				});
			});
		});

		$("#exportUserEmails").click(function()
		{
			proApp.loadModal('{{ url('accounts/export') }}' , 'Export' );
		});

	</script>
@endsection
