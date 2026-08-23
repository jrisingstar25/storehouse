<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Session-backed shopping cart.
 *
 * The session holds only product_id => qty. Names, prices, stock and
 * availability are re-read from the products table on every access, so a
 * cart can never show a stale price or let a delisted item through
 * checkout. Lines whose product has disappeared or been deactivated are
 * dropped silently and reported through last_removed().
 */
class Cart_model extends CI_Model {

	const SESSION_KEY = 'cart';
	const MAX_QTY     = 99;

	/** @var array Names of lines dropped during the last contents() call. */
	protected $removed = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
	}

	/** @return array product_id => qty */
	protected function raw()
	{
		$cart = $this->session->userdata(self::SESSION_KEY);

		return is_array($cart) ? $cart : array();
	}

	protected function save(array $cart)
	{
		$this->session->set_userdata(self::SESSION_KEY, $cart);
	}

	/**
	 * Add a product, clamping to available stock and MAX_QTY.
	 *
	 * @return array{ok: bool, message: string}
	 */
	public function add($product_id, $qty = 1)
	{
		$product = $this->product_model->get($product_id);

		if ( ! $product OR (int) $product['is_active'] !== 1)
		{
			return array('ok' => FALSE, 'message' => 'That product is not available.');
		}

		$qty = max(1, (int) $qty);
		$cart = $this->raw();
		$id   = (int) $product['id'];

		$current = isset($cart[$id]) ? (int) $cart[$id] : 0;
		$wanted  = $current + $qty;
		$ceiling = min((int) $product['stock'], self::MAX_QTY);

		if ($ceiling < 1)
		{
			return array('ok' => FALSE, 'message' => e($product['name']) . ' is out of stock.');
		}

		if ($wanted > $ceiling)
		{
			$cart[$id] = $ceiling;
			$this->save($cart);

			return array(
				'ok'      => TRUE,
				'message' => 'Only ' . $ceiling . ' of ' . e($product['name']) . ' available - cart adjusted.',
			);
		}

		$cart[$id] = $wanted;
		$this->save($cart);

		return array('ok' => TRUE, 'message' => e($product['name']) . ' added to your cart.');
	}

	/**
	 * Set an exact quantity. A qty of 0 or less removes the line.
	 *
	 * @return array{ok: bool, message: string}
	 */
	public function update_qty($product_id, $qty)
	{
		$cart = $this->raw();
		$id   = (int) $product_id;

		if ( ! isset($cart[$id]))
		{
			return array('ok' => FALSE, 'message' => 'That item is not in your cart.');
		}

		$qty = (int) $qty;

		if ($qty < 1)
		{
			unset($cart[$id]);
			$this->save($cart);

			return array('ok' => TRUE, 'message' => 'Item removed from your cart.');
		}

		$product = $this->product_model->get($id);

		if ( ! $product OR (int) $product['is_active'] !== 1)
		{
			unset($cart[$id]);
			$this->save($cart);

			return array('ok' => FALSE, 'message' => 'That product is no longer available.');
		}

		$ceiling = min((int) $product['stock'], self::MAX_QTY);

		if ($qty > $ceiling)
		{
			$qty = $ceiling;
			$message = 'Only ' . $ceiling . ' available - quantity adjusted.';
		}
		else
		{
			$message = 'Cart updated.';
		}

		if ($qty < 1)
		{
			unset($cart[$id]);
			$this->save($cart);

			return array('ok' => FALSE, 'message' => e($product['name']) . ' is out of stock and was removed.');
		}

		$cart[$id] = $qty;
		$this->save($cart);

		return array('ok' => TRUE, 'message' => $message);
	}

	public function remove($product_id)
	{
		$cart = $this->raw();
		unset($cart[(int) $product_id]);
		$this->save($cart);
	}

	public function clear()
	{
		$this->session->unset_userdata(self::SESSION_KEY);
	}

	/**
	 * Hydrated cart lines.
	 *
	 * @return array List of {product_id, name, image, price, qty,
	 *               stock, subtotal, over_stock}
	 */
	public function contents()
	{
		$cart = $this->raw();
		$this->removed = array();

		if (empty($cart))
		{
			return array();
		}

		$products = $this->db->where_in('id', array_map('intval', array_keys($cart)))
			->get('products')
			->result_array();

		$by_id = array();

		foreach ($products as $product)
		{
			$by_id[(int) $product['id']] = $product;
		}

		$lines = array();
		$dirty = FALSE;

		foreach ($cart as $id => $qty)
		{
			$id  = (int) $id;
			$qty = (int) $qty;

			if ( ! isset($by_id[$id]) OR (int) $by_id[$id]['is_active'] !== 1)
			{
				$this->removed[] = isset($by_id[$id]) ? $by_id[$id]['name'] : 'An item';
				unset($cart[$id]);
				$dirty = TRUE;
				continue;
			}

			$product = $by_id[$id];
			$price   = (float) $product['price'];

			$lines[] = array(
				'product_id' => $id,
				'name'       => $product['name'],
				'image'      => $product['image'],
				'price'      => $price,
				'qty'        => $qty,
				'stock'      => (int) $product['stock'],
				'subtotal'   => $price * $qty,
				'over_stock' => $qty > (int) $product['stock'],
			);
		}

		if ($dirty)
		{
			$this->save($cart);
		}

		return $lines;
	}

	/** @return array Names dropped by the most recent contents() call. */
	public function last_removed()
	{
		return $this->removed;
	}

	/** Total number of units in the cart (used for the header badge). */
	public function count_items()
	{
		$total = 0;

		foreach ($this->raw() as $qty)
		{
			$total += (int) $qty;
		}

		return $total;
	}

	public function subtotal()
	{
		$total = 0.0;

		foreach ($this->contents() as $line)
		{
			$total += $line['subtotal'];
		}

		return $total;
	}

	public function is_empty()
	{
		return $this->count_items() === 0;
	}

	/**
	 * Lines whose quantity now exceeds stock. Checkout blocks on this.
	 *
	 * @return array
	 */
	public function stock_problems()
	{
		$problems = array();

		foreach ($this->contents() as $line)
		{
			if ($line['over_stock'])
			{
				$problems[] = $line;
			}
		}

		return $problems;
	}
}
