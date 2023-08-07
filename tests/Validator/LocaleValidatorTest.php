<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\Validator;

use HumanDirect\LocaleBundle\Validator\Locale;
use HumanDirect\LocaleBundle\Validator\LocaleValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Test for the LocaleValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleValidatorTest extends BaseMetaValidator
{
    public function testLanguageIsValid(): void
    {
        $constraint = new Locale();
        $this->context->expects(self::never())
            ->method('addViolation')
        ;
        $this->getLocaleValidator()->validate('de', $constraint);
        $this->getLocaleValidator()->validate('deu', $constraint);
        $this->getLocaleValidator()->validate('en', $constraint);
        $this->getLocaleValidator()->validate('eng', $constraint);
        $this->getLocaleValidator()->validate('fr', $constraint);
    }

    public function testLocaleWithRegionIsValid(): void
    {
        $constraint = new Locale();
        $this->context->expects(self::never())
            ->method('addViolation')
        ;
        $this->getLocaleValidator()->validate('de_DE', $constraint);
        $this->getLocaleValidator()->validate('en_US', $constraint);
        $this->getLocaleValidator()->validate('en_PH', $constraint);  // Filipino English
        $this->getLocaleValidator()->validate('fr_FR', $constraint);
        $this->getLocaleValidator()->validate('fr_CH', $constraint);
        $this->getLocaleValidator()->validate('fr_US', $constraint);
    }

    public function testLocaleWithScriptValid(): void
    {
        $constraint = new Locale();
        $this->context->expects(self::never())
            ->method('addViolation')
        ;
        $this->getLocaleValidator()->validate('zh_Hant_HK', $constraint);
    }

    public function testLocaleIsInvalid(): void
    {
        $constraint = new Locale();
        // Need to distinguish, since the intl fallback allows every combination of languages, script and regions
        $this->context->expects(self::exactly(3))
            ->method('addViolation')
        ;

        $this->getLocaleValidator()->validate('foobar', $constraint);
        $this->getLocaleValidator()->validate('de_FR', $constraint);
        $this->getLocaleValidator()->validate('fr_US', $constraint);
        $this->getLocaleValidator()->validate('foo_bar', $constraint);
        $this->getLocaleValidator()->validate('foo_bar_baz', $constraint);
    }

    public function testValidateThrowsUnexpectedTypeException(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $validator = new LocaleValidator();
        $validator->validate([], $this->getMockConstraint());
    }

    public function testValidateEmptyLocale(): void
    {
        $validator = new LocaleValidator();

        $validator->validate(null, $this->getMockConstraint());
        $validator->validate('', $this->getMockConstraint());

        $this->context->expects(self::never())
            ->method('addViolation')
        ;
    }

    /**
     * @return MockObject|Constraint
     */
    protected function getMockConstraint()
    {
        return $this->createMock(Constraint::class);
    }
}
