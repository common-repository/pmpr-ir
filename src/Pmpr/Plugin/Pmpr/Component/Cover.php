<?php

namespace Pmpr\Plugin\Pmpr\Component;

use Pmpr\Plugin\Pmpr\Component\ListTable\Cover\Install;
use Pmpr\Plugin\Pmpr\Component\ListTable\Cover\Installed;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Cover
 * @package Pmpr\Plugin\Pmpr\Component
 */
class Cover extends Base
{
	/**
	 * Cover constructor.
	 */
	public function __construct()
	{
		$this->args = [
			Constants::TYPE     => Constants::CVR,
			Constants::NAME     => Constants::COVER,
			Constants::POSITION => 5,
		];

		parent::__construct();
	}

	public function setTranslations()
	{
		parent::setTranslations();
		$this->addArg(Constants::PLURAL_NAME, __('Covers', PR__PLG__PMPR))
			 ->addArg(Constants::SINGULAR_NAME, __('Cover', PR__PLG__PMPR))
			 ->addArg(Constants::ALTERNATIVE_NAME, __('Theme', PR__PLG__PMPR))
			 ->addArg(Constants::NOT_FOUND, sprintf(__('Wordpress %s will be available here to get installed, very soon.', PR__PLG__PMPR), __('Covers', PR__PLG__PMPR)));
	}

	public function initListTables()
	{
		$args = $this->getArgs();

		$this->install   = new Install($args);
		$this->installed = new Installed($args);
	}
}