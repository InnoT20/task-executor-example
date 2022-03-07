<?php


namespace App\Infrastructure\Service\Task\Page;


use App\Domain\Doctrine\Task\Enum\TaskEnum;
use App\Infrastructure\Helper\FileHelper;
use App\Infrastructure\Service\FileProcessing\CssProcessor;
use App\Infrastructure\Service\FileProcessing\ImageProcessor;
use App\Infrastructure\Service\FileProcessing\JsProcessor;
use App\Infrastructure\Service\FileProcessing\VideoProcessor;
use App\Infrastructure\Service\Task\Context\PageTaskContext;
use App\Infrastructure\Service\Task\PageTaskInterface;
use App\Infrastructure\Service\Task\TaskResult;

final class ConvertImageTask implements PageTaskInterface
{
    public function __construct(
        private readonly ImageProcessor $imageProcessor,
        private readonly CssProcessor $cssProcessor,
        private readonly JsProcessor $jsProcessor,
        private readonly VideoProcessor $videoProcessor,
        private readonly string $tempDir
    ) {
    }

    public static function name(): TaskEnum
    {
        return TaskEnum::CONVERT_IMAGE_AND_MINIFICATION;
    }

    public function execute(PageTaskContext $context): TaskResult
    {
        $lastFile = $context->getPage()->getLastFile();

        if ($lastFile === null) {
            return TaskResult::error("File is empty");
        }

        $tempDir = $lastFile->getTempFileLocation($this->tempDir);

        try {
            $imageList = FileHelper::dirToArray($tempDir, '*.{png,jpg,jpeg}');
            foreach ($imageList as $image) {
                if (filesize($image) === 0) {
                    continue;
                }

                $this->imageProcessor->convertImageToWebp($image);
                $this->imageProcessor->convertImageToJp2($image);
            }

            $cssList = FileHelper::dirToArray($tempDir, '*.css');
            foreach ($cssList as $css) {
                $this->cssProcessor->minimize($css);
            }

            $jsList = FileHelper::dirToArray($tempDir, '*.js');
            foreach ($jsList as $js) {
                $this->jsProcessor->minimize($js);
            }

            $videoList = FileHelper::dirToArray($tempDir, '*.{mp4,webm,ogg}');
            foreach ($videoList as $video) {
                $this->videoProcessor->minimize($video);
            }

            return TaskResult::success();
        } catch (\Throwable $e) {
            return TaskResult::error($e->getMessage());
        }
    }

    public function next(): ?TaskEnum
    {
        return AddSuccessTemplateTask::name();
    }
}