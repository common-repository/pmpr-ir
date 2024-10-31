<?php

namespace Pmpr\Plugin\Pmpr\Component;

use Pmpr\Plugin\Pmpr\Component\ListTable\Custom\Install;
use Pmpr\Plugin\Pmpr\Component\ListTable\Custom\Installed;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Custom
 * @package Pmpr\Plugin\Pmpr\Component
 */
class Custom extends Base
{
	/**
	 * Package constructor.
	 */
	public function __construct()
	{
		$this->args = [
			Constants::TYPE     => Constants::CST,
			Constants::NAME     => Constants::CUSTOM,
			Constants::POSITION => 6,
		];

		parent::__construct();
	}

	public function setTranslations()
	{
		parent::setTranslations();

		$this->addArg(Constants::PLURAL_NAME, __('Customs', PR__PLG__PMPR))
			 ->addArg(Constants::SINGULAR_NAME, __('Custom', PR__PLG__PMPR))
			->addArg(Constants::NOT_FOUND, sprintf(__('no %s founded for your domain.', PR__PLG__PMPR), __('Custom', PR__PLG__PMPR)));
	}

	public function initListTables()
	{
		$args = $this->getArgs();

		$this->install   = new Install($args);
		$this->installed = new Installed($args);
	}
}