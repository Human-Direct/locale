<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\Validator;

use HumanDirect\LocaleBundle\Validator\LocaleAllowed;
use HumanDirect\LocaleBundle\Validator\LocaleAllowedValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Test for the LocaleAllowedValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleAllowedValidatorTest extends BaseMetaValidator
{
    public function testLocaleIsAllowed(): void
    {
        $constraint = new LocaleAllowed();
        $this->context->expects(self::never())
            ->method('addViolation')
        ;
        $this->getLocaleAllowedValidator(['en', 'de'], false)->validate('en', $constraint);
    }

    public function testLocaleIsAllowedNonStrict(): void
    {
        $constraint = new LocaleAllowed();
        $this->context->expects(self::never())
            ->method('addViolation')
        ;
        $this->getLocaleAllowedValidator(['en', 'de'], false)->validate('de_DE', $constraint);
    }

    public function testEmptyAllowedList(): void
    {
        $constraint = new LocaleAllowed();
        $this->context->expects(self::once())
            ->method('addViolation')
        ;
        $this->getLocaleAllowedValidator([], false)->validate('en', $constraint);
    }

    public function testLocaleIsNotAllowed(): void
    {
        $locale = 'fr';
        $constraint = new LocaleAllowed();
        $this->context->expects(self::exactly(2))
            ->method('addViolation')
            ->with($this->equalTo($constraint->message), $this->equalTo(['%string%' => $locale]))
        ;
        $this->getLocaleAllowedValidator(['en', 'de'], false)->validate($locale, $constraint);
        $this->getLocaleAllowedValidator(['en_US', 'de_DE'], false)->validate($locale, $constraint);
    }

    public function testLocaleIsAllowedStrict(): void
    {
        $constraint = new LocaleAllowed();
        $this->context->expects(self::never())
            ->method('addViolation')
        ;
        $this->getLocaleAllowedValidator(['en', 'de', 'fr'], true)->validate('fr', $constraint);
        $this->getLocaleAllowedValidator(['de_AT', 'de_CH', 'fr_FR'], true)->validate('fr_FR', $constraint);
        $this->getLocaleAllowedValidator(['de_AT', 'en', 'fr'], true)->validate('fr', $constraint);
        $this->getLocaleAllowedValidator(['de_AT', 'en', 'fr'], true)->validate('de_AT', $constraint);
    }

    public function testLocaleIsNotAllowedStrict(): void
    {
        $constraint = new LocaleAllowed();
        $this->context->expects(self::exactly(2))
            ->method('addViolation')
        ;
        $this->getLocaleAllowedValidator(['en', 'de'], true)->validate('de_AT', $constraint);
        $this->getLocaleAllowedValidator(['en_US', 'de_DE'], true)->validate('de', $constraint);
    }

    public function testValidateThrowsUnexpectedTypeException(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $validator = new LocaleAllowedValidator();
        /** @phpstan-ignore-next-line To test with invalid type */
        $validator->validate([], $this->getMockConstraint());
    }

    public function testValidateEmptyLocale(): void
    {
        $validator = new LocaleAllowedValidator();

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
