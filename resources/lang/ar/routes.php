<?php
/**
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
 */

$lcRoutes = [
    /*
    |--------------------------------------------------------------------------
    | Routes Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the global website.
    |
    */
	
	// Countries
    'countries' => 'countries',
	
	// Auth
    'login'    => 'login',
    'logout'   => 'logout',
    'register' => 'register',
	
	// Post
	'post'   => '{slug}/{id}.html',
	'v-post' => ':slug/:id.html',
	
	// Page
    'page'   => 'page/{slug}.html',
    't-page' => 'page',
    'v-page' => 'page/:slug.html',
	
	// Contact
    'contact' => 'contact.html',
];

if (config('larapen.core.multiCountriesWebsite')) {
    // Sitemap
    $lcRoutes['sitemap'] = '{countryCode}/sitemap.html';
    $lcRoutes['v-sitemap'] = ':countryCode/sitemap.html';

    // Latest Ads
    $lcRoutes['search'] = '{countryCode}/search';
    $lcRoutes['t-search'] = 'search';
    $lcRoutes['v-search'] = ':countryCode/search';

    // Search by Sub-Category
    $lcRoutes['search-subCat'] = '{countryCode}/category/{catSlug}/{subCatSlug}';
    $lcRoutes['t-search-subCat'] = 'category';
    $lcRoutes['v-search-subCat'] = ':countryCode/category/:catSlug/:subCatSlug';

    // Search by Category
    $lcRoutes['search-cat'] = '{countryCode}/category/{catSlug}';
    $lcRoutes['t-search-cat'] = 'category';
    $lcRoutes['v-search-cat'] = ':countryCode/category/:catSlug';

    // Search by Location
    $lcRoutes['search-city'] = '{countryCode}/free-ads/{city}/{id}';
    $lcRoutes['t-search-city'] = 'free-ads';
    $lcRoutes['v-search-city'] = ':countryCode/free-ads/:city/:id';

    // Search by User
    $lcRoutes['search-user'] = '{countryCode}/users/{id}/ads';
    $lcRoutes['t-search-user'] = 'users';
    $lcRoutes['v-search-user'] = ':countryCode/users/:id/ads';

    // Search by Username
    $lcRoutes['search-username'] = '{countryCode}/profile/{username}';
    $lcRoutes['v-search-username'] = ':countryCode/profile/:username';
	
	// Search by Tag
	$lcRoutes['search-tag'] = '{countryCode}/tag/{tag}';
	$lcRoutes['t-search-tag'] = 'tag';
	$lcRoutes['v-search-tag'] = ':countryCode/tag/:tag';
} else {
    // Sitemap
    $lcRoutes['sitemap'] = 'sitemap.html';
    $lcRoutes['v-sitemap'] = 'sitemap.html';

    // Latest Ads
    $lcRoutes['search'] = 'search';
    $lcRoutes['t-search'] = 'search';
    $lcRoutes['v-search'] = 'search';

    // Search by Sub-Category
    $lcRoutes['search-subCat'] = 'category/{catSlug}/{subCatSlug}';
    $lcRoutes['t-search-subCat'] = 'category';
    $lcRoutes['v-search-subCat'] = 'category/:catSlug/:subCatSlug';

    // Search by Category
    $lcRoutes['search-cat'] = 'category/{catSlug}';
    $lcRoutes['t-search-cat'] = 'category';
    $lcRoutes['v-search-cat'] = 'category/:catSlug';

    // Search by Location
    $lcRoutes['search-city'] = 'free-ads/{city}/{id}';
    $lcRoutes['t-search-city'] = 'free-ads';
    $lcRoutes['v-search-city'] = 'free-ads/:city/:id';

    // Search by User
    $lcRoutes['search-user'] = 'users/{id}/ads';
    $lcRoutes['t-search-user'] = 'users';
    $lcRoutes['v-search-user'] = 'users/:id/ads';
	
	// Search by Username
    $lcRoutes['search-username'] = 'profile/{username}';
    $lcRoutes['v-search-username'] = 'profile/:username';
	
	// Search by Tag
	$lcRoutes['search-tag'] = 'tag/{tag}';
	$lcRoutes['t-search-tag'] = 'tag';
	$lcRoutes['v-search-tag'] = 'tag/:tag';
}

// Posts Permalink Collection
$vPost = config('larapen.core.permalink.posts');

// Posts Permalink
if (isset($vPost[config('settings.seo.posts_permalink', '{slug}/{id}')])) {
	$lcRoutes['post'] = config('settings.seo.posts_permalink', '{slug}/{id}') . config('settings.seo.posts_permalink_ext', '');
	$lcRoutes['v-post'] = $vPost[config('settings.seo.posts_permalink', '{slug}/{id}')] . config('settings.seo.posts_permalink_ext', '');
}

return $lcRoutes;
