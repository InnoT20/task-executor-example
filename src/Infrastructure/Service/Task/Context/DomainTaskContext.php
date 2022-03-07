<?php

namespace App\Infrastructure\Service\Task\Context;

use App\Domain\Doctrine\Domain\Entity\Domain;

final class DomainTaskContext implements TaskContextInterface
{
    public function __construct(private readonly Domain $domain)
    {
    }

    public function getDomain(): Domain
    {
        return $this->domain;
    }

}