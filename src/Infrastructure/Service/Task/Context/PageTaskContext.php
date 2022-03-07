<?php

namespace App\Infrastructure\Service\Task\Context;

use App\Domain\Doctrine\Page\Entity\Page;

final class PageTaskContext implements TaskContextInterface
{
    public function __construct(private readonly Page $page)
    {
    }

    public function getPage(): Page
    {
        return $this->page;
    }
}