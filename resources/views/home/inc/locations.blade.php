<?php
// Default Map's values
$loc = [
	'show'       => false,
	'showButton' => false,
];
$map = ['show' => false];

// Get Admin Map's values
if (isset($citiesOptions)) {
	if (isset($citiesOptions['show_cities']) and $citiesOptions['show_cities'] == '1') {
		$loc['show'] = true;
	}
	if (isset($citiesOptions['show_post_btn']) and $citiesOptions['show_post_btn'] == '1') {
		$loc['showButton'] = true;
	}
	
	if (isset($country) and file_exists(config('larapen.core.maps.path') . strtolower($country->get('code')) . '.svg')) {
		if (isset($citiesOptions['show_map']) and $citiesOptions['show_map'] == '1') {
			$map['show'] = true;
		}
	}
}
?>
@if ($loc['show'] || $map['show'])
@include('home.inc.spacer')
<div class="container">
	<div class="row">
		<div class="col-lg-12 page-content">
			<div class="inner-box">
				@if (!$map['show'])
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12">
							<h2 class="title-3 no-padding">
								<i class="icon-location-2"></i>&nbsp;{{ t('Choose a city') }}
							</h2>
						</div>
					</div>
				@endif
				<?php
				$leftClassCol = '';
				$ulCol = 'col-xs-3'; // Cities Columns
				$rightClassCol = '';
				
				if ($loc['show'] && $map['show']) {
					// Display the Cities & the Map
					$leftClassCol = 'col-lg-8 col-md-8 col-sm-12';
					$ulCol = 'col-xs-4';
					$rightClassCol = 'col-lg-3 col-md-3 col-sm-12';
				} else {
					if ($loc['show'] && !$map['show']) {
						// Display the Cities & Hide the Map
						$leftClassCol = 'col-lg-12 col-md-12 col-sm-12';
					}
					if (!$loc['show'] && $map['show']) {
						// Display the Map & Hide the Cities
						$rightClassCol = 'col-lg-12 col-md-12 col-sm-12';
					}
				}
				?>
				@if ($loc['show'])
				<div class="{{ $leftClassCol }} page-content no-margin no-padding">
					@if (isset($cities))
						<div class="relative location-content" style="text-align: center;">
							<div class="row">
								<div class="col-lg-12 col-md-12 col-sm-12">
									<div>
										@if ($loc['show'] && $map['show'])
											<h2 class="title-3">
												<i class="icon-location-2"></i>&nbsp;{{ t('Choose a city or region') }}
											</h2>
										@endif
										<div class="row" style="padding: 0 10px 0 20px;">
											@foreach ($cities as $key => $items)
												<ul class="cat-list {{ $ulCol }} {{ (count($cities) == $key+1) ? 'cat-list-border' : '' }}">
													@foreach ($items as $k => $city)
														<li>
															@if ($city->id == 999999999)
																<a href="#browseAdminCities" id="dropdownMenu1" data-toggle="modal">{{ $city->name }}</a>
															@else
																<a href="{{ lurl(trans('routes.v-search-city',
																[
																	'countryCode' => $country->get('icode'),
																	'city'        => slugify($city->name),
																	'id'          => $city->id
																])) }}">
																	{{ $city->name }}
																</a>
															@endif
														</li>
													@endforeach
												</ul>
											@endforeach
										</div>
									</div>
								</div>
							</div>
							
							@if ($loc['showButton'])
								@if (!\Auth::check() and config('settings.single.guests_can_post_ads') != '1')
									<a class="btn btn-lg btn-add-listing" href="#quickLogin" data-toggle="modal">
										<i class="fa fa-plus-circle"></i> {{ t('Add Listing') }}
									</a>
								@else
									<a class="btn btn-lg btn-add-listing" href="{{ lurl('posts/create') }}" style="padding-left: 30px; padding-right: 30px; text-transform: none;">
										<i class="fa fa-plus-circle"></i> {{ t('Add Listing') }}
									</a>
								@endif
							@endif
	
						</div>
					@endif
				</div>
				@endif
				
				@include('layouts.inc.tools.svgmap')
	
			</div>
		</div>
	</div>
</div>
@endif

@section('modal_location')
	@parent
	@if ($loc['show'] || $map['show'])
		@include('layouts.inc.modal.location')
	@endif
@endsection
