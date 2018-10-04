@extends('layouts.app')

@section('title')
	{{ __('insights.title') }}
@endsection

@section('content')
	<style>
		.btn-success
		{
			background: #4b82c6;
			color: #FFFFFF;
			border: 0 !important;
			border-radius: 0;
			min-width: 75px;

		}
		.btn-success:hover
		{
			background: #4a77ba !important;
		}

		.btn-default
		{
			background: #D6DEE3;
			border: 0 !important;
			border-radius: 0;
			min-width: 75px;
		}
	</style>
	<div class="container-fluid charts">
		<div class="row bg-white">
			<div class="col-md-6 col-sm-12 col-xs-12 rightBorder">
				<div class="row">
					<h5 class="chartTitle">{{ __('insights.publishing') }}
						<span style="float: right; margin-right: 20px;">
							<button type="button" class="btn btn-sm btn-{{ $reportType == 'monthly' ? 'success' : 'default' }}" onclick="location.href = '{{ url('insights?report_type=monthly') }}'">{{ __('insights.monthly') }}</button>
							<button type="button" class="btn btn-sm btn-{{ $reportType == 'monthly' ? 'default' : 'success' }}" onclick="location.href = '{{ url('insights?report_type=daily') }}'">{{ __('insights.dayly') }}</button>
						</span>
					</h5>
					<br>
				</div>
				<div class="row chart-1">
					<!--CHART-1 BLOCK-->
					<div class="chart-container" style="position: relative; height:50vh;">
						<canvas id="myChart_1"></canvas>
					</div>

					<script type="text/javascript">
						let myChart_1 = document.getElementById('myChart_1').getContext('2d');
						let massPopChart = new Chart(myChart_1, {
							type: 'bar',
							data: {
								labels: {!! json_encode($statistics1['labels']) !!},
								datasets: [{
									label: '{{ __('insights.Success') }}',
									data: {!! json_encode($statistics1['success']) !!},
									backgroundColor: '#9BEAC3'
								},
								{
									label: '{{ __('insights.Fail') }}',
									data: {!! json_encode($statistics1['fails']) !!},
									backgroundColor: '#ED7271'
								}]
							},
							options: {
								maintainAspectRatio: false,
								scales: {
									yAxes: [{
										stacked: true,

									}],
									xAxes: [{
										gridLines: {
											display: false
										},
										stacked: true
									}]
								},
								legend: {
									display: false
								},
								layout: {
									padding: {
										left: 10,
										right: 30,
										bottom: 20,
										top: 0
									}
								},
								tooltips: {
									mode: 'index',
									intersect: false
								}
							}
						});
					</script>
				</div>
			</div>
			<div class="col-md-6 col-sm-12 col-xs-12">
				<ul class="nav nav-tabs" style="margin-top: 10px;">
					<li class="active"><a data-toggle="tab" href="#home">{{ __('insights.my_fb_accounts') }}</a></li>
					<li><a data-toggle="tab" href="#menu1">{{ __('insights.my_data_results') }}</a></li>
				</ul>

				<div class="tab-content">
					<div id="home" class="tab-pane fade in active">

						<div class="row row-pie mar-top-20" style="width: 100%;">
							<div class="chartt">
								<div class="col-md-4 col-sm-5 col-xs-6 marTop">
									<div class="row pad-20"><span class="fc-title grey">{{ __('insights.total_groups') }} {{ $statistics2[1] }}</span></div>
									<div class="row pad-20"><span class="fc-title bluee">{{ __('insights.total_fb_account') }} {{ $statistics2[0] }}</span></div>
									<div class="row pad-20"><span class="fc-title green">{{ __('insights.total_pages') }} {{ $statistics2[2] }}</span></div>
								</div>
								<div class="col-md-8 col-sm-7 col-xs-6 chart-1">
									<!--CHART-2 BLOCK-->
									<div class="chart-container pie-chart">
										<canvas id="myChart"></canvas>
									</div>
									<script type="text/javascript">
										Chart.defaults.doughnutLabels = Chart.helpers.clone(Chart.defaults.doughnut);

										var helpers = Chart.helpers;
										var defaults = Chart.defaults;

										Chart.controllers.doughnutLabels = Chart.controllers.doughnut.extend({
											updateElement: function(arc, index, reset) {
												var _this = this;
												var chart = _this.chart,
													chartArea = chart.chartArea,
													opts = chart.options,
													animationOpts = opts.animation,
													arcOpts = opts.elements.arc,
													centerX = (chartArea.left + chartArea.right) / 2,
													centerY = (chartArea.top + chartArea.bottom) / 2,
													startAngle = opts.rotation, // non reset case handled later
													endAngle = opts.rotation, // non reset case handled later
													dataset = _this.getDataset(),
													circumference = reset && animationOpts.animateRotate ? 0 : arc.hidden ? 0 : _this.calculateCircumference(dataset.data[index]) * (opts.circumference / (2.0 * Math.PI)),
													innerRadius = reset && animationOpts.animateScale ? 0 : _this.innerRadius,
													outerRadius = reset && animationOpts.animateScale ? 0 : _this.outerRadius,
													custom = arc.custom || {},
													valueAtIndexOrDefault = helpers.getValueAtIndexOrDefault;

												helpers.extend(arc, {
													// Utility
													_datasetIndex: _this.index,
													_index: index,

													// Desired view properties
													_model: {
														x: centerX + chart.offsetX,
														y: centerY + chart.offsetY,
														startAngle: startAngle,
														endAngle: endAngle,
														circumference: circumference,
														outerRadius: outerRadius,
														innerRadius: innerRadius,
														label: valueAtIndexOrDefault(dataset.label, index, chart.data.labels[index])
													},

													draw: function() {
														var ctx = this._chart.ctx,
															vm = this._view,
															sA = vm.startAngle,
															eA = vm.endAngle,
															opts = this._chart.config.options;

														var labelPos = this.tooltipPosition();
														var segmentLabel = vm.circumference / opts.circumference * 100;

														ctx.beginPath();

														ctx.arc(vm.x, vm.y, vm.outerRadius, sA, eA);
														ctx.arc(vm.x, vm.y, vm.innerRadius, eA, sA, true);

														ctx.closePath();
														ctx.strokeStyle = vm.borderColor;
														ctx.lineWidth = vm.borderWidth;

														ctx.fillStyle = vm.backgroundColor;

														ctx.fill();
														ctx.lineJoin = 'bevel';

														if (vm.borderWidth) {
															ctx.stroke();
														}

														if (vm.circumference > 0.15) {
															ctx.beginPath();
															ctx.font = helpers.fontString(opts.defaultFontSize, opts.defaultFontStyle, opts.defaultFontFamily);
															ctx.fillStyle = "#fff";
															ctx.textBaseline = "top";
															ctx.textAlign = "center";
															ctx.fontStyle="bold";

															// Round percentage in a way that it always adds up to 100%
															ctx.fillText(segmentLabel.toFixed(0) + "%", labelPos.x, labelPos.y);
														}
													}
												});

												var model = arc._model;
												model.backgroundColor = custom.backgroundColor ? custom.backgroundColor : valueAtIndexOrDefault(dataset.backgroundColor, index, arcOpts.backgroundColor);
												model.hoverBackgroundColor = custom.hoverBackgroundColor ? custom.hoverBackgroundColor : valueAtIndexOrDefault(dataset.hoverBackgroundColor, index, arcOpts.hoverBackgroundColor);
												model.borderWidth = custom.borderWidth ? custom.borderWidth : valueAtIndexOrDefault(dataset.borderWidth, index, arcOpts.borderWidth);
												model.borderColor = custom.borderColor ? custom.borderColor : valueAtIndexOrDefault(dataset.borderColor, index, arcOpts.borderColor);

												// Set correct angles if not resetting
												if (!reset || !animationOpts.animateRotate) {
													if (index === 0) {
														model.startAngle = opts.rotation;
													} else {
														model.startAngle = _this.getMeta().data[index - 1]._model.endAngle;
													}

													model.endAngle = model.startAngle + model.circumference;
												}

												arc.pivot();
											}
										});

										var config = {
											type: 'doughnutLabels',
											data: {
												datasets: [{
													data: {!! json_encode($statistics2) !!},
													backgroundColor: ['#5d8fcc', '#D6DEE3', '#7be4d0'],
													label: 'Dataset 1'
												}],
												labels: [
													"Red",
													"Green",
													"Yellow"
												]
											},
											options: {
												elements: {
													arc: {
														borderWidth: 0
													}
												},
												responsive: true,
												legend: {
													display: false
												},
												title: {
													display: false,
													text: 'Chart.js Doughnut Chart'
												},
												animation: {
													animateScale: true,
													animateRotate: true
												},
												tooltips: {
													enabled: false
												}
											}
										};

										var ctx = document.getElementById("myChart").getContext("2d");
										new Chart(ctx, config);
									</script>

								</div>
							</div>
						</div>

					</div>
					<div id="menu1" class="tab-pane fade">

						<div class="row row-pie mar-top-20" style="width: 100%;">
							<div class="chartt">
								<div class="col-md-4 col-sm-5 col-xs-6 marTop">
									<div class="row pad-20"><span class="fc-title green">{{ __('insights.saved_posts') }} {{ $statistics3[0] }}</span></div>
									<div class="row pad-20"><span class="fc-title bluee">{{ __('insights.schedules') }} {{ $statistics3[1] }}</span></div>
								</div>
								<div class="col-md-8 col-sm-7 col-xs-6 chart-1">
									<!--CHART-2 BLOCK-->
									<div class="chart-container pie-chart" style="width: 100%; height: 100%;">
										<canvas id="myChart2"></canvas>
									</div>
									<script type="text/javascript">
										var config2 = {
											type: 'doughnutLabels',
											data: {
												datasets: [{
													data: {!! json_encode($statistics3) !!},
													backgroundColor: ['#7be4d0', '#5d8fcc'],
													label: 'Dataset 1'
												}],
												labels: [
													"Red",
													"Green",
													"Yellow"
												]
											},
											options: {
												elements: {
													arc: {
														borderWidth: 0
													}
												},
												responsive: true,
												legend: {
													display: false
												},
												title: {
													display: false,
													text: 'Chart.js Doughnut Chart'
												},
												animation: {
													animateScale: true,
													animateRotate: true
												},
												tooltips: {
													enabled: false
												}
											}
										};

										var ctx2 = document.getElementById("myChart2").getContext("2d");
										new Chart(ctx2, config2);
									</script>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
		<div class="row row-3">
			<h5 class="myAccountlabel">{{ __('insights.my_account') }}</h5>
		</div>
		<div class="row bg-white">
			<div class="col-md-6 col-sm-6 col-xs-12 no-padding">
				<ul class="list-group">
					<li class="list-group-item">
						{{ __('insights.posts_per_day') }}: <span class="badge blue">{{ $postCount }} / {{ @Auth::user()->User_role->max_posts_per_day ?? '∞' }}</span>
					</li>
					<li class="list-group-item">
						{{ __('insights.facebook_accounts') }}: <span class="badge blue">{{ $statistics2[0] }} / {{ @Auth::user()->User_role->max_fb_accounts ?? '∞' }}</span>
					</li>
					<li class="list-group-item">
						{{ __('insights.package') }}: <span class="badge blue">{{ @Auth::user()->User_role->name }}</span>
					</li>
					<li class="list-group-item">
						{{ __('insights.account_expiry_in') }}: <span class="badge blue">{{ @Auth::user()->User_role->expire_on ? date('Y-m-d' , strtotime(@Auth::user()->User_role->expire_on)) : 'Never' }}</span>
					</li>
				</ul>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12 no-padding">
				<ul class="list-group">
					<li class="list-group-item">
						{{ __('insights.upload_images') }}: <span class="badge orange">{{ @Auth::user()->User_role->upload_images ? 'Yes' : 'No' }}</span>
					</li>
					<li class="list-group-item">
						{{ __('insights.upload_videos') }}: <span class="badge orange">{{ @Auth::user()->User_role->upload_videos ? 'Yes' : 'No' }}</span>
					</li>
					<li class="list-group-item">
						{{ __('insights.upload_usage') }}: <span class="badge orange">{{ $dirSize > @Auth::user()->User_role->max_upload_mb ? @Auth::user()->User_role->max_upload_mb : $dirSize }} Mb / {{ @Auth::user()->User_role->max_upload_mb ? @Auth::user()->User_role->max_upload_mb . ' Mb' : '∞' }}</span>
					</li>
				</ul>
			</div>
		</div>
	</div>

@endsection

@section('script')
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.min.js"></script>
@endsection
