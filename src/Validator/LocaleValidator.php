<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Validator;

use Symfony\Component\Intl\Locales;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for a locale
 *
 * @author Matthias Bredin <mb@lunetics.com>
 */
class LocaleValidator extends ConstraintValidator
{
    /**
     * @param mixed $value The locale to be validated
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $locale = (string) $value;

        $primary = \Locale::getPrimaryLanguage($locale);
        $region = \Locale::getRegion($locale);
        $locales = Locales::getLocales();

        if (!\in_array($locale, $locales, true)
            && !\in_array($primary, $locales, true)
            && (null !== $region && mb_strtolower($primary) !== mb_strtolower($region))) {
            /** @var Locale|LocaleAllowed $constraint */
            $this->context->addViolation($constraint->message, ['%string%' => $locale]);
        }
    }
}
