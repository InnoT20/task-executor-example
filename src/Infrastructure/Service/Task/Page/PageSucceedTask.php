<?php


namespace App\Infrastructure\Service\Task\Page;


use App\Domain\Doctrine\Common\Transactions\TransactionInterface;
use App\Domain\Doctrine\Page\Enum\PageFileStatusEnum;
use App\Domain\Doctrine\Task\Enum\TaskEnum;
use App\Infrastructure\Helper\ZipHelper;
use App\Infrastructure\Service\Task\Context\PageTaskContext;
use App\Infrastructure\Service\Task\PageTaskInterface;
use App\Infrastructure\Service\Task\TaskResult;

final class PageSucceedTask implements PageTaskInterface
{
    public function __construct(
        private readonly TransactionInterface $transaction,
        private readonly string $tempDir,
        private readonly string $publicDir,
    ) {
    }

    public static function name(): TaskEnum
    {
        return TaskEnum::PAGE_SUCCEED;
    }

    public function execute(PageTaskContext $context): TaskResult
    {
        $page = $context->getPage();
        $lastFile = $page->getLastFile();

        if ($lastFile === null) {
            return TaskResult::error("File is empty");
        }

        try {
            ZipHelper::archive(
                $lastFile->getTempFileLocation($this->tempDir),
                sprintf('%s/%s.zip', $this->publicDir, $page->getId())
            );

            $this->transaction->transactional(
                function () use ($page, $lastFile) {
                    $lastFile->changeStatus(PageFileStatusEnum::SUCCEEDED);
                    $page->setCurrentFile($page->getLastFile());
                    $page->addSlaveTask(TaskEnum::SEND_FILE_TO_SLAVE);
                }
            );

            return TaskResult::success();
        } catch (\Throwable $e) {
            return TaskResult::error($e->getMessage());
        }
    }

    public function next(): ?TaskEnum
    {
        return null;
    }
}