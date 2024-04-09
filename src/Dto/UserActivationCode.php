<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Trait\BlameableTrait;
use App\Dto\Trait\IdentifiableTrait;
use App\Dto\Trait\TimestampableTrait;

class UserActivationCode
{
    use BlameableTrait;
    use IdentifiableTrait;
    use TimestampableTrait;

    public ?string $code = null;
    public ?\DateTimeImmutable $codeExpire = null;
    public ?string $username = null;
    public ?array $roles = [];
}
