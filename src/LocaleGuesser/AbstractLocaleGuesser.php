<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
abstract class AbstractLocaleGuesser implements LocaleGuesserInterface
{
    protected ?string $identifiedLocale = null;

    public function getIdentifiedLocale(): ?string
    {
        if (!$this->identifiedLocale) {
            return null;
        }

        return $this->identifiedLocale;
    }
}
