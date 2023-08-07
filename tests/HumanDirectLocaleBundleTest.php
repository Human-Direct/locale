<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests;

use HumanDirect\LocaleBundle\DependencyInjection\Compiler\GuesserCompilerPass;
use HumanDirect\LocaleBundle\HumanDirectLocaleBundle;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class HumanDirectLocaleBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $container = $this->getMockContainer();
        $container
            ->expects(self::exactly(1))
            ->method('addCompilerPass')
            ->withConsecutive([new GuesserCompilerPass()])
        ;

        $bundle = new HumanDirectLocaleBundle();
        $bundle->build($container);
    }

    /**
     * @return MockObject&ContainerBuilder
     */
    protected function getMockContainer(): MockObject
    {
        return $this->createMock(ContainerBuilder::class);
    }
}
