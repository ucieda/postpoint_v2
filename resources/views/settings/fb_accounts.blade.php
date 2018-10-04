@extends('layouts.app')

@section('title')
	{{ __('settings.fb_accounts.title') }}
@endsection

@section('content')
    <div class="tab-white">
        <div >
            <div id="main_section">
                <div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'fb_accounts'])
                    <div class="resp-tabs-container">
                        <div>
                            <section id="facebook_login">
                                <div class="container-fluid">
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
                                        {{ Form::open(array('url' => 'settings/fb_accounts/save')) }}

                                        <div class="col-md-12 mar-top-20">
                                            <div class="radio_button">
                                                <div class="group checkbox-holder">
                                                    {{ Form::checkbox('load_my_groups', null , Auth::user()->load_my_groups , ['id' => 'load_my_groups']) }}
                                                    <label for="load_my_groups" class="check-box"></label>
                                                    <label class="text-label-txt" for="load_my_groups">{{ __('settings.fb_accounts.Load my groups') }}</label>
                                                </div>
                                                <div class="sec checkbox-holder">
                                                    {{ Form::checkbox('load_my_pages', null , Auth::user()->load_my_pages , ['id' => 'load_my_pages']) }}
                                                    <label for="load_my_pages" class="check-box"></label>
                                                    <label class="text-label-txt" for="load_my_pages">{{ __('settings.fb_accounts.Load my pages') }}</label>
                                                </div>
                                                <div class="sec checkbox-holder">
                                                    {{ Form::checkbox('load_my_ownpages', null , Auth::user()->load_my_ownpages , ['id' => 'load_my_ownpages']) }}
                                                    <label for="load_my_ownpages" class="check-box"></label>
                                                    <label class="text-label-txt" for="load_my_ownpages">{{ __('settings.fb_accounts.Load my ownpages') }}</label>
                                                </div>
                                            </div>
                                            <div class="import">
                                                <label for="">{{ __('settings.fb_accounts.Maximum groups to import') }}</label>
                                                {{ Form::number('max_groups_to_import' , Auth::user()->max_groups_to_import) }}

                                                <label for="">{{ __('settings.fb_accounts.Maximum pages to import') }}</label>
                                                {{ Form::number('max_pages_to_import' , Auth::user()->max_pages_to_import) }}

                                                <div class="button-div">
                                                    <button class="button-save">{{ __('settings.fb_accounts.Save changes') }}</button>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="line"></div>
                                                <div class="col-md-12">
                                                    <div class="plus">
                                                        <button type="button" class="btn btn-info button-normalizer" onclick="proApp.loadModal('{{ url('settings/addAccount') }}' , '...');">
                                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="user_data">
                                                <div class="table-holder">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr class="first">
                                                            <th class="head_line">{{ __('settings.fb_accounts.Full name') }}</th>
                                                            <th class="head_line">{{ __('settings.fb_accounts.ID') }}</th>
                                                            <th class="head_line">{{ __('settings.fb_accounts.E-mail') }}</th>
                                                            <th class="head_line"></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach( $accounts AS $account )
                                                                <tr data-id="{{ $account->id }}">
                                                                    <td>
                                                                        <img class="img-circle " src="https://graph.facebook.com/{{ $account->fb_account_id }}/picture?redirect=1&height=25&width=25&type=normal" width="30px" height="30px">
                                                                        <a href="https://fb.com/{{ $account->fb_account_id }}" target="_blank">{{ $account->name }}</a>
                                                                    </td>
                                                                    <td>{{ $account->fb_account_id }}</td>
                                                                    <td>{{ $account->email }}</td>
                                                                    <td>
                                                                        <button type="button" class="btn btn-primary updateBtn"><i class="fa fa-refresh"></i></button>
																		<button type="button" class="btn btn-danger deleteBtn"><i class="fa fa-trash"></i></button>
																	</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
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
    <script>
		$(".deleteBtn").click(function()
		{
			var t = this;
			proApp.confirm('{{ __('settings.fb_accounts.Delete confirmation') }}' , '{{ __('settings.fb_accounts.Are you sure you want to delete the account?') }}' , function()
			{
				var id = $(t).closest('tr').attr('data-id');

				proApp.ajax('{{ url('ajax/settings/fb_accounts/delete') }}' , { 'id': id } , function (result)
				{
					location.reload();
				});
			});
		});

		$(".updateBtn").click(function()
		{
			var t = this;
			proApp.confirm('{{ __('settings.fb_accounts.Update confirmation') }}' , '{{ __('settings.fb_accounts.Are you sure you want to update the account?') }}' , function()
			{
				var id = $(t).closest('tr').attr('data-id');

				proApp.ajax('{{ url('ajax/settings/fb_accounts/update') }}' , { 'id': id } , function (result)
				{
					location.reload();
				});
			});
		});
    </script>
@endsection
