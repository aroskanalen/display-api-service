<?php

namespace App\Utils;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Utils
{
    public function __construct(
        private ValidatorInterface $validator,
        private string $bindDefaultDataFormat
    ) {
    }

    public function validateDate(string $date): \DateTime
    {
        $errors = $this->validator->validate($date, new Assert\DateTime($this->bindDefaultDataFormat));
        if (0 !== count($errors)) {
            throw new InvalidArgumentException('Date format not valid');
        }

        return new \DateTime($date);
    }

    /**
     * Extract ULID from IRI string and validate the ULID format.
     *
     * @param string $iri The IRI to extract ULID from
     *
     * @return string The ULID as string
     */
    public function getUlidFromIRI(string $iri): string
    {
        preg_match('@^/v\d/\w+([\w\/]*)/([A-Za-z0-9]{26})$@', $iri, $matches);
        if (3 !== count($matches)) {
            throw new InvalidArgumentException('Unknown resource IRI');
        }
        $ulid = end($matches);

        if (!Ulid::isValid($ulid)) {
            throw new InvalidArgumentException('ULID format not valid');
        }

        return $ulid;
    }
}
