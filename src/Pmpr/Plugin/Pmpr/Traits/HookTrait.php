<?php

namespace Pmpr\Plugin\Pmpr\Traits;

/**
 * Traits HookTrait
 * @package Pmpr\Plugin\Pmpr\Traits
 */
trait HookTrait
{
	/**
	 * @param $tag
	 * @param ...$args
	 *
	 * @return $this
	 */
	final public function doAction($tag, ...$args): self
	{
		do_action($tag, ...$args);

		return $this;
	}

	/**
	 * @param $tag
	 * @param ...$args
	 *
	 * @return mixed
	 */
    final public function applyFilters($tag, ...$args)
	{
		return apply_filters($tag, ...$args);
	}

	/**
	 * @param     $tag
	 * @param     $callback
	 * @param int $priority
	 * @param int $args
	 *
	 * @return $this
	 */
    final public function addAction($tag, $callback, $priority = 10, $args = 1): self
	{
		add_action($tag, $callback, $priority, $args);

		return $this;
	}

	/**
	 * @param     $tag
	 * @param     $callback
	 * @param int $priority
	 *
	 * @return $this
	 */
    final public function removeAction($tag, $callback, $priority = 10): self
	{
		remove_action($tag, $callback, $priority);

		return $this;
	}

    /**
     * @param $tag
     * @param $callback
     * @param $priority
     * @param $args
     * @param $target
     *
     * @return $this
     */
    final public function addAjaxAction($tag, $callback, $priority = 10, $args = 1, $target = 'private'): self
	{
		if (in_array($target, ['private', 'both'])) {

			$this->addAction("wp_ajax_{$tag}", $callback, $priority, $args);
		}
		if (in_array($target, ['public', 'both'])) {

			$this->addAction("wp_ajax_nopriv_{$tag}", $callback, $priority, $args);
		}

		return $this;
	}

	/**
	 * @param     $tag
	 * @param     $callback
	 * @param int $priority
	 * @param int $args
	 *
	 * @return $this
	 */
    final public function addFilter($tag, $callback, $priority = 10, $args = 1): self
	{
		add_filter($tag, $callback, $priority, $args);

		return $this;
	}

	/**
	 * @param     $tag
	 * @param     $callback
	 * @param int $priority
	 *
	 * @return $this
	 */
    final public function removeFilter($tag, $callback, $priority = 10): self
	{
		remove_filter($tag, $callback, $priority);

		return $this;
	}
}