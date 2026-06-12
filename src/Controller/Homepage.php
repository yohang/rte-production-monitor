<?php

declare(strict_types=1);

namespace App\Controller;

use App\UX\Map\HomepageMapFactory;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/', name: 'homepage')]
#[Template('homepage.html.twig')]
final readonly class Homepage
{
    public function __construct(
        private HomepageMapFactory $homepageMapFactory,
    ) {
    }

    public function __invoke(): array
    {
        return [
            'map' => $this->homepageMapFactory->create(),
        ];
    }
}
