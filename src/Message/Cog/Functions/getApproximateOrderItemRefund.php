<?php

define("ORDER_DISCOUNT_TOKEN", 'token');
define("ORDER_DISCOUNT_CAMPAIGN", 'campaign');

/*
 * Created on 2 Dec 2009
 *
 * For an item in an order, work out the amount that would be refunded
 * based on bundle/order discounts 
 */
 function getApproximateOrderItemRefund(&$order, $itemID) 
 {
		
		//GET THE ITEM
		$i = $order->getItems($itemID);
		
		//TO START, PRICE IS ITEM PRICE
		$price = $i->price;
		
		//CALCULATE ORDER DISCOUNT APPLIED
		foreach ($order->getDiscounts() as $discount) {
			if ($discount->typeName == ORDER_DISCOUNT_CAMPAIGN) {
				//PERCENTAGE BIT IS EASY..
				if ($discount->percentage) {
					$price -= ($price * ($discount->percentage / 100));
				//BUT FOR AN AMOUNT WE NEED TO INVESTIGATE
				} else if ($discount->amount) {
					//GRAB THE CAMPAIGN
					$c = new Campaign($discount->discountID);
					if ($c->getID()) {
						foreach ($c->getProductIDs() as $id) {
							//IF THE ITEM IS LINKED TO THE PRODUCT ID THEN THE AMOUNT WAS A DISCOUNT AGAINST THIS ITEM
							if ($i->productID == $id) {
								$price -= $discount->amount;
								break;
							}
						}
					}
					//FAILING THAT, THE AMOUNT WAS A FIXED DISCOUNT AGAINST THE ORIGINAL ORDER
					//THEY HAVE RARELY IMPLEMENTED THIS
					//@TODO
				}
				break;
			}
		}
		return round($price, 2);
	}
?>
