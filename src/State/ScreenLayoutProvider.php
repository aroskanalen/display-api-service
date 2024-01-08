<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\State\ProviderInterface;
use App\Dto\ScreenLayout as ScreenLayoutDTO;
use App\Entity\ScreenLayout;
use App\Repository\ScreenLayoutRepository;

class ScreenLayoutProvider extends AbstractProvider
{
    public function __construct(
        ProviderInterface $collectionProvider,
        ScreenLayoutRepository $entityRepository,
    ) {
        parent::__construct($collectionProvider, $entityRepository);
    }

    public function toOutput(object $object): ScreenLayoutDTO
    {
        assert($object instanceof ScreenLayout);
        $output = new ScreenLayoutDTO();
        $output->id = $object->getId();
        $output->title = $object->getTitle();
        $output->grid['rows'] = $object->getGridRows();
        $output->grid['columns'] = $object->getGridColumns();

        $output->regions = $object->getRegions();

        $output->setRelationsModified($object->getRelationsModified());

        return $output;
    }
}
