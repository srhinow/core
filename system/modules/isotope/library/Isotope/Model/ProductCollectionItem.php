<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model;


/**
 * ProductCollectionItem represents an item in a product collection.
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class ProductCollectionItem extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_item';

    /**
     * Cache the current product
     * @var IsotopeProduct
     */
    protected $objProduct;

    /**
     * True if product collection is locked
     * @var bool
     */
    protected $blnLocked = false;


    /**
     * Return true if product collection item is locked
     */
    public function isLocked()
    {
        return $this->blnLocked;
    }


    /**
     * Lock item, necessary if product collection is locked
     */
    public function lock()
    {
        $this->blnLocked = true;
        $this->objProduct = null;
    }


    /**
     * Get the product related to this item
     * @return IsotopeProduct|null
     */
    public function getProduct()
    {
        if (null === $this->objProduct) {

            $strClass = $GLOBALS['ISO_PRODUCT'][$this->type]['class'];

            if ($strClass == '' || class_exists($strClass)) {
                $strClass = 'Isotope\Product\Standard';
            }

            $arrData = array('sku'=>$this->sku, 'name'=>$this->name, 'price'=>$this->price, 'tax_free_price'=>$this->tax_free_price);

            $objProductData = \Database::getInstance()->prepare($strClass::getSelectStatement() . " WHERE p1.language='' AND p1.id=?")
                                                      ->execute($this->product_id);

            if ($objProductData->numRows) {
                $arrData = $this->blnLocked ? array_merge($objProductData->row(), $arrData) : $objProductData->row();
            }

            $this->objProduct = new $strClass($arrData, deserialize($this->options), $this->blnLocked, $this->quantity);
            $this->objProduct->collection_id = $this->id;
            $this->objProduct->tax_id = $this->tax_id;
            $this->objProduct->reader_jumpTo_Override = $this->href_reader;
        }

        return $this->objProduct;
    }
}
