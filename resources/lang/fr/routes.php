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
    'countries' => 'pays',
	
	// Auth
    'login'    => 'connexion',
    'logout'   => 'deconnexion',
    'register' => 'inscription',
	
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
    $lcRoutes['sitemap'] = '{countryCode}/plan-du-site.html';
    $lcRoutes['v-sitemap'] = ':countryCode/plan-du-site.html';

    // Latest Ads
    $lcRoutes['search'] = '{countryCode}/recherche';
    $lcRoutes['t-search'] = 'recherche';
    $lcRoutes['v-search'] = ':countryCode/recherche';

    // Search by Sub-Category
    $lcRoutes['search-subCat'] = '{countryCode}/categorie/{catSlug}/{subCatSlug}';
    $lcRoutes['t-search-subCat'] = 'categorie';
    $lcRoutes['v-search-subCat'] = ':countryCode/categorie/:catSlug/:subCatSlug';

    // Search by Category
    $lcRoutes['search-cat'] = '{countryCode}/categorie/{catSlug}';
    $lcRoutes['t-search-cat'] = 'categorie';
    $lcRoutes['v-search-cat'] = ':countryCode/categorie/:catSlug';

    // Search by Location
    $lcRoutes['search-city'] = '{countryCode}/annonces/{city}/{id}';
    $lcRoutes['t-search-city'] = 'annonces';
    $lcRoutes['v-search-city'] = ':countryCode/annonces/:city/:id';

    // Search by User
    $lcRoutes['search-user'] = '{countryCode}/vendeurs/{id}/annonces';
    $lcRoutes['t-search-user'] = 'vendeurs';
    $lcRoutes['v-search-user'] = ':countryCode/vendeurs/:id/annonces';
	
	// Search by Username
	$lcRoutes['search-username'] = '{countryCode}/profile/{username}';
	$lcRoutes['v-search-username'] = ':countryCode/profile/:username';
	
	// Search by Tag
	$lcRoutes['search-tag'] = '{countryCode}/mot-cle/{tag}';
	$lcRoutes['t-search-tag'] = 'mot-cle';
	$lcRoutes['v-search-tag'] = ':countryCode/mot-cle/:tag';
} else {
    // Sitemap
    $lcRoutes['sitemap'] = 'plan-du-site.html';
    $lcRoutes['v-sitemap'] = 'plan-du-site.html';

    // Latest Ads
    $lcRoutes['search'] = 'recherche';
    $lcRoutes['t-search'] = 'recherche';
    $lcRoutes['v-search'] = 'recherche';

    // Search by Sub-Category
    $lcRoutes['search-subCat'] = 'categorie/{catSlug}/{subCatSlug}';
    $lcRoutes['t-search-subCat'] = 'categorie';
    $lcRoutes['v-search-subCat'] = 'categorie/:catSlug/:subCatSlug';

    // Search by Category
    $lcRoutes['search-cat'] = 'categorie/{catSlug}';
    $lcRoutes['t-search-cat'] = 'categorie';
    $lcRoutes['v-search-cat'] = 'categorie/:catSlug';

    // Search by Location
    $lcRoutes['search-city'] = 'annonces/{city}/{id}';
    $lcRoutes['t-search-city'] = 'annonces';
    $lcRoutes['v-search-city'] = 'annonces/:city/:id';

    // Search by User
    $lcRoutes['search-user'] = 'vendeurs/{id}/annonces';
    $lcRoutes['t-search-user'] = 'vendeurs';
    $lcRoutes['v-search-user'] = 'vendeurs/:id/annonces';
	
	// Search by Username
	$lcRoutes['search-username'] = 'profile/{username}';
	$lcRoutes['v-search-username'] = 'profile/:username';
	
	// Search by Tag
	$lcRoutes['search-tag'] = 'mot-cle/{tag}';
	$lcRoutes['t-search-tag'] = 'mot-cle';
	$lcRoutes['v-search-tag'] = 'mot-cle/:tag';
}

// Posts Permalink Collection
$vPost = config('larapen.core.permalink.posts');

// Posts Permalink
if (isset($vPost[config('settings.seo.posts_permalink', '{slug}/{id}')])) {
	$lcRoutes['post'] = config('settings.seo.posts_permalink', '{slug}/{id}') . config('settings.seo.posts_permalink_ext', '');
	$lcRoutes['v-post'] = $vPost[config('settings.seo.posts_permalink', '{slug}/{id}')] . config('settings.seo.posts_permalink_ext', '');
}

return $lcRoutes;
