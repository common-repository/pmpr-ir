<?php

namespace Pmpr\Plugin\Pmpr\Traits;

use Pmpr\Plugin\Pmpr\Manager;

/**
 * Traits ManagerTrait
 * @package Pmpr\Plugin\Pmpr\Traits
 */
trait ManagerTrait
{
	/**
	 * @var Manager|null
	 */
	public ?Manager $manager = null;

    /**
     * @return Manager
     */
	public function getManager(): Manager
	{
		if (!$this->manager) {

			$this->manager = Manager::getInstance();
		}

		return $this->manager;
	}
}