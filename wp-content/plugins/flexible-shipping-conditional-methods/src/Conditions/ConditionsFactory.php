<?php
/**
 * Class ConditionsFactory
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

use WPDesk\FS\ConditionalMethods\Conditions\Location\Country;

/**
 * Can create conditions.
 */
class ConditionsFactory {

	/**
	 * @return Condition[]
	 */
	public function create_conditions() {
		$product          = new Product();
		$product_tag      = new ProductTag();
		$product_category = new ProductCategory();
		$shipping_class   = new ShippingClass();
		$weight           = new Weight();
		$price            = new Price();
		$shipping_method  = new ShippingMethod();
		$free_shipping    = new FreeShipping();
		$day_of_the_week  = new DayOfTheWeek();
		$time_of_the_day  = new TimeOfTheDay();
		$location         = new Location( new Country() );

		$conditions = array(
			$product->get_option_id()          => $product,
			$product_tag->get_option_id()      => $product_tag,
			$product_category->get_option_id() => $product_category,
			$shipping_class->get_option_id()   => $shipping_class,
			$weight->get_option_id()           => $weight,
			$price->get_option_id()            => $price,
			$shipping_method->get_option_id()  => $shipping_method,
			$free_shipping->get_option_id()    => $free_shipping,
			$day_of_the_week->get_option_id()  => $day_of_the_week,
			$time_of_the_day->get_option_id()  => $time_of_the_day,
			$location->get_option_id()         => $location,
		);

		return apply_filters( 'flexible-shipping-conditional-shipping/conditions', $conditions );
	}
}
