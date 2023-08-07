<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Twig\Environment;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class LocaleSwitchHelper extends Helper
{
    public function __construct(private readonly Environment $twig)
    {
    }

    /**
     * @param array<mixed> $parameters
     */
    public function renderSwitch(string $template, array $parameters = []): string
    {
        return $this->twig->render($template, $parameters);
    }

    public function getName(): string
    {
        return 'locale_switch_helper';
    }
}
