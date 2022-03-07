<?php

namespace App\Infrastructure\Service\Task;

use App\Infrastructure\Service\Task\Context\DomainTaskContext;
use App\Infrastructure\Service\Task\Context\PageTaskContext;

interface DomainTaskInterface extends TaskInterface
{
    public function execute(DomainTaskContext $context): TaskResult;
}