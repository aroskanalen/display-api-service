<?php

namespace App\Controller;

use App\Repository\PlaylistScreenRegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Uid\Ulid;

#[AsController]
class PlaylistScreenRegionGetController extends AbstractController
{
    private PlaylistScreenRegionRepository $playlistScreenRegionRepository;

    public function __construct(PlaylistScreenRegionRepository $playlistScreenRegionRepository)
    {
        $this->playlistScreenRegionRepository = $playlistScreenRegionRepository;
    }

    public function __invoke(Request $request, string $ulid, string $regionUlid)
    {
        if (!(Ulid::isValid($ulid) && Ulid::isValid($regionUlid))) {
            return new JsonResponse(null, 500);
        }

        $page = (int) $request->query->get('page', '1');
        $itemsPerPage = (int) $request->query->get('itemsPerPage', '10');

        $screenUlidObj = Ulid::fromString($ulid);
        $regionUlidObj = Ulid::fromString($regionUlid);

        return $this->playlistScreenRegionRepository->getPlaylistsByScreenRegion($screenUlidObj, $regionUlidObj, $page, $itemsPerPage);
    }
}
