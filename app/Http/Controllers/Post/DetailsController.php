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

namespace App\Http\Controllers\Post;

use App\Events\PostWasVisited;
use App\Helpers\Arr;
use App\Http\Controllers\Post\Traits\CustomFieldTrait;
use App\Http\Requests\SendMessageRequest;
use App\Models\Post;
use App\Models\PostType;
use App\Models\Category;
use App\Models\City;
use App\Models\Message;
use App\Models\Package;
use App\Models\Payment;
use App\Http\Controllers\FrontController;
use App\Models\User;
use App\Models\Scopes\VerifiedScope;
use App\Models\Scopes\ReviewedScope;
use App\Notifications\SellerContacted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Jenssegers\Date\Date;
use Larapen\TextToImage\Facades\TextToImage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Torann\LaravelMetaTags\Facades\MetaTag;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;

class DetailsController extends FrontController
{
	use CustomFieldTrait;
	
	/**
	 * Post expire time (in months)
	 *
	 * @var int
	 */
	public $expireTime = 24;
	
	public $reviewsPlugin;
	
	/**
	 * DetailsController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// From Laravel 5.3.4 or above
		$this->middleware(function ($request, $next) {
			$this->commonQueries();
			
			return $next($request);
		});
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		// Check Country URL for SEO
		$countries = CountryLocalizationHelper::transAll(CountryLocalization::getCountries());
		view()->share('countries', $countries);
		
		// Check and load Reviews plugin
		$this->reviewsPlugin = load_installed_plugin('reviews');
		view()->share('reviewsPlugin', $this->reviewsPlugin);
	}
	
	/**
	 * Show Dost's Details.
	 *
	 * @param $postId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function index($postId)
	{
		$data = [];
		
		// Get and Check the Controller's Method Parameters
		$parameters = Request::route()->parameters();
		
		// Show 404 error if the Post's ID is not numeric
		if (!isset($parameters['id']) || empty($parameters['id']) || !is_numeric($parameters['id'])) {
			abort(404);
		}
		
		// Set the Parameters
		$postId = $parameters['id'];
		if (isset($parameters['slug'])) {
			$slug = $parameters['slug'];
		}
		
		// GET POST'S DETAILS
		if (Auth::check()) {
			// Get post's details even if it's not activated and reviewed
			$cacheId = 'post.withoutGlobalScopes.with.user.city.pictures.' . $postId;
			$post = Cache::remember($cacheId, $this->cacheExpiration, function () use ($postId) {
				$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])->unarchived()->where('id', $postId)->with(['user', 'city', 'pictures'])->first();
				
				return $post;
			});
			
			// If the logged user is not an admin user...
			if (Auth::user()->is_admin != 1) {
				// Then don't get post that are not from the user
				if (!empty($post) && $post->user_id != Auth::user()->id) {
					$cacheId = 'post.with.user.city.pictures.' . $postId;
					$post = Cache::remember($cacheId, $this->cacheExpiration, function () use ($postId) {
						$post = Post::unarchived()->where('id', $postId)->with(['user', 'city', 'pictures'])->first();
						
						return $post;
					});
				}
			}
		} else {
			$cacheId = 'post.with.user.city.pictures.' . $postId;
			$post = Cache::remember($cacheId, $this->cacheExpiration, function () use ($postId) {
				$post = Post::unarchived()->where('id', $postId)->with(['user', 'city', 'pictures'])->first();
				
				return $post;
			});
		}
		// Preview Post after activation
		if (Input::filled('preview') && Input::get('preview') == 1) {
			// Get post's details even if it's not activated and reviewed
			$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])->where('id', $postId)->with(['user', 'city', 'pictures'])->first();
		}
		
		// Post not found
		if (empty($post) || empty($post->city)) {
			abort(404, t('Post not found'));
		}
		
		// Share post's details
		view()->share('post', $post);
		
		
		// Get category details
		$cacheId = 'category.' . $post->category_id . '.' . config('app.locale');
		$cat = Cache::remember($cacheId, $this->cacheExpiration, function () use ($post) {
			$cat = Category::transById($post->category_id);
			
			return $cat;
		});
		view()->share('cat', $cat);
		
		// Get post's type details
		$cacheId = 'postType.' . $post->post_type_id . '.' . config('app.locale');
		$postType = Cache::remember($cacheId, $this->cacheExpiration, function () use ($post) {
			$postType = PostType::transById($post->post_type_id);
			
			return $postType;
		});
		view()->share('postType', $postType);
		
		
		// Require info
		if (empty($cat) || empty($postType)) {
			abort(404);
		}
		
		
		// Get Category nested IDs
		$catNestedIds = (object)[
			'parentId' => $cat->parent_id,
			'id'       => $cat->tid,
		];
		
		// Get Custom Fields
		$customFields = $this->getPostFieldsValues($catNestedIds, $post->id);
		view()->share('customFields', $customFields);
		
		
		// Get package details
		$package = null;
		if ($post->featured == 1) {
			$payment = Payment::where('post_id', $post->id)->orderBy('id', 'DESC')->first();
			if (!empty($payment)) {
				$package = Package::transById($payment->package_id);
			}
		}
		view()->share('package', $package);
		
		
		// Get Post's user decision about comments activation
		$commentsAreDisabledByUser = false;
		// Get possible Post's user
		if (isset($post->user_id) && !empty($post->user_id)) {
			$possibleUser = User::find($post->user_id);
			if (!empty($possibleUser)) {
				if ($possibleUser->disable_comments == 1) {
					$commentsAreDisabledByUser = true;
				}
			}
		}
		view()->share('commentsAreDisabledByUser', $commentsAreDisabledByUser);
		
		
		// GET PARENT CATEGORY
		if ($cat->parent_id == 0) {
			$parentCat = $cat;
		} else {
			$parentCat = Category::transById($cat->parent_id, config('app.locale'));
		}
		view()->share('parentCat', $parentCat);
		
		// Increment Post visits counter
		Event::fire(new PostWasVisited($post));
		
		// GET SIMILAR POSTS
		$featured = $this->getCategorySimilarPosts($cat, $post->id);
		// $featured = $this->getLocationSimilarPosts($post->city, $post->id);
		$data['featured'] = $featured;
		
		// SEO
		$title = $post->title . ', ' . $post->city->name;
		$description = str_limit(str_strip(strip_tags($post->description)), 200);
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		if (!empty($post->tags)) {
			MetaTag::set('keywords', str_replace(',', ', ', $post->tags));
		}
		
		// Open Graph
		$this->og->title($title)
				 ->description($description)
				 ->type('article')
				 ->article(['author' => config('settings.social_link.facebook_page_url')])
				 ->article(['publisher' => config('settings.social_link.facebook_page_url')]);
		if (!$post->pictures->isEmpty()) {
			if ($this->og->has('image')) {
				$this->og->forget('image')->forget('image:width')->forget('image:height');
			}
			foreach ($post->pictures as $picture) {
				$this->og->image(resize($picture->filename, 'large'), [
					'width'  => 600,
					'height' => 600,
				]);
			}
		}
		view()->share('og', $this->og);
		
		// Expiration Info
		$today_dt = Date::now(config('timezone.id'));
		if ($today_dt->gt($post->created_at->addMonths($this->expireTime))) {
			flash(t("Warning! This ad has expired. The product or service is not more available (may be)"))->error();
		}
		
		// Reviews Plugin Data
		if (isset($this->reviewsPlugin) and !empty($this->reviewsPlugin)) {
			try {
				$rvPost = \App\Plugins\reviews\app\Models\Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])->find($post->id);
				view()->share('rvPost', $rvPost);
			} catch (\Exception $e) {
			}
		}
		
		// View
		return view('post.details', $data);
	}
	
	/**
	 * @param $postId
	 * @param SendMessageRequest $request
	 * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function sendMessage($postId, SendMessageRequest $request)
	{
		// Get the Post
		$post = Post::unarchived()->findOrFail($postId);
		
		// Store Message
		$message = new Message([
			'post_id'      => $post->id,
			'from_user_id' => Auth::check() ? Auth::user()->id : 0,
			'from_name'    => $request->input('name'),
			'from_email'   => $request->input('email'),
			'from_phone'   => $request->input('phone'),
			'to_user_id'   => $post->user_id,
			'to_name'      => $post->contact_name,
			'to_email'     => $post->email,
			'to_phone'     => $post->phone,
			'subject'      => $post->title,
			'message'      => $request->input('message')
				. '<br><br>'
				. t('Related to the ad')
				. ': <a href="' . lurl($post->uri) . '">' . t('Click here to see') . '</a>',
		]);
		$message->save();
		
		// Save and Send user's resume
		if ($request->hasFile('filename')) {
			$message->filename = $request->file('filename');
			$message->save();
		}
		
		// Send a message to publisher
		try {
			$post->notify(new SellerContacted($post, $message));
			
			$msg = t("Your message has sent successfully to :contact_name.", ['contact_name' => $post->contact_name]);
			flash($msg)->success();
		} catch (\Exception $e) {
			flash($e->getMessage())->error();
		}
		
		return redirect(config('app.locale') . '/' . $post->uri);
	}
	
	/**
	 * Get similar Posts (Posts in the same Category)
	 *
	 * @param $cat
	 * @param int $currentPostId
	 * @return array|null|\stdClass
	 */
	private function getCategorySimilarPosts($cat, $currentPostId = 0)
	{
		$limit = 20;
		$featured = null;
		
		// Get the sub-categories of the current ad parent's category
		$similarCatIds = [];
		if (!empty($cat)) {
			if ($cat->tid == $cat->parent_id) {
				$similarCatIds[] = $cat->tid;
			} else {
				if (!empty($cat->parent_id)) {
					$similarCatIds = Category::trans()->where('parent_id', $cat->parent_id)->get()->keyBy('id')->keys()->toArray();
					$similarCatIds[] = (int)$cat->parent_id;
				} else {
					$similarCatIds[] = (int)$cat->tid;
				}
			}
		}
		
		// Get ads from same category
		$posts = [];
		if (!empty($similarCatIds)) {
			if (count($similarCatIds) == 1) {
				$similarPostSql = 'AND a.category_id=' . ((isset($similarCatIds[0])) ? (int)$similarCatIds[0] : 0) . ' ';
			} else {
				$similarPostSql = 'AND a.category_id IN (' . implode(',', $similarCatIds) . ') ';
			}
			$reviewedCondition = '';
			if (config('settings.single.posts_review_activation')) {
				$reviewedCondition = ' AND a.reviewed = 1';
			}
			$sql = 'SELECT a.* ' . '
				FROM ' . table('posts') . ' as a
				WHERE a.country_code = :countryCode ' . $similarPostSql . '
					AND (a.verified_email=1 AND a.verified_phone=1)
					AND a.archived!=1 
					AND a.deleted_at IS NULL ' . $reviewedCondition . '
					AND a.id != :currentPostId
				ORDER BY a.id DESC
				LIMIT 0,' . (int)$limit;
			$bindings = [
				'countryCode'   => config('country.code'),
				'currentPostId' => $currentPostId,
			];
			
			$cacheId = 'posts.similar.category.' . $cat->tid . '.post.' . $currentPostId;
			$posts = Cache::remember($cacheId, $this->cacheExpiration, function () use ($sql, $bindings) {
				try {
					$posts = DB::select(DB::raw($sql), $bindings);
				} catch (\Exception $e) {
					return [];
				}
				
				return $posts;
			});
		}
		
		if (count($posts) > 0) {
			// Append the Posts 'uri' attribute
			$posts = collect($posts)->map(function ($post) {
				$post->uri = trans('routes.v-post', ['slug' => slugify($post->title), 'id' => $post->id]);
				
				return $post;
			})->toArray();
			
			// Randomize the Posts
			$posts = collect($posts)->shuffle()->toArray();
			
			// Featured Area Data
			$featured = [
				'title' => t('Similar Ads'),
				'link'  => qsurl(config('app.locale') . '/' . trans('routes.v-search', ['countryCode' => $this->country->get('icode')]), array_merge(Request::except('c'), ['c' => $cat->tid])),
				'posts' => $posts,
			];
			$featured = Arr::toObject($featured);
		}
		
		return $featured;
	}
	
