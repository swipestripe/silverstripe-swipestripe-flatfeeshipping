<?php
/**
 * Tax rates that can be set in {@link SiteConfig}. Several flat rates can be set 
 * for any supported shipping country.
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage shipping
 */
class FlatFeeShippingRate extends DataObject {
	
	/**
	 * Fields for this tax rate
	 * 
	 * @var Array
	 */
	private static $db = array(
		'Title' => 'Varchar',
		'Description' => 'Varchar',
		'Price' => 'Decimal(19,4)'
	);
	
	/**
	 * Tax rates are associated with SiteConfigs.
	 * 
	 * TODO The CTF in SiteConfig does not save the SiteConfig ID correctly so this is moot
	 * 
	 * @var unknown_type
	 */
	private static $has_one = array(
		'ShopConfig' => 'ShopConfig',
		'Country' => 'Country_Shipping'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'Description' => 'Description',
		'SummaryOfPrice' => 'Amount',
		'Country.Title' => 'Country'
	);

    public function providePermissions()
    {
        return array(
            'EDIT_FLATFEESHIPPING' => 'Edit Flat Fee Shipping',
        );
    }

    public function canEdit($member = null)
    {
        return Permission::check('EDIT_FLATFEESHIPPING');
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canDelete($member = null)
    {
        return Permission::check('EDIT_FLATFEESHIPPING');
    }

    public function canCreate($member = null)
    {
        return Permission::check('EDIT_FLATFEESHIPPING');
    }
	
	/**
	 * Field for editing a {@link FlatFeeShippingRate}.
	 * 
	 * @return FieldSet
	 */
	public function getCMSFields() {

		return new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('ShippingRate',
					TextField::create('Title', _t('FlatFeeShippingRate.TITLE', 'Title')),
					TextField::create('Description', _t('FlatFeeShippingRate.DESCRIPTION', 'Description'))
						->setRightTitle('Label used in checkout form.'),
					DropdownField::create('CountryID', _t('FlatFeeShippingRate.COUNTRY', 'Country'), Country_Shipping::get()->map()->toArray()),
					PriceField::create('Price')
				)
			)
		);
	}
	
	/**
	 * Label for using on {@link FlatFeeShippingModifierField}s.
	 * 
	 * @see FlatFeeShippingModifierField
	 * @return String
	 */
	public function Label() {
		return $this->Description . ' - ' . $this->Price()->Nice();
	}
	
	/**
	 * Summary of the current tax rate
	 * 
	 * @return String
	 */
	public function SummaryOfPrice() {
		return $this->Amount()->Nice();
	}

	public function Amount() {

		// TODO: Multi currency

		$shopConfig = ShopConfig::current_shop_config();

		$amount = new Price();
		$amount->setAmount($this->Price);
		$amount->setCurrency($shopConfig->BaseCurrency);
		$amount->setSymbol($shopConfig->BaseCurrencySymbol);
		
		$this->extend('updateAmount', $amount);
		
		return $amount;
	}

	/**
	 * Display price, can decorate for multiple currency etc.
	 * 
	 * @return Price
	 */
	public function Price() {
		
		$amount = $this->Amount();
		$this->extend('updatePrice', $amount);
		return $amount;
	}
	
}

/**
 * So that {@link FlatFeeShippingRate}s can be created in {@link SiteConfig}.
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage shipping
 */
class FlatFeeShippingRate_Extension extends DataExtension {

	/**
	 * Attach {@link FlatFeeShippingRate}s to {@link SiteConfig}.
	 * 
	 * @see DataObjectDecorator::extraStatics()
	 */
	private static $has_many = array(
		'FlatFeeShippingRates' => 'FlatFeeShippingRate'
	);

}

class FlatFeeShippingRate_Admin extends ShopAdmin {

	private static $tree_class = 'ShopConfig';
	
	private static $allowed_actions = array(
		'FlatFeeShippingSettings',
		'FlatFeeShippingSettingsForm',
		'saveFlatFeeShippingSettings'
	);

	private static $url_rule = 'ShopConfig/FlatFeeShipping';
	protected static $url_priority = 110;
	private static $menu_title = 'Shop Flat Fee Shipping Rates';

	private static $url_handlers = array(
		'ShopConfig/FlatFeeShipping/FlatFeeShippingSettingsForm' => 'FlatFeeShippingSettingsForm',
		'ShopConfig/FlatFeeShipping' => 'FlatFeeShippingSettings'
	);

