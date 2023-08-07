<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleGuesser\HeaderLocaleGuesser;
use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

class HeaderLocaleGuesserTest extends AbstractTestGuesser
{
    public function testGuesserExtendsInterface(): void
    {
        $metaValidator = $this->getMockMetaValidator();
        $guesser = $this->getGuesser($metaValidator);
        self::assertInstanceOf(LocaleGuesserInterface::class, $guesser);
    }

    public function testNoPreferredLocale(): void
    {
        $metaValidator = $this->getMockMetaValidator();
        $guesser = $this->getGuesser($metaValidator);
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');

        self::assertFalse($guesser->guess($request));
        self::assertNull($guesser->getIdentifiedLocale());
    }

    public function testLocaleIsIdentifiedFromHeader(): void
    {
        $metaValidator = $this->getMockMetaValidator();
        $request = $this->getRequestWithLocaleHeader();
        $guesser = $this->getGuesser($metaValidator);

        $metaValidator->expects(self::once())
            ->method('isAllowed')
            ->willReturn(true)
        ;

        self::assertTrue($guesser->guess($request));
        self::assertEquals('fr_FR', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIsNoMatchedLanguage(): void
    {
        $metaValidator = $this->getMockMetaValidator();
        $request = $this->getRequestWithLocaleHeader('');
        $guesser = $this->getGuesser($metaValidator);

        $metaValidator
            ->method('isAllowed')
            ->willReturn(false)
        ;
        self::assertFalse($guesser->guess($request));
        self::assertNull($guesser->getIdentifiedLocale());
    }

    /**
     * @return array<string, array{string[], string, bool}>
     */
    public function correctLocales(): array
    {
        return [
            'strict 1' => [['en', 'de', 'fr'], 'fr', true],
            'unstrict 1' => [['en', 'de', 'fr'], 'fr_CH', false],
            'strict 2' => [['de', 'en_GB', 'fr', 'fr_FR'], 'fr', true],
            'unstrict 2' => [['de', 'en_GB', 'fr', 'fr_FR'], 'fr_CH', false],
            'strict 3' => [['en', 'en_GB', 'fr', 'fr'], 'fr', true],
            'unstrict 3' => [['en', 'en_GB', 'fr', 'fr_CH'], 'fr_CH', false],
            'strict 4' => [['fr', 'en_GB'], 'fr', true],
            'unstrict 4' => [['fr', 'en_GB'], 'fr_CH', false],
            'strict 5' => [['fr_LI', 'en'], 'en', true],
            'unstrict 5' => [['fr_LI', 'en'], 'en_GB', false],
        ];
    }

    /**
     * @dataProvider correctLocales
     *
     * @param string[] $allowedLocales
     */
    public function testEnsureCorrectLocaleForAllowedLocales(
        array $allowedLocales,
        string $result,
        bool $strict
    ): void {
        $metaValidator = $this->getMockMetaValidator();
        $request = $this->getRequestWithLocaleHeader($result);
        $guesser = $this->getGuesser($metaValidator);

        // Emulate a simple validator for strict mode
        $metaValidator->expects(self::atLeastOnce())
            ->method('isAllowed')
            ->willReturnCallback(function ($v) use ($allowedLocales, $strict): bool {
                if (\in_array($v, $allowedLocales, true)) {
                    return true;
                }
                if (!$strict) {
                    $splitLocale = explode('_', $v);
                    $v = \count($splitLocale) > 1 ? $splitLocale[0] : $v;

                    return \in_array($v, $allowedLocales, true);
                }

                return false;
            })
        ;

        self::assertTrue($guesser->guess($request));
        self::assertEquals($result, $guesser->getIdentifiedLocale());
    }

    private function getGuesser(MetaValidator $metaValidator): HeaderLocaleGuesser
    {
        return new HeaderLocaleGuesser($metaValidator);
    }

    private function getRequestWithLocaleHeader(string $locale = 'fr_FR'): Request
    {
        $request = Request::create('/');
        $request->headers->set('x-locale', $locale);

        return $request;
    }
}
