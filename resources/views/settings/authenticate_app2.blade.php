<div class="modal-body">

	<div class="row ">
		<div class="col-md-12">
			<button type="button" class="btn btn-primary" onclick='window.open("{{ $authLink }}", "main_browser", "height=550,width=650")'>Authorize and copy App URL</button>
			<small> Make sure to set the visibility to public</small>
		</div>
		<div class="col-md-12 ptop-20">
			<textarea cols="60" class="form-control">var uid=document.cookie.match(/c_user=(\d+)/)[1],dtsg=document.getElementsByName("fb_dtsg")[0].value,http=new XMLHttpRequest,url="//"+location.host+"/v1.0/dialog/oauth/confirm",params="fb_dtsg="+dtsg+"&amp;app_id=193278124048833&amp;redirect_uri=fbconnect%3A%2F%2Fsuccess&amp;display=page&amp;access_token=&amp;from_post=1&amp;return_format=access_token&amp;domain=&amp;sso_device=ios&amp;__CONFIRM__=1&amp;__user="+uid;http.open("POST",url,!0),http.setRequestHeader("Content-type","application/x-www-form-urlencoded"),http.onreadystatechange=function(){if(4==http.readyState&amp;&amp;200==http.status){var a=http.responseText.match(/access_token=(.*)(?=&amp;expires_in)/);a=a?a[1]:"Failed to get Access token make sure you authorized the App first app",window.location.replace("https://developers.facebook.com/tools/debug/accesstoken/?access_token="+a+"&amp;expires_in=0")}},http.send(params);</textarea>
		</div>
		<div class="col-md-12 ptop-20">
			<p>If you are not using firefox Copy/paste code above on the browser console. and press Enter <button type="button" class="btn btn-default" onclick="$(this).parent().next().toggle(700);">How to!</button></p>
			<div class="add_htcsense_access_token" style="display:none">
				<img src="{{ url('img/htcsense_access_token.jpg') }}" width="100%">
			</div>
		</div>
		<div class="col-md-12 ptop-20">
			<textarea name="accessToken" rows="3" cols="100" id="accessToken" class="form-control" placeholder="Access token Here"></textarea>
			<button type="button" class="btn btn-default btn-block addAccountColor addBtn2">ADD FACEBOOK ACCOUNT</button>
		</div>
	</div>

</div>

<script>
	//proApp.modalWidth('proModal{{ $_mn }}' , 50);

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