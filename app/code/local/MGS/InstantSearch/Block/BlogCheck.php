<?php

class MGS_InstantSearch_Block_Blogcheck extends Mage_Adminhtml_Block_Template {
	
	public function _prepareLayout()
	{
	    if (Mage::app()->getRequest()->getParam('section') == 'instantsearch') {
	        echo "string";	    
	    }
	    return parent::_prepareLayout();
	}
}

?>