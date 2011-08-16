<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Kamil Kuzminski 2011 
 * @author     Kamil Kuzminski <http://qzminski.com> 
 * @package    IsotopeWishlist 
 * @license    GNU/LGPL 
 * @filesource
 */


/**
 * Class ModuleIsotopeWishlistEmail 
 *
 * @copyright  Kamil Kuzminski 2011 
 * @author     Kamil Kuzminski <http://qzminski.com> 
 * @package    Controller
 */
class ModuleIsotopeWishlistEmail extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_wishlistemail';

	/**
	 * Disable caching of the frontend page if this module is in use.
	 * @var bool
	 */
	protected $blnDisableCache = true;


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: WISHLIST ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = $this->Environment->script.'?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		$this->import('Isotope');
		$this->import('IsotopeWishlist');
		$this->IsotopeWishlist->initializeWishlist((int) $this->Isotope->Config->id, (int) $this->Isotope->Config->store_id);
		
		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		$arrProducts = $this->IsotopeWishlist->getProducts();

		if (!count($arrProducts))
		{
			$this->Template->empty = true;
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInWishlist'];
			return;
		}
		
		// Prepare fields
		$arrFields = array
		(
			'email' => array
			(
				'name'      => 'email',
				'label'     => $GLOBALS['TL_LANG']['MSC']['wishlistEmail'],
				'inputType' => 'text',
				'eval'      => array('mandatory'=>true, 'rgxp'=>'email')
			)
		);
		
		$objForm = new HasteForm($this->id, $arrFields);
		$objForm->submit = $GLOBALS['TL_LANG']['MSC']['sendWishlist'];
		
		// Add captcha
		if (!$this->disableCaptcha)
		{
			$objForm->addCaptcha();
		}

		// Send wishlist
		if ($objForm->validate())
		{
			$arrData = array
			(
				'items'			=> $this->IsotopeWishlist->items,
				'products'		=> $this->IsotopeWishlist->products,
				'subTotal'		=> $this->Isotope->formatPriceWithCurrency($this->IsotopeWishlist->subTotal, false),
				'taxTotal'		=> $this->Isotope->formatPriceWithCurrency($this->IsotopeWishlist->taxTotal, false),
				'shippingPrice'	=> $this->Isotope->formatPriceWithCurrency($this->IsotopeWishlist->Shipping->price, false),
				'paymentPrice'	=> $this->Isotope->formatPriceWithCurrency($this->IsotopeWishlist->Payment->price, false),
				'grandTotal'	=> $this->Isotope->formatPriceWithCurrency($this->IsotopeWishlist->grandTotal, false),
				'cart_text'		=> strip_tags($this->replaceInsertTags($this->IsotopeWishlist->getProducts('iso_products_text'))),
				'cart_html'		=> $this->replaceInsertTags($this->IsotopeWishlist->getProducts('iso_products_html')),
			);
			
			$this->Isotope->sendMail($this->iso_mail_customer, $objForm->fetchSingle('email'), $this->language, $arrData);
			
			$this->jumpToOrReload($this->iso_wishlist_jumpTo);
		}
		
		$objForm->addFormToTemplate($this->Template);
	}
}

?>