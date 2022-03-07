<?php


namespace App\Infrastructure\Service\Task\Page;


use App\Domain\Doctrine\Page\Entity\PageFile;
use App\Domain\Doctrine\Task\Enum\TaskEnum;
use App\Infrastructure\Service\Task\Context\PageTaskContext;
use App\Infrastructure\Service\Task\PageTaskInterface;
use App\Infrastructure\Service\Task\TaskResult;
use Symfony\Component\Filesystem\Filesystem;

final class ValidatorTask implements PageTaskInterface
{
    private readonly Filesystem $filesystem;

    public function __construct(private readonly string $tempDir)
    {
        $this->filesystem = new Filesystem();
    }

    public static function name(): TaskEnum
    {
        return TaskEnum::VALIDATOR_UNIT;
    }

    public function execute(PageTaskContext $context): TaskResult
    {
        $lastFile = $context->getPage()->getLastFile();

        if ($lastFile === null) {
            return TaskResult::error("File is empty");
        }

        foreach ($context->getPage()->getRequiredFiles() as $requiredFiles) {
            $locations = $this->getLocations($context->getPage()->getLastFile(), $requiredFiles);

            if ($this->filesystem->exists($locations)) {
                return TaskResult::success();
            }
        }

        return TaskResult::error("Error: files index.twig.html or desktop.twig.html and mobile.twig.html is required");
    }

    private function getLocations(PageFile $file, array $files): array
    {
        return array_map(
            fn(string $filename) => sprintf('%s/%s', $file->getTempFileLocation($this->tempDir), $filename),
            $files
        );
    }

    public function next(): ?TaskEnum
    {
        return ConvertImageTask::name();
    }
}