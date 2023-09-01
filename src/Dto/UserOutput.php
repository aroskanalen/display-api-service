<?php

namespace App\Dto;

use App\Enum\UserTypeEnum;

class UserOutput
{
    public ?string $fullName;
    public ?string $externalUserCode = null;
    public ?\DateTime $externalUserExpire = null;
    public ?UserTypeEnum $userType = null;
}
