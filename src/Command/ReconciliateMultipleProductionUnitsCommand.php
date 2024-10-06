<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\ProductionUnit;
use App\Repository\ProductionUnitRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reconciliate:multiple-production-units',
    description: 'Reconciliate multiple production units on the same site',
)]
final class ReconciliateMultipleProductionUnitsCommand extends Command
{
    public function __construct(
        private readonly ProductionUnitRepository $productionUnitRepository,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $finishingByNumber = $this->productionUnitRepository->findFinishingByNumber();
        $i = 0;
        foreach ($finishingByNumber as $productionUnit) {
            /** @var ProductionUnit $productionUnit */

            if (null !== $productionUnit->getFirstUnitOfGroup()) {
                continue;
            }

            $prefix = preg_replace('/([\w ]+) \d+$/i', '$1', $productionUnit->getName());
            foreach ($this->productionUnitRepository->findByNamePrefix($prefix) as $otherProductionUnit) {
                $otherProductionUnit->setFirstUnitOfGroup($productionUnit);
                $this->productionUnitRepository->save($otherProductionUnit);
            }

            $i++;
        }

        $io->success('Processed ' . $i . ' production units groups');

        return self::SUCCESS;
    }
}
