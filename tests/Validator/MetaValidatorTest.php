<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\Validator;

/**
 * Test for the LocaleValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class MetaValidatorTest extends BaseMetaValidator
{
    public function testLocaleIsAllowedNonStrict(): void
    {
        $metaValidator = $this->getMetaValidator(['en', 'de']);

        self::assertTrue($metaValidator->isAllowed('en'));
        self::assertTrue($metaValidator->isAllowed('en_US'));
        self::assertTrue($metaValidator->isAllowed('de'));
        self::assertTrue($metaValidator->isAllowed('de_AT'));
        self::assertTrue($metaValidator->isAllowed('de_FR'));
    }

    public function testLocaleIsNotAllowedNonStrict(): void
    {
        $metaValidator = $this->getMetaValidator(['en', 'de']);

        self::assertFalse($metaValidator->isAllowed('fr'));
        self::assertFalse($metaValidator->isAllowed('fr_FR'));
    }

    public function testLocaleIsAllowedStrict(): void
    {
        $metaValidator = $this->getMetaValidator(['en', 'de_AT'], true);

        self::assertTrue($metaValidator->isAllowed('en'));
        self::assertTrue($metaValidator->isAllowed('de_AT'));
    }

    public function testLocaleIsNotAllowedStrict(): void
    {
        $metaValidator = $this->getMetaValidator(['en', 'de_AT'], true);

        self::assertFalse($metaValidator->isAllowed('en_US'));
        self::assertFalse($metaValidator->isAllowed('de'));
    }
}
