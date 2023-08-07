<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Cookie Guesser for retrieving a previously detected locale from a cookie.
 *
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class CookieLocaleGuesser extends AbstractLocaleGuesser
{
    public function __construct(
        private readonly MetaValidator $metaValidator,
        private readonly string $localeCookieName
    ) {
    }

    public function guess(Request $request): bool
    {
        if (!$request->cookies->has($this->localeCookieName)) {
            return false;
        }

        $previousLocale = $request->cookies->get($this->localeCookieName);
        if ($this->metaValidator->isAllowed($previousLocale)) {
            $this->identifiedLocale = $previousLocale;

            return true;
        }

        return false;
    }
}
