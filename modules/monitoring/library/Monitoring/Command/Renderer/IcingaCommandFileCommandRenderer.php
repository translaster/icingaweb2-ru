<?php

namespace Icinga\Module\Monitoring\Command\Renderer;

use Icinga\Module\Monitoring\Command\Instance\DisableNotificationsExpireCommand;
use Icinga\Module\Monitoring\Command\Object\AcknowledgeProblemCommand;
use Icinga\Module\Monitoring\Command\Object\AddCommentCommand;
use Icinga\Module\Monitoring\Command\Object\DeleteCommentCommand;
use Icinga\Module\Monitoring\Command\Object\DeleteDowntimeCommand;
use Icinga\Module\Monitoring\Command\Object\ProcessCheckResultCommand;
use Icinga\Module\Monitoring\Command\Object\RemoveAcknowledgementCommand;
use Icinga\Module\Monitoring\Command\Object\ScheduleServiceCheckCommand;
use Icinga\Module\Monitoring\Command\Object\ScheduleServiceDowntimeCommand;
use Icinga\Module\Monitoring\Command\Object\SendCustomNotificationCommand;
use Icinga\Module\Monitoring\Command\Object\ToggleObjectFeatureCommand;
use Icinga\Module\Monitoring\Command\IcingaCommand;
use InvalidArgumentException;

/**
 * Icinga command renderer for the Icinga command file
 */
class IcingaCommandFileCommandRenderer implements IcingaCommandRendererInterface
{
    /**
     * Escape a command string
     *
     * @param   string $commandString
     *
     * @return  string
     */
    public function escape($commandString)
    {
        return str_replace(array("\r", "\n"), array('\r', '\n'), $commandString);
    }

    /**
     * Render a command
     *
     * @param   IcingaCommand $command
     * @param   int|null $now
     *
     * @return  string
     */
    public function render(IcingaCommand $command, $now = null)
    {
        $renderMethod = 'render' . $command->getName();
        if (! method_exists($this, $renderMethod)) {
            die($renderMethod);
        }
        if ($now === null) {
            $now = time();
        }
        return sprintf('[%u] %s', $now, $this->$renderMethod($command));
    }

    public function renderAddComment(AddCommentCommand $command)
    {
        $object = $command->getObject();
        if ($command->getObject()->getType() === $command::TYPE_HOST) {
            /** @var \Icinga\Module\Monitoring\Object\Host $object */
            $commandString = sprintf(
                'ADD_HOST_COMMENT;%s',
                $object->getHost()
            );
        } else {
            /** @var \Icinga\Module\Monitoring\Object\Service $object */
            $commandString = sprintf(
                'ADD_SVC_COMMENT;%s;%s',
                $object->getHost(),
                $object->getService()
            );
        }
        return sprintf(
            '%s;%u;%s;%s',
            $commandString,
            $command->getPersistent(),
            $command->getAuthor(),
            $command->getComment()
        );
    }

    public function renderSendCustomNotification(SendCustomNotificationCommand $command)
    {
        $object = $command->getObject();
        if ($command->getObject()->getType() === $command::TYPE_HOST) {
            /** @var \Icinga\Module\Monitoring\Object\Host $object */
            $commandString = sprintf(
                'SEND_CUSTOM_HOST_NOTIFICATION;%s',
                $object->getHost()
            );
        } else {
            /** @var \Icinga\Module\Monitoring\Object\Service $object */
            $commandString = sprintf(
                'SEND_CUSTOM_SVC_NOTIFICATION;%s;%s',
                $object->getHost(),
                $object->getService()
            );
        }
        $options = 0; // 0 for no options
        if ($command->getBroadcast() === true) {
            $options |= 1;
        }
        if ($command->getForced() === true) {
            $options |= 2;
        }
        return sprintf(
            '%s;%u;%s;%s',
            $commandString,
            $options,
            $command->getAuthor(),
            $command->getComment()
        );
    }

    public function renderProcessCheckResult(ProcessCheckResultCommand $command)
    {
        $object = $command->getObject();
        if ($command->getObject()->getType() === $command::TYPE_HOST) {
            /** @var \Icinga\Module\Monitoring\Object\Host $object */
            $commandString = sprintf(
                'PROCESS_HOST_CHECK_RESULT;%s',
                $object->getHost()
            );
        } else {
            /** @var \Icinga\Module\Monitoring\Object\Service $object */
            $commandString = sprintf(
                'PROCESS_SVC_CHECK_RESULT;%s;%s',
                $object->getHost(),
                $object->getService()
            );
        }
        $output = $command->getOutput();
        if ($command->getPerformanceData() !== null) {
            $output .= '|' . $command->getPerformanceData();
        }
        return sprintf(
            '%s;%u;%s',
            $commandString,
            $command->getStatus(),
            $output
        );
    }

