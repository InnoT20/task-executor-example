<?php


namespace App\Infrastructure\Service\Task;


use App\Domain\Doctrine\Task\Entity\Task;
use Symfony\Component\Filesystem\Filesystem;

final class TaskExecutorService
{
    private readonly Filesystem $filesystem;

    /** @var array<string, DomainTaskInterface|PageTaskInterface> */
    private readonly array $tasks;

    public function __construct(
        private readonly string $tempDir,
        \Traversable $tasks
    ) {
        $this->filesystem = new Filesystem();
        $this->tasks = iterator_to_array($tasks);
    }

    public function execute(Task $task): TaskResult
    {
        $job = $this->tasks[$task->getType()->name];

        $result = $job->execute($task->getTaskContext());

        if ($result->getStatus() === TaskResult::SUCCESS) {
            $result->addNextTask($job->next());
        } elseif ($result->getStatus() === TaskResult::ERROR) {
            $lastFile = $task->getTaskContext()->getPage()->getLastFile();

            if ($lastFile !== null) {
                $this->filesystem->remove($lastFile->getTempFileLocation($this->tempDir));
            }
        }

        return $result;
    }
}