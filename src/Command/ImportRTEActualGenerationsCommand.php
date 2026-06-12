<?php

declare(strict_types=1);

namespace App\Command;

use App\Importer\RTEActualGenerationsImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

#[AsCommand(
    name: 'app:import:rte-actual-generations',
    description: 'Fetch RTE actual generations'
)]
class ImportRTEActualGenerationsCommand extends Command
{
    public function __construct(private readonly RTEActualGenerationsImporter $importer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('start-date', null, InputOption::VALUE_REQUIRED, 'Start date for the import (all php input date formats are supported)');
        $this->addOption('end-date', null, InputOption::VALUE_REQUIRED, 'End date for the import (all php input date formats are supported)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $startDate = $input->getOption('start-date') ? new \DateTimeImmutable($input->getOption('start-date')) : null;
        $endDate = $input->getOption('end-date') ? new \DateTimeImmutable($input->getOption('end-date')) : null;

        try {
            $progress = null;
            foreach ($this->importer->import($startDate, $endDate) as $importState) {
                if (null === $progress) {
                    $progress = $io->createProgressBar($importState->rowsToProcess);
                }

                $progress->setProgress($importState->getRowsProcessed());
            }

            $progress?->finish();
            $progress?->clear();

            $io->success('Imported RTE actual generations');
        } catch (ClientExceptionInterface $e) {
            $io->error('An error occurred while fetching RTE actual generations');
            $io->error($e->getResponse()->getContent(false));
        }

        return self::SUCCESS;
    }
}
