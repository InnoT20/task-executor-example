<?php


namespace App\Infrastructure\Service\Task;


use App\Domain\Doctrine\Task\Enum\TaskEnum;
use Webmozart\Assert\Assert;

final class TaskResult
{
    public const SUCCESS = 'success';
    public const ERROR = 'error';
    public const SKIPPED = 'skipped';

    private readonly string $status;
    private ?TaskEnum $nextTask = null;

    protected function __construct(string $status, private readonly ?string $comment = null)
    {
        Assert::inArray($status, [self::SUCCESS, self::ERROR, self::SKIPPED]);

        $this->status = $status;
    }

    public static function success(): self
    {
        return new self(self::SUCCESS);
    }

    public static function skipped(): self
    {
        return new self(self::SKIPPED);
    }

    public static function error(?string $comment = null): self
    {
        return new self(self::ERROR, $comment);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isStatus(string $status): bool
    {
        Assert::inArray($status, [self::SUCCESS, self::ERROR, self::SKIPPED]);

        return $this->status === $status;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function addNextTask(?TaskEnum $task): void
    {
        $this->nextTask = $task;
    }

    public function getNextTask(): ?TaskEnum
    {
        return $this->nextTask;
    }
}