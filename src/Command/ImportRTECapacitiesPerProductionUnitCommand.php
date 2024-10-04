<?php
declare(strict_types=1);

namespace App\Command;

use App\Importer\RTECapacitiesPerProductionUnitImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

#[AsCommand(
    name: 'app:import:rte-capacities-per-production-unit',
    description: 'Fetch RTE generation installed capacities'
)]
class ImportRTECapacitiesPerProductionUnitCommand extends Command
{
    public function __construct(private readonly RTECapacitiesPerProductionUnitImporter $importer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $progress = null;
            foreach ($this->importer->import() as $importState) {
                if (null === $progress) {
                    $progress = $io->createProgressBar($importState->rowsToProcess);
                }

                $progress->setProgress($importState->getRowsProcessed());
            }

            $progress?->finish();
            $progress?->clear();

            $io->success('Imported RTE capacities per production unit');
        } catch (ClientExceptionInterface $e) {
            $io->error('An error occurred while fetching RTE capacities per production unit');
            $io->error($e->getResponse()->getContent(false));
        }

        return self::SUCCESS;
    }
}
