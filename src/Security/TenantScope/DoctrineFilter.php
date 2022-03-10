<?php

namespace App\Security\TenantScope;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * DoctrineFilter Class.
 *
 * This filter adds a 'tenant' filter to all queries to ensure that
 * only content from the users active tenant is shown.
 *
 * @see App\Security\TenantScope\DoctrineFilter
 */
class DoctrineFilter extends SQLFilter
{
    /** {@inheritDoc} */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->getReflectionClass()->implementsInterface('App\Entity\Interfaces\TenantScopedInterface')) {
            return sprintf('%s.tenant_id = %s', $targetTableAlias, $this->getParameter('tenant_id'));
        }

        return '';
    }
}
