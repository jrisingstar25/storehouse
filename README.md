# Jinjong

A small e-commerce application on CodeIgniter 3.1.9: customer accounts, an admin
dashboard, product CRUD, and a shopping cart with checkout.

## Requirements

- PHP 7.3+ with `mysqli` and `gd`
- MySQL / MariaDB
- Apache (the project is written for XAMPP at `htdocs/jinjong`)

## Setup

1. **Create the database.** From the project root:

   ```
   mysql -u root < application/database/schema.sql
   mysql -u root < application/database/seed.sql
   ```

   The seed step is optional but gives you three categories, nine products and
   the two accounts below.

   Upgrading a database created before accounts moved from email to username?
   Run `application/database/upgrade-username.sql` instead - read its header
   first, it explains how existing addresses are converted and where they can
   collide.

2. **Check the credentials** in `application/config/database.php`. They default
   to the XAMPP standard (`root`, no password, database `jinjong`).

3. **Check the base URL** in `application/config/config.php`. It is set to
   `http://localhost/jinjong/`.

4. Open <http://localhost/jinjong/>. The site opens on the sign-in page - the
   shop is only visible to signed-in users, so register or sign in with a demo
   account below.

### Demo accounts

| Role     | Username   | Password      |
| -------- | ---------- | ------------- |
| Admin    | `admin`    | `admin123`    |
| Customer | `customer` | `customer123` |

**Change both passwords before putting this anywhere real.**

### Clean URLs

URLs are clean (`/jinjong/shop`, not `/jinjong/index.php/shop`). This is done by
the `.htaccess` in the project root, which routes anything that is not a real
file through `index.php`, together with `$config['index_page'] = '';`. It needs
`mod_rewrite`, which XAMPP enables by default.

Two things to know if you move the project:

- `RewriteBase` in `.htaccess` and `$config['base_url']` both hard-code
  `/jinjong/`. Change both if you rename or relocate the folder.
- If `mod_rewrite` is ever unavailable, set `$config['index_page']` back to
  `'index.php'`. The site works either way; only the URLs change.

Old `index.php/...` links keep working, so existing bookmarks do not break.

## What is included

### Authentication
Sign up, sign in and sign out, with passwords stored as bcrypt hashes
(`password_hash`) and transparently re-hashed on sign-in when PHP's default cost
changes. Sessions store only a user id; the account is re-read on every request,
so deactivating or demoting somebody takes effect immediately. Login failures
return one generic message, and take about the same time, so the form cannot be
used to discover which usernames exist. The session id is regenerated on
sign-in to prevent session fixation.

Accounts are identified by a **username**, not an email address - the system
stores no email addresses anywhere, on accounts or on orders. Usernames allow
letters, numbers, underscores and dashes, and the column collation is
case-insensitive, so `Admin` and `admin` cannot both exist.

### Storefront
**The storefront is private.** Browsing, the cart and checkout all require a
signed-in account; an anonymous visitor is sent to the sign-in page and
returned to whatever they asked for once they sign in.

Sign-up is open, on the web at `/register` and in the app at
`POST /api/auth/register`. Both go through `Authentication::register()`, which
forces the role to `customer` - so neither channel can create an administrator,
whatever it posts. Admin accounts are made by an existing admin under
**Admin -> Users**.

Product catalogue with category filtering, search, sorting and pagination;
product detail pages with related items and live stock status. Inactive products
are hidden from customers but remain viewable by admins for previewing.

### Shopping cart
**Add to cart happens in the background** — no page reload. The button posts
with jQuery (`$.ajax`), the navbar counter updates in place, and the result
appears as a toast. The forms are ordinary `<form>` elements, so with JavaScript disabled
they submit normally and fall back to the old redirect.

One wrinkle worth knowing about: CodeIgniter issues a new CSRF token every time
it verifies a POST, including one it rejects. A token rendered into the page is
therefore stale the moment anything posts — from this tab or another. The
script reads the current token from its cookie immediately before each request
and re-syncs every form on the page from the reply, so a catalogue page full of
add-to-cart buttons keeps working, and a rejected request heals itself instead
of leaving a dead button. Because the script always makes the submitted token
match the cookie, this also means a CSRF rejection is now effectively
unreachable from our own pages; the recovery path stays in for the case where
the cookie is missing entirely. See `assets/js/app.js`.

jQuery 3.7.1 is loaded from a CDN in `application/views/layouts/public.php`.
Bootstrap 5 does not need it — it is there for this script.

Session-backed cart holding only `product_id => qty`. Names, prices, stock and
availability are re-read from the database on every access, so the cart can
never show a stale price, and a product that is deleted or delisted is dropped
from the cart with a notice rather than silently changing the total. Quantities
are clamped to available stock on add and on update.

### Checkout
Prefilled from the signed-in account. Flat
shipping of &yen;600, free over &yen;15,000. Placing an order writes the order
and its lines inside a transaction and decrements stock with a
`WHERE stock >= qty` guard, so two people racing for the last unit cannot both
succeed — the loser's order rolls back whole. Each order line snapshots the
product name and price, so order history stays readable after a product is
edited or deleted.

### Customer account
Profile and password editing, plus order history. Customers can only open their
own orders.

### Admin dashboard
Revenue, order, product and customer totals; a 14-day revenue chart; recent
orders; best sellers; and a low-stock list.

### Admin CRUD
- **Products** — create, edit, delete, active/inactive toggle, image upload with
  filtering by search, category, status and sort order.
- **Categories** — create, edit, delete. Deleting a category leaves its products
  in place and simply uncategorises them.