    public function renderScheduleCheck(ScheduleServiceCheckCommand $command)
    {
        $object = $command->getObject();
        if ($command->getObject()->getType() === $command::TYPE_HOST) {
            /** @var \Icinga\Module\Monitoring\Object\Host $object */
            /** @var \Icinga\Module\Monitoring\Command\Object\ScheduleHostCheckCommand $command */
            if ($command->getOfAllServices() === true) {
                if ($command->getForced() === true) {
                    $commandName = 'SCHEDULE_FORCED_HOST_SVC_CHECKS';
                } else {
                    $commandName = 'SCHEDULE_HOST_SVC_CHECKS';
                }
            } else {
                if ($command->getForced() === true) {
                    $commandName = 'SCHEDULE_FORCED_HOST_CHECK';
                } else {
                    $commandName = 'SCHEDULE_HOST_CHECK';
                }
            }
            $commandString = sprintf(
                '%s;%s',
                $commandName,
                $object->getHost()
            );
        } else {
            /** @var \Icinga\Module\Monitoring\Object\Service $object */
            $commandString = sprintf(
                '%s;%s;%s',
                $command->getForced() === true ? 'SCHEDULE_FORCED_SVC_CHECK' : 'SCHEDULE_SVC_CHECK',
                $object->getHost(),
                $object->getService()
            );
        }
        return sprintf(
            '%s;%u',
            $commandString,
            $command->getCheckTime()
        );
    }

    public function renderScheduleDowntime(ScheduleServiceDowntimeCommand $command)
    {
        $object = $command->getObject();
        if ($command->getObject()->getType() === $command::TYPE_HOST) {
            /** @var \Icinga\Module\Monitoring\Object\Host $object */
            /** @var \Icinga\Module\Monitoring\Command\Object\ScheduleHostDowntimeCommand $command */
            if ($command->getForAllServices() === true) {
                $commandName = 'SCHEDULE_HOST_SVC_DOWNTIME';
            } else {
                $commandName = 'SCHEDULE_HOST_DOWNTIME';
            }
            $commandString = sprintf(
                '%s;%s',
                $commandName,
                $object->getHost()
            );
        } else {
            /** @var \Icinga\Module\Monitoring\Object\Service $object */
            $commandString = sprintf(
                '%s;%s;%s',
                'SCHEDULE_SVC_DOWNTIME',
                $object->getHost(),
                $object->getService()
            );
        }
        return sprintf(
            '%s;%u;%u;%u;%u;%u;%s;%s',
            $commandString,
            $command->getStart(),
            $command->getEnd(),
            $command->getFixed(),
            $command->getTriggerId(),
            $command->getDuration(),
            $command->getAuthor(),
            $command->getComment()
        );
    }

    public function renderAcknowledgeProblem(AcknowledgeProblemCommand $command)
    {
        $object = $command->getObject();
        if ($command->getObject()->getType() === $command::TYPE_HOST) {
            /** @var \Icinga\Module\Monitoring\Object\Host $object */
            $commandString = sprintf(
                '%s;%s',
                $command->getExpireTime() !== null ? 'ACKNOWLEDGE_HOST_PROBLEM_EXPIRE' : 'ACKNOWLEDGE_HOST_PROBLEM',
                $object->getHost()
            );
        } else {
            /** @var \Icinga\Module\Monitoring\Object\Service $object */
            $commandString = sprintf(
                '%s;%s;%s',
                $command->getExpireTime() !== null ? 'ACKNOWLEDGE_SVC_PROBLEM_EXPIRE' : 'ACKNOWLEDGE_SVC_PROBLEM',
                $object->getHost(),
                $object->getService()
            );
        }
        $commandString = sprintf(
            '%s;%u;%u;%u',
            $commandString,
            $command->getSticky(),
            $command->getNotify(),
            $command->getPersistent()
        );
        if ($command->getExpireTime() !== null) {
            $commandString = sprintf(
                '%s;%u',
                $commandString,
                $command->getExpireTime()
            );
        }
        return sprintf(
            '%s;%s;%s',
            $commandString,
            $command->getAuthor(),
            $command->getComment()
        );
    }

