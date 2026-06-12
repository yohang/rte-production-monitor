<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ProductionUnitRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Response\StreamableInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AsCommand(
    name: 'app:import:power-plant-informations-from-opsd',
    description: 'Import power plant informations from Open Power System Data',
)]
final class ImportPowerPlantInformationsFromOPSDCommand extends Command
{
    public function __construct(
        private readonly ProductionUnitRepository $productionUnitRepository,
        private readonly ManagerRegistry $managerRegistry,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'csv',
                InputArgument::OPTIONAL,
                'The source of the data',
                'https://data.open-power-system-data.org/conventional_power_plants/2020-10-01/conventional_power_plants_EU.csv'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = HttpClient::create();
        /** @var StreamableInterface&ResponseInterface $response */
        $response = $client->request('GET', $input->getArgument('csv'));
        $stream = $response->toStream();

        $headerLine = fgetcsv($stream);
        while (($data = fgetcsv($stream)) !== false) {
            $latitude = $data[array_search('lat', $headerLine)];
            $longitude = $data[array_search('lon', $headerLine)];
            $eicCode = $data[array_search('eic_code', $headerLine)];

            if (null === $eicCode) {
                continue;
            }

            try {
                $productionUnit = $this->productionUnitRepository->findOneByEicCode($eicCode);
                $io->info(sprintf('Updating %s', $productionUnit->getName()));

                if (null !== $latitude && null !== $longitude) {
                    $productionUnit->setLatitude((float) $latitude);
                    $productionUnit->setLongitude((float) $longitude);
                }

                $this->productionUnitRepository->save($productionUnit);
                $this->managerRegistry->getManager()->clear();
            } catch (NoResultException) {
                continue;
            }
        }

        return Command::SUCCESS;
    }
}
