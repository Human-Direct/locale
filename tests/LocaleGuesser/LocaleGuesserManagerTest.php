<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use HumanDirect\LocaleBundle\LocaleGuesser\QueryLocaleGuesser;
use HumanDirect\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class LocaleGuesserManagerTest extends AbstractTestGuesser
{
    public function testLocaleGuessingInvalidGuesser(): void
    {
        $guesserManager = new LocaleGuesserManager([0 => 'foo']);
        $guesserManager->addGuesser($this->getGuesserMock(), 'bar');
        $this->expectException(InvalidConfigurationException::class);
        $guesserManager->guess($this->getRequestWithLocaleQuery(null));
    }

    public function testLocaleIsIdentifiedByTheQueryGuessingService(): void
    {
        $request = $this->getRequestWithLocaleQuery('fr');
        $metaValidator = $this->getMockMetaValidator();

        $metaValidator
            ->method('isAllowed')
            ->with('fr')
            ->willReturn(true)
        ;

        $logger = $this->getMockLogger();
        $logger
            ->expects(self::exactly(2))
            ->method('debug')
            ->withConsecutive(
                ['Locale Query Guessing Service Loaded'],
                ['Locale has been identified by guessing service: ( Query )']
            )
        ;

        $order = [0 => 'query', 1 => 'router'];
        $manager = new LocaleGuesserManager($order, $logger);
        $manager->addGuesser(new RouterLocaleGuesser($metaValidator), 'router');
        $manager->addGuesser(new QueryLocaleGuesser($metaValidator), 'query');

        $guesserMock = $this->getGuesserMock();
        $guesserMock
            ->method('guess')
            ->willReturn(false)
        ;
        $manager->addGuesser($guesserMock, 'browser');
        $locale = $manager->guess($request);
        self::assertEquals('fr', $locale);
    }

    public function testLocaleIsNotIdentifiedIfNoQueryParamsExist(): void
    {
        $request = $this->getRequestWithLocaleQuery(null);
        $metaValidator = $this->getMockMetaValidator();

        $metaValidator->expects(self::never())
            ->method('isAllowed')
        ;

        $order = [0 => 'query', 1 => 'router'];
        $manager = new LocaleGuesserManager($order);
        $manager->addGuesser(new RouterLocaleGuesser($metaValidator), 'router');
        $manager->addGuesser(new QueryLocaleGuesser($metaValidator), 'query');
        $guesserMock = $this->getGuesserMock();
        $guesserMock
            ->method('guess')
            ->willReturn(false)
        ;
        $manager->addGuesser($guesserMock, 'browser');
        $guessing = $manager->guess($request);
        self::assertNull($guessing);
    }

    public function testGetPreferredLocales(): void
    {
        $manager = new LocaleGuesserManager([]);
        $value = [uniqid('preferredLocales:')];

        $reflectionsClass = new \ReflectionClass(\get_class($manager));
        $property = $reflectionsClass->getProperty('preferredLocales');
        $property->setAccessible(true);
        $property->setValue($manager, $value);

        self::assertEquals($value, $manager->getPreferredLocales());
    }

    public function testGetGuessingOrder(): void
    {
        $order = [0 => 'query', 1 => 'router'];
        $manager = new LocaleGuesserManager($order);

        self::assertEquals($order, $manager->getGuessingOrder());
    }

    public function testRemoveGuesser(): void
    {
        $order = [0 => 'query', 1 => 'router'];
        $manager = new LocaleGuesserManager($order);
        $manager->addGuesser($this->getGuesserMock(), 'mock');

        $manager->removeGuesser('mock');
        self::assertNull($manager->getGuesser('mock'));
    }

    /**
     * @return LocaleGuesserInterface|MockObject
     */
    private function getGuesserMock(): LocaleGuesserInterface
    {
        return $this->createMock(LocaleGuesserInterface::class);
    }
}
