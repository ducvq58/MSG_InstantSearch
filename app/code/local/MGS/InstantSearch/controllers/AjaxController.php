<?php
class MGS_InstantSearch_AjaxController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
    {
        $this->loadLayout('search');
		$this->renderLayout();
    }
}