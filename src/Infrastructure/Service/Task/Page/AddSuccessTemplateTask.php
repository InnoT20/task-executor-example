<?php


namespace App\Infrastructure\Service\Task\Page;


use App\Domain\Doctrine\Task\Enum\TaskEnum;
use App\Infrastructure\Service\Task\Context\PageTaskContext;
use App\Infrastructure\Service\Task\PageTaskInterface;
use App\Infrastructure\Service\Task\TaskResult;
use Symfony\Component\Filesystem\Filesystem;

final class AddSuccessTemplateTask implements PageTaskInterface
{
    private readonly Filesystem $filesystem;

    public function __construct(
        private readonly string $tempDir,
        private readonly string $templateDir
    ) {
        $this->filesystem = new Filesystem();
    }

    public static function name(): TaskEnum
    {
        return TaskEnum::ADD_SUCCESS_TEMPLATE;
    }

    public function execute(PageTaskContext $context): TaskResult
    {
        $lastFile = $context->getPage()->getLastFile();

        if ($lastFile === null) {
            return TaskResult::error("File is empty");
        }

        $tempDir = $lastFile->getTempFileLocation($this->tempDir);

        if (!file_exists(sprintf('%s/success.html.twig', $tempDir))) {
            if ($context->getPage()->getLanguage() === 'ru') {
                $filename = 'success-ru.html.twig';
            } else {
                $filename = 'success.html.twig';
            }

            $this->filesystem->copy(
                sprintf('%s/success/%s', $this->templateDir, $filename),
                sprintf('%s/success.html.twig', $tempDir)
            );
        }

        return TaskResult::success();
    }

    public function next(): ?TaskEnum
    {
        return AddPrivatePolicyTemplateTask::name();
    }
}