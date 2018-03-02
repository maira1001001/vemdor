<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


/*
|--------------------------------------------------------------------------
| Upgrading
|--------------------------------------------------------------------------
|
| The upgrading process routes
|
*/
Route::group(['middleware' => ['web'], 'namespace'  => 'App\Http\Controllers'], function() {
	Route::get('upgrade', 'UpgradeController@version');
});


/*
|--------------------------------------------------------------------------
| Installation
|--------------------------------------------------------------------------
|
| The installation process routes
|
*/
Route::group([
    'middleware' => ['web', 'installChecker'],
    'namespace'  => 'App\Http\Controllers',
], function() {
    Route::get('install', 'InstallController@starting');
    Route::get('install/site_info', 'InstallController@siteInfo');
    Route::post('install/site_info', 'InstallController@siteInfo');
    Route::get('install/system_compatibility', 'InstallController@systemCompatibility');
    Route::get('install/database', 'InstallController@database');
    Route::post('install/database', 'InstallController@database');
    Route::get('install/database_import', 'InstallController@databaseImport');
    Route::get('install/cron_jobs', 'InstallController@cronJobs');
    Route::get('install/finish', 'InstallController@finish');
});


/*
|--------------------------------------------------------------------------
| Back-end
|--------------------------------------------------------------------------
|
| The admin panel routes
|
*/
Route::group([
    'middleware' => ['admin', 'bannedUser', 'installChecker', 'preventBackHistory'],
    'prefix'     => config('larapen.admin.route_prefix', 'admin'),
    'namespace'  => 'App\Http\Controllers\Admin',
], function() {
    // CRUD
    CRUD::resource('advertising', 'AdvertisingController');
    CRUD::resource('blacklist', 'BlacklistController');
    CRUD::resource('category', 'CategoryController');
    CRUD::resource('category/{catId}/sub_category', 'SubCategoryController');
    CRUD::resource('category/{catId}/field', 'CategoryFieldController');
    CRUD::resource('city', 'CityController');
    CRUD::resource('country', 'CountryController');
    CRUD::resource('country/{countryCode}/city', 'CityController');
    CRUD::resource('country/{countryCode}/loc_admin1', 'SubAdmin1Controller');
    CRUD::resource('currency', 'CurrencyController');
    CRUD::resource('field', 'FieldController');
    CRUD::resource('field/{cfId}/option', 'FieldOptionController');
    CRUD::resource('field/{cfId}/category', 'CategoryFieldController');
    CRUD::resource('gender', 'GenderController');
    CRUD::resource('homepage_section', 'HomeSectionController');
    CRUD::resource('loc_admin1/{admin1Code}/city', 'CityController');
    CRUD::resource('loc_admin1/{admin1Code}/loc_admin2', 'SubAdmin2Controller');
    CRUD::resource('loc_admin2/{admin2Code}/city', 'CityController');
    CRUD::resource('meta_tag', 'MetaTagController');
    CRUD::resource('package', 'PackageController');
    CRUD::resource('page', 'PageController');
    CRUD::resource('payment', 'PaymentController');
    CRUD::resource('payment_method', 'PaymentMethodController');
    CRUD::resource('picture', 'PictureController');
    CRUD::resource('post', 'PostController');
    CRUD::resource('p_type', 'PostTypeController');
    CRUD::resource('report_type', 'ReportTypeController');
    CRUD::resource('time_zone', 'TimeZoneController');
    CRUD::resource('user', 'UserController');

    // Others
    Route::get('account', 'UserController@account');
    Route::post('ajax/{table}/{field}', 'AjaxController@saveAjaxRequest');
    Route::get('clear_cache', 'CacheController@clear');
	Route::get('test_cron', 'TestCronController@run');
    
    // Re-send Email or Phone verification message
    Route::get('verify/user/{id}/resend/email', 'UserController@reSendVerificationEmail');
    Route::get('verify/user/{id}/resend/sms', 'UserController@reSendVerificationSms');
    Route::get('verify/post/{id}/resend/email', 'PostController@reSendVerificationEmail');
    Route::get('verify/post/{id}/resend/sms', 'PostController@reSendVerificationSms');

    // Plugins
    Route::get('plugin', 'PluginController@index');
    Route::get('plugin/{plugin}/install', 'PluginController@install');
    Route::get('plugin/{plugin}/uninstall', 'PluginController@uninstall');
    Route::get('plugin/{plugin}/delete', 'PluginController@delete');
});


