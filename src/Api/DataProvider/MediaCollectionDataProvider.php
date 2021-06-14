<?php

namespace App\Api\DataProvider;

use ApiPlatform\Core\DataProvider\ArrayPaginator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Api\Fixtures\MediaFixtures;
use App\Api\Model\Media;

/**
 * Class MediaCollectionDataProvider
 */
final class MediaCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @{inheritdoc}
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Media::class === $resourceClass;
    }

    /**
     * @{inheritdoc}
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $page = (int) $context['filters']['page'];
        $itemsPerPage = (int) $context['filters']['itemsPerPage']; // @TODO: figure out to get this from config if not sent in request.
        $current = ($page-1)*$itemsPerPage;

        $results =[
            (new MediaFixtures())->getMedia('497f6eca-4576-4883-cfeb-53cbffba6f08'),
            (new MediaFixtures())->getMedia('597f6eca-4576-1454-cf15-52cb3eba6b85'),
        ];

        $start = ($page-1)*$itemsPerPage;
        return new ArrayPaginator($results, 0, $itemsPerPage);
    }
}
