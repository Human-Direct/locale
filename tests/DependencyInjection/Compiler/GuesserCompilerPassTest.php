<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\DependencyInjection\Compiler;

use HumanDirect\LocaleBundle\DependencyInjection\Compiler\GuesserCompilerPass;
use HumanDirect\LocaleBundle\LocaleGuesser\BrowserLocaleGuesser;
use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use HumanDirect\LocaleBundle\LocaleGuesser\QueryLocaleGuesser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class GuesserCompilerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $container->register(LocaleGuesserManager::class);
        $container
            ->register(QueryLocaleGuesser::class)
            ->addTag('human_direct_locale.guesser', ['alias' => 'query'])
        ;
        $container
            ->register(BrowserLocaleGuesser::class)
            ->addTag('human_direct_locale.guesser', ['alias' => 'browser'])
        ;

        $container->setParameter('human_direct_locale.guessing_order', ['query']);

        $this->process($container);

        $methodCalls = $container
            ->getDefinition(LocaleGuesserManager::class)
            ->getMethodCalls()
        ;

        self::assertCount(1, $methodCalls);

        $methodName = $methodCalls[0][0];
        $argument = $methodCalls[0][1][1];

        self::assertEquals('addGuesser', $methodName);
        self::assertEquals('query', $argument);
    }

    protected function process(ContainerBuilder $container): void
    {
        $pass = new GuesserCompilerPass();
        $pass->process($container);
    }
}
