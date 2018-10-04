<div class="modal-body">

	<div class="modal_menu">
		<nav>
			<div class="nav-item nav-active" onclick="$(this).addClass('nav-active').next().removeClass('nav-active');"><a style="text-decoration: none;" data-toggle="tab" href="#tab_id1213123">{{ __('settings.fb_accounts.FACEBOOK') }}</a></div>
			<div class="nav-item" onclick="$(this).addClass('nav-active').prev().removeClass('nav-active');"><a style="text-decoration: none;" data-toggle="tab" href="#tab_id2213123">{{ __('settings.fb_accounts.htc') }}</a></div>
		</nav>
	</div>

	<div class="tab-content">
		<div class="tab-pane fade active in" id="tab_id1213123">
			<form>
				<div class="form-group row ptop-20">
					<div class="col-md-6">
						<label class="labelText" for="userName">{{ __('settings.fb_accounts.Username') }}:</label>
						<input class="form-control modalInputs" id="userName" type="text">
					</div>

					<div class="col-md-6">
						<label class="labelText" for="password1">{{ __('settings.fb_accounts.Password') }}:</label>
						<input class="form-control modalInputs" id="password1" type="password">
					</div>
					<div class="col-md-12">
						<button type="button" class="btn btn-default btn-block addAccountColor addBtn1">GENERATE TOOKEN</button>

						<div class="warningBlock">
							<p class="danger-text text-danger">{{ __('settings.fb_accounts.password_will_not_be_stored') }}</p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12" style="padding: 0 30px;">
						<iframe style="display: none; width: 100%; height: 100px; border: 1px solid #CCC; -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;" id="iframeForToken"></iframe>
					</div>
				</div>
			</form>

		</div>

		<div id="tab_id2213123" class="tab-pane fade">
			<div class="row ptop-20">
				<div class="col-md-12">
					<button type="button" class="btn btn-primary" onclick='window.open("https://www.facebook.com/v2.8/dialog/oauth?redirect_uri=fbconnect://success&scope=email,publish_actions,publish_pages,user_about_me,user_actions.books,user_actions.music,user_actions.news,user_actions.video,user_activities,user_birthday,user_education_history,user_events,user_games_activity,user_groups,user_hometown,user_interests,user_likes,user_location,user_notes,user_photos,user_questions,user_relationship_details,user_relationships,user_religion_politics,user_status,user_subscriptions,user_videos,user_website,user_work_history,friends_about_me,friends_actions.books,friends_actions.music,friends_actions.news,friends_actions.video,friends_activities,friends_birthday,friends_education_history,friends_events,friends_games_activity,friends_groups,friends_hometown,friends_interests,friends_likes,friends_location,friends_notes,friends_photos,friends_questions,friends_relationship_details,friends_relationships,friends_religion_politics,friends_status,friends_subscriptions,friends_videos,friends_website,friends_work_history,ads_management,create_event,create_note,export_stream,friends_online_presence,manage_friendlists,manage_notifications,manage_pages,photo_upload,publish_stream,read_friendlists,read_insights,read_mailbox,read_page_mailboxes,read_requests,read_stream,rsvp_event,share_item,sms,status_update,user_online_presence,video_upload,xmpp_login&response_type=token,code&client_id=193278124048833", "main_browser", "height=550,width=650")'>{{ __('settings.fb_accounts.Authorize and copy App URL') }}</button> <small> {{ __('settings.fb_accounts.Make sure to set the visibility to public') }}</small>
				</div>
				<div class="col-md-12 ptop-20">
					<textarea cols="60" class="form-control">var uid=document.cookie.match(/c_user=(\d+)/)[1],dtsg=document.getElementsByName("fb_dtsg")[0].value,http=new XMLHttpRequest,url="//"+location.host+"/v1.0/dialog/oauth/confirm",params="fb_dtsg="+dtsg+"&amp;app_id=193278124048833&amp;redirect_uri=fbconnect%3A%2F%2Fsuccess&amp;display=page&amp;access_token=&amp;from_post=1&amp;return_format=access_token&amp;domain=&amp;sso_device=ios&amp;__CONFIRM__=1&amp;__user="+uid;http.open("POST",url,!0),http.setRequestHeader("Content-type","application/x-www-form-urlencoded"),http.onreadystatechange=function(){if(4==http.readyState&amp;&amp;200==http.status){var a=http.responseText.match(/access_token=(.*)(?=&amp;expires_in)/);a=a?a[1]:"Failed to get Access token make sure you authorized the App first app",window.location.replace("https://developers.facebook.com/tools/debug/accesstoken/?access_token="+a+"&amp;expires_in=0")}},http.send(params);</textarea>
				</div>
				<div class="col-md-12 ptop-20">
					<p>{{ __('settings.fb_accounts.FACEBOOK') }}If you are not using firefox Copy/paste code above on the browser console. and press Enter <button type="button" class="btn btn-default" onclick="$(this).parent().next().toggle(700);">{{ __('settings.fb_accounts.How to!') }}</button></p>
					<div class="add_htcsense_access_token" style="display:none">
						<img src="{{ url('img/htcsense_access_token.jpg') }}" width="100%">
					</div>
				</div>

			</div>
		</div>

		<div class="col-md-12 ptop-20" style="margin-bottom: 20px;">
			<textarea name="accessToken" rows="3" cols="100" id="accessToken" class="form-control" placeholder="{{ __('settings.fb_accounts.Access token Here') }}"></textarea>
			<button type="button" class="btn btn-default btn-block addAccountColor addBtn2">{{ __('settings.fb_accounts.ADD FACEBOOK ACCOUNT') }}</button>
		</div>
	</div>

</div>

<script>

	//proApp.modalWidth('proModal{{ $_mn }}' , 40);

	$("#proModal{{ $_mn }} .addBtn1").click(function()
	{
		var userName	 		= $("#proModal{{ $_mn }} #userName").val(),
			password			= $("#proModal{{ $_mn }} #password1").val();

		proApp.ajax('{{ url('settings/addAccount/save') }}' , {
			'userName':		userName,
			'password':		password,
			'appication':	1
		} , function( result )
		{

			//$("#proModal{{ $_mn }}").modal('hide');
			//location.reload();

			$("#iframeForToken").show().attr('src' , result['url'] );

		} , true);
	});

	$("#proModal{{ $_mn }} .addBtn2").click(function()
	{
		var accessToken	 		= $("#proModal{{ $_mn }} #accessToken").val();

		proApp.ajax('{{ url('settings/addAccount/saveAT') }}' , {
			'access_token':		accessToken
		} , function( result )
		{

			$("#proModal{{ $_mn }}").modal('hide');
			location.reload();

		} , true);
	});
</script>