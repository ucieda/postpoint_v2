@extends('layouts.app')

@section('title')
    {{ __('settings.profile.title') }}
@endsection

@section('content')
    <div class="tab-white">
        <div >
            <div id="main_section">
                <div id="horizontalTab">
                    @include('settings.menu1' , ['menu1' => 'general'])
                    <div class="resp-tabs-container">
                        <div>
                            <section id="general_setings">
                                <div class="container margin-normalizer">
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
                                        {{ Form::open(array('url' => 'settings/general/save')) }}

                                        <div class="col-lg-12 gen">
                                            <label for="">{{ __('settings.general.Records per page') }}</label>
                                            {!! Form::text('records_per_page' , $userInf->records_per_page) !!}
                                            <label for="">{{ __('settings.general.Timezone | Current') }} <time>{{ date(dateFormat().' H:i') }}</time></label>
                                            {{ Form::select('timezone' , \App\Http\Controllers\SettingsController::timezones() , $userInf->timezone , ['class' => 'select']) }}
                                            <label for="">{{ __('settings.general.Language') }}</label>
                                         {{ Form::select('language' , $languages , $userInf->language_id , ['class' => 'select']) }}
                                            <div class="button-div">
                                                <button class="button-save"> {{ __('settings.general.Save changes') }}</button>
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