	/**
	 * Get Posts in the same Location
	 *
	 * @param $city
	 * @param int $currentPostId
	 * @return array|null|\stdClass
	 */
	private function getLocationSimilarPosts($city, $currentPostId = 0)
	{
		$distance = 50; // km OR miles
		$limit = 10;
		$featured = null;
		
		if (!empty($city)) {
			// Get ads from same location (with radius)
			$reviewedCondition = '';
			if (config('settings.single.posts_review_activation')) {
				$reviewedCondition = ' AND a.reviewed = 1';
			}
			$sql = 'SELECT a.*, 3959 * acos(cos(radians(' . $city->latitude . ')) * cos(radians(a.lat))'
				. '* cos(radians(a.lon) - radians(' . $city->longitude . '))'
				. '+ sin(radians(' . $city->latitude . ')) * sin(radians(a.lat))) as distance
				FROM ' . table('posts') . ' as a
				INNER JOIN ' . table('categories') . ' as c ON c.id=a.category_id AND c.active=1
				WHERE a.country_code = :countryCode
					AND (a.verified_email=1 AND a.verified_phone=1)
					AND a.archived!=1  ' . $reviewedCondition . '
					AND a.id != :currentPostId
				HAVING distance <= ' . $distance . ' 
				ORDER BY distance ASC, a.id DESC
				LIMIT 0,' . (int)$limit;
			$bindings = [
				'countryCode'   => config('country.code'),
				'currentPostId' => $currentPostId,
			];
			
			$cacheId = 'posts.similar.city.' . $city->id . '.post.' . $currentPostId;
			$posts = Cache::remember($cacheId, $this->cacheExpiration, function () use ($sql, $bindings) {
				try {
					$posts = DB::select(DB::raw($sql), $bindings);
				} catch (\Exception $e) {
					return [];
				}
				
				return $posts;
			});
			
			if (count($posts) > 0) {
				// Append the Posts 'uri' attribute
				$posts = collect($posts)->map(function ($post) {
					$post->uri = trans('routes.v-post', ['slug' => slugify($post->title), 'id' => $post->id]);
					
					return $post;
				})->toArray();
				
				// Randomize the Posts
				$posts = collect($posts)->shuffle()->toArray();
				
				// Featured Area Data
				$featured = [
					'title' => t('More ads at :distance :unit around :city', [
						'distance' => $distance,
						'unit'     => unitOfLength(config('country.code')),
						'city'     => $city->name,
					]),
					'link'  => qsurl(config('app.locale') . '/' . trans('routes.v-search', ['countryCode' => $this->country->get('icode')]), array_merge(Request::except(['l', 'location']), ['l' => $city->id])),
					'posts' => $posts,
				];
				$featured = Arr::toObject($featured);
			}
		}
		
		return $featured;
	}
}
