<?php

declare(strict_types=1);

namespace App\Importer;

use App\Bridge\RTE\RTEClient;
use App\Entity\ProductionUnit;
use App\Entity\ProductionValue;
use App\Repository\ProductionUnitRepository;
use App\Repository\ProductionValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class RTEActualGenerationsImporter
{
    public function __construct(
        private RTEClient $rteClient,
        private ProductionUnitRepository $productionUnitRepository,
        private ProductionValueRepository $productionValueRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        #[Autowire('%kernel.debug%')] private bool $debug,
    ) {
    }

    /**
     * @return \Generator<ImportState>
     */
    public function import(?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate): \Generator
    {
        if ($this->debug) {
            $this->logger->warning('Importing actual generations with debug mode enabled. This can cause memory leaks.');
        }

        $productionUnitIds = $this->productionUnitRepository->findAllIds();

        $importState = new ImportState(count($productionUnitIds));
        yield $importState;

        foreach ($productionUnitIds as $productionUnitId) {
            /** @var ProductionUnit $productionUnit */
            $productionUnit = $this->productionUnitRepository->find($productionUnitId);

            foreach ($productionUnit->getSubUnits() as $subUnit) {
                $actualGenerationsPerUnit = $this->rteClient->fetchActualGenerations(
                    $subUnit->getEicCode(),
                    $startDate,
                    $endDate,
                );

                foreach ($actualGenerationsPerUnit->actualGenerationsPerUnit as $actualGenerationPerUnit) {
                    foreach ($actualGenerationPerUnit->values as $actualGenerationPerUnitValues) {
                        try {
                            $productionValue = $this->productionValueRepository->findOneByProductionSubUnitAndStartDate(
                                $subUnit,
                                $actualGenerationPerUnitValues->startDate,
                            );
                            $productionValue->syncWithRTE($actualGenerationPerUnitValues);
                        } catch (NoResultException) {
                            $productionValue = ProductionValue::fromRTEProductionUnit($subUnit, $actualGenerationPerUnitValues);
                        }

                        $this->productionValueRepository->save($productionValue);
                    }
                }
            }

            $this->entityManager->clear();
            $importState->processRow();

            yield $importState;
        }
    }
}
