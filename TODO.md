# TODO

Planned work, roughly in the order it makes sense to tackle it. Notes under
each item are starting points from the current code, not decisions.

---

## 1. Preview product images when adding or editing a product

- [ ] Show the chosen file before the form is saved, on both create and edit.

A reusable preview module already exists: `assets/js/document-upload.js`, built
for the doctor application documents. Any file input marked
`data-preview="#target"` gets a thumbnail, the filename, the size, a warning
when it breaks the type or size rule, and a close icon to drop the selection.
It should slot straight into `admin/products/form.php` by adding those
attributes and loading the script through the `page_scripts` slot.

The limits should be rendered from the same constants the upload code
enforces, as the sign-up form does, so the hints cannot drift from the rules.

> A multi-image version of this (gallery table, cover selection, lightbox) was
> built and then reverted. Worth deciding early whether this item is
> single-image preview only, or the gallery again.

---

## 2. Add `price`, `sale_price` and `cost_price` to a product

- [ ] Three money columns instead of the single `price` today.

`products` currently has only `price DECIMAL(12,2)`. Points to settle:

- **Which one is charged?** Presumably `sale_price` when set, otherwise
  `price`. Whatever the rule, it needs to live in one place — the cart,
  checkout and order code all read the price independently today.
- **Nullable, not zero.** `sale_price` and `cost_price` should be `NULL` when
  unset, so "not on sale" stays distinguishable from "free", and "cost
  unknown" from "cost nothing".
- **`cost_price` is internal.** It must never reach a storefront view or the
  mobile API response.
- Validation should reject a `sale_price` at or above `price`, rather than
  accepting it and silently not showing a discount.

> An `original_price` column covering the "was / now" half of this was built
> and reverted. The display work (struck-through price, saving percentage) can
> be lifted from that if it is wanted again.

---

## 3. Sales reporting from `cost_price` and sold price

- [ ] Let an admin see revenue, cost and margin.

**The important part: `order_items` records `price` but no cost.** Cost has to
be snapshotted onto the line at checkout the same way `product_name` and
`price` already are. Without that, changing a product's cost silently rewrites
the margin on every past order, and deleting a product loses it entirely.

So this item depends on #2 landing first, and on an `order_items.cost_price`
column being filled at purchase time.

Then: profit per order, per product and per period, probably as a section on
the admin dashboard next to the existing revenue chart. Cancelled orders are
already excluded from revenue — the same rule should apply here.

---

## 4. Drag-and-drop category ordering

- [ ] Let an admin arrange categories by dragging.

`categories` has no ordering column; `Category_model::get_all()` sorts by
`name ASC`. Needs:

- a `sort_order INT` column, backfilled from the current alphabetical order;
- every read that lists categories switched to it — the storefront sidebar,
  the admin list, and the product form dropdown;
- an endpoint that accepts the reordered ids and rewrites `sort_order` in one
  transaction, so a half-applied order cannot be left behind.

Drag-and-drop needs a library. Worth checking the licence before adding one,
as with Fancybox. The order must also be changeable without dragging, so it
stays usable by keyboard and without JavaScript.

---

## 5. Product types (Medical Products / Medical & Healthcare, Everyday Products / Daily Necessities, Health Foods / Food & Beverages)

- [ ] Classify products by type, alongside the existing category.

Two things to pin down before building:

- **One type per product, or several?** "Product can have several type"
  reads like many-to-many, which means a `product_types` table plus a
  `product_type` join table. If it is really one, a `type_id` column on
  `products` is far less machinery.
- **How does this differ from a category?** Products already belong to one
  category, and the examples above could equally be categories. If types are a
  genuinely separate axis — regulatory treatment, say, or tax — that is worth
  writing down, because otherwise two overlapping taxonomies will drift.

Once settled, it touches storefront filtering, the admin product form, and
probably the catalogue sidebar.

---

## 6. Detect whether a visitor arrived from a specific domain

- [ ] Know when someone reaches the site from a particular referring website.

Nothing reads the referrer today. `$this->input->server('HTTP_REFERER')` is
where it would come from, matched against a configured list of domains.

**What this is for changes the design, so it is worth settling first:**

- **Attribution / analytics** — "how many visitors came from partner X". A
  referrer is fine for this. Record the match once per session (the header is
  only sent on the first click, not on later navigation within the site) and
  read it back when an order is placed.
- **Gating access or content** — "only show this to people who came from X".
  A referrer must **not** be trusted for that. It is set by the client, so
  anyone can send any value; browsers also omit it entirely under common
  privacy settings and on HTTPS-to-HTTP hops, which would lock out legitimate
  visitors. If access needs gating, use a signed token or link parameter the
  partner includes, not the header.

Either way the match should be on the host portion only, parsed with
`parse_url()`, so that a domain of `example.com` is not matched by
`example.com.attacker.test`.

---

## 7. Restrict admin URLs to allowed IP addresses

- [ ] Only serve the admin area to a configured list of addresses.

There is one gate to add this to: `Admin_Controller::__construct()` in
`application/core/MY_Controller.php`, which every admin page already passes
through for the sign-in and role checks. The API and storefront should be
unaffected.

CodeIgniter provides the pieces: `$this->input->ip_address()` and
`$this->input->valid_ip()`.

Things to get right:

- **Lockout risk.** This can shut the only admin out of their own site, with
  no way back in through the UI. The allowlist wants to live in a config file
  that is editable without the app running, and an empty list should mean
  "allow everything" so a fresh install is not bricked.
- **Proxies.** Behind a load balancer or Cloudflare, `ip_address()` returns
  the proxy unless `$config['proxy_ips']` names it — it is `''` today. Without
  that, every request looks like it comes from one address. With it wrongly
  set, `X-Forwarded-For` becomes client-controlled and the allowlist means
  nothing.
- **Ranges.** An office or VPN is usually a CIDR block rather than single
  addresses, so matching should handle `203.0.113.0/24`, not just exact
  strings.
- **IPv6.** A connection may arrive as `::1` or an IPv6 address even where an
  IPv4 one was expected.
- Refusal should be a plain 403, not a redirect to the sign-in page — the
  address is wrong, signing in will not help.

This is defence in depth, not a replacement for the role check: it narrows
where an admin session can be used, and does nothing about who holds one.
