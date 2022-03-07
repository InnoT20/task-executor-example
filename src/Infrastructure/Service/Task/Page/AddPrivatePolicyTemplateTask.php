<?php


namespace App\Infrastructure\Service\Task\Page;


use App\Domain\Doctrine\Task\Enum\TaskEnum;
use App\Infrastructure\Service\Task\Context\PageTaskContext;
use App\Infrastructure\Service\Task\PageTaskInterface;
use App\Infrastructure\Service\Task\TaskResult;
use Symfony\Component\Filesystem\Filesystem;

final class AddPrivatePolicyTemplateTask implements PageTaskInterface
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
        return TaskEnum::ADD_PRIVATE_POLICY_TEMPLATE;
    }

    public function execute(PageTaskContext $context): TaskResult
    {
        $lastFile = $context->getPage()->getLastFile();

        if ($lastFile === null) {
            return TaskResult::error("File is empty");
        }

        $tempDir = $lastFile->getTempFileLocation($this->tempDir);

        $htmlPP = sprintf('%s/pp.html', $tempDir);
        $htmlPPTwig = sprintf('%s/pp.html.twig', $tempDir);

        if (!$this->filesystem->exists($htmlPP) && !$this->filesystem->exists($htmlPPTwig)) {
            $this->filesystem->copy(sprintf('%s/pp.html.twig', $this->templateDir), $htmlPPTwig);
        }

        if (file_exists($htmlPP)) {
            $this->filesystem->rename($htmlPP, $htmlPPTwig);
            $this->filesystem->remove($htmlPP);
        }

        return TaskResult::success();
    }

    public function next(): ?TaskEnum
    {
        return PageSucceedTask::name();
    }
}