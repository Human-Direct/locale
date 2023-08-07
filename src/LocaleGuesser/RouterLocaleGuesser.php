<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Locale Guesser for detecting the locale in the router.
 *
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class RouterLocaleGuesser extends AbstractLocaleGuesser
{
    public function __construct(private readonly MetaValidator $metaValidator)
    {
    }

    /**
     * Method that guess the locale based on the Router parameters.
     */
    public function guess(Request $request): bool
    {
        $locale = $request->attributes->get('_locale');
        if (!$locale || !\is_string($locale)) {
            return false;
        }

        if ($this->metaValidator->isAllowed($locale)) {
            $this->identifiedLocale = $locale;

            return true;
        }

        return false;
    }
}
