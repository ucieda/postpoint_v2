@extends('layouts.app')

@section('title')
	{{ __('home.title') }}
@endsection

@section('content')
	<style>
		.uploadBtn
		{
			    background: #4b82c6;
    color: #fff !important;
    height: 40px;
    padding: 10px;
    border: none;
    font-weight: 500;
    text-shadow: none;
    -webkit-transition: all ease 0.3s;
    -moz-transition: all ease 0.3s;
    transition: all ease 0.3s;
		}
        .uploadBtn:hover{
                background-color: #4b82c647;
    background-position: 0 -15px;
    color: #4c83c7 !important;
        }
		#imageLinks .delImage
		{
			color: #e74c3c;
			cursor: pointer;
            font-size: 18px;
		}
		#imageLinks .linkLine
		{
			margin-bottom: 5px;
		}
		#fbPresetsList .statusBackgroundImg
		{
			width: 27px;
			height: 25px;
			cursor: pointer;
			border-radius: 5px;
			display: inline-block;
			position: relative;
			border: 1px solid #DDD;
		}
		#fbPresetsList .statusBackgroundImg.selectedFPS:before
		{
			content: ' ';
			width: 10px;
			height: 10px;
			position: absolute;
            bottom: -10px;
            left: 5px;
			border: 8px solid transparent;
			border-bottom-color: #27ae60 !important;
		}

		.withBackground
		{
			height: 350px !important;
			background-position: center;
			background-size: cover;
			margin-left: -1px;
			margin-right: -1px;
			color: #FFF;
			text-align: center;
			vertical-align: middle;
			font-size: 26px;
			font-weight: 700;
			overflow: auto;
		}
        #messageId:not(.withBackground) *
		{
			font-size: 14px !important;
			line-height: 1.42857143 !important;
			color: #555 !important;
			background-color: #fff !important;
			font-weight: 400 !important;
			text-align: left !important;
		}
		.withBackground *
		{
			background-color: transparent !important;
			font-size: 26px !important;
			font-weight: 700 !important;
			color: #FFF !important;
		}
	
		#messageId
		{
            min-height: 200px;
			height: auto;
		}

		#nodesTable tr.successTr
		{
			background-color: #9eeab18f;
		}
		#nodesTable tr.errorTr
		{
			background-color: #eb636078;
		}

		.linkPreview
		{
			border: 1px solid rgba(0, 0, 0, .1);
			display: none;
		}
		.linkPreview_image
		{
			width: 100%;
			height: 249px;
			background-position: 50% 50%;
			background-repeat: no-repeat;
			display: block;
			position: relative;
			overflow: hidden;
			background-color: #fff;
			border-bottom: 1px solid rgba(0, 0, 0, .1);
		}
		.linkPreview_info
		{
			height: auto;
			margin: 10px 12px;
			max-height: 100px;
			position: relative;
		}
		.linkPreview_info_title
		{
			color: #1d2129;
			font-size: 18px;
			font-weight: 500;
			line-height: 22px;
			word-wrap: break-word;
			margin-bottom: 5px;
		}
		.linkPreview_info_description
		{
			line-height: 16px;
			max-height: 80px;
			overflow: hidden;
			font-size: 12px;
			word-wrap: break-word;
			color: #1d2129;
		}
		.linkPreview_info_domain
		{
			color: #606770;
			font-size: 12px;
			line-height: 11px;
			text-transform: uppercase;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			word-wrap: break-word;
			padding-top: 9px;
		}

		.imagePreview
		{
			padding: 0 14px;
		}

		.imagePreview_img
		{
			padding: 2px !important;
		}

		.imagePreview_img>div
		{
			border: 1px solid #DDD;
			background-repeat: no-repeat;
			background-color: #fff;
			background-position: center;
			background-size: cover;
			width: 100%;
			height: 100%;
			position: relative;
		}

		.imagePreview_img>div[data-more]:after
		{
			content: attr(data-more);
			position: absolute;
			width: 100%;
			height: 100%;
			background: rgba(0,0,0,0.35);
			color: #FFF;
			font-size: 40px;
			font-weight: 600;
			text-align: center;
			padding-top: 51px;
		}

		.videoPreview > video
		{
			width: 100%;
		}

		.bootstrap-datetimepicker-widget
		{
			z-index: 9999999 !important;
			visibility: visible !important;
			opacity: 1 !important;
		}
	</style>

	<div id="newPost">

		<div class="row">
			<div class="col-md-12">
				@if( $fbAccountId == 0 )
					<div class="alert alert-warning" role="alert">
						<i class="fa fa-exclamation-circle fa-fw" aria-hidden="true"></i>
						{{ __('home.no_fb_account_available') }} <a href="{{ url('settings/fb_accounts') }}">{{ __('home.add_fb_account') }}</a>
					</div>
				@endif
				@if( $fbAccountId == 0 || $fbAccInf->default_app_id == 0 )
					<div class="alert alert-warning" role="alert">
						<i class="fa fa-exclamation-circle fa-fw" aria-hidden="true"></i>
						{{ __('home.no_app_has_been_selected') }} <a href="{{ url('settings/publish') }}">{{ __('home.select_default_fb_app') }}</a>
					</div>
				@endif
				@if( !Auth::user()->is_admin && !empty(Auth::user()->expire_on) && (strtotime(Auth::user()->expire_on) - time() ) < 10 * 60 * 60 * 24 /* 10 day */ )
				<div class="alert alert-danger" role="alert">
					<i class="fa fa-exclamation-triangle fa-fw" aria-hidden="true"></i>
					{{ __('home.your_account_will_expire' , ['days' => ceil( (strtotime(Auth::user()->expire_on) - time()) / 60 / 60 / 24 ) ]) }}
				</div>
				@endif
			</div>
		</div>

		<div class="row">
			<div class="col-md-6" >
				<div class="left-side" >
					<div id="left-content-1" >
						<div class="navbar add-post-tabs">

							<div class="col-md-12">

								<ul id="postTypeTabs">
									<li data-type="status" tabindex="1"{!! $postInf['post_type'] == 'status' ? ' class="active-tab"' : '' !!}>
										<img src="{{ url('img/menu/status.png') }}">
										<span>{{ __('home.status') }}</span>
									</li>
									<li data-type="link" tabindex="2"{!! $postInf['post_type'] == 'link' ? ' class="active-tab"' : '' !!}>
										<img src="{{ url('img/menu/link.png') }}">
										<span>{{ __('home.link') }}</span>
									</li>
									<li data-type="image" tabindex="3"{!! $postInf['post_type'] == 'image' ? ' class="active-tab"' : '' !!}>
										<img src="{{ url('img/menu/image.png') }}">
										<span>{{ __('home.image') }}</span>
									</li>
									<li data-type="video" tabindex="4"{!! $postInf['post_type'] == 'video' ? ' class="active-tab"' : '' !!}>
										<img src="{{ url('img/menu/video.png') }}">
										<span>{{ __('home.video') }}</span>
									</li>
								</ul>
							</div>
							<div class="clearfix"></div>
						</div>

						<div class="col-md-12">

							<div class="form-section">
								<div class="add-post-contents">

									<div class="form-group">
										<label for="message">{{ __('home.message') }}</label>
										<div data-emojiarea data-type="unicode" data-global-picker="false" class="textarea-section" style="position: relative;">
											<div class="emoji-button">&#x1f60e;</div>

											<table style="width: 100%;word-break: break-all;">
												<tr>
													<td contenteditable="" spellcheck="false" id="messageId" class="form-control">{!! nl2br(htmlspecialchars($postInf['message'])) !!}</td>
												</tr>
											</table>

										</div>
									</div>

									<div class="content-item{{ $postInf['post_type'] == 'status' ? ' active-content' : '' }}">

										<div style="margin-top: 10px;" id="fbPresetsList">
											<div data-id="0" style="background-color: rgb(255,255,255);" class="statusBackgroundImg selectedFPS"></div>
											<div data-id="106018623298955" style="background-color: rgb(198, 0, 255);" class="statusBackgroundImg"></div>
											<div data-id="2070672249875122" style="background-image: url({{ url('img/status_backgrounds/2070672249875122.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="993780377442365" style="background-image: url({{ url('img/status_backgrounds/993780377442365.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="2041051642773322" style="background-image: url({{ url('img/status_backgrounds/2041051642773322.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="2137016686532204" style="background-image: url({{ url('img/status_backgrounds/2137016686532204.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="2009883262560924" style="background-image: url({{ url('img/status_backgrounds/2009883262560924.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="166541304078067" style="background-image: url({{ url('img/status_backgrounds/166541304078067.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="1879686378959026" style="background-image: url({{ url('img/status_backgrounds/1879686378959026.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="1421927957874387" style="background-image: url({{ url('img/status_backgrounds/1421927957874387.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="816008591908985" style="background-image: url({{ url('img/status_backgrounds/816008591908985.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="373215586408461" style="background-image: url({{ url('img/status_backgrounds/373215586408461.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="1867224010255891" style="background-image: url({{ url('img/status_backgrounds/1867224010255891.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="495180284215970" style="background-image: url({{ url('img/status_backgrounds/495180284215970.jpg') }});" class="statusBackgroundImg"></div>
											<div data-id="2080100245554727" style="background-image: url({{ url('img/status_backgrounds/2080100245554727.jpg') }});" class="statusBackgroundImg"></div>
										</div>

									</div>

									<div class="content-item{{ $postInf['post_type'] == 'link' ? ' active-content' : '' }}">
										<div class="form-group">
											<form>
												<label for="linkInput">{{ __('home.link_url') }}</label>
												<input type="text" class="form-control" id="linkInput" value="{{ $postInf['link'] }}">
											</form>
										</div>


										<div{!! siteOption('enable_link_customization') && Auth::user()->link_customization ? '' : ' style="display: none;"' !!}><button class="main-btn-bc" type="button" onclick="$(this).parent().next().toggle(400 , function(){ $(this).find('input,textarea').val(''); });">{{ __('home.customize_link_fields') }}</button></div>

										<div{!! siteOption('enable_link_customization') && Auth::user()->link_customization && (!empty($postInf['link_picture']) || !empty($postInf['link_title']) || !empty($postInf['link_caption']) || !empty($postInf['link_description'])) ? '' : ' style="display: none;"' !!}>
											<div class="form-group">
												<form>
													<label for="linkPicture">{{ __('home.picture') }}</label>
												</form>
											</div>

											<div class="linkLine">
												<div class="input-group">
													<input class="form-control" placeholder="URL" type="text" value="{{ $postInf['link_picture'] }}" id="linkPicture">
													<div class="input-group-btn">
														<a type="button" class="btn btn-default uploadBtn">{{ __('home.upload') }}</a>
													</div>
												</div>
											</div>

											<div class="form-group">
												<form>
													<label for="linkInput">{{ __('home.link_title') }}</label>
													<input type="text" class="form-control" id="linkTitle" value="{{ $postInf['link_title'] }}">
												</form>
											</div>
											<div class="form-group">
												<form>
													<label for="linkInput">{{ __('home.link_caption') }}</label>
													<input type="text" class="form-control" id="linkCaption" value="{{ $postInf['link_caption'] }}">
												</form>
											</div>
											<div class="form-group">
												<form>
													<label for="linkInput">{{ __('home.link_description') }}</label>
													<textarea type="text" class="form-control" id="linkDescription">{{ $postInf['link_description'] }}</textarea>
												</form>
											</div>
										</div>

									</div>

									<div class="content-item dropdown-content{{ $postInf['post_type'] == 'image' ? ' active-content' : '' }}">

										<div class="form-group" id="imageLinks">
											@foreach( $postInf['fb_post_image'] AS $imageURL )
											<div class="linkLine">
												<div class="input-group">
													<input class="form-control imageUrlInput" placeholder="URL" type="text" onkeyup="imagePreviewConstruct();" value="{{ $imageURL['image'] }}">
													<div class="input-group-btn">
														<a type="button" class="btn btn-default uploadBtn">{{ __('home.upload') }}</a>
														<a class="btn btn-link delImage" style="display:none;"><i class="fa fa-times"></i></a>
													</div>

												</div>
											</div>
											@endforeach
										</div>
										<div class="add-img"><a href="javascript: addNewImageLine();" type="button"><i class="fa fa-plus"></i> {{ __('home.add_new_image') }}</a></div>
									</div>

									<div class="content-item{{ $postInf['post_type'] == 'video' ? ' active-content' : '' }}">
										<div class="form-group">
											<form>
												<label for="videoURL">{{ __('home.video_url') }}</label>

												<div class="input-group">
													<input type="text" class="form-control" id="videoURL" placeholder="Video URL" value="{{ $postInf['video'] }}">
													<div class="input-group-btn">
														<button type="button" class="btn btn-default uploadBtn" style="margin-top: 0;">{{ __('home.upload') }}</button>
													</div>
												</div>
											</form>
										</div>
									</div>

									<div class="form-group">
										<label for="post-interval">{{ __('home.post_interval') }}</label>
										<select class="form-control" id="postIntervalSelect">
											@for($i = (int)siteOption('minimum_immediate_post_interval'); $i < (int)siteOption('minimum_immediate_post_interval') + 600; $i = $i+10)
												<option value="{{ $i }}"{{ $postInf['post_interval'] == $i ? ' selected' : '' }}>{{ $i }} {{ __('home.sec') }}</option>
											@endfor
										</select>
									</div>


									<button class="main-btn-bc" type="button" onclick="$(this).next().toggle(400, function(){ $(this).find('input').val(''); });"{!! siteOption('enable_sale_post') ? '' : ' style="display: none;"' !!}>{{ __('home.add_product_info') }}</button>
									<div style="display: {{ siteOption('enable_sale_post') && ( !empty($postInf['product_name']) || !empty($postInf['product_price']) ) ? 'block' : 'none' }};">
										<div class="form-group">
											<label for="post-interval">{{ __('home.product_name') }}</label>
											<input type="text" class="form-control" id="productName" value="{{ siteOption('enable_sale_post') ? $postInf['product_name'] : '' }}">
										</div>
										<div class="form-group">
											<label for="post-interval">{{ __('home.product_price') }}</label>
											<input type="text" class="form-control" id="productPrice" value="{{ siteOption('enable_sale_post') ? $postInf['product_price'] : '' }}">
										</div>
									</div>

								</div>

								<div class="form-group">
									@if(siteOption('enable_instant_post'))
									<button id="sendPostBtn" {!! $autoScheduleData ? ' style="display: none;"' : '' !!} type="button"{!! ($fbAccountId == 0 || $fbAccInf->default_app_id == 0) ? " class=\"second-btn-bc\" disabled" : " class=\"main-btn-bc\"" !!}>{{ __('home.send_now') }}</button>
									@endif
									<button class="main-btn-bc" {!! $autoScheduleData ? ' style="width: auto; padding-left: 15px; padding-right: 15px; max-width: 300px;"' : '' !!} id="savePostBtn" type="button">{{ $postId > 0 ? __('home.update_post') : ($autoScheduleData ? __('home.save_and_shcedule_post') : __('home.save_post')) }}</button>

									<button class="orrange-btn-bc" {!! $autoScheduleData ? ' style="display: none;"' : '' !!} id="scheduled-post" type="button">{{ __('home.scheduled_post') }}</button>
								</div>
							</div>
						</div>
					</div>


					<!-- ==========	left-content-2 	========= -->

					<div id="left-content-2">
						<div class="col-md-12">
							<div class="form-section">
								<div class="add-post-contents">
									<form>
										<div class="form-group">
											<label for="message">{{ __('home.schedule_post_interval') }}</label>
										</div>

										<div class="form-group pad-reset">
											<div class="col-md-8 reset-mar-pad">
												<div class="settings-option ">
													<div class="group checkbox-holder">
														<div class="custom-checkbox">
															<input id='id-1' type="radio" class="schedule_duration" name="schedule_duration" value="minutes"{{ $postInf['schedule_post_interval'] % 60 == 0 ? '' : ' checked' }}>
															<label for="id-1" class='checkbox'></label>
														</div>
														<label class="checkbox-title" for="id-1">{{ __('home.minutes') }}</label>
													</div>

													<div class="group checkbox-holder">
														<div class="custom-checkbox">
															<input id='id-2' type="radio" class="schedule_duration" name="schedule_duration" value="hours"{{ $postInf['schedule_post_interval'] % 60 == 0 ? ' checked' : '' }}>
															<label for="id-2" class='checkbox'></label>
														</div>
														<label class="checkbox-title" for="id-2">{{ __('home.hours') }}</label>
													</div>
												</div>
											</div>
											<div class="col-md-4 reset-mar-pad">
												<select class="schedule_duration_n">
													@for($h = (int)siteOption('minimum_schedule_post_interval'); $h <= (int)siteOption('minimum_schedule_post_interval')+90; $h++)
													<option value="{{ $h }}"{{ ($postInf['schedule_post_interval'] % 60 == 0 ? $postInf['schedule_post_interval']/60 : $postInf['schedule_post_interval']) == $h ? ' selected' : '' }}>{{ $h }}</option>
													@endfor
												</select>
											</div>

											<div class="col-md-6 reset-mar-pad mar-top-20 md-pad-r15 date-app-control">
												<label class="labelText" for="date">{{ __('home.schedule_post_start') }}</label>
												<input type="text" autocomplete="off" value="{{ empty($postInf['schedule_start']) ? date(dateFormat() . ' H:i') : date(dateFormat() . ' H:i' , strtotime($postInf['schedule_start'])) }}" autocomplete="off" class="form-control modalInputs dateInput schedule_start" aria-describedby="sizing-addon1">
												<div class="calendar"><img src="{{ url('img/calendar.png') }}"></div>
												<span class="help-block">{{ __('home.server_time') }} {{ date(dateFormat() . ' H:i') }}</span>
											</div>

											<div class="col-md-6 reset-mar-pad mar-top-20">
												<label class="labelText">{{ __('home.facebook_app') }}</label>
												<select class="schedule_fb_app">
													@foreach($fbApps AS $appInf)
													<option value="{{ $appInf['id'] }}"{{ $postInf['schedule_fb_app_id'] == $appInf['id'] ? ' selected' : '' }}>{{ $appInf['name'] }}</option>
													@endforeach
												</select>
											</div>

										</div>

										<div class="col-md-12  reset-mar-pad mar-top-20">
											<label class="labelText">{{ __('home.auto_pause_after') }}</label>
											<select class="schedule_auto_pause">
												<option value="0">{{ __('home.off') }}</option>
												@for($h = 1; $h <= 100; $h++)
													<option value="{{ $h }}"{{ $postInf['schedule_auto_pause'] == $h ? ' selected' : '' }}>{{ $h }}</option>
												@endfor
											</select>
										</div>

										<div class="mar-top-20">
											<div class="form-group">
												<label for="message">{{ __('home.auto_resume_after') }}</label>
											</div>
										</div>

										<div class="form-group pad-reset">
											<div class="col-md-8 reset-mar-pad">
												<div class="settings-option ">
													<div class="group checkbox-holder">
														<div class="custom-checkbox">
															<input id='id-3' type="radio" class="schedule_auto_resume" name="schedule_auto_resume" value="minutes"{{ $postInf['schedule_auto_resume'] % 60 == 0 ? '' : ' checked' }}>
															<label for="id-3" class='checkbox'></label>
														</div>
														<label class="checkbox-title" for="id-3">{{ __('home.minutes') }}</label>
													</div>

													<div class="group checkbox-holder">
														<div class="custom-checkbox">
															<input id='id-4' type="radio" class="schedule_auto_resume" name="schedule_auto_resume" value="hours"{{ $postInf['schedule_auto_resume'] % 60 == 0 ? ' checked' : '' }}>
															<label for="id-4" class='checkbox'></label>
														</div>
														<label class="checkbox-title" for="id-4">{{ __('home.hours') }}</label>
													</div>
												</div>
											</div>
											<div class="col-md-4 reset-mar-pad">
												<select class="bc-color-gray schedule_auto_resume_n">
													@for($h = 1; $h <= 90; $h++)
														<option value="{{ $h }}"{{ $postInf['schedule_auto_resume'] == $h ? ' selected' : '' }}>{{ $h }}</option>
													@endfor
												</select>
											</div>

											<div class="col-md-6 reset-mar-pad mar-top-20 md-pad-r15">
												<label class="labelText">{{ __('home.repeat_frequency') }}</label>
												<select class="schedule_frequency">
													<option value="0">{{ __('home.once') }}</option>
													<option value="1"{{ $postInf['schedule_frequency'] == 1 ? ' selected' : '' }}>{{ __('home.every_day') }}</option>
													@for($h = 2; $h <= 30; $h++)
														<option value="{{ $h }}"{{ $postInf['schedule_frequency'] == $h ? ' selected' : '' }}>{{ __('home.every_n_days' , ['days' => $h]) }}</option>
													@endfor
													@for($h = 3; $h <= 15; $h++)
														<option value="{{ $h*10 }}"{{ $postInf['schedule_frequency'] == $h ? ' selected' : '' }}>{{ __('home.every_n_days' , ['days' => $h*10]) }}</option>
													@endfor
												</select>
											</div>

											<div class="col-md-6 reset-mar-pad mar-top-20 date-app-control">
												<label class="labelText">{{ __('home.end_on') }}</label>
												<input value="{{ empty($postInf['schedule_end']) ? '' : date(dateFormat() . ' H:i' , strtotime($postInf['schedule_end'])) }}" type="text" class="form-control modalInputs dateInput schedule_end" autocomplete="off" aria-describedby="sizing-addon1">
												<div class="calendar"><img src="{{ url('img/calendar.png') }}"></div>
											</div>

										</div>

										<div class="form-group">
											<button class="orrange-btn-bc" id="saveSchedule" type="button">
												{{ __('home.complete') }}
											</button>
											<button class="orrange-btn-bc" id="complete-post" type="button" style="display: none;">
												{{ __('home.complete') }}
											</button>
										</div>


									</form>

								</div>

							</div>
						</div>
					</div>
					<!-- ==========	end left-content-2 	========= -->

					<div class="clearfix"></div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="right-side">
					<div class="head-section">
						<div class="col-md-12">
							<div class="post-wrapper">
								<img src="{{ url('img/menu/status.png') }}">
								<span>{{ __('home.post_preview') }}</span>
							</div>
						</div>
						<div class="clearfix"></div>
					</div>

					<div class="content-section">
						<div class="col-md-12">
							<div class="user-profil">
								<div class="user-img">
									@if(isset($fbAccInf['fb_account_id']))
										<img src="https://graph.facebook.com/{{ $fbAccInf['fb_account_id'] }}/picture?redirect=1&height=40&width=40&type=normal" class="img-circle" alt="">
									@else
										<img src="{{ url('img/facebookUser.jpg') }}" class="img-circle" alt="">
									@endif
								</div>
								<div class="about-user">
									<p>{{ $fbAccInf['name'] ?? __('home.facebook_user') }}</p>
									<span>{{ __('home.now') }} · {{ __('home.app_name') }}</span>
								</div>
							</div>
							<div style="margin-top: 10px;" class="preview-holder" id="postPreviewId"></div>
							<div class="linkPreview">
								<div class="linkPreview_image"></div>
								<div class="linkPreview_info">
									<div class="linkPreview_info_title"></div>
									<div class="linkPreview_info_description"></div>
									<div class="linkPreview_info_domain"></div>
								</div>
							</div>
							<div class="imagePreview"></div>
							<div class="videoPreview"></div>

							<div style="height: 10px;"> </div>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>

			</div>

			<div class="clearfix visible-md-block"></div>

			<div class="col-md-12">
				<div class="result-content">

					<div class="col-md-6">
						<div class="result-info">
							<p>
								{{ __('home.groups') }} {{ $statistics['group'] }} |
								{{ __('home.pages') }} {{ $statistics['page'] }} |
								{{ __('home.elapsed') }} <span id="elapsedTime">~ 0 {{ __('home.min') }}</span> |
								{{ __('home.time_left') }} <span id="leftTime">00:00</span>
							</p>
						</div>
					</div>
					<div class="col-md-6">
						<div class="result-confirm pull-right">
							<button type="button" id="postPause" class="orrange-btn-bc" style="display: none; width: 90px;"><i class="fa fa-pause"></i> {{ __('home.pause') }} </button>
							<button type="button" id="postResume" class="main-btn-bc" style="display: none; width: 90px;"><i class="fa fa-play"></i> {{ __('home.resume') }}</button>
						</div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>

			<div class="clearfix"></div>

			<div class="col-md-12">
				<div class="settings-content">

					<div class="col-md-2">
						<div class="settings-range">
							<select id="nodeCategoriesSelect" style="width: 120px; display: inline-block;">
								<option value="">{{ __('home.all') }}</option>
								@foreach( $nodeCategories AS $nodeInf )
								<option value="{{ $nodeInf->id }}"{{ $nodeInf->id == $categoryId ? ' selected' : '' }}>{{ $nodeInf->name }}</option>
								@endforeach
							</select>
							<button type="button" id="removeCategory" style="display: inline-block; min-width: 30px !important; width: 40px;" class="red-btn thin-button"><i class="fa fa-trash"></i></button>
						</div>
					</div>
					<div class="col-md-6">
						<div class="settings-confirm ">
							<button class="main-btn-bc thin-button" id="createNewCategory">{{ __('home.create_new_category') }}</button>
							|
							<button class="main-btn-bc thin-button" id="addNode"><i class="fa fa-plus"></i> {{ __('home.add_node') }}</button>
							@if( $categoryId > 0 )
								<button class="red-btn thin-button" id="removeNodesBtn"><i class="fa fa-trash"></i> {{ __('home.remove_node') }}</button>
							@endif
							|
							<button class="red-btn thin-button" id="hideNode"><i class="fa fa-eye-slash"></i> {{ __('home.hide_node') }}</button>
							<button class="main-btn-bc thin-button" id="unHideNode"><i class="fa fa-eye"></i> {{ __('home.unhide_node') }}</button>
						</div>
					</div>

					<div class="col-md-4">
						<div class="settings-option ">
							<div class="group checkbox-holder">
								<input type="checkbox" id="cbtest" class="settingCheckBox" data-type="groups"{{ session('home_settings_show_groups') ? ' checked' : '' }}>
								<label for="cbtest" class="check-box"></label>
								<label class="checkbox-title" for="cbtest">{{ __('home.show_groups') }}</label>
							</div>

							<div class="group checkbox-holder">
								<input type="checkbox" id="cbtest1" class="settingCheckBox" data-type="pages"{{ session('home_settings_show_pages') ? ' checked' : '' }}>
								<label for="cbtest1" class="check-box"></label>
								<label class="checkbox-title" for="cbtest1">{{ __('home.show_pages') }}</label>
							</div>

							<div class="group checkbox-holder">
								<input type="checkbox" id="cbtest2" class="settingCheckBox" data-type="hiddens"{{ session('home_settings_show_hiddens') ? ' checked' : '' }}>
								<label for="cbtest2" class="check-box"></label>
								<label class="checkbox-title" for="cbtest2">{{ __('home.show_hidden_nodes') }}</label>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="clearfix"></div>

			<div class="col-md-12 mar-top-20">
				<div class="table-responsive" style="border: none; background: #FFF;">
					<table class="table table-bordered" id="nodesTable" style="margin-top: 10px;">
						<thead>
							<tr>
								<th style="width: 40px;"></th>
								<th colspan="4">{{ __('home.timeline') }}</th>
								<th>{{ __('home.post_status') }}</th>
							</tr>
							<tr data-id="-1">
								<td style="text-align: center" class="ch-1">
									<input type="checkbox" id="myFbAccountCheckbox"/>
									<label for="myFbAccountCheckbox" class="check-box red"></label>
								</td>
								<td colspan="4"><a href="https://fb.com/{{ $fb_account_id }}" target="_blank">{{ $fb_account_name }}</a></td>
								<td></td>
							</tr>
							<tr>
								<th style="text-align: center" class="ch-1">
									<input type="checkbox" id="nodeCheckboxAll"/>
									<label for="nodeCheckboxAll" class="check-box red" style="margin: 0;"></label>
								</th>
								<th>{{ __('home.node_name') }}</th>
								<th>{{ __('home.node_type') }}</th>
								<th>{{ __('home.likes') }}</th>
								<th>{{ __('home.privacy') }}</th>
								<th>{{ __('home.post_status') }}</th>
							</tr>
						</thead>
						<tbody id="nodesList">
							@foreach( $nodesList AS $nodeInf )
							<tr data-id="{{ $nodeInf->id }}">
								<td class="tdChckbx ch-1" style="text-align: center">
									<input type="checkbox" class="nodeCheckbox" id="nodeCheckbox{{ $nodeInf->id }}" style="height: auto;">
									<label for="nodeCheckbox{{ $nodeInf->id }}" class="check-box red" style="margin: 0;"></label>
								</td>
								<td>
									<a href="https://fb.com/{{ $nodeInf->node_id }}" target="_blank">{{ $nodeInf->name }}</a>
									@if($nodeInf->is_hidden)
										<span class="badge badge-danger hiddenNodeClss">{{ __('home.hidden') }}</span>
									@endif
								</td>
								<td>{{ ucfirst($nodeInf->node_type) }}</td>
								<td>{{ number_format($nodeInf->fan_count) }}</td>
								<td>{{ $nodeInf->node_type == 'group' ? $nodeInf->category : '-' }}</td>
								<td></td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>

			<script>

				var processIntervalTimer,
					processNodes = [],
					processLeftTime = 0,
					processTimerInterval,
					processFullTime = 0,
					_postId = '{{ $postId }}';

				function Timer(callback, delay)
				{
					var timerId, start, remaining = delay;

					this.pause = function() {
						window.clearTimeout(timerId);
						remaining -= new Date() - start;
					};

					this.resume = function() {
						start = new Date();
						window.clearTimeout(timerId);
						timerId = window.setTimeout(callback, remaining);
					};

					this.stop = function()
					{
						window.clearTimeout(timerId);
					}

					this.resume();
				}

				function Interval(callback, delay)
				{
					var paused = false;

					var timerId = window.setInterval(function()
					{
						if( !paused )
						{
							callback();
						}
					}, delay);

					this.pause = function()
					{
						paused = true;
					};

					this.resume = function()
					{
						paused = false;
					};

					this.stop = function()
					{
						window.clearInterval(timerId);
					}
				}

				function getPostInterval()
				{
					var postInterval = parseInt( $("#postIntervalSelect").val() );
					return rand( postInterval , postInterval + parseInt('{{ siteOption('instant_random_interval') }}') );
				}

				function calcProcessLeftTime()
				{
					var sum = 0;
					for(var i in processNodes)
					{
						sum -= -processNodes[i][1];
					}

					return sum;
				}

				$("#sendPostBtn").click(function()
				{
					var postType			=	$("#postTypeTabs .active-tab").attr('data-type'),
						link				=	postType == 'link' ? $("#linkInput").val() : '',
						linkPicture			=	postType == 'link' ? $("#linkPicture").val() : '',
						linkTitle			=	postType == 'link' ? $("#linkTitle").val() : '',
						linkCaption			=	postType == 'link' ? $("#linkCaption").val() : '',
						linkDescription		=	postType == 'link' ? $("#linkDescription").val() : '',
						message				=	$("#messageId")[0].innerText,
						presetId			=	postType == 'status' ? $("#fbPresetsList .selectedFPS").attr('data-id') : '0',
						nodes				=	[],
						images				=	[],
						video				=	postType == 'video' ? $("#videoURL").val() : '',
						productName			=	$("#productName").val(),
						productPrice		=	$("#productPrice").val();

					if( postType == 'status' && message.trim() == '' )
					{
						proApp.alert('{{ __('home.status_text_is_empty') }}');
						return;
					}

					if( postType == 'image' )
					{
						var imageError = false;
						$("#imageLinks .imageUrlInput").each(function()
						{
							var url = $(this).val().trim();
							if( url == '' )
							{
								imageError = true;
							}
							images.push( url );
						});

						if( imageError )
						{
							proApp.alert('{{ __('home.image_url_is_empty') }}');
							return;
						}
					}
					if( postType == 'video' && video == '' )
					{
						proApp.alert('{{ __('home.video_url_is_empty') }}');
						return;
					}

					if( $("#myFbAccountCheckbox").is(":checked") )
					{
						nodes.push(['-1' , getPostInterval()]);
					}

					$("#nodesTable .nodeCheckbox:checked").each(function ()
					{
						nodes.push( [$(this).closest('tr').attr('data-id') , getPostInterval()] );
					});

					if( nodes.length == 0 )
					{
						proApp.alert('{{ __('home.nodes_not_selected') }}');
						return;
					}

					processNodes = nodes;
					processFullTime = calcProcessLeftTime();

					$("#postPause").show();

					processNext({
						'type'				: postType ,
						'message'			: message ,
						'preset_id'			: presetId,
						'link'				: link,
						'link_picture'		: linkPicture,
						'link_title'		: linkTitle,
						'link_caption'		: linkCaption,
						'link_description'	: linkDescription,
						'video'				: video,
						'images'			: images,
						'product_name'		: productName,
						'product_price'		: productPrice
					})
				});

				function processNext(data )
				{
					var nodeInf 		= processNodes.shift(),
						postInterval	=	nodeInf[1],
						nodeId			=	nodeInf[0];

					$("#postPause").attr("disabled" , "disabled");

					$.post('{{ url('ajax/home/send_post') }}' , {
						'post_id'			:	_postId,
						'node_id'			:	nodeId,
						'message'			:	data['message'],
						'preset_id'			:	data['preset_id'],
						'type'				:	data['type'],
						'link'				:	data['link'],
						'link_picture'		:	data['link_picture'],
						'link_title'		:	data['link_title'],
						'link_caption'		:	data['link_caption'],
						'link_description'	:	data['link_description'],
						'video'				:	data['video'],
						'images'			:	data['images'],
						'product_name'		:	data['product_name'],
						'product_price'		:	data['product_price']
					} , function(result)
					{
						if( result['status'] == 'ok' )
						{
							$("#nodesTable tr[data-id='"+nodeId+"']")
								.addClass('successTr')
								.children(':last')
								.addClass('successTr')
								.removeClass('errorTr')
								.html('<i class="fa fa-check"></i> <a href="https://fb.com/'+result['id']+'" target="_blank">{{ __('home.view_post')}}</a>');
						}
						else if( result['status'] == 'error' )
						{
							var errorMsg = typeof result['error_msg'] != 'undefined' ? result['error_msg'] : 'Error';

							$("#nodesTable tr[data-id='"+nodeId+"']")
								.addClass('errorTr')
								.removeClass('successTr')
								.children(':last')
								.html('<i class="fa fa-info-circle"></i> ' + errorMsg);
						}
						$("#postPause").removeAttr("disabled");

						if( processTimerInterval )
							processTimerInterval.stop();

						processLeftTime = calcProcessLeftTime();

						if( processNodes.length > 0 )
						{
							processIntervalTimer = new Timer(function()
							{

								processNext(data);
								$("#elapsedTime").text('~' + parseInt( (processFullTime - processLeftTime) / 60 ) + ' min.');

							} , (postInterval - 2) * 1000);

							processTimerInterval = new Interval(function()
							{
								processLeftTime--;
								processLeftTime = processLeftTime > 0 ? processLeftTime : 0;

								var min = parseInt(processLeftTime / 60),
									sec = processLeftTime % 60;

								$("#leftTime").text(proApp.zeroPad(min) + ':' + proApp.zeroPad(sec));
							}, 995);
						}
						else
						{
							clearProcess();
						}
					});
				}

				$("#postPause").click(function()
				{
					processIntervalTimer.pause();
					processTimerInterval.pause();

					$(this).hide();
					$("#postResume").show();
				});

				$("#postResume").click(function()
				{
					processIntervalTimer.resume();
					processTimerInterval.resume();

					$(this).hide();
					$("#postPause").show();
				});

				function clearProcess()
				{
					$("#leftTime").text('00:00');
					$("#elapsedTime").text('~ 0 {{ __('home.min') }}');

					if( processIntervalTimer )
					{
						processIntervalTimer.stop();
					}

					processIntervalTimer = null;
					processNodes = [];
					processFullTime = 0;
					processLeftTime = 0;

					if( processTimerInterval )
					{
						processTimerInterval.stop();
					}

					processTimerInterval = null;

					$("#postPause").hide();
					$("#postResume").hide();
				}

				function addNewImageLine()
				{
					$("#imageLinks").append( $("#imageLinks>div:eq(0)")[0].outerHTML );
					var newLine = $("#imageLinks>div:eq(-1)");

					newLine.find('input').val('');
					newLine.find('.delImage').show();

					imagePreviewConstruct();
				}

				function imagePreviewConstruct()
				{
					var imagesCount = $("#imageLinks>div").length;

					if( imagesCount == 0 )
					{
						$(".imagePreview").empty();
						return;
					}
					else if( imagesCount == 1 )
					{
						var map = [[ '12|1|5' ]];
					}
					else if( imagesCount == 2 )
					{
						var map = [[ '6|1|2.5' , '6|2|2.5' ]];
					}
					else if( imagesCount == 3 )
					{
						var map = [[ '12|1|2.5' ], [ '6|2|2.5' , '6|3|2.5' ]];
					}
					else if( imagesCount == 4 )
					{
						var map = [[ '6|1|2.5' , '6|2|2.5' ], [ '6|3|2.5' , '6|4|2.5' ]];
					}
					else
					{
						var map = [[ '6|1|3' , '6|2|3' ], [ '4|3|2' , '4|4|2' , '4|5|2|last' ]];
					}

					var html = '',
						maxHeight = 400;

					map.forEach( function( map2 )
					{
						html += '<div class="row">';

						map2.forEach(function( map3 )
						{
							map3 = map3.split('|');

							var colMdSze	= map3[0],
								dataKey		= parseInt(map3[1])-1,
								height		= parseInt( maxHeight / 5 * map3[2] ),
								imageURL	= proApp.spintax( $("#imageLinks .imageUrlInput:eq(" + dataKey + ")").val() ),
								lastAttr	= typeof map3[3] != 'undefined' && imagesCount > 5 ? imagesCount-5 : 0;

							lastAttr = lastAttr > 0 ? ' data-more="+' + lastAttr + '"' : '';

							html +=
								'<div class="col-md-'+colMdSze+' imagePreview_img" style="height: '+height+'px;">' +
									'<div style="background-image: url(' + proApp.htmlspecialchars(imageURL) + ');"'+lastAttr+'></div>' +
								'</div>';
						});

						html += '</div>';
					});

					$(".imagePreview").html(html);
				}

				$("#imageLinks").on('click' , '.delImage' , function()
				{
					$(this).closest('.linkLine').fadeOut(200 , function()
					{
						$(this).remove();
						imagePreviewConstruct();
					});
				});

				$("#savePostBtn").click(function()
				{
					var mn = proApp.modal('Save post' , '<div class="modal-body"><input type="text" placeholder="Post title" class="form-control postTitle"></div><div class="modal-footer"><button type="button" class="btn btn-primary savePostBtn2">Save</button><button type="button" class="btn btn-default" data-dismiss="modal">Close</button> </div>');
					$("#proModal" + mn + " .postTitle").val({!! printForJs($postInf['title']) !!});

					$("#proModal" + mn + ' .savePostBtn2').click(function()
					{
						var postType		=	$("#postTypeTabs .active-tab").attr('data-type'),
							title 			=	$("#proModal" + mn + " .postTitle").val(),
							link			=	postType == 'link' ? $("#linkInput").val() : '',
							linkPicture		=	postType == 'link' ? $("#linkPicture").val() : '',
							linkTitle		=	postType == 'link' ? $("#linkTitle").val() : '',
							linkCaption		=	postType == 'link' ? $("#linkCaption").val() : '',
							linkDescription	=	postType == 'link' ? $("#linkDescription").val() : '',
							message			=	$("#messageId")[0].innerText,
							postInterval	=	$("#postIntervalSelect").val(),
							presetId		=	$("#fbPresetsList .selectedFPS").attr('data-id'),
							images			=	[],
							video			=	postType == 'video' ? $("#videoURL").val() : '',
							productName		=	$("#productName").val(),
							productPrice	=	$("#productPrice").val()
							tBtn 			= $(this);

						if( title == '' )
						{
							proApp.alert('{{ __('home.title_is_empty') }}');
						}

						if( postType == 'image' )
						{
							$("#imageLinks .imageUrlInput").each(function()
							{
								images.push( $(this).val().trim() );
							});
						}

						proApp.ajax('{{ url('ajax/home/savePost') }}' , {
							'post_id'			:	_postId,
							'title'				:	title,
							'message'			:	message.replace(/\r\n|\r|\n/g,"\n"),
							'interval'			:	postInterval,
							'preset_id'			:	presetId,
							'type'				:	postType,
							'link'				:	link,
							'link_picture'		:	linkPicture,
							'link_title'		:	linkTitle,
							'link_caption'		:	linkCaption,
							'link_description'	:	linkDescription,
							'images'			:	images,
							'video'				:	video,
							'product_name'		:	productName,
							'product_price'		:	productPrice
						} , function(result)
						{
							tBtn.attr('disabled' , true);
							_postId = result['post_id'];
							window.history.pushState('page2', 'Saved post', '{{ url('home') }}/' + result['post_id']);
							$("#proModal" + mn).modal('hide');
							@if( $autoScheduleData )
								$("#scheduled-post").click();

							@endif
						});
					});
				});

				/**************************************************/

				$("#nodesList").on('click' , '.tdChckbx' , function(e)
				{
					if( $(e.target).attr('class') == 'tdChckbx' )
					{
						$(this).find('input').click();
					}
				});

				// select all nodes checkbox
				$("#nodeCheckboxAll").click(function()
				{
					if( $(this).is(":checked") )
					{
						$("#nodesList .nodeCheckbox:not(:checked)").prop('checked' , true);
					}
					else
					{
						$("#nodesList .nodeCheckbox:checked").prop('checked' , false);
					}
				});

				// select category
				$("#nodeCategoriesSelect").change(function()
				{
					location.href = '{{ url('home') . ($postId > 0 ? '/' . $postId : '') }}?cat_id=' + $(this).val();
				});

				// remove node category
				$("#removeCategory").click(function()
				{
					var catId = $("#nodeCategoriesSelect").val();

					if( catId > 0 )
					{
						proApp.confirm('{{ __('home.delete?') }}' , '{{ __('home.are_you_sure_to_delete') }}' , function()
						{
							proApp.ajax('{{ url('ajax/deleteCategory') }}' , {'id': catId} , function( )
							{
								location.href = '{{ url('home') }}';
							});
						});
					}
				});

				// remove nodes
				$("#removeNodesBtn").click(function()
				{
					var nodes = [],
						catId = $("#nodeCategoriesSelect").val();

					$("#nodesList .nodeCheckbox:checked").each(function()
					{
						nodes.push( $(this).closest('tr').attr('data-id') );
					});

					if( nodes.length > 0 && catId > 0 )
					{
						proApp.confirm('{{ __('home.delete?') }}' , '{{ __('home.are_you_sure_to_delete') }}' , function()
						{
							proApp.ajax('{{ url('ajax/deleteNodes') }}' , {
								'nodes':	nodes,
								'cat_id':	catId
							} , function( )
							{
								$("#nodesList .nodeCheckbox:checked").each(function()
								{
									$(this).closest('tr').remove();
								});
							});
						});
					}
				});

				// create new category
				$("#createNewCategory").click(function()
				{
					proApp.loadModal('{{ url('ajax/addNodeCategory') }}' , '...');
				});

				// add node to category
				$("#addNode").click(function()
				{
					proApp.loadModal('{{ url('ajax/addNode') }}' , '...');
				});

				// hide / unhide nodes
				$("#hideNode , #unHideNode").click(function()
				{
					var type = $(this).attr('id') == 'hideNode' ? 'hide' : 'unhide',
						nodes = [];

					$("#nodesList .nodeCheckbox:checked").each(function()
					{
						nodes.push( $(this).closest('tr').attr('data-id') );
					});

					if( nodes.length > 0 )
					{
						proApp.ajax('{{ url('ajax/hideUnhideNodes') }}' , {
							'nodes':	nodes,
							'type':		type
						} , function( )
						{
							$("#nodesList .nodeCheckbox:checked").each(function()
							{
								$(this).closest('tr').find('.hiddenNodeClss').remove();
								if(type == 'hide')
								{
									$(this).closest('td').next('td').append('<span class="badge badge-danger hiddenNodeClss">hidden</span>');
								}
							});
						});
					}
				});

				$(".settingCheckBox").change(function()
				{
					var type = $(this).attr('data-type');

					proApp.ajax('{{ url('ajax/changeSettings') }}' , {
						'state':	$(this).is(':checked') ? 1 : 0,
						'type':		type
					} , function( )
					{
						location.reload();
					});
				});

				$("#postTypeTabs>li").click(function()
				{
					var type = $(this).attr('data-type');

					if( type != 'status' )
					{
						$("#fbPresetsList .statusBackgroundImg[data-id=0]").click();
					}

					if( type == 'link' )
					{
						$("#linkInput").trigger('keyup');
					}
					else
					{
						$(".linkPreview").hide();
					}

					if( type != 'image' )
					{
						$(".imagePreview").empty();
					}
					else
					{
						imagePreviewConstruct();
					}

					if( type != 'video' )
					{
						$(".videoPreview").empty();
					}
					else
					{
						$("#videoURL").trigger('keyup');
					}

				});

				$("#fbPresetsList .statusBackgroundImg").click(function()
				{
					$("#fbPresetsList .statusBackgroundImg.selectedFPS").removeClass('selectedFPS');
					$(this).addClass('selectedFPS');
					if( $(this).attr('data-id') > 0 )
					{
						$("#messageId").addClass('withBackground').removeClass('form-control').css('background-image' , 'url({{ url('img/status_backgrounds') }}/'+$(this).attr('data-id')+'_b.jpg)');
					}
					else
					{
						$("#messageId").removeClass('withBackground').addClass('form-control').css('background-image' , '');
					}

					$("#messageId").trigger('keyup');
				});

				$("#messageId").keyup(function()
				{
					var html = $($(this).closest('table')[0].outerHTML);

					html.find('td')
						.removeAttr('contenteditable')
						.removeClass('form-control')
						.removeAttr('id').css('margin-bottom' , '10px');

					html.find('td').html( proApp.spintax( html.find('td').html() ) );

					$("#postPreviewId").html( html );
				});

				$("#linkInput").keyup(function()
				{
					var url = proApp.spintax( $(this).val() );

					if( url == '' || $(this).data('old_url') == url )
					{
						if( url != '' )
						{
							$(".linkPreview").show();
						}
						return;
					}

					$(this).data('old_url' , url);

					$.post( '{{ url('ajax/home/get_url_info') }}' , {'url' : url} , function(result)
					{
						if( result['status'] == 'ok' )
						{
							var data = result['data'];
							$(".linkPreview_info_title").text(data['title']);
							$(".linkPreview_info_description").text(data['description']);
							$(".linkPreview_info_domain").text(data['domain']);
							$(".linkPreview_image").css('background-image' , 'url(' + data['image'] + ')');
							if( $("#postTypeTabs .active-tab").attr('data-type') == 'link' )
							{
								$(".linkPreview").show();
							}
						}
						else
						{
							$(".linkPreview").hide();
						}
					});
				});

				$("#videoURL").keyup(function()
				{
					var url = proApp.spintax( $(this).val() );

					if( url == '' )
					{
						$(".videoPreview").empty();
						return;
					}

					$(".videoPreview").html('<video controls><source src="' + url + '"></video>');
				});


				$("#saveSchedule").click(function()
				{
					var schedule_duration_type		=	$(".schedule_duration:checked").val(),
						schedule_duration			=	$(".schedule_duration_n").val(),
						schedule_start				=	$(".schedule_start").val(),
						schedule_fb_app				=	$(".schedule_fb_app").val(),
						schedule_auto_pause			=	$(".schedule_auto_pause").val(),
						schedule_auto_resume_type	=	$(".schedule_auto_resume:checked").val(),
						schedule_auto_resume		=	$(".schedule_auto_resume_n").val(),
						schedule_frequency			=	$(".schedule_frequency").val(),
						schedule_end				=	$(".schedule_end").val(),
						nodes						=	[];


					if( $("#myFbAccountCheckbox").is(":checked") )
					{
						nodes.push( '-1' );
					}

					$("#nodesTable .nodeCheckbox:checked").each(function ()
					{
						nodes.push( $(this).closest('tr').attr('data-id') );
					});

					if( nodes.length == 0 )
					{
						proApp.alert('{{ __('home.nodes_not_selected') }}');
						return;
					}

					proApp.ajax('{{ url('ajax/home/schedulePost') }}' , {
						'post_id'					:	_postId,
						'schedule_duration'			:	schedule_duration * (schedule_duration_type == 'hours' ? 60 : 1),
						'schedule_start'			:	schedule_start,
						'schedule_fb_app'			:	schedule_fb_app,
						'schedule_auto_pause'		:	schedule_auto_pause,
						'schedule_auto_resume'		:	schedule_auto_resume * (schedule_auto_resume_type == 'hours' ? 60 : 1),
						'schedule_frequency'		:	schedule_frequency,
						'schedule_end'				:	schedule_end,
						'nodes'						:	nodes
					} , function( result )
					{
						$("#complete-post").click();
						@if($autoScheduleData)
						location.reload();
						@endif
					});
				});

				$(".schedule_auto_pause").change(function()
				{
					var val = $(this).val();
					if(val > 0)
					{
						$(".schedule_auto_resume_n").removeAttr('disabled').removeClass('bc-color-gray');
					}
					else
					{
						$(".schedule_auto_resume_n").attr('disabled' , 'disabled').addClass('bc-color-gray');
					}
				}).trigger('change');

				$(".schedule_frequency").change(function()
				{
					var val = $(this).val();
					if(val > 0)
					{
						$(".schedule_end").removeAttr('disabled').removeClass('bc-color-gray');
					}
					else
					{
						$(".schedule_end").attr('disabled' , 'disabled').addClass('bc-color-gray');
					}
				}).trigger('change');

				$("#scheduled-post").click(function()
				{
					if(!(_postId > 0))
					{
						proApp.alert("{{ __('home.save_the_post_first') }}");
						preventDefault();
					}
				});


				var clickedUploadBtn;
				$(document).on('click' , '.uploadBtn' , function()
				{
					clickedUploadBtn = $(this);
					proApp.loadModal('{{ url('ajax/file_browser') }}' , '...' , {});
				});

				$(document).ready( function ()
				{
					@if( !siteOption('lite_mode_nodes_table') )
					$('#nodesTable').DataTable({
						"columnDefs": [ {
							"targets": 0,
							"orderable": false
						} ],
						"order": [[1, 'asc']],
						"language": {
							"lengthMenu":		"{{ __('home.dt.lengthMenu') }}",
							"zeroRecords":		"{{ __('home.dt.zeroRecords') }}",
							"info":				"{{ __('home.dt.info') }}",
							"infoEmpty":		"{{ __('home.dt.infoEmpty') }}",
							"infoFiltered":		"{{ __('home.dt.infoFiltered') }}",
							"emptyTable":		"{{ __('home.dt.emptyTable') }}",
							"search":			"{{ __('home.dt.search') }}",
							"paginate": {
								"first":      "{{ __('home.dt.paginate.first') }}",
								"last":       "{{ __('home.dt.paginate.last') }}",
								"next":       "{{ __('home.dt.paginate.next') }}",
								"previous":   "{{ __('home.dt.paginate.previous') }}"
							}
						},
						"pageLength": 100
					});
					@endif

					$(".dateInput").datetimepicker({
						format: '{{ dateFormat(2) }} HH:mm'
					});

					$(".statusBackgroundImg[data-id='{{ $postInf['preset_id'] }}']").click();

					$("#postTypeTabs>li[data-type='{{ $postInf['post_type'] }}']").click();
				});

				function rand(min,max)
				{
					return Math.floor(Math.random()*(max-min+1)+min);
				}
			</script>

		</div>
	</div>
@endsection

@section('style')
	<link rel="stylesheet" href="{{ url('plugin/datatables/jquery.dataTables.min.css') }}"/>
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

	<link rel="stylesheet" href="{{ url('plugin/datetimepicker/css/bootstrap-datetimepicker.min.css') }}" />
@endsection
@section('script')
	<script src="{{ url('plugin/datatables/jquery.dataTables.min.js') }}"></script>
	<script type="text/javascript" src="{{ url('plugin/emoji/jquery.emojiarea.js') }}"></script>

	<script type="text/javascript" src="{{ url('plugin/moment/moment.js') }}"></script>
	<script type="text/javascript" src="{{ url('plugin/datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>
@endsection
