@extends('layouts.app')

@section('title')
	{{ __('settings.roles.title') }}
@endsection

@section('content')
	@if(!\Illuminate\Support\Facades\Auth::user()->is_admin)
		{!! die() !!}
	@endif
	<div class="tab-white">
		<div >
			<div id="main_section">
				<div id="horizontalTab">
					@include('settings.menu1' , ['menu1' => 'roles'])
					<div class="resp-tabs-container">
						<div>
							<section id="facebook_login">
								<div class="container-fluid" style="margin: 20px;">
									<div class="row">
										<div class="col-md-6">
											<div class="row">
												<div class="line"></div>
												<div class="col-md-12">
													<div class="plus">
														<button type="button" class="btn btn-info button-normalizer" onclick="proApp.loadModal('{{ url('settings/roles/addEdit') }}' , '...' , {'id': 0});">
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
															<th class="head_line">{{ __('settings.roles.Name') }}</th>
															<th class="head_line" style="width: 250px;"></th>
														</tr>
														</thead>
														<tbody>
														@foreach( $roles AS $roleInf )
															<tr>
																<td>{{ $roleInf->name }}</td>
																<td>
																	<button type="button" class="btn btn-sm btn-info" onclick="proApp.loadModal('{{ url('settings/roles/info') }}' , '...' , {'id': '{{ $roleInf->id }}' });">Info</button>
																	<button type="button" class="btn btn-sm btn-primary" onclick="proApp.loadModal('{{ url('settings/roles/addEdit') }}' , '...' , {'id': '{{ $roleInf->id }}' });">Edit</button>
																	@if( !$roleInf->is_for_demo )
																	<button type="button" class="btn btn-sm btn-danger" onclick="proApp.confirm('{{ __('settings.roles.Delete confirmation') }}' , '{{ __('settings.roles.Are you sure you want to delete the role?') }}' , function(){ proApp.ajax('{{ url('settings/roles/delete') }}' , {'id': '{{ $roleInf->id }}'} , function(){ location.reload(); } ) } , '{{ __('settings.roles.Delete') }}' , '{{ __('settings.roles.Cancel') }}' , false);">{{ __('settings.roles.Delete') }}</button>
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
							</section>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
