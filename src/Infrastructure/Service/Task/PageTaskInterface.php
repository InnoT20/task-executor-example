<?php

namespace App\Infrastructure\Service\Task;

use App\Infrastructure\Service\Task\Context\PageTaskContext;

interface PageTaskInterface extends TaskInterface
{
    public function execute(PageTaskContext $context): TaskResult;
}