{{--
 * LaraClassified - Geo Classified Ads CMS
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
--}}
<?php
	$fullUrl = url(\Illuminate\Support\Facades\Request::getRequestUri());
	$tmpExplode = explode('?', $fullUrl);
	$fullUrlNoParams = current($tmpExplode);
?>
@extends('layouts.master')

@section('search')
	@parent
	@include('search.inc.form')
@endsection

@section('content')
	<div class="main-container">
		
		@include('search.inc.breadcrumbs')
		@include('search.inc.categories')
		<?php if (\App\Models\Advertising::where('slug', 'top')->count() > 0): ?>
			@include('layouts.inc.advertising.top', ['paddingTopExists' => true])
		<?php
			$paddingTopExists = false;
		else:
			if (isset($paddingTopExists) and $paddingTopExists) {
				$paddingTopExists = false;
			}
		endif;
		?>
		@include('common.spacer')
		
		<div class="container">
			<div class="row">

				<!-- Sidebar -->
                @if (config('settings.listing.left_sidebar'))
                    @include('search.inc.sidebar')
                    <?php $contentColSm = 'col-sm-9'; ?>
                @else
                    <?php $contentColSm = 'col-sm-12'; ?>
                @endif

				<!-- Content -->
				<div class="{{ $contentColSm }} page-content col-thin-left">
					<div class="category-list">
						<div class="tab-box">

							<!-- Nav tabs -->
							<ul id="postType" class="nav nav-tabs add-tabs" role="tablist">
                                <?php
                                $liClass = '';
                                $spanClass = 'alert-danger';
                                if (!Request::filled('type') or Request::get('type') == '') {
                                    $liClass = 'class="active"';
                                    $spanClass = 'progress-bar-danger';
                                }
                                ?>
								<li {!! $liClass !!}>
									<a href="{!! qsurl($fullUrlNoParams, Request::except(['page', 'type'])) !!}" role="tab" data-toggle="tab">
										{{ t('All Ads') }} <span class="badge {!! $spanClass !!}">{{ $count->get('all') }}</span>
									</a>
								</li>
                                @if (!empty($postTypes))
                                    @foreach ($postTypes as $postType)
                                        <?php
                                            $postTypeUrl = qsurl($fullUrlNoParams, array_merge(Request::except(['page']), ['type' => $postType->tid]));
                                            $postTypeCount = ($count->has($postType->tid)) ? $count->get($postType->tid) : 0;
                                        ?>
                                        @if (Request::filled('type') && Request::get('type') == $postType->tid)
                                            <li class="active">
                                                <a href="{!! $postTypeUrl !!}" role="tab" data-toggle="tab">
                                                    {{ $postType->name }}
                                                    <span class="badge progress-bar-danger">
                                                        {{ $postTypeCount }}
                                                    </span>
                                                </a>
                                            </li>
                                        @else
                                            <li>
                                                <a href="{!! $postTypeUrl !!}" role="tab" data-toggle="tab">
                                                    {{ $postType->name }}
                                                    <span class="badge alert-danger">
                                                        {{ $postTypeCount }}
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                @endif
							</ul>
						
							@if (config('settings.listing.left_sidebar'))
								<!-- Mobile Filter bar -->
								<div class="mobile-filter-bar col-lg-12">
									<ul class="list-unstyled list-inline no-margin no-padding">
										<li class="filter-toggle">
											<a class="">
												<i class="icon-th-list"></i> {{ t('Filters') }}
											</a>
										</li>
										<li>
											<div class="dropdown">
												<a data-toggle="dropdown" class="dropdown-toggle"><i class="caret "></i>{{ t('Sort by') }}</a>
												<ul class="dropdown-menu">
													<li>
														<a href="{!! qsurl($fullUrlNoParams, Request::except(['orderBy', 'distance'])) !!}" rel="nofollow">
															{{ t('Sort by') }}
														</a>
													</li>
													<li>
														<a href="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'priceAsc'])) !!}" rel="nofollow">
															{{ t('Price : Low to High') }}
														</a>
													</li>
													<li>
														<a href="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'priceDesc'])) !!}" rel="nofollow">
															{{ t('Price : High to Low') }}
														</a>
													</li>
													<li>
														<a href="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'relevance'])) !!}" rel="nofollow">
															{{ t('Relevance') }}
														</a>
													</li>
													<li>
														<a href="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'date'])) !!}" rel="nofollow">
															{{ t('Date') }}
														</a>
													</li>
													@if (isset($isCitySearch) and $isCitySearch)
														@for($iDist = 0; $iDist <= config('settings.listing.search_distance_max', 500); $iDist += config('settings.listing.search_distance_interval', 50))
															<li>
																<a href="{!! qsurl($fullUrlNoParams, array_merge(Request::except('distance'), ['distance' => $iDist])) !!}" rel="nofollow">
																	{{ t('Around :distance :unit', ['distance' => $iDist, 'unit' => unitOfLength()]) }}
																</a>
															</li>
														@endfor
													@endif
												</ul>
											
											</div>
										</li>
									</ul>
								</div>
								<div class="menu-overly-mask"></div>
								<!-- Mobile Filter bar End-->
								
								<?php
									$tabFilterHideXs = 'hide-xs';
									$listingFilterHiddenXs = 'hidden-xs';
								?>
							@else
								<?php
									$tabFilterHideXs = '';
									$listingFilterHiddenXs = '';
								?>
							@endif
							
							
							<div class="tab-filter {{ $tabFilterHideXs }}">
								<select id="orderBy" class="selecter" data-style="btn-select" data-width="auto">
									<option value="{!! qsurl($fullUrlNoParams, Request::except(['orderBy', 'distance'])) !!}">{{ t('Sort by') }}</option>
									<option{{ (Request::get('orderBy')=='priceAsc') ? ' selected="selected"' : '' }}
											value="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'priceAsc'])) !!}">
										{{ t('Price : Low to High') }}
									</option>
									<option{{ (Request::get('orderBy')=='priceDesc') ? ' selected="selected"' : '' }}
											value="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'priceDesc'])) !!}">
										{{ t('Price : High to Low') }}
									</option>
									<option{{ (Request::get('orderBy')=='relevance') ? ' selected="selected"' : '' }}
											value="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'relevance'])) !!}">
										{{ t('Relevance') }}
									</option>
									<option{{ (Request::get('orderBy')=='date') ? ' selected="selected"' : '' }}
											value="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'date'])) !!}">
										{{ t('Date') }}
									</option>
									@if (isset($isCitySearch) and $isCitySearch)
										@for($iDist = 0; $iDist <= config('settings.listing.search_distance_max', 500); $iDist += config('settings.listing.search_distance_interval', 50))
											<option{{ (Request::get('distance', config('settings.listing.search_distance_default', 100))==$iDist) ? ' selected="selected"' : '' }}
													value="{!! qsurl($fullUrlNoParams, array_merge(Request::except('distance'), ['distance' => $iDist])) !!}">
												{{ t('Around :distance :unit', ['distance' => $iDist, 'unit' => unitOfLength()]) }}
											</option>
										@endfor
									@endif
								</select>
							</div>

						</div>

						<div class="listing-filter {{ $listingFilterHiddenXs }}">
							<div class="pull-left col-sm-10 col-xs-12">
								<div class="breadcrumb-list text-center-xs">
									{!! (isset($htmlTitle)) ? $htmlTitle : '' !!}
								</div>
                                <div style="clear:both;"></div>
							</div>
                            
							@if ($paginator->getCollection()->count() > 0)
								<div class="pull-right col-xs-2 text-right listing-view-action">
									<span class="list-view"><i class="icon-th"></i></span>
									<span class="compact-view"><i class="icon-th-list"></i></span>
									<span class="grid-view active"><i class="icon-th-large"></i></span>
								</div>
							@endif

							<div style="clear:both"></div>
						</div>

						<div class="adds-wrapper{{ ($contentColSm == 'col-sm-12') ? ' noSideBar' : '' }}">
							@include('search.inc.posts')
						</div>

						<div class="tab-box save-search-bar text-center">
							@if (Request::filled('q') and Request::get('q') != '' and $count->get('all') > 0)
								<a name="{!! qsurl($fullUrlNoParams, Request::except(['_token', 'location'])) !!}" id="saveSearch"
								   count="{{ $count->get('all') }}">
									<i class="icon-star-empty"></i> {{ t('Save Search') }}
								</a>
							@else
								<a href="#"> &nbsp; </a>
							@endif
						</div>
					</div>

					<div class="pagination-bar text-center">
						{!! $paginator->appends(request()->query())->render() !!}
					</div>

					<div class="post-promo text-center" style="margin-bottom: 30px;">
						<h2> {{ t('Do have anything to sell or rent?') }} </h2>
						<h5>{{ t('Sell your products and services online FOR FREE. It\'s easier than you think !') }}</h5>
						@if (!auth()->check() and config('settings.single.guests_can_post_ads') != '1')
							<a href="#quickLogin" class="btn btn-border btn-post btn-add-listing" data-toggle="modal">{{ t('Start Now!') }}</a>
						@else
							<a href="{{ lurl('posts/create') }}" class="btn btn-border btn-post btn-add-listing">{{ t('Start Now!') }}</a>
						@endif
					</div>

				</div>
				
				<div style="clear:both;"></div>

				<!-- Advertising -->
				@include('layouts.inc.advertising.bottom')

			</div>
		</div>
	</div>
@endsection

@section('modal_location')
	@include('layouts.inc.modal.location')
@endsection

@section('after_scripts')
	<script>
		$(document).ready(function () {
			$('#postType a').click(function (e) {
				e.preventDefault();
				var goToUrl = $(this).attr('href');
				redirect(goToUrl);
			});
			$('#orderBy').change(function () {
				var goToUrl = $(this).val();
				redirect(goToUrl);
			});
		});
	</script>
@endsection
