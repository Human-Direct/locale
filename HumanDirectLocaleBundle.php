<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle;

use HumanDirect\LocaleBundle\DependencyInjection\Compiler\GuesserCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * LocaleBundle
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class HumanDirectLocaleBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new GuesserCompilerPass());
    }
}
