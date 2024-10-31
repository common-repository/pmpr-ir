<?php

namespace Pmpr\Plugin\Pmpr\Container;

use Pmpr\Plugin\Pmpr\Container\Traits\SingletonTrait;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use Pmpr\Plugin\Pmpr\Traits\APITrait;
use Pmpr\Plugin\Pmpr\Traits\HelperTrait;
use Pmpr\Plugin\Pmpr\Traits\HookTrait;

/**
 * Class Container
 * @package Pmpr\Plugin\Pmpr\Container
 */
class Container
{
    const REMOTE_TAB_CONTENT = Constants::PLUGIN_PREFIX . 'remote_tab_content_transient';

    use APITrait,
        HookTrait,
        HelperTrait,
		SingletonTrait;

	public function __construct()
	{
		$this->setup();
	}

	public function setup()
	{
		$this->addActions();
		$this->addFilters();
	}

	public function addActions()
	{

	}

	public function addFilters()
	{

	}

	/**
	 * @param string $target
	 *
	 * @return string|null
	 */
	public function getTranslation(string $target): ?string
	{
		$result = null;
		switch ($target) {
			case Constants::COMMONS_INSTALLATION_PROBLEM:
				$result = __('There is a problem in commons installation. Please refresh the page to try again. Contact us, if the problem remain.', PR__PLG__PMPR);
				break;
		}

		return $result;
	}
}