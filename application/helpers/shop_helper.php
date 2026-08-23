<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('money'))
{
	/**
	 * Format an amount for display. Prices are stored as DECIMAL and come
	 * back from the driver as strings, so cast before formatting.
	 */
	function money($amount)
	{
		return '&yen;' . number_format((float) $amount, 0, '.', ',');
	}
}

if ( ! function_exists('slugify'))
{
	/**
	 * Build a URL-safe slug. Non-ASCII names (Japanese product titles, for
	 * one) can reduce to an empty string, so fall back to a random token
	 * rather than returning '' and colliding on the unique index.
	 */
	function slugify($text)
	{
		$slug = strtolower(trim((string) $text));
		$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
		$slug = trim($slug, '-');

		return $slug !== '' ? $slug : 'item-' . substr(md5($text . microtime(TRUE)), 0, 8);
	}
}

if ( ! function_exists('product_image_url'))
{
	/** Public URL for a product image, or the shared placeholder. */
	function product_image_url($image)
	{
		if (empty($image))
		{
			return base_url('assets/img/placeholder.svg');
		}

		return base_url('uploads/products/' . $image);
	}
}

if ( ! function_exists('order_status_class'))
{
	/** Bootstrap badge suffix for an order status. */
	function order_status_class($status)
	{
		$map = array(
			'pending'    => 'secondary',
			'paid'       => 'info',
			'processing' => 'primary',
			'shipped'    => 'warning',
			'completed'  => 'success',
			'cancelled'  => 'danger',
		);

		return isset($map[$status]) ? $map[$status] : 'secondary';
	}
}

if ( ! function_exists('order_statuses'))
{
	/** The order status vocabulary, matching the ENUM column. */
	function order_statuses()
	{
		return array('pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled');
	}
}

if ( ! function_exists('e'))
{
	/** Escape for HTML output. */
	function e($value)
	{
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	}
}
