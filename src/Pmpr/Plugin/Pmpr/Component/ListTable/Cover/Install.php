<?php

namespace Pmpr\Plugin\Pmpr\Component\ListTable\Cover;

use Pmpr\Plugin\Pmpr\Component\ListTable\Install as BaseClass;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Install
 * @package Pmpr\Plugin\Pmpr\Component\ListTable\Cover
 */
class Install extends BaseClass
{
	/**
	 * @param $tabs
	 * @return mixed
	 */
	public function getTabs($tabs)
	{
		$tabs[Constants::DEDICATED] = __('Dedicated', PR__PLG__PMPR);

		return $tabs;
	}
}