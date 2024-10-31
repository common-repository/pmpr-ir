<?php

namespace Pmpr\Plugin\Pmpr\Component;

use Pmpr\Plugin\Pmpr\Component\ListTable\Module\Install;
use Pmpr\Plugin\Pmpr\Component\ListTable\Module\Installed;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Module
 * @package Pmpr\Plugin\Pmpr\Component
 */
class Module extends Base
{
	/**
	 * Module constructor.
	 */
	public function __construct()
	{
		$this->args = [
			Constants::TYPE     => Constants::MDL,
			Constants::NAME     => Constants::MODULE,
			Constants::POSITION => 0,
		];

		parent::__construct();
	}

	public function setTranslations()
	{
		parent::setTranslations();
		$this->addArg(Constants::PLURAL_NAME, __('Modules', PR__PLG__PMPR))
			 ->addArg(Constants::SINGULAR_NAME, __('Module', PR__PLG__PMPR))
			 ->addArg(Constants::ALTERNATIVE_NAME, __('Plugin', PR__PLG__PMPR));
	}

	public function initListTables()
	{
		$args = $this->getArgs();

		$this->install   = new Install($args);
		$this->installed = new Installed($args);
	}
}