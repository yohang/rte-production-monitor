<?php

declare(strict_types=1);

namespace App\Scheduler;

use App\Importer\RTEActualGenerationsImporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsMessageHandler]
final readonly class ImportRTEActualGenerationsHandler
{
    public function __construct(
        private RTEActualGenerationsImporter $importer,
        private LoggerInterface $logger,
        private Stopwatch $stopwatch = new Stopwatch(),
    ) {
    }

    public function __invoke(ImportRTEActualGenerations $message): void
    {
        $this->logger->info('Importing RTE actual generations (via message handler)');

        $this->stopwatch->start('import_rte_actual_generations');
        foreach ($this->importer->import(null, null) as $importState) {
        }
        $event = $this->stopwatch->stop('import_rte_actual_generations');

        $this->logger->info(
            'Imported RTE actual generations (via message handler)',
            ['duration' => $event->getDuration()],
        );
    }
}
