<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Matcher;

use HumanDirect\LocaleBundle\LocaleInformation\AllowedLocalesProvider;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
class BestLocaleMatcher implements BestLocaleMatcherInterface
{
    public function __construct(private readonly AllowedLocalesProvider $allowedLocales)
    {
    }

    public function match(string $locale): bool|string
    {
        $allowedLocales = $this->allowedLocales->getAllowedLocales();

        uasort($allowedLocales, static fn ($a, $b): int => mb_strlen($b) - mb_strlen($a));

        foreach ($allowedLocales as $allowedLocale) {
            if (str_starts_with($locale, $allowedLocale)) {
                return $allowedLocale;
            }
        }

        return false;
    }
}
