<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\Validator;

use HumanDirect\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use HumanDirect\LocaleBundle\Validator\LocaleAllowedValidator;
use HumanDirect\LocaleBundle\Validator\LocaleValidator;
use HumanDirect\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * Test for the LocaleValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class BaseMetaValidator extends TestCase
{
    /**
     * @var ExecutionContextInterface|MockObject
     */
    protected $context;

    protected function setUp(): void
    {
        $this->context = $this->getContext();
    }

    /**
     * @param string[] $allowedLocales
     */
    public function getMetaValidator(
        array $allowedLocales = [],
        bool $strict = false
    ): MetaValidator {
        $factory = new ConstraintValidatorFactory(
            $this->getLocaleValidator(),
            $this->getLocaleAllowedValidator($allowedLocales, $strict)
        );
        $validator = Validation::createValidatorBuilder();
        $validator->setConstraintValidatorFactory($factory);

        return new MetaValidator($validator->getValidator());
    }

    /**
     * @return ExecutionContextInterface|MockObject
     */
    public function getContext()
    {
        return $this
            ->getMockBuilder(ExecutionContext::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function getLocaleValidator(): LocaleValidator
    {
        $validator = new LocaleValidator();
        $validator->initialize($this->context);

        return $validator;
    }

    /**
     * @param string[] $allowedLocales
     */
    public function getLocaleAllowedValidator(
        array $allowedLocales = [],
        bool $strictMode = false
    ): LocaleAllowedValidator {
        $allowedLocalesProvider = new AllowedLocalesProvider($allowedLocales);
        $validator = new LocaleAllowedValidator($allowedLocalesProvider, $strictMode);
        $validator->initialize($this->context);

        return $validator;
    }
}

class ConstraintValidatorFactory implements ConstraintValidatorFactoryInterface
{
    /**
     * @var array<string, ConstraintValidatorInterface>
     */
    protected array $validators = [];
    protected LocaleValidator $localeValidator;
    protected LocaleAllowedValidator $localeAllowedValidator;

    public function __construct(
        LocaleValidator $localeValidator,
        LocaleAllowedValidator $localeAllowedValidator
    ) {
        $this->localeValidator = $localeValidator;
        $this->localeAllowedValidator = $localeAllowedValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance(Constraint $constraint): ConstraintValidatorInterface
    {
        $className = $constraint->validatedBy();

        if ($className === 'human_direct_locale.validator.locale') {
            $this->validators[$className] = $this->localeValidator;
        }

        if ($className === 'human_direct_locale.validator.locale_allowed') {
            $this->validators[$className] = $this->localeAllowedValidator;
        }

        return $this->validators[$className];
    }
}
