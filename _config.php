<?php

//Extensions
Object::add_extension('ShopConfig', 'FlatFeeShippingRate_Extension');
Object::add_extension('OrderForm', 'FlatFeeShippingModifierField_Extension');

if (class_exists('ExchangeRate_Extension')) {
	Object::add_extension('FlatFeeShippingRate', 'ExchangeRate_Extension');
}