<?php

namespace Pmpr\Plugin\Pmpr;

use ActionScheduler;
use ActionScheduler_Store;
use DateTime;
use Exception;
use Pmpr\Plugin\Pmpr\Container\Container;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Queue
 *
 * @package Pmpr\Plugin\Pmpr
 */
class Queue extends Container
{
    /**
     * @var string
     */
    protected string $group = 'pmpr-ir';

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * Enqueue an action to run one time, as soon as possible
     *
     * @param string $hook The hook to trigger.
     * @param array $args Arguments to pass when the hook triggers.
     *
     * @return int The action ID.
     */
    public function addAsync(string $hook, array $args = []): int
    {
        try {
            if (function_exists('as_enqueue_async_action')) {
                return as_enqueue_async_action($hook, $args, $this->getGroup());
            }
            return $this->scheduleSingle(time() + MINUTE_IN_SECONDS, $hook, $args);
        } catch (Exception $exception) {

            $this->exceptionLog($exception);
            return 0;
        }
    }

    /**
     * @param int $timestamp
     * @param string $hook
     * @param array $args
     *
     * @return int
     */
    public function scheduleSingle(int $timestamp, string $hook, array $args = []): int
    {
        try {

            return as_schedule_single_action($timestamp, $hook, $args, $this->getGroup());
        } catch (Exception $exception) {

            $this->exceptionLog($exception);
            return 0;
        }
    }


    /**
     * Schedule a recurring action
     *
     * @param int $timestamp When the first instance of the job will run.
     * @param int $intervalInSeconds How long to wait between runs.
     * @param string $hook The hook to trigger.
     * @param array $args Arguments to pass when the hook triggers.
     *
     * @return int The action ID.
     */
    public function scheduleRecurring(int $timestamp, int $intervalInSeconds, string $hook, array $args = []): int
    {
        if ($this->isScheduled($hook, $args)) {

            // TODO: When v3.3.0 from Action Scheduler is commonly used use the array notation for status to reduce search queries to one.
            $pendingActions = $this->search([
                Constants::HOOK   => $hook,
                Constants::STATUS => Constants::PENDING,
            ], Constants::IDS);

            if (1 < count($pendingActions)) {

                $this->cancelAll($hook, $args);
                return 0;
            }

            $runningActions = $this->search([
                Constants::HOOK   => $hook,
                Constants::STATUS => Constants::IN_PROGRESS,
            ], Constants::IDS);

            if (1 === count($pendingActions) + count($runningActions)) {

                return 0;
            }

            $this->cancelAll($hook, $args);
        }

        try {

            return as_schedule_recurring_action($timestamp, $intervalInSeconds, $hook, $args, $this->getGroup());
        } catch (Exception $exception) {

            $this->exceptionLog($exception);
            return 0;
        }
    }

    /**
     * Checks if the hook is scheduled.
     *
     * @param string $hook The hook to check.
     * @param array $args Passed arguments.
     *
     * @return bool
     */
    public function isScheduled(string $hook, array $args = []): bool
    {
        if (!function_exists('as_has_scheduled_action')) {

            return !is_null($this->getNext($hook, $args));
        }

        try {

            return as_has_scheduled_action($hook, $args, $this->getGroup());
        } catch (Exception $exception) {

            $this->exceptionLog($exception);
            return false;
        }
    }

    /**
     * @param string $hook
     * @param array $args
     *
     * @return DateTime|bool
     */
    public function getScheduleDueTime(string $hook, array $args = [])
    {
        $query = [
            'hook'     => $hook,
            'status'   => Constants::PENDING,
            'group'    => $this->getGroup(),
            'orderby'  => 'none',
            'per_page' => 1,
        ];

        if ($args) {

            $query['args'] = $args;
        }

        $due     = false;
        try {

            $store = $this->getStore();
            if ($actionID = $store->query_action($query)) {

                $due = $store->get_date($actionID);
            }
        } catch (Exception $exception) {

        }

        return $due;
    }

    /**
     * Schedule an action that recurs on a cron-like schedule.
     *
     * @param int $timestamp The schedule will start on or after this time.
     * @param string $cronSchedule A cron-link schedule string.
     * @param string $hook The hook to trigger.
     * @param array $args Arguments to pass when the hook triggers.
     * @return int The action ID
     * @see http://en.wikipedia.org/wiki/Cron
     *   *    *    *    *    *    *
     *   ┬    ┬    ┬    ┬    ┬    ┬
     *   |    |    |    |    |    |
     *   |    |    |    |    |    + year [optional]
     *   |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
     *   |    |    |    +---------- month (1 - 12)
     *   |    |    +--------------- day of month (1 - 31)
     *   |    +-------------------- hour (0 - 23)
     *   +------------------------- min (0 - 59)
     */
    public function scheduleCron(int $timestamp, string $cronSchedule, string $hook, array $args = [])
    {
        if ($this->isScheduled($hook, $args)) {

            return '';
        }

        try {

            return as_schedule_cron_action($timestamp, $cronSchedule, $hook, $args, $this->getGroup());
        } catch (Exception $exception) {

            $this->exceptionLog($exception);
            return 0;
        }
    }

