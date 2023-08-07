<?php

namespace HumanDirect\LocaleBundle\Tests\LocaleInformation;

use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use HumanDirect\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use HumanDirect\LocaleBundle\LocaleInformation\LocaleInformation;
use HumanDirect\LocaleBundle\Tests\Validator\BaseMetaValidator;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for the LocaleInformation
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleInformationTest extends BaseMetaValidator
{
    /**
     * @var string[]
     */
    protected array $allowedLocales = ['en', 'de', 'fr'];

    public function testGetAllowedLocalesFromConfiguration(): void
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales);
        $information = new LocaleInformation(
            $metaValidator,
            $this->getGuesserManagerMock(),
            new AllowedLocalesProvider($this->allowedLocales)
        );

        self::assertSame($this->allowedLocales, $information->getAllowedLocalesFromConfiguration());
    }

    public function testGetAllAllowedLocales(): void
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock());
        $foundLocales = $information->getAllAllowedLocales();

        self::assertContains('en_GB', $foundLocales);
        self::assertContains('en_US', $foundLocales);
        self::assertContains('de_CH', $foundLocales);
        self::assertContains('de_AT', $foundLocales);
        self::assertContains('fr_CH', $foundLocales);
        self::assertContains('de', $foundLocales);
        self::assertContains('en', $foundLocales);
    }

    public function testGetAllAllowedLocalesStrict(): void
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales, true);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock());
        $foundLocales = $information->getAllAllowedLocales();

        self::assertNotContains('en_US', $foundLocales);
        self::assertNotContains('de_AT', $foundLocales);
        self::assertContains('de', $foundLocales);
        self::assertContains('en', $foundLocales);
        self::assertContains('fr', $foundLocales);
    }

    public function testGetAllAllowedLocalesLanguageIdenticalToRegion(): void
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock());
        $foundLocales = $information->getAllAllowedLocales();

        self::assertContains('de_CH', $foundLocales);
        self::assertContains('fr_CH', $foundLocales);
    }

    public function testGetAllAllowedLanguages(): void
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock());
        $foundLanguages = $information->getAllAllowedLanguages();

        self::assertContains('de', $foundLanguages);
        self::assertNotContains('de_LI', $foundLanguages);
    }

    public function testGetAllAllowedLanguagesStrict(): void
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales, true);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock());
        $foundLanguages = $information->getAllAllowedLanguages();

        self::assertCount(\count($this->allowedLocales), $foundLanguages);
        foreach ($foundLanguages as $locale) {
            self::assertContains($locale, $this->allowedLocales);
        }
    }

    /**
     * @param string[] $preferredLocales
     */
    private function getLocaleInformation(array $preferredLocales): LocaleInformation
    {
        $allowedLocales = ['en', 'fr', 'es'];

        $guesserManager = $this->getGuesserManagerMock();
        $guesserManager
            ->expects(self::once())
            ->method('getPreferredLocales')
            ->willReturn($preferredLocales)
        ;

        return new LocaleInformation(
            $this->getMetaValidator($allowedLocales),
            $guesserManager,
            new AllowedLocalesProvider($allowedLocales)
        );
    }

    public function testGetPreferredLocales(): void
    {
        $info = $this->getLocaleInformation(['en', 'de']);

        self::assertEquals(['en'], $info->getPreferredLocales());
    }

    /**
     * Make sure we don't crash when a browser fails to define a preferred language.
     */
    public function testGetPreferredLocalesNoneDefined(): void
    {
        $info = $this->getLocaleInformation([]);

        self::assertEquals([], $info->getPreferredLocales());
    }

    /**
     * @return LocaleGuesserManager|MockObject
     */
    protected function getGuesserManagerMock()
    {
        return $this
            ->getMockBuilder(LocaleGuesserManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
