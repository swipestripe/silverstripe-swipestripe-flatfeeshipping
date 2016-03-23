<?php

class FlatFeeShippingModification extends Modification
{

    private static $has_one = array(
        'FlatFeeShippingRate' => 'FlatFeeShippingRate'
    );

    private static $defaults = array(
        'SubTotalModifier' => true,
        'SortOrder' => 50
    );

    private static $default_sort = 'SortOrder ASC';

    public function add($order, $value = null)
    {
        $this->OrderID = $order->ID;

        $country = Country_Shipping::get()
                ->filter("Code", $order->ShippingCountryCode)
                ->first();

        $rates = $this->getFlatShippingRates($country);
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

    public function getFlatShippingRates(Country_Shipping $country)
    {
        //Get valid rates for this country
        $countryID = ($country && $country->exists()) ? $country->ID : null;
        $rates = FlatFeeShippingRate::get()->filter("CountryID", $countryID);
        $this->extend("updateFlatShippingRates", $rates, $country);
        return $rates;
    }

    public function getFormFields()
    {
        $fields = new FieldList();
        $rate = $this->FlatFeeShippingRate();
        $rates = $this->getFlatShippingRates($rate->Country());

        if ($rates && $rates->exists()) {
            if ($rates->count() > 1) {
                $field = FlatFeeShippingModifierField_Multiple::create(
                    $this,
                    _t('FlatFeeShippingModification.FIELD_LABEL', 'Shipping'),
                    $rates->map('ID', 'Label')->toArray()
                )->setValue($rate->ID);
            } else {
                $newRate = $rates->first();
                $field = FlatFeeShippingModifierField::create(
                    $this,
                    $newRate->Title,
                    $newRate->ID
                )->setAmount($newRate->Price());
            }

            $fields->push($field);
        }

        if (!$fields->exists()) {
            Requirements::javascript('swipestripe-flatfeeshipping/javascript/FlatFeeShippingModifierField.js');
        }

        return $fields;
    }
}