- **Orders** — browse, filter, view, change status, delete. Cancelling an order
  returns its items to stock, exactly once.
- **Users** — create, edit, delete, role and activation control. Customers can
  also sign themselves up; **admin accounts can only be made here**, by setting
  **Role** to *Admin* on the new-user form.

  An admin cannot remove their own admin access or delete their own account,
  and the last admin account cannot be removed. Because the admin performing
  any change is always an active admin and cannot target themselves, at least
  one active admin always survives - the system cannot be locked out of its
  own admin area. Deleting a user keeps their past orders, which simply lose
  their account link.

## Mobile API

A JSON API for the mobile app.

Base URL: `http://localhost/jinjong/api`

The paths carry no version segment. If a future change has to break the
contract, it will need either a version prefix introduced then or additive-only
changes, since apps already installed will keep calling these paths.

### Conventions

Requests may send a JSON body (`Content-Type: application/json`) or ordinary
form encoding. Every response is JSON in one of two shapes:

```json
{ "success": true,  "data": { ... } }
{ "success": false, "message": "...", "errors": { "field": "why" } }
```

`errors` appears only on validation failures (422). Status codes used: 200, 201,
400, 401, 404, 405, 422.

### Authentication

Endpoints other than login and register expect a bearer token:

```
Authorization: Bearer <token>
```

Tokens are **not** the web session — the API sets no cookies and writes no
session files. They last 30 days, and only a SHA-256 of each one is stored, so
a dump of `api_tokens` cannot be replayed. The plaintext is returned once, at
issue.

A token stops working the moment its account is deactivated or deleted, and
expired rows are removed as they are encountered.

### Endpoints

#### `POST /auth/register`

Self-service sign-up. **The role is always `customer`** — posting `role=admin`
is ignored, so the API can never mint an administrator. Admin accounts remain
something only an existing admin can create, in the web back office.

```json
{ "name": "Mobile Mary", "username": "mary_m", "password": "phone12345",
  "phone": "090-7777-8888", "address": "12 App Street", "device": "Pixel 8" }
```

`name`, `username` (3-60 chars, letters/numbers/underscore/dash) and `password`
(min 8) are required; `phone`, `address` and `device` are optional. `device`
labels the token so a user can later be shown their sessions. Returns **201** with a token, as below — the app does not need
to call login after registering.

#### `POST /auth/login`

```json
{ "username": "customer", "password": "customer123", "device": "Pixel 8" }
```

**200**:

```json
{ "success": true, "data": {
    "token": "3f679b34…",
    "expires_at": "2026-09-22 16:28:47",
    "user": { "id": 2, "name": "Demo Customer", "username": "customer",
              "role": "customer", "phone": "080-1111-2222", "address": "1-2-3 Shibuya, Tokyo" }
} }
```

A wrong password, an unknown username and a disabled account all return the same
**401** and take about the same time, so the endpoint cannot be used to discover
which usernames exist.

#### `POST /auth/logout`

Requires a token. Revokes the calling token. Send `{"all_devices": true}` to
revoke every token the user holds.

#### `GET /auth/me`

Requires a token. Returns the account behind it — also the cheapest way for an
app to check on launch whether its stored token is still good.

### A note on CSRF

`api` and everything under it is listed in `$config['csrf_exclude_uris']`. That is safe precisely
because the API authenticates with a header the caller must know rather than a
cookie the browser attaches automatically: there is no ambient credential for a
forged cross-site request to ride on. The web side keeps full CSRF protection —
posting to `/login` or `/cart/add` without a token is still rejected.

### Not built yet

Worth adding before this is exposed beyond a local network:

- **Rate limiting on login and register.** Nothing currently slows down
  credential stuffing or bulk account creation.
- **HTTPS.** Bearer tokens are only as private as the transport.
- **Account recovery.** With no email address on file there is no self-service
  password reset; a locked-out user needs an admin to set a new password.

## Layout

```
application/
	config/            routes, database, autoload, app config
	controllers/       Shop, Cart, Checkout, Auth, Account
		admin/           Dashboard, Products, Categories, Orders, Users
		api/             Auth (mobile API), Fallback
	core/              MY_Controller (Public/Customer/Admin bases) and
	                   API_Controller (JSON + bearer tokens)
	libraries/         Authentication.php (reachable as $this->auth)
	models/            User, Category, Product, Cart, Order, Api_token
	helpers/           shop_helper.php (money, slugify, escaping, statuses)
	views/
		layouts/         public.php, admin.php
		...
	database/          schema.sql, seed.sql, upgrade-username.sql
assets/              css, js and the product-image placeholder
uploads/products/    uploaded product images (not tracked in git)
```

Controllers extend one of three base classes in
`application/core/MY_Controller.php`, which is where access control lives:

- `Public_Controller` — open to everyone; signing in, out and up
- `Customer_Controller` — requires a signed-in user: the whole storefront,
  cart, checkout and account pages. Adds the cart badge
- `Admin_Controller` — requires the `admin` role, renders the admin layout

## Notes on security

- CSRF protection is enabled globally; every state-changing action is a POST
  submitted through `form_open()`.
- All state-changing admin endpoints (delete, toggle, status) reject GET.
- Output is escaped with the `e()` helper; queries go through the query builder
  or bound parameters.
- Uploads are restricted by type and size, stored under randomised filenames,
  and `uploads/.htaccess` refuses to serve anything executable from that folder.
- The library is named `Authentication`, not `Auth`, because CodeIgniter loads
  libraries and controllers into the same global namespace and the `Auth`
  controller would collide with it.
