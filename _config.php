<?php

//TODO: Move this into .yml and test
if (class_exists('ExchangeRate_Extension')) {
	Object::add_extension('FlatFeeShippingRate', 'ExchangeRate_Extension');
}