<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleInformation;

class AllowedLocalesProvider
{
    /**
     * @param string[] $allowedLocales
     */
    public function __construct(protected array $allowedLocales = [])
    {
    }

    /**
     * @return string[]
     */
    public function getAllowedLocales(): array
    {
        return $this->allowedLocales;
    }

    /**
     * @param string[] $allowedLocales
     */
    public function setAllowedLocales(array $allowedLocales): void
    {
        $this->allowedLocales = $allowedLocales;
    }
}
