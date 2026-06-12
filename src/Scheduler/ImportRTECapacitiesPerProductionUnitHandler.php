<?php

declare(strict_types=1);

namespace App\Scheduler;

use App\Importer\RTECapacitiesPerProductionUnitImporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsMessageHandler]
final readonly class ImportRTECapacitiesPerProductionUnitHandler
{
    public function __construct(
        private RTECapacitiesPerProductionUnitImporter $importer,
        private LoggerInterface $logger,
        private Stopwatch $stopwatch = new Stopwatch(),
    ) {
    }

    public function __invoke(ImportRTEActualGenerations $message): void
    {
        $this->logger->info('Importing RTE capacities per production unit (via message handler)');

        $this->stopwatch->start('import_rte_capacities_per_production_unit');
        foreach ($this->importer->import() as $importState) {
        }
        $event = $this->stopwatch->stop('import_rte_capacities_per_production_unit');

        $this->logger->info(
            'Imported RTE capacities per production unit (via message handler)',
            ['duration' => $event->getDuration()],
        );
    }
}