    public function renderToggleObjectFeature(ToggleObjectFeatureCommand $command)
    {
        if ($command->getEnabled() === true) {
            $commandPrefix = 'ENABLE';
        } else {
            $commandPrefix = 'DISABLE';
        }
        switch ($command->getFeature()) {
            case ToggleObjectFeatureCommand::FEATURE_ACTIVE_CHECKS:
                $commandFormat = sprintf('%s_%%s_CHECK', $commandPrefix);
                break;
            case ToggleObjectFeatureCommand::FEATURE_PASSIVE_CHECKS:
                $commandFormat = sprintf('%s_PASSIVE_%%s_CHECKS', $commandPrefix);
                break;
            case ToggleObjectFeatureCommand::FEATURE_OBSESSING:
                if ($command->getEnabled() === true) {
                    $commandPrefix = 'START';
                } else {
                    $commandPrefix = 'STOP';
                }
                $commandFormat = sprintf('%s_OBSESSING_OVER_%%s', $commandPrefix);
                break;
            case ToggleObjectFeatureCommand::FEATURE_NOTIFICATIONS:
                $commandFormat = sprintf('%s_%%s_NOTIFICATIONS', $commandPrefix);
                break;
            case ToggleObjectFeatureCommand::FEATURE_EVENT_HANDLER:
                $commandFormat = sprintf('%s_%%s_EVENT_HANDLER', $commandPrefix);
                break;
            case ToggleObjectFeatureCommand::FEATURE_FLAP_DETECTION:
                $commandFormat = sprintf('%s_%%s_FLAP_DETECTION', $commandPrefix);
                break;
            default:
                throw new InvalidArgumentException($command->getFeature());
        }
        $object = $command->getObject();
        if ($object->getType() === ToggleObjectFeatureCommand::TYPE_HOST) {
            /** @var \Icinga\Module\Monitoring\Object\Host $object */
            $commandString = sprintf(
                $commandFormat . ';%s',
                'HOST',
                $object->getHost()
            );
        } else {
            /** @var \Icinga\Module\Monitoring\Object\Service $object */
            $commandString = sprintf(
                $commandFormat . ';%s;%s',
                'SVC',
                $object->getHost(),
                $object->getService()
            );
        }
        return $commandString;
    }

    public function renderDeleteComment(DeleteCommentCommand $command)
    {
        $object = $command->getObject();
        if ($command->getObject()->getType() === $command::TYPE_HOST) {
            /** @var \Icinga\Module\Monitoring\Object\Host $object */
            $commandString = sprintf(
                '%s;%s',
                'DEL_HOST_DOWNTIME',
                $object->getHost()
            );
        } else {
            /** @var \Icinga\Module\Monitoring\Object\Service $object */
            $commandString = sprintf(
                '%s;%s;%s',
                'DEL_SVC_COMMENT',
                $object->getHost(),
                $object->getService()
            );
        }
        return sprintf(
            '%s;%u',
            $commandString,
            $command->getCommentId()
        );
    }

    public function renderDeleteDowntime(DeleteDowntimeCommand $command)
    {
        $object = $command->getObject();
        if ($command->getObject()->getType() === $command::TYPE_HOST) {
            /** @var \Icinga\Module\Monitoring\Object\Host $object */
            $commandString = sprintf(
                '%s;%s',
                'DEL_HOST_DOWNTIME',
                $object->getHost()
            );
        } else {
            /** @var \Icinga\Module\Monitoring\Object\Service $object */
            $commandString = sprintf(
                '%s;%s;%s',
                'DEL_SVC_DOWNTIME',
                $object->getHost(),
                $object->getService()
            );
        }
        return sprintf(
            '%s;%u',
            $commandString,
            $command->getDowntimeId()
        );
    }

    public function renderRemoveAcknowledgement(RemoveAcknowledgementCommand $command)
    {
        $object = $command->getObject();
        if ($command->getObject()->getType() === $command::TYPE_HOST) {
            /** @var \Icinga\Module\Monitoring\Object\Host $object */
            $commandString = sprintf(
                '%s;%s',
                'REMOVE_HOST_ACKNOWLEDGEMENT',
                $object->getHost()
            );
        } else {
            /** @var \Icinga\Module\Monitoring\Object\Service $object */
            $commandString = sprintf(
                '%s;%s;%s',
                'REMOVE_SVC_ACKNOWLEDGEMENT',
                $object->getHost(),
                $object->getService()
            );
        }
        return $commandString;
    }

    public function renderDisableNotificationsExpire(DisableNotificationsExpireCommand $command)
    {
        return sprintf(
            '%s;%u',
            'DISABLE_NOTIFICATIONS_EXPIRE_TIME',
            $command->getExpireTime()
        );
    }
}
