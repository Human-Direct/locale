<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Matcher;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
interface BestLocaleMatcherInterface
{
    public function match(string $locale): bool|string;
}
