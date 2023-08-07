<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Locale Guesser for detecting the locale from the browser Accept-language string.
 *
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class BrowserLocaleGuesser extends AbstractLocaleGuesser
{
    public function __construct(private readonly MetaValidator $metaValidator)
    {
    }

    /**
     * Guess the locale based on the browser settings.
     */
    public function guess(Request $request): bool
    {
        $validator = $this->metaValidator;

        // get the preferred locale from the browser
        $preferredLocale = $request->getPreferredLanguage();
        $availableLocales = $request->getLanguages();

        if (!$preferredLocale || 0 === \count($availableLocales)) {
            return false;
        }

        // if the preferred primary locale is allowed, return the locale
        if ($validator->isAllowed($preferredLocale)) {
            $this->identifiedLocale = $preferredLocale;

            return true;
        }

        // get a list of available and allowed locales and return the first result
        $matchLocale = static fn ($v): bool => $validator->isAllowed($v);

        /** @var array<int, string> $result */
        $result = array_values(array_filter($availableLocales, $matchLocale));
        if (!empty($result)) {
            $this->identifiedLocale = $result[0];

            return true;
        }

        return false;
    }
}
