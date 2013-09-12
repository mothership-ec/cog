<?php

namespace Message\Cog\Helper;

/**
 * Helps with prorating items
 */
class ProrateHelper {

	protected $_getBasisPercentage;

	/**
	 * @var Closure $_assignProrateAmount A closure to do something with the prorate amount for each basis
	 */
	protected $_assignProrateAmount;

	public function setGetBasisPercentage(\Closure $getBasisPercentage)
	{
		$this->_getBasisPercentage = $getBasisPercentage;

		return $this;
	}

	public function setAssignProrateAmount(\Closure $assignProrateAmount)
	{
		$this->_assignProrateAmount = $assignProrateAmount;

		return $this;
	}

	/**
	 * Prorate a value across a set of values.
	 * 
	 * This is used, for example, to prorate a fixed discount amount across items in
	 * an order.
	 * 
	 * This algorithm avoids rounding errors as the remainder is accounted for
	 * during the pro-rating. This tends to mean that values in the middle of the
	 * basis (set of values) get the remainder amounts assigned to them.
	 * 
	 * Example:
	 * 
	 * <code>
	 * prorateValue(
	 * 	$discountAmount,
	 *  $order->getItems(),
	 *  function($item) use ($order)
	 *  {
	 *  	return $item->originalPrice / $order->total;
	 *  },
	 *  function($item, $proratedValue)
	 *  {
	 *  	$item->discount(round($proratedValue, 2));
	 *  }
	 * );
	 * </code>
	 *
	 * @link http://stackoverflow.com/a/1925719
	 * 
	 * @param float   $amount              The amount to prorate across the basis
	 * @param array   $basis               The basis amounts to prorate $amount across
	 * @param Closure $getBasisPercentage  A closure to define the basis percentage 
	 *                                     (if not passed this is assumed as the values in the $basis array)
	 * 
	 * @return array                       An array with keys matching 
	 **/
	function prorateValue($amount, array $basis)
	{
		$result    = array();
		$remainder = 0;

		foreach ($basis as $key => $itemBasis) {
			// Get basis percentage if a closure was passed to define it
			if (is_callable($this->_getBasisPercentage)) {
				$basisPercentage = call_user_func($this->_getBasisPercentage, $itemBasis);
			}
			// Otherwise just use the basis array element's value
			else {
				$basisPercentage = $itemBasis;
			}

			$oldRemainder  = $remainder;
			$remainder    += $basisPercentage * $amount;
			$proratedValue = round($remainder, 2) - round($oldRemainder, 2);
			$result[$key]  = $proratedValue;

			// If a closure was passed to do something with the prorated value, call it now
			if (is_callable($this->_assignProrateAmount)) {
				call_user_func($this->_assignProrateAmount, $itemBasis, $proratedValue);
			}
		}

		return $result;
	}
}