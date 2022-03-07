<?php


namespace App\Infrastructure\Service\Task\Slave;


use App\Domain\Doctrine\Page\Entity\Page;
use App\Domain\Doctrine\Slave\Entity\Slave;
use App\Domain\Doctrine\Slave\Repository\SlaveReadRepositoryInterface;
use App\Domain\Doctrine\Task\Enum\TaskEnum;
use App\Infrastructure\Service\Task\Context\PageTaskContext;
use App\Infrastructure\Service\Task\Context\TaskContextInterface;
use App\Infrastructure\Service\Task\PageTaskInterface;
use App\Infrastructure\Service\Task\TaskInterface;
use App\Infrastructure\Service\Task\TaskResult;
use GuzzleHttp\Client;

final class SendFileToSlaveTask implements PageTaskInterface
{
    private readonly Client $client;

    public function __construct(private SlaveReadRepositoryInterface $slaveReadRepository)
    {
        $this->client = (new Client()); // todo: timeout
    }

    public function execute(PageTaskContext $context): TaskResult
    {
        if (!$context->getPage()->isHaveFile()) {
            return TaskResult::skipped();
        }

        $slaves = $this->slaveReadRepository->findAll();

        try {
            foreach ($slaves as $slave) {
                $this->sendRequest($slave, $context->getPage());
            }

            return TaskResult::success();
        } catch (\Throwable $exception) {
            return TaskResult::error($exception->getMessage());
        }
    }

    private function sendRequest(Slave $slave, Page $page): array
    {
        $response = $this->client->post("http://{$slave->getIp()}/api/upload-from-url", [
            'json' => ['id' => $page->getIntId()]
        ]);

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public static function name(): TaskEnum
    {
        return TaskEnum::SEND_FILE_TO_SLAVE;
    }

    public function next(): ?TaskEnum
    {
        return null;
    }
}