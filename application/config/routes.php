<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
// The storefront is private, so an anonymous visitor lands on the sign-in
// page. Auth::login() forwards anyone already signed in to the shop (or to
// the dashboard, for an admin).
$route['default_controller'] = 'auth/login';
$route['404_override']       = '';
$route['translate_uri_dashes'] = FALSE;

/* -------------------------------------------------------------------
 * Authentication
 * ---------------------------------------------------------------- */
// There is no public sign-up: accounts are created by an admin under
// /admin/users.
$route['login']  = 'auth/login';
$route['logout'] = 'auth/logout';

/* -------------------------------------------------------------------
 * Storefront
 * ---------------------------------------------------------------- */
$route['shop']                 = 'shop/index';
$route['category/(:any)']      = 'shop/category/$1';
$route['product/(:any)']       = 'shop/product/$1';

/* -------------------------------------------------------------------
 * Cart & checkout
 * ---------------------------------------------------------------- */
$route['cart']                 = 'cart/index';
$route['checkout']             = 'checkout/index';

/* -------------------------------------------------------------------
 * Customer account
 * ---------------------------------------------------------------- */
$route['account']              = 'account/index';
$route['account/orders']       = 'account/orders';
$route['account/orders/(:num)'] = 'account/order/$1';

/* -------------------------------------------------------------------
 * Admin  (controllers live in application/controllers/admin/)
 * ---------------------------------------------------------------- */
$route['admin'] = 'admin/dashboard/index';

/* -------------------------------------------------------------------
 * Mobile API (controllers live in application/controllers/api/)
 *
 * Bearer-token authenticated, never the web session - see
 * application/core/API_Controller.php.
 *
 * The routes below look like identity mappings, and they are: they exist to
 * claim each real endpoint before the catch-all further down, which would
 * otherwise swallow them into the 404 handler.
 * ---------------------------------------------------------------- */
$route['api/auth/register'] = 'api/auth/register';
$route['api/auth/login']    = 'api/auth/login';
$route['api/auth/logout']   = 'api/auth/logout';
$route['api/auth/me']       = 'api/auth/me';

// Anything else under the API prefix is a JSON 404, not an HTML error page.
// A real regex, not (:any): CodeIgniter expands (:any) to [^/]+, which stops
// at the first slash and so would miss api/auth/typo.
$route['api']      = 'api/fallback/index';
$route['api/(.+)'] = 'api/fallback/index';
