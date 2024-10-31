<?php

namespace Pmpr\Plugin\Pmpr\Component;

use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use Pmpr\Plugin\Pmpr\Queue;

/**
 * Class Process
 * @package Pmpr\Plugin\Pmpr\Component
 */
class Process extends Queue
{
    const MIDNIGHT_UPDATE_JOB = Constants::PLUGIN_PREFIX . '_midnight_update';
    const INSTALL_REQUIRED_COMMONS = Constants::PLUGIN_PREFIX . '_install_required_commons';
    const CHECK_COMPONENTS_UPDATE_JOB = Constants::PLUGIN_PREFIX . '_check_components_update';

    public function __construct()
    {
        $this->group .= 'components';

        parent::__construct();
    }

    /**
     * Add a recurring job for checking components update.
     *
     * @return int
     */
    public function scheduleComponentsUpdateCheckerJob(): int
    {
        return $this->scheduleRecurring(time(), DAY_IN_SECONDS, self::CHECK_COMPONENTS_UPDATE_JOB);
    }

    /**
     * @return int
     */
    public function scheduleMidnightUpdateSingleJob(): int
    {
        $return = 0;

        if (!$this->checkMidnightUpdateSingleExists()) {

            $return = $this->scheduleSingle(strtotime('tomorrow midnight'), self::MIDNIGHT_UPDATE_JOB);
        }

        return $return;
    }

    /**
     * @return int
     */
    public function addInstallRequiredCommonsAsyncJob(): int
    {
        $return = 0;

        if (!$this->checkInstallRequiredCommonsAsyncExists()) {

            $return = $this->addAsync(self::INSTALL_REQUIRED_COMMONS);
        }

        return $return;
    }

    /**
     * @return bool
     */
    public function checkMidnightUpdateSingleExists()
    {
        return $this->exists([
            Constants::HOOK   => self::MIDNIGHT_UPDATE_JOB,
            Constants::STATUS => [Constants::PENDING, Constants::IN_PROGRESS],
        ]);
    }

    /**
     * @return bool
     */
    public function checkInstallRequiredCommonsAsyncExists()
    {
        return $this->exists([
            Constants::HOOK   => self::INSTALL_REQUIRED_COMMONS,
            Constants::STATUS => [Constants::PENDING, Constants::IN_PROGRESS],
        ]);
    }
}