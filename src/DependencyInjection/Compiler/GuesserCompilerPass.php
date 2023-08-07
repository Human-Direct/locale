<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\DependencyInjection\Compiler;

use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Webmozart\Assert\Assert;

/**
 * @author Matthias Breddin <mb@lunetics.com>
 */
class GuesserCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition(LocaleGuesserManager::class)) {
            return;
        }

        $definition = $container->getDefinition(LocaleGuesserManager::class);
        $taggedServiceIds = $container->findTaggedServiceIds('human_direct_locale.guesser');
        $neededServices = $container->getParameter('human_direct_locale.guessing_order');
        Assert::isArray($neededServices);
        Assert::allString($neededServices);

        foreach ($taggedServiceIds as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (\in_array($attributes['alias'], $neededServices, true)) {
                    $definition->addMethodCall('addGuesser', [new Reference($id), $attributes['alias']]);
                }
            }
        }
    }
}