    /**
     * Dequeue the next scheduled instance of an action with a matching hook (and optionally matching args and group).
     *
     * Any recurring actions with a matching hook should also be cancelled, not just the next scheduled action.
     *
     * While technically only the next instance of a recurring or cron action is unscheduled by this method, that will also
     * prevent all future instances of that recurring or cron action from being run. Recurring and cron actions are scheduled
     * in a sequence instead of all being scheduled at once. Each successive occurrence of a recurring action is scheduled
     * only after the former action is run. As the next instance is never run, because it's unscheduled by this function,
     * then the following instance will never be scheduled (or exist), which is effectively the same as being unscheduled
     * by this method also.
     *
     * @param string $hook The hook that the job will trigger.
     *
     * @param array $args Args that would have been passed to the job.
     */
    public function cancel(string $hook, array $args = [])
    {
        try {

            as_unschedule_action($hook, $args, $this->group);
        } catch (Exception $exception) {

            $this->exceptionLog($exception);
        }
    }

    /**
     * Dequeue all actions with a matching hook (and optionally matching args and group) so no matching actions are ever run.
     *
     * @param string $hook The hook that the job will trigger.
     * @param array $args Args that would have been passed to the job.
     */
    public function cancelAll(string $hook, array $args = [])
    {
        try {

            as_unschedule_all_actions($hook, $args, $this->group);
        } catch (Exception $exception) {

            $this->exceptionLog($exception);
        }
    }

    /**
     * Get the date and time for the next scheduled occurence of an action with a given hook
     * (an optionally that matches certain args and group), if any.
     *
     * @param string $hook The hook that the job will trigger.
     * @param array $args Filter to a hook with matching args that will be passed to the job when it runs.
     * @return int|null The date and time for the next occurrence, or null if there is no pending, scheduled action for the given hook.
     */
    public function getNext(string $hook, array $args = null)
    {
        try {
            $nextTimestamp = as_next_scheduled_action($hook, $args, $this->group);

            if (is_numeric($nextTimestamp)) {

                return $nextTimestamp;
            }
            return null;
        } catch (Exception $exception) {

            $this->exceptionLog($exception);
            return null;
        }
    }

    /**
     * Find scheduled actions
     *
     * @param array $args Possible arguments, with their default values:
     *        'hook' => '' - the name of the action that will be triggered
     *        'args' => null - the args array that will be passed with the action
     *        'date' => null - the scheduled date of the action. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime(). Used in UTC timezone.
     *        'date_compare' => '<=' - operator for testing "date". accepted values are '!=', '>', '>=', '<', '<=', '='
     *        'modified' => null - the date the action was last updated. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime(). Used in UTC timezone.
     *        'modified_compare' => '<=' - operator for testing "modified". accepted values are '!=', '>', '>=', '<', '<=', '='
     *        'group' => '' - the group the action belongs to
     *        'status' => '' - ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING
     *        'claimed' => null - TRUE to find claimed actions, FALSE to find unclaimed actions, a string to find a specific claim ID
     *        'per_page' => 5 - Number of results to return
     *        'offset' => 0
     *        'orderby' => 'date' - accepted values are 'hook', 'group', 'modified', or 'date'
     *        'order' => 'ASC'.
     *
     * @param string $output OBJECT, ARRAY_A, or ids.
     *
     * @return array
     */
    public function search(array $args = [], string $output = OBJECT): array
    {
        try {

            return as_get_scheduled_actions($args, $output);
        } catch (Exception $exception) {

            $this->exceptionLog($exception);
            return [];
        }
    }

    /**
     * @param array $args
     *
     * @return bool
     */
    public function exists(array $args): bool
    {
        $rowFound = $this->search($args, Constants::IDS);

        return count($rowFound) > 0;
    }

    /**
     * @return ActionScheduler_Store
     */
    public function getStore(): ActionScheduler_Store
    {
        return ActionScheduler::store();
    }

    /**
     * @param Exception $exception
     */
    public function exceptionLog(Exception $exception)
    {
        error_log($exception->getMessage() . ' Action Scheduler Queue', LOG_ALERT);
    }
}