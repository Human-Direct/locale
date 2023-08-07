<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Twig\Extension;

use HumanDirect\LocaleBundle\Switcher\TargetInformationBuilder;
use HumanDirect\LocaleBundle\Templating\Helper\LocaleSwitchHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleSwitcherExtension extends AbstractExtension
{
    public function __construct(
        private readonly TargetInformationBuilder $targetInformationBuilder,
        private readonly LocaleSwitchHelper $localeSwitchHelper
    ) {
    }

    /**
     * @return array<int, TwigFunction> The added functions
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('locale_switcher', [$this, 'renderSwitcher'], ['is_safe' => ['html']]),
        ];
    }

    public function getName(): string
    {
        return 'locale_switcher';
    }

    /**
     * @param array<mixed> $parameters
     *
     * @throws \Exception
     */
    public function renderSwitcher(
        string $template,
        ?string $route = null,
        array $parameters = []
    ): string {
        return $this->localeSwitchHelper->renderSwitch(
            $template,
            $this->targetInformationBuilder->getTargetInformation($route, $parameters)
        );
    }
}
