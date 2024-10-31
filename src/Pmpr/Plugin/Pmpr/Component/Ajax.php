<?php

namespace Pmpr\Plugin\Pmpr\Component;

use Pmpr\Plugin\Pmpr\Container\Container;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Ajax
 * @package Pmpr\Plugin\Pmpr\Component
 */
class Ajax extends Container
{
	const SEARCH_COMPONENTS           = Constants::PLUGIN_PREFIX . '_search_components';
	const SEARCH_INSTALLED_COMPONENTS = Constants::PLUGIN_PREFIX . '_search_installed_components';

	const ACTIONS = [
		'search_component'           => self::SEARCH_COMPONENTS,
		'search_installed_component' => self::SEARCH_INSTALLED_COMPONENTS,
	];

	public function addActions()
	{
		$this->addAjaxAction(self::SEARCH_COMPONENTS, [$this, 'searchComponents'])
			 ->addAjaxAction(self::SEARCH_INSTALLED_COMPONENTS, [$this, 'searchInstalledComponents']);
	}

	public function searchComponents()
	{
		$result  = '';
		$filters = '';

		if ($object = $this->getObjectByType()) {

			$object->initListTables();

			$tableObj = $object->getInstall();
			if ($tableObj) {

				$tableObj->prepare_items();
				ob_start();
				$tableObj->display();

				$result = ob_get_clean();

				$filters = $tableObj->get_filters();
			}
		}

        $this->getHelper()->getServer()->ajaxResponse([
			'result'  => $result,
			'filters' => $filters,
		], true, false);

	}

	public function searchInstalledComponents()
	{
		$table    = '';
		$search   = '';

		if ($object = $this->getObjectByType()) {

			$object->initListTables();

			$tableObj = $object->getInstalled();
			if ($tableObj) {

				$tableObj->prepare_items();
				ob_start();
				$tableObj->display();

				$table  = ob_get_clean();
				$search = $tableObj->search_result(false);
			}
		}

        $this->getHelper()->getServer()->ajaxResponse([
			'table'  => $table,
			'search' => $search,
		], true, false);
	}

	/**
	 * @param null $type
	 *
	 * @return Base|null
	 */
	public function getObjectByType($type = null): ?Base
	{
		if (!$type) {

			$type = $this->getHelper()->getServer()->getPost(Constants::TYPE, Constants::MODULE);
		}
		$object = null;
		switch ($type) {
			case Constants::MODULE:
				$object = Module::getInstance();
				break;
			case Constants::COVER:
				$object = Cover::getInstance();
				break;
			case Constants::CUSTOM:
				$object = Custom::getInstance();
				break;
		}

		return $object;
	}
}