<?php

namespace App\Dto;

class Screen
{
    public string $title = '';
    public string $description = '';
    public string $size = '';
    public \DateTimeInterface $created;
    public \DateTimeInterface $modified;
    public string $modifiedBy = '';
    public string $createdBy = '';
    public \App\Entity\ScreenLayout $screenLayout;
    public string $location = '';
    public array $regions = [];
    public string $inScreenGroups = '';
    public array $dimensions = [
        'width' => 0,
        'height' => 0,
    ];
}
