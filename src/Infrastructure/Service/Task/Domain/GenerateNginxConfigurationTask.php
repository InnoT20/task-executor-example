<?php

namespace App\Infrastructure\Service\Task\Domain;

use App\Domain\Doctrine\Slave\Repository\SlaveReadRepositoryInterface;
use App\Domain\Doctrine\Task\Enum\TaskEnum;
use App\Infrastructure\Service\Task\Context\DomainTaskContext;
use App\Infrastructure\Service\Task\DomainTaskInterface;
use App\Infrastructure\Service\Task\TaskResult;
use GuzzleHttp\Client;

final class GenerateNginxConfigurationTask implements DomainTaskInterface
{
    private readonly Client $client;

    public function __construct(private SlaveReadRepositoryInterface $slaveReadRepository)
    {
        $this->client = (new Client()); // todo: timeout
    }

    public function execute(DomainTaskContext $context): TaskResult
    {
        $slaves = $this->slaveReadRepository->findAll();

        foreach ($slaves as $slave) {
            $this->client->post("http://{$slave->getIp()}/api/nginx/generate", [
                'json' => [
                    'domain' => $context->getDomain()->getName()
                ]
            ]);
        }

        return TaskResult::success();
    }

    public static function name(): TaskEnum
    {
        return TaskEnum::GENERATE_NGINX_CONFIGURATION;
    }

    public function next(): ?TaskEnum
    {
        return null;
    }
}