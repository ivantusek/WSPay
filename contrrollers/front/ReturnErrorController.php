<?php
class ReturnErrorController extends FrontController
{
	public $php_self = 'return-error';
	public $page_name = 'return-error';
	public $ssl = true;

	//funkcija za postavljanje css i js
	public function setMedia()
	{
		parent::setMedia();

	}

	//funkcija kontent
	public function initContent()
	{
		parent::initContent();

		//dodavanje na stranicu
		$this->context->smarty->assign(array('HOOK_HOME' => Hook::exec('displayHome'),
				'HOOK_HOME_TAB' => Hook::exec('displayHomeTab'),
				'HOOK_HOME_TAB_CONTENT' => Hook::exec('displayHomeTabContent')
		));
		
		
		
		//postavljanje template-a za url
		$this->setTemplate(_PS_THEME_DIR_.'return-error.tpl');


	}

}
