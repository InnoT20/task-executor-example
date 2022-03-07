<?php


namespace App\Application\Cli\TaskHandler;


use App\Application\Cqs\Task\Command\ExecuteTaskCommand;
use App\Domain\Doctrine\Task\Repository\PageTaskRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class PageTaskCommand extends Command
{
    public static $defaultName = 'cli:task-handler-page';

    public function __construct(
        private readonly PageTaskRepositoryInterface $taskPageReadRepository,
        private readonly ExecuteTaskCommand $taskCommand,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $tasks = $this->taskPageReadRepository->findTaskByStatusCreatedCollection(100);

        $this->logger->info('Tasks: ' . count($tasks));

        foreach ($tasks as $task) {
            $this->taskCommand->execute($task);
        }

        return Command::SUCCESS;
    }
}