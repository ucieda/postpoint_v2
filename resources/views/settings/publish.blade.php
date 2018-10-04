@extends('layouts.app')

@section('title')
	{{ __('settings.publish.title') }}
@endsection

@section('content')
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'publish'])
					<div class="resp-tabs-container">
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
                                        {{ Form::open(array('url' => 'settings/publish/save')) }}

                                        <div class="col-md-12 mar-top-20">
                                            <div class="radio_button">
                                                <div class="group checkbox-holder">
                                                    {{ Form::checkbox('show_open_groups_only', null , Auth::user()->show_open_groups_only , ['id' => 'show_open_groups_only']) }}
                                                    <label for="show_open_groups_only" class="check-box"></label>
                                                    <label class="text-label-txt" for="show_open_groups_only">{{ __('settings.publish.Show Open groups only') }}</label>
                                                </div>
                                                <div class="sec checkbox-holder">
                                                    {{ Form::checkbox('unique_post', null , Auth::user()->unique_post , ['id' => 'unique_post']) }}
                                                    <label for="unique_post" class="check-box"></label>
                                                    <label class="text-label-txt" for="unique_post">{{ __('settings.publish.Unique post') }}</label>
                                                </div>
												<div class="sec checkbox-holder">
													{{ Form::checkbox('unique_link', null , Auth::user()->unique_link , ['id' => 'unique_link']) }}
													<label for="unique_link" class="check-box"></label>
													<label class="text-label-txt" for="unique_link">{{ __('settings.publish.Unique link') }}</label>
												</div>
												<div class="sec checkbox-holder"{!! !siteOption('enable_link_customization') ? ' style="display: none;"' : '' !!}>
													{{ Form::checkbox('link_customization', null , Auth::user()->link_customization , ['id' => 'link_customization']) }}
													<label for="link_customization" class="check-box"></label>
													<label class="text-label-txt" for="link_customization">{{ __('settings.publish.Enable link customization') }}</label>
												</div>
                                            </div>
                                            <div class="import">
                                                <label for="">{{ __('settings.publish.Post interval (In seconds)') }}</label>
                                                {{ Form::number('post_interval' , Auth::user()->post_interval) }}

                                                <label for="">{{ __('settings.publish.Facebook app') }}</label>
                                                {{ Form::select('fb_app_id' , $apps , $defaultApp , ['class'=>'select']) }}

                                                <div class="button-div">
                                                    <button class="button-save">{{ __('settings.publish.Save changes') }}</button>
                                                </div>
                                            </div>
                                            <br>
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
