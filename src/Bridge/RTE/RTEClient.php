<?php
declare(strict_types=1);

namespace App\Bridge\RTE;

use App\Bridge\RTE\Model\AccessToken;
use App\Bridge\RTE\Model\ActualGenerationsPerUnit;
use App\Bridge\RTE\Model\CapacitiesPerProductionUnit;
use App\Bridge\RTE\Model\EicData;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class RTEClient
{
    private HttpClientInterface $client;

    public function __construct(
        private SerializerInterface $serializer,
        private CacheInterface      $cache,
        private string              $rteClientId,
        private string              $rteClientSecret,
    )
    {
        $this->client = HttpClient::createForBaseUri('https://digital.iservices.rte-france.com');
    }

    public function authenticate(): AccessToken
    {
        return $this->cache->get('rte|access_token', function (ItemInterface $item) {
            $response = $this->client->request(
                'POST',
                '/token/oauth',
                [
                    'headers' => [
                        'Content-Type'  => 'application/x-www-form-urlencoded',
                        'Authorization' => 'Basic ' . base64_encode($this->rteClientId . ':' . $this->rteClientSecret),
                    ],
                ]
            );

            $accessToken = $this->serializer->deserialize($response->getContent(), AccessToken::class, 'json');
            $item->expiresAfter($accessToken->expiresIn);

            return $accessToken;
        });
    }

    public function fetchInstalledCapacities(): CapacitiesPerProductionUnit
    {
        $response = $this->client->request(
            'GET',
            '/open_api/generation_installed_capacities/v1/capacities_per_production_unit',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authenticate()->accessToken,
                ],
            ]
        );

        return $this->serializer->deserialize($response->getContent(), CapacitiesPerProductionUnit::class, 'json');
    }

    /**
     * @return array<int, EicData>
     */
    public function fetchEicDataByParentEic(string $parentEicCode): array
    {
        return $this->cache->get(
            'rte|eic_data|' . $parentEicCode,
            function (ItemInterface $item) use ($parentEicCode): array {
                $response = $this->client->request(
                    'GET',
                    'https://www.services-rte.com/cms/v1/eiccode?type=W&eic_parent=' . $parentEicCode,
                );

                $item->expiresAfter(3600);

                return $this->serializer->deserialize($response->getContent(), EicData::class . '[]', 'json');
            });
    }

    public function fetchActualGenerations(
        string              $eicCode,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
    ): ActualGenerationsPerUnit
    {
        $url = sprintf(
            '/open_api/actual_generation/v1/actual_generations_per_unit?unit_eic_code=%s',
            $eicCode,
        );

        if (null !== $startDate && null !== $endDate) {
            $url = sprintf(
                '/open_api/actual_generation/v1/actual_generations_per_unit?unit_eic_code=%s&start_date=%s&end_date=%s',
                $eicCode,
                $startDate->format('c'),
                $endDate->format('c'),
            );
        }

        $cacheKey = 'rte|actual_generation|' . $eicCode . '|';
        $cacheKey .= null !== $startDate ? $startDate->format('c') : 'null';
        $cacheKey .= '|';
        $cacheKey .= null !== $endDate ? $endDate->format('c') : 'null';
        $cacheKey = str_replace(':', '_', $cacheKey);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($url, $endDate) {
            if ($endDate && $endDate < new \DateTimeImmutable('now')) {
                $item->expiresAfter(3600);
            } else {
                $item->expiresAfter(600);
            }

            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->authenticate()->accessToken,
                    ],
                ],
            );

            return $this->serializer->deserialize($response->getContent(), ActualGenerationsPerUnit::class, 'json');
        });
    }
}
