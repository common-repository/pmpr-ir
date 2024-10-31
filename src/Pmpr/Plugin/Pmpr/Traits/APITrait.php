<?php

namespace Pmpr\Plugin\Pmpr\Traits;

use Pmpr\Plugin\Pmpr\API;

/**
 * Traits APITrait
 * @package Pmpr\Plugin\Pmpr\Traits
 */
trait APITrait
{
	/**
	 * @var API|null
	 */
	public ?API $api = null;

	/**
	 * @return API|null
	 */
	public function getApi(): ?API
	{
		if (!$this->api) {

			$this->api = API::getInstance();
		}

		return $this->api;
	}
}