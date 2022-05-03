<?php

namespace App\Dto;

use App\Entity\Tenant\Playlist;
use App\Entity\Tenant\Slide;

class PlaylistSlide
{
    public Slide $slide;
    public Playlist $playlist;
    public int $weight = 0;
}
