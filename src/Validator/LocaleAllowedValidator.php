<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Validator;

use HumanDirect\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator to check if a locale is allowed by the configuration
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleAllowedValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ?AllowedLocalesProvider $allowedLocalesProvider = null,
        private readonly bool $strictMode = false
    ) {
    }

    /**
     * @param string|object|null $value The locale to be validated
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

        /** @var Locale|LocaleAllowed $constraint */
        if ($this->strictMode) {
            if (!\in_array($locale, $this->getAllowedLocales(), true)) {
                $this->context->addViolation($constraint->message, ['%string%' => $locale]);
            }
        } else {
            $primary = \Locale::getPrimaryLanguage($locale);

            if (!\in_array($locale, $this->getAllowedLocales(), true)
                && (!\in_array($primary, $this->getAllowedLocales(), true))) {
                $this->context->addViolation($constraint->message, ['%string%' => $locale]);
            }
        }
    }

    /**
     * @return string[]
     */
    protected function getAllowedLocales(): array
    {
        if (null !== $this->allowedLocalesProvider) {
            return $this->allowedLocalesProvider->getAllowedLocales();
        }

        return [];
    }
}