	public function init() {
		parent::init();
		$this->modelClass = 'ShopConfig';
	}

	public function Breadcrumbs($unlinked = false) {

		$request = $this->getRequest();
		$items = parent::Breadcrumbs($unlinked);

		if ($items->count() > 1) $items->remove($items->pop());

		$items->push(new ArrayData(array(
			'Title' => 'Flat Fee Shipping',
			'Link' => $this->Link(Controller::join_links($this->sanitiseClassName($this->modelClass), 'FlatFeeShipping'))
		)));

		return $items;
	}

	public function SettingsForm($request = null) {
		return $this->FlatFeeShippingSettingsForm();
	}

	public function FlatFeeShippingSettings($request) {

		if ($request->isAjax()) {
			$controller = $this;
			$responseNegotiator = new PjaxResponseNegotiator(
				array(
					'CurrentForm' => function() use(&$controller) {
						return $controller->FlatFeeShippingSettingsForm()->forTemplate();
					},
					'Content' => function() use(&$controller) {
						return $controller->renderWith('ShopAdminSettings_Content');
					},
					'Breadcrumbs' => function() use (&$controller) {
						return $controller->renderWith('CMSBreadcrumbs');
					},
					'default' => function() use(&$controller) {
						return $controller->renderWith($controller->getViewer('show'));
					}
				),
				$this->response
			); 
			return $responseNegotiator->respond($this->getRequest());
		}

		return $this->renderWith('ShopAdminSettings');
	}

	public function FlatFeeShippingSettingsForm() {

		$shopConfig = ShopConfig::get()->First();

		$fields = new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('Shipping',
					GridField::create(
						'FlatFeeShippingRates',
						'FlatFeeShippingRates',
						$shopConfig->FlatFeeShippingRates(),
						GridFieldConfig_HasManyRelationEditor::create()
					)
				)
			)
		);

		$actions = new FieldList();
		$actions->push(FormAction::create('saveFlatFeeShippingSettings', _t('GridFieldDetailForm.Save', 'Save'))
			->setUseButtonTag(true)
			->addExtraClass('ss-ui-action-constructive')
			->setAttribute('data-icon', 'add'));

		$form = new Form(
			$this,
			'EditForm',
			$fields,
			$actions
		);

		$form->setTemplate('ShopAdminSettings_EditForm');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm');
		$form->addExtraClass('cms-content cms-edit-form center ss-tabset');
		if($form->Fields()->hasTabset()) $form->Fields()->findOrMakeTab('Root')->setTemplate('CMSTabSet');
		$form->setFormAction(Controller::join_links($this->Link($this->sanitiseClassName($this->modelClass)), 'FlatFeeShipping/FlatFeeShippingSettingsForm'));

		$form->loadDataFrom($shopConfig);

		return $form;
	}

	public function saveFlatFeeShippingSettings($data, $form) {

		//Hack for LeftAndMain::getRecord()
		self::$tree_class = 'ShopConfig';

		$config = ShopConfig::get()->First();
		$form->saveInto($config);
		$config->write();
		$form->sessionMessage('Saved Flat Fee Shipping Settings', 'good');

		$controller = $this;
		$responseNegotiator = new PjaxResponseNegotiator(
			array(
				'CurrentForm' => function() use(&$controller) {
					//return $controller->renderWith('ShopAdminSettings_Content');
					return $controller->FlatFeeShippingSettingsForm()->forTemplate();
				},
				'Content' => function() use(&$controller) {
					//return $controller->renderWith($controller->getTemplatesWithSuffix('_Content'));
				},
				'Breadcrumbs' => function() use (&$controller) {
					return $controller->renderWith('CMSBreadcrumbs');
				},
				'default' => function() use(&$controller) {
					return $controller->renderWith($controller->getViewer('show'));
				}
			),
			$this->response
		); 
		return $responseNegotiator->respond($this->getRequest());
	}

	public function getSnippet() {

		if (!$member = Member::currentUser()) return false;
		if (!Permission::check('CMS_ACCESS_' . get_class($this), 'any', $member)) return false;

		return $this->customise(array(
			'Title' => 'Flat Fee Shipping Management',
			'Help' => 'Create flat fee shipping rates',
			'Link' => Controller::join_links($this->Link('ShopConfig'), 'FlatFeeShipping'),
			'LinkTitle' => 'Edit flat fee shipping rates'
		))->renderWith('ShopAdmin_Snippet');
	}

}
