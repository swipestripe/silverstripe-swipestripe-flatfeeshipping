<?php

class FlatFeeShippingModification extends Modification {

	public static $has_one = array(
	  'FlatFeeShippingRate' => 'FlatFeeShippingRate'
	);

	public static $defaults = array(
		'SubTotalModifier' => true,
	  'SortOrder' => 50
	);

	public static $default_sort = 'SortOrder ASC';

	public function add($order, $value = null) {

		//Get valid rates for this order
		$rates = null;

    $address = $order->ShippingAddress();
    $countryID = ($address && $address->exists()) ? $address->CountryID : null;
    $rates = ($countryID) 
    	? $rates = FlatFeeShippingRate::get()->where("\"CountryID\" = '$countryID'")
    	: null;

    if ($rates && $rates->exists()) {

    	//Pick the rate
    	$rate = $rates->find('ID', $value);

    	if (!$rate || !$rate->exists()) {
    		$rate = $rates->first();
    	}

    	//Generate the Modification now that we have picked the correct rate
    	$mod = new FlatFeeShippingModification();

    	$mod->Price = $rate->Amount()->getAmount();

    	$mod->Description = $rate->Description;
    	$mod->OrderID = $order->ID;
    	$mod->Value = $rate->ID;
    	$mod->FlatFeeShippingRateID = $rate->ID;
    	$mod->write();
    }
	}

	public function getFormFields() {

		$fields = new FieldList();

		$rate = $this->FlatFeeShippingRate();
		$countryID = ($rate && $rate->exists()) ? $rate->CountryID : null;

		$rates = ($countryID) 
    	? $rates = FlatFeeShippingRate::get()->where("\"CountryID\" = '$countryID'")
    	: null;

    if ($rates && $rates->exists()) {

    	$field = FlatFeeShippingModifierField_Multiple::create(
        $this,
        $rate->Title,
        $rates->map('ID', 'Label')->toArray()
      )->setValue($rate->ID);

      $fields->push($field);
    }

    if (!$fields->exists()) Requirements::javascript('swipestripe-flatfeeshipping/javascript/FlatFeeShippingModifierField.js');

		return $fields;
	}
}