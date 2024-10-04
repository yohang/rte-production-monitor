<?php
declare(strict_types=1);

namespace App\Importer;

use App\Bridge\RTE\RTEClient;
use App\Entity\Producer;
use App\Entity\ProductionSubUnit;
use App\Entity\ProductionUnit;
use App\Entity\ProductionUnitValues;
use App\Repository\ProducerRepository;
use App\Repository\ProductionSubUnitRepository;
use App\Repository\ProductionUnitRepository;
use App\Repository\ProductionUnitValuesRepository;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

final readonly class RTECapacitiesPerProductionUnitImporter
{
    public function __construct(
        private RTEClient                      $rteClient,
        private ProductionUnitRepository       $productionUnitRepository,
        private ProducerRepository             $producerRepository,
        private ProductionUnitValuesRepository $productionUnitValues,
        private ProductionSubUnitRepository    $productionSubUnitRepository,
        private LoggerInterface                $logger,
    )
    {
    }

    /**
     * @return \Generator<ImportState>
     */
    public function import(): \Generator
    {
        $capacitiesPerProductionUnit = $this->rteClient->fetchInstalledCapacities();
        $importState                 = new ImportState(count($capacitiesPerProductionUnit->capacitiesPerProductionUnit));

        yield $importState;

        foreach ($capacitiesPerProductionUnit->capacitiesPerProductionUnit as $capacityPerProductionUnit) {
            try {
                $productionUnit = $this->productionUnitRepository->findOneByEicCode($capacityPerProductionUnit->productionUnit->eicCode);
                $productionUnit->syncWithRTE($capacityPerProductionUnit->productionUnit);
            } catch (NoResultException) {
                $productionUnit = ProductionUnit::fromRTEProductionUnit($capacityPerProductionUnit->productionUnit);
            }

            if (null !== $capacityPerProductionUnit->productionUnit->producerEicCode) {
                try {
                    $producer = $this->producerRepository->findOneByEicCode($capacityPerProductionUnit->productionUnit->producerEicCode);
                    $producer->syncWithRTE($capacityPerProductionUnit->productionUnit);
                } catch (NoResultException) {
                    $producer = Producer::fromRTEProductionUnit($capacityPerProductionUnit->productionUnit);
                }

                $productionUnit->setProducer($producer);
                $this->producerRepository->save($producer);
            }

            $this->productionUnitRepository->save($productionUnit);

            try {
                $eicData = $this->rteClient->fetchEicDataByParentEic($capacityPerProductionUnit->productionUnit->eicCode);
                foreach ($eicData as $data) {
                    try {
                        $productionSubUnit = $this->productionSubUnitRepository->findOneByEicCode($data->eicCode);
                        $productionSubUnit->syncWithRTE($data);
                    } catch (NoResultException) {
                        $productionSubUnit = ProductionSubUnit::fromEicData($productionUnit, $data);
                    }

                    $this->productionSubUnitRepository->save($productionSubUnit);
                }
            } catch (ServerExceptionInterface|ClientExceptionInterface) {
                $this->logger->warning('Could not fetch EIC data for production unit', ['eicCode' => $capacityPerProductionUnit->productionUnit->eicCode]);
            }

            foreach ($capacityPerProductionUnit->values as $value) {
                try {
                    $productionUnitValues = $this->productionUnitValues->findOneByProductionUnitAndStartDate($productionUnit, $value->startDate);
                    $productionUnitValues->syncWithRTE($value);
                } catch (NoResultException) {
                    $productionUnitValues = ProductionUnitValues::fromRTEProductionUnitValues($productionUnit, $value);
                }

                $productionUnitValues->setProductionUnit($productionUnit);
                $this->productionUnitValues->save($productionUnitValues);
            }

            $importState->processRow();

            yield $importState;
        }
    }
}