/*
|--------------------------------------------------------------------------
| Front-end
|--------------------------------------------------------------------------
|
| The not translated front-end routes
|
*/
Route::group([
    'middleware' => ['web', 'installChecker'],
    'namespace'  => 'App\Http\Controllers',
], function($router) {
    // AJAX
    Route::group(['prefix' => 'ajax'], function($router) {
        Route::get('countries/{countryCode}/admins/{adminType}', 'Ajax\LocationController@getAdmins');
        Route::get('countries/{countryCode}/admins/{adminType}/{adminCode}/cities', 'Ajax\LocationController@getCities');
        Route::get('countries/{countryCode}/cities/{id}', 'Ajax\LocationController@getSelectedCity');
        Route::post('countries/{countryCode}/cities/autocomplete', 'Ajax\LocationController@searchedCities');
        Route::post('countries/{countryCode}/admin1/cities', 'Ajax\LocationController@getAdmin1WithCities');
        Route::post('category/sub-categories', 'Ajax\CategoryController@getSubCategories');
		Route::post('category/custom-fields', 'Ajax\CategoryController@getCustomFields');
        Route::post('save/post', 'Ajax\PostController@savePost');
        Route::post('save/search', 'Ajax\PostController@saveSearch');
        Route::post('post/phone', 'Ajax\PostController@getPhone');
		Route::post('post/pictures/reorder', 'Ajax\PostController@picturesReorder');
    });

    // SEO
    Route::get('robots.txt', 'RobotsController@index');
    Route::get('sitemaps.xml', 'SitemapsController@index');
	
    // Impersonate (As admin user, login as an another user)
	Route::group(['middleware' => 'auth'], function ($router) {
		Route::impersonate();
	});
});


