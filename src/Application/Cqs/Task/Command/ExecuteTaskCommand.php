<?php


namespace App\Application\Cqs\Task\Command;


use App\Domain\Doctrine\Task\Entity\Task;
use App\Domain\Doctrine\Task\Enum\TaskStatus;
use App\Domain\Doctrine\Task\Service\TaskService;
use App\Infrastructure\Service\Task\TaskExecutorService;
use App\Infrastructure\Service\Task\TaskResult;
use Psr\Log\LoggerInterface;

final class ExecuteTaskCommand
{
    public function __construct(
        private readonly TaskExecutorService $taskExecutor,
        private readonly TaskService $taskService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Task $task): void
    {
        $this->logger->info("Perform the task {$task->getType()->value}");

        $this->taskService->changeStatus($task, TaskStatus::PROCESSING);

        $result = $this->taskExecutor->execute($task);

        if ($result->isStatus(TaskResult::ERROR)) {
            $task->setComment($result->getComment());
        }

        $this->taskService->changeStatus($task, TaskStatus::getStatusByTaskResult($result));

        $this->logger->info(
            'Task was executed successfully',
            [
                'status' => TaskStatus::getStatusByTaskResult($result),
                'comment' => $result->getComment()
            ]
        );

        if ($result->getNextTask() !== null) {
            $this->logger->info(
                'Next task',
                [
                    'status' => $result->getNextTask()->value
                ]
            );

            $this->taskService->addNextTask($task, $result->getNextTask());
        }
    }
}