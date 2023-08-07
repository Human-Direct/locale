<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for a guesser
 *
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 * @author Matthias Breddin <mb@lunetics.com>
 */
interface LocaleGuesserInterface
{
    public function guess(Request $request): bool;

    public function getIdentifiedLocale(): ?string;
}
