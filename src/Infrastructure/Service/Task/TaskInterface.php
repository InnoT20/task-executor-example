<?php


namespace App\Infrastructure\Service\Task;


use App\Domain\Doctrine\Task\Enum\TaskEnum;
use App\Infrastructure\Service\Task\Context\TaskContextInterface;

interface TaskInterface
{
    public static function name(): TaskEnum;

    public function next(): ?TaskEnum;
}