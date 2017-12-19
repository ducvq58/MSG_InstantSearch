<?php

class MGS_InstantSearch_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('instantsearch/general/enable', $store);
    }

	public function getLimit($store = null)
    {
        return (int) Mage::getStoreConfig('instantsearch/general/number_of_results', $store);
    }

    public function getViewMoreLabel($store = null)
    {
        return Mage::getStoreConfig('instantsearch/general/view_more_label', $store);
    }

    public function isProductSearchEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('instantsearch/product_search/enable', $store);
    }

    public function getProductDropdownPosition($store = null)
    {
        return Mage::getStoreConfig('instantsearch/product_search/dropdown_position', $store);
    }

    public function showShortDescription($store = null)
    {
        return Mage::getStoreConfigFlag('instantsearch/product_search/show_short_description', $store);
    }

    public function showReview($store = null)
    {
        return Mage::getStoreConfigFlag('instantsearch/product_search/show_review', $store);
    }

    public function isCategorySearchEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('instantsearch/category_search/enable', $store);
    }

    public function getCategoryDropdownPosition($store = null)
    {
        return Mage::getStoreConfig('instantsearch/category_search/dropdown_position', $store);
    }

    public function isCmsSearchEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('instantsearch/cms_page_search/enable', $store);
    }

    public function getCmsDropdownPosition($store = null)
    {
        return Mage::getStoreConfig('instantsearch/cms_page_search/dropdown_position', $store);
    }


	public function isBlogSearchEnabled($store = null)
    {
    	$isBlogEnable = Mage::getConfig()->getModuleConfig('AW_Blog')->is('active', 'true');
    	if ($isBlogEnable){
    		return Mage::getStoreConfigFlag('instantsearch/blog_search/enable', $store);
    	}
    	else{
    		return false;
    	}
    }
        

    public function getBlogDropdownPosition($store = null)
    {
        return Mage::getStoreConfig('instantsearch/blog_search/dropdown_position', $store);
    }

}    