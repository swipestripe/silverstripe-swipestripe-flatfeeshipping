<?php
/**
 * Form field that represents {@link FlatFeeShippingRate}s in the Checkout form.
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage shipping
 */
class FlatFeeShippingModifierField extends ModificationField_Hidden
{
    
    /**
     * The amount this field represents e.g: 15% * order subtotal
     * 
     * @var Money
     */
    protected $amount;

    /**
     * Render field with the appropriate template.
     *
     * @see FormField::FieldHolder()
     * @return String
     */
    public function FieldHolder($properties = array())
    {
        Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
        Requirements::javascript('swipestripe-flatfeeshipping/javascript/FlatFeeShippingModifierField.js');
        return $this->renderWith($this->template);
    }
    
    /**
     * Set the amount that this field represents.
     * 
     * @param Money $amount
     */
    public function setAmount(Price $amount)
    {
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * Return the amount for this tax rate for displaying in the {@link CheckoutForm}
     * 
     * @return String
     */
    public function Description()
    {
        return $this->amount->Nice();
    }

    /**
     * Shipping field modifies {@link Order} sub total by default.
     * 
     * @return Boolean True
     */
    public function modifiesSubTotal()
    {
        return true;
    }
}

class FlatFeeShippingModifierField_Multiple extends ModificationField_Dropdown
{
    
    /**
     * The amount this field represents e.g: 15% * order subtotal
     * 
     * @var Money
     */
    protected $amount;

    /**
     * Render field with the appropriate template.
     *
     * @see FormField::FieldHolder()
     * @return String
     */
    public function FieldHolder($properties = array())
    {
        Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
        Requirements::javascript('swipestripe-flatfeeshipping/javascript/FlatFeeShippingModifierField.js');
        return $this->renderWith($this->template);
    }
    
    /**
     * Set the amount that this field represents.
     * 
     * @param Money $amount
     */
    public function setAmount(Price $amount)
    {
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * Return the amount for this tax rate for displaying in the {@link CheckoutForm}
     * 
     * @return String
     */
    public function Description()
    {
        return $this->amount->Nice();
    }

    /**
     * Shipping field modifies {@link Order} sub total by default.
     * 
     * @see ModificationField_Dropdown::modifiesSubTotal()
     * @return Boolean True
     */
    public function modifiesSubTotal()
    {
        return true;
    }
}

class FlatFeeShippingModifierField_Extension extends Extension
{

    public function updateFields($fields)
    {
        Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
        Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
        Requirements::javascript('swipestripe-flatfeeshipping/javascript/FlatFeeShippingModifierField.js');
    }
}