/*
|--------------------------------------------------------------------------
| Front-end
|--------------------------------------------------------------------------
|
| The translated front-end routes
|
*/
Route::group([
    'prefix'     => LaravelLocalization::setLocale(),
    'middleware' => ['locale'],
    'namespace'  => 'App\Http\Controllers',
], function($router) {
    Route::group(['middleware' => ['web', 'installChecker']], function($router) {
        // HOMEPAGE
        Route::group(['middleware' => ['httpCache:yes']], function() {
            Route::get('/', 'HomeController@index');
            Route::get(LaravelLocalization::transRoute('routes.countries'), 'CountriesController@index');
        });


        // AUTH
        Route::group(['middleware' => ['guest', 'preventBackHistory']], function() {
            // Registration Routes...
            Route::get(LaravelLocalization::transRoute('routes.register'), 'Auth\RegisterController@showRegistrationForm');
            Route::post(LaravelLocalization::transRoute('routes.register'), 'Auth\RegisterController@register');
            Route::get('register/finish', 'Auth\RegisterController@finish');

            // Authentication Routes...
            Route::get(LaravelLocalization::transRoute('routes.login'), 'Auth\LoginController@showLoginForm');
            Route::post(LaravelLocalization::transRoute('routes.login'), 'Auth\LoginController@login');
			
            // Forgot Password Routes...
            Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
            Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
	
			// Reset Password using Token
			Route::get('password/token', 'Auth\ForgotPasswordController@showTokenRequestForm');
			Route::post('password/token', 'Auth\ForgotPasswordController@sendResetToken');
	
			// Reset Password using Link (Core Routes...)
            Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
            Route::post('password/reset', 'Auth\ResetPasswordController@reset');

            // Social Authentication
            Route::get('auth/facebook', 'Auth\SocialController@redirectToProvider');
            Route::get('auth/facebook/callback', 'Auth\SocialController@handleProviderCallback');
            Route::get('auth/google', 'Auth\SocialController@redirectToProvider');
            Route::get('auth/google/callback', 'Auth\SocialController@handleProviderCallback');
            Route::get('auth/twitter', 'Auth\SocialController@redirectToProvider');
            Route::get('auth/twitter/callback', 'Auth\SocialController@handleProviderCallback');
        });

        // Email Address or Phone Number verification
		$router->pattern('field', 'email|phone');
        Route::get('verify/user/{id}/resend/email', 'Auth\RegisterController@reSendVerificationEmail');
		Route::get('verify/user/{id}/resend/sms', 'Auth\RegisterController@reSendVerificationSms');
        Route::get('verify/user/{field}/{token?}', 'Auth\RegisterController@verification');
        Route::post('verify/user/{field}/{token?}', 'Auth\RegisterController@verification');

        // User Logout
        Route::get(LaravelLocalization::transRoute('routes.logout'), 'Auth\LoginController@logout');


        // POSTS
        Route::group(['namespace' => 'Post'], function($router) {
            $router->pattern('id', '[0-9]+');
			$router->pattern('slug', '.*');
			
            Route::get('posts/create/{tmpToken?}', 'CreateController@getForm');
            Route::post('posts/create', 'CreateController@postForm');
            Route::put('posts/create/{tmpToken}', 'CreateController@postForm');
            Route::get('posts/create/{tmpToken}/photos', 'PhotoController@getForm');
            Route::post('posts/create/{tmpToken}/photos', 'PhotoController@postForm');
            Route::post('posts/create/{tmpToken}/photos/{id}/delete', 'PhotoController@delete');
            Route::get('posts/create/{tmpToken}/packages', 'PackageController@getForm');
            Route::post('posts/create/{tmpToken}/packages', 'PackageController@postForm');
            Route::get('posts/create/{tmpToken}/finish', 'CreateController@finish');
    
            // Payment Gateway Success & Cancel
            Route::get('posts/create/{tmpToken}/payment/success', 'PackageController@paymentConfirmation');
            Route::get('posts/create/{tmpToken}/payment/cancel', 'PackageController@paymentCancel');
    
            // Email Address or Phone Number verification
            $router->pattern('field', 'email|phone');
            Route::get('verify/post/{id}/resend/email', 'CreateController@reSendVerificationEmail');
            Route::get('verify/post/{id}/resend/sms', 'CreateController@reSendVerificationSms');
            Route::get('verify/post/{field}/{token?}', 'CreateController@verification');
            Route::post('verify/post/{field}/{token?}', 'CreateController@verification');
    
            Route::group(['middleware' => 'auth'], function ($router) {
                $router->pattern('id', '[0-9]+');
				
                Route::get('posts/{id}/edit', 'EditController@getForm');
                Route::put('posts/{id}/edit', 'EditController@postForm');
                Route::get('posts/{id}/photos', 'PhotoController@getForm');
                Route::post('posts/{id}/photos', 'PhotoController@postForm');
                Route::post('posts/{token}/photos/{id}/delete', 'PhotoController@delete');
                Route::get('posts/{id}/packages', 'PackageController@getForm');
                Route::post('posts/{id}/packages', 'PackageController@postForm');
        
                // Payment Gateway Success & Cancel
                Route::get('posts/{id}/payment/success', 'PackageController@paymentConfirmation');
                Route::get('posts/{id}/payment/cancel', 'PackageController@paymentCancel');
            });
            
            // Post's Details
            Route::get(LaravelLocalization::transRoute('routes.post'), 'DetailsController@index');
            
            // Contact Post's Author
            Route::post('posts/{id}/contact', 'DetailsController@sendMessage');
    
            // Send report abuse
            Route::get('posts/{id}/report', 'ReportController@showReportForm');
            Route::post('posts/{id}/report', 'ReportController@sendReport');
        });


        // ACCOUNT
        Route::group(['middleware' => ['auth', 'bannedUser', 'preventBackHistory'], 'namespace' => 'Account'], function($router) {
            $router->pattern('id', '[0-9]+');

            // Users
            Route::get('account', 'EditController@index');
			Route::group(['middleware' => 'impersonate.protect'], function () {
				Route::put('account', 'EditController@updateDetails');
				Route::put('account/settings', 'EditController@updateSettings');
				Route::put('account/preferences', 'EditController@updatePreferences');
			});
			Route::get('account/close', 'CloseController@index');
			Route::group(['middleware' => 'impersonate.protect'], function () {
				Route::post('account/close', 'CloseController@submit');
			});

			// Posts
            Route::get('account/saved-search', 'PostsController@getSavedSearch');
            $router->pattern('pagePath', '(my-posts|archived|favourite|pending-approval|saved-search)+');
            Route::get('account/{pagePath}', 'PostsController@getPage');
            Route::get('account/{pagePath}/{id}/repost', 'PostsController@getArchivedPosts');
            Route::get('account/{pagePath}/{id}/delete', 'PostsController@destroy');
            Route::post('account/{pagePath}/delete', 'PostsController@destroy');
	
            // Conversations
			Route::get('account/conversations', 'ConversationsController@index');
			Route::get('account/conversations/{id}/delete', 'ConversationsController@destroy');
			Route::post('account/conversations/delete', 'ConversationsController@destroy');
			Route::post('account/conversations/{id}/reply', 'ConversationsController@reply');
			$router->pattern('msgId', '[0-9]+');
			Route::get('account/conversations/{id}/messages', 'ConversationsController@messages');
			Route::get('account/conversations/{id}/messages/{msgId}/delete', 'ConversationsController@destroy');
			Route::post('account/conversations/{id}/messages/delete', 'ConversationsController@destroy');
			
			// Transactions
			Route::get('account/transactions', 'TransactionsController@index');
        });
	
	
		// FEEDS
		Route::feeds();


        // Country Code Pattern
        $countryCodePattern = implode('|', array_map('strtolower', array_keys(getCountries())));
        $router->pattern('countryCode', $countryCodePattern);


        // XML SITEMAPS
        Route::get('{countryCode}/sitemaps.xml', 'SitemapsController@site');
        Route::get('{countryCode}/sitemaps/pages.xml', 'SitemapsController@pages');
        Route::get('{countryCode}/sitemaps/categories.xml', 'SitemapsController@categories');
        Route::get('{countryCode}/sitemaps/cities.xml', 'SitemapsController@cities');
        Route::get('{countryCode}/sitemaps/posts.xml', 'SitemapsController@posts');


        // STATICS PAGES
        Route::group(['middleware' => 'httpCache:yes'], function() {
            Route::get(LaravelLocalization::transRoute('routes.page'), 'PageController@index');
            Route::get(LaravelLocalization::transRoute('routes.contact'), 'PageController@contact');
            Route::post(LaravelLocalization::transRoute('routes.contact'), 'PageController@contactPost');
            Route::get(LaravelLocalization::transRoute('routes.sitemap'), 'SitemapController@index');
        });

        // DYNAMIC URL PAGES
        $router->pattern('id', '[0-9]+');
        $router->pattern('username', '[a-zA-Z0-9]+');
        Route::get(LaravelLocalization::transRoute('routes.search'), 'Search\SearchController@index');
        Route::get(LaravelLocalization::transRoute('routes.search-user'), 'Search\UserController@index');
        Route::get(LaravelLocalization::transRoute('routes.search-username'), 'Search\UserController@profile');
		Route::get(LaravelLocalization::transRoute('routes.search-tag'), 'Search\TagController@index');
        Route::get(LaravelLocalization::transRoute('routes.search-city'), 'Search\CityController@index');
        Route::get(LaravelLocalization::transRoute('routes.search-subCat'), 'Search\CategoryController@index');
        Route::get(LaravelLocalization::transRoute('routes.search-cat'), 'Search\CategoryController@index');
    });
});
