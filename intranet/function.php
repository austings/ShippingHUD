/**
* Modify shipping methods for appropriate day, and show Saturday delivery option if Thurs after 5/Friday before 5
*
* @param array $rates Array of rates found for the package
* @param array $package The package array/object being shipped
* @return array of modified rates
*/
add_filter( 'woocommerce_package_rates', 'change_shipping_methods_label_names', 1020, 2 );
function change_shipping_methods_label_names( $rates, $package )
{
	//clear the cache
	rocket_clean_domain();
	wp_cache_flush();
	//Get the current date
	date_default_timezone_set('US/Eastern');
	$day_of_week =  (int)date("N");
	$time_of_day = date("Gi");
	$month_and_day = date("F j");

	//Set default delivery dates
	$month_and_day_Delivery = date("F j",strtotime($month_and_day . ' +1 day'));
	$deliveryDate = date("l",strtotime("+1 day"));

	//Set holiday/weekend variables
	$holidayArr = "{
			\"holidays\":[
			\"July 4\",
			\"December 25\",
			\"January 1\"]
		}";
	//get the holidays
	try {

		$holidayArr = file_get_contents('shippingHoliday.json');
	} catch (Exception $e) {}
	$json = json_decode($holidayArr, true);
	$isHoliday = false;
	$addSaturday = false;

	//The new array to be returned
	$new_rates = array();

	//Calculate the next delivery day
	if ($day_of_week>=5)
		//Weekend Options
	{
		if ($day_of_week==5 AND floatval($time_of_day)<=(int)$json['cutoverTime']) {

			$addSaturday = true;
			$deliveryDate = "Monday";
			$month_and_day_Delivery = date("F j",strtotime($month_and_day . ' +3 days'));
		} else {
			$deliveryDate = "Tuesday";
			$month_and_day_Delivery = date("F j",strtotime("Next Tuesday"));
		}
	} else
		//M-Th Options
	{
		if (floatval($time_of_day)>(int)$json['cutoverTime']) {
			$deliveryDate = date('l', strtotime("+2 days"));
			$month_and_day_Delivery =  date("F j",strtotime($month_and_day . ' +2 days'));
		}
		//for thursday orders after 5pm we must deliver on monday but offer saturday
		if ($deliveryDate=="Saturday") {
			$deliveryDate = date("l",strtotime($deliveryDate . ' +2 days'));
			$month_and_day_Delivery =  date("F j",strtotime($month_and_day_Delivery . ' +2 days'));
			$addSaturday = true;
		}
	}



	//if today is a shipping holiday, add an additional day to delivery
	foreach ($json['holidays'] as $holiday) {
		if ($holiday==$month_and_day) {
			$month_and_day_Delivery =  date("F j",strtotime($month_and_day_Delivery . ' +1 day'));
			$deliveryDate = date("l",strtotime($deliveryDate . ' +1 day'));
			if ($deliveryDate=="Saturday") {
				$deliveryDate = date("l",strtotime($deliveryDate . ' +2 days'));
				$month_and_day_Delivery =  date("F j",strtotime($month_and_day_Delivery . ' +2 days'));
				$addSaturday = true;
			}
			if ($deliveryDate=="Sunday") {
				$deliveryDate = date("l",strtotime($deliveryDate . ' +1 day'));
				$month_and_day_Delivery =  date("F j",strtotime($month_and_day_Delivery . ' +1 day'));
			}
		}
	}
	//if our delivery date is a shipping holiday, add an additional day to delivery
	do {
		$isHoliday = false;
		//if delivery date falls on a holiday, push forward the day of the week for expected delivery until it is no longer a holiday
		foreach ($json['holidays'] as $holiday) {
			//if the delivery date is a holiday, extend delivery date by another day.
			if ($holiday==$month_and_day_Delivery) {
				$isHoliday = true;
				$month_and_day_Delivery =  date("F j",strtotime($month_and_day_Delivery . ' +1 day'));
				$deliveryDate = date("l",strtotime($deliveryDate . ' +1 day'));
				if ($deliveryDate=="Saturday") {
					$deliveryDate = date("l",strtotime($deliveryDate . ' +2 days'));
					$month_and_day_Delivery =  date("F j",strtotime($month_and_day_Delivery . ' +2 days'));
					$addSaturday = true;
				}
				if ($deliveryDate=="Sunday") {
					$deliveryDate = date("l",strtotime($deliveryDate . ' +1 day'));
					$month_and_day_Delivery =  date("F j",strtotime($month_and_day_Delivery . ' +1 day'));
				}

				break;
			}
		}
	} while ($isHoliday);

	$base_text = "Fedex Overnight ";
	$add_on_text = " (" . $deliveryDate . " Delivery)" ;
	$add_on_textsig= " (" . $deliveryDate . ", Insured+Sig)";

	$label_text = $base_text . $add_on_text;
	$label_textsig = $base_text . $add_on_textsig;

	//find the label names and replace them
	foreach ( $rates as $rate_key => $rate ) {
		if ( strpos($rates[$rate_key]->label,'Overnight (Monday Delivery') ) {
			$rates[$rate_key]->label = __( $label_text , 'woocommerce' ); // New label name
		}
		if ( strpos($rates[$rate_key]->label,'Overnight (Monday, Insured')) {
			$rates[$rate_key]->label = __( $label_textsig, 'woocommerce' ); // New label name
		}
		if (strpos($rates[$rate_key]->label,'Saturday')==true) {
			if ($addSaturday) {
				foreach ($json['holidays'] as $holiday) {
					if ($holiday==date("F j",strtotime("Next Saturday")))
						$addSaturday = false;
				}
				if ($addSaturday)
					$new_rates[$rate_key] = $rate;
			}

		} else {
			$new_rates[$rate_key] = $rate;
		}
	}


	return $new_rates;
}