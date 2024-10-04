<?php
declare(strict_types=1);

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('recurrent')]
final readonly class RecurrentScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule)->with(
            RecurringMessage::cron('5 * * * *', new ImportRTEActualGenerations, new \DateTimeZone('Europe/Paris')),
            RecurringMessage::cron('0 2 * * *', new ImportRTECapacitiesPerProductionUnit, new \DateTimeZone('Europe/Paris')),
        );
    }
}
