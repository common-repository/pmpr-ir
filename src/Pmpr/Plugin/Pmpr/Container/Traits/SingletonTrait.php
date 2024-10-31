<?php

namespace Pmpr\Plugin\Pmpr\Container\Traits;

use Pmpr\Plugin\Pmpr\Container\Storage;

/**
 * Traits SingletonTrait
 * @package Pmpr\Plugin\Pmpr\Container\Traits
 */
trait SingletonTrait
{
	/**
	 * @param ...$args
	 *
	 * @return static
	 */
	public static function getInstance(...$args): self
	{
		return Storage::get(static::class, null, ...$args);
	}

	/**
	 * @param string|null $id
	 * @param ...$args
	 *
	 * @return static|null
	 */
	public static function getInstanceByKey(?string $id = null, ...$args): ?self
	{
		return Storage::get(static::class, $id, ...$args);
	}

}