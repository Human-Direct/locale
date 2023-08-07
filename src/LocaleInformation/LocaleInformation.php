<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleInformation;

use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\Locales;

/**
 * Information about locales.
 */
class LocaleInformation
{
    public function __construct(
        private readonly MetaValidator $metaValidator,
        private readonly LocaleGuesserManager $manager,
        private readonly ?AllowedLocalesProvider $allowedLocalesProvider = null
    ) {
    }

    /**
     * Returns the configuration of allowed locales.
     *
     * @return string[]
     */
    public function getAllowedLocalesFromConfiguration(): array
    {
        if (null !== $this->allowedLocalesProvider) {
            return $this->allowedLocalesProvider->getAllowedLocales();
        }

        return [];
    }

    /**
     * Returns an array of all allowed locales based on the configuration.
     *
     * @return string[]
     */
    public function getAllAllowedLocales(): array
    {
        return $this->filterAllowed(Locales::getLocales());
    }

    /**
     * Returns an array of all allowed languages based on the configuration.
     *
     * @return string[]
     */
    public function getAllAllowedLanguages(): array
    {
        return $this->filterAllowed(array_keys(Languages::getNames()));
    }

    /**
     * Returns an array of preferred locales.
     *
     * @return string[]
     */
    public function getPreferredLocales(): array
    {
        return $this->filterAllowed($this->manager->getPreferredLocales());
    }

    /**
     * Filter function which returns locales / languages.
     *
     * @param string[] $localeList
     *
     * @return string[]
     */
    private function filterAllowed(array $localeList): array
    {
        $validator = $this->metaValidator;
        $matchLocale = static fn ($locale): bool => $validator->isAllowed($locale);

        $availableLocales = array_values(array_filter($localeList, $matchLocale));
        if (!empty($availableLocales)) {
            return $availableLocales;
        }

        return [];
    }
}
