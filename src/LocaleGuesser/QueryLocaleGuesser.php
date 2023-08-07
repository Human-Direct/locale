<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

/**
 * This guesser class checks the query parameter for a locale.
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class QueryLocaleGuesser extends AbstractLocaleGuesser
{
    public function __construct(
        private readonly MetaValidator $metaValidator,
        private readonly string $queryParameterName = '_locale'
    ) {
    }

    /**
     * Guess the locale based on the query parameter variable.
     */
    public function guess(Request $request): bool
    {
        if (!$request->query->has($this->queryParameterName)) {
            return false;
        }

        $queryLocale = $request->query->get($this->queryParameterName);
        if ($this->metaValidator->isAllowed($queryLocale)) {
            $this->identifiedLocale = $queryLocale;

            return true;
        }

        return false;
    }
}
