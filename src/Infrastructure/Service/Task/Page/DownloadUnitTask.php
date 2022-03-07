<?php


namespace App\Infrastructure\Service\Task\Page;


use App\Domain\Doctrine\Page\Entity\PageFile;
use App\Domain\Doctrine\Task\Enum\TaskEnum;
use App\Infrastructure\Helper\ZipHelper;
use App\Infrastructure\Service\Task\Context\PageTaskContext;
use App\Infrastructure\Service\Task\Exception\InvalidFileException;
use App\Infrastructure\Service\Task\Exception\UnitFileException;
use App\Infrastructure\Service\Task\PageTaskInterface;
use App\Infrastructure\Service\Task\TaskResult;
use Symfony\Component\Filesystem\Filesystem;

final class DownloadUnitTask implements PageTaskInterface
{
    private const AVAILABLE_EXTENSIONS = ['zip'];

    private readonly Filesystem $filesystem;

    public function __construct(private readonly string $tempDir)
    {
        $this->filesystem = new Filesystem();
    }

    public static function name(): TaskEnum
    {
        return TaskEnum::DOWNLOAD_UNIT;
    }

    public function execute(PageTaskContext $context): TaskResult
    {
        $lastFile = $context->getPage()->getLastFile();

        if ($lastFile === null) {
            return TaskResult::error("File is empty");
        }

        try {
            if (!in_array($lastFile->getFileExtension(), self::AVAILABLE_EXTENSIONS, true)) {
                throw new InvalidFileException('File extension is invalid. Available extensions: zip');
            }

            $this->filesystem->mkdir($this->tempDir);

            $this->downloadFile($lastFile);

            ZipHelper::unpack(
                $this->getZipFileLocation($lastFile),
                $lastFile->getTempFileLocation($this->tempDir)
            );

            $this->filesystem->remove($this->getZipFileLocation($lastFile));

            return TaskResult::success();
        } catch (\Throwable $e) {
            return TaskResult::error($e->getMessage());
        }
    }

    private function getZipFileLocation(PageFile $file): string
    {
        return sprintf('%s/%s.zip', $this->tempDir, $file->getId());
    }

    private function downloadFile(PageFile $file): void
    {
        try {
            file_put_contents(
                $this->getZipFileLocation($file),
                file_get_contents($file->getFileUrl())
            );
        } catch (\Throwable) {
            throw new UnitFileException('Downloading file was failed');
        }
    }

    public function next(): ?TaskEnum
    {
        return ValidatorTask::name();
    }
}