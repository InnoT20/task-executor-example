<?php

namespace App\Infrastructure\DependencyInjection;

use App\Infrastructure\Service\Task\TaskInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TaskCompiler implements CompilerPassInterface
{
    private const TAG = 'page.tasks';

    public function process(ContainerBuilder $container): void
    {
        $tagged = $container->findTaggedServiceIds(self::TAG);

        /** @var class-string<TaskInterface> $id */
        foreach (array_keys($tagged) as $id) {
            $definition = $container->getDefinition($id);

            $definition->clearTags();
            $definition->addTag(self::TAG, ['name' => $id::name()->name]);
        }
    }
}