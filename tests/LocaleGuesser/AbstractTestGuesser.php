<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleGuesser;

use HumanDirect\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractTestGuesser extends TestCase
{
    /**
     * @return MockObject&Request
     */
    protected function getMockRequest(): MockObject
    {
        return $this->createMock(Request::class);
    }

    /**
     * @return MockObject&MetaValidator
     */
    protected function getMockMetaValidator(): MockObject
    {
        return $this->createMock(MetaValidator::class);
    }

    /**
     * @return MockObject&LoggerInterface
     */
    protected function getMockLogger(): MockObject
    {
        return $this->createMock(LoggerInterface::class);
    }

    protected function getRequestWithLocaleQuery(?string $locale = 'en'): Request
    {
        $request = Request::create('/hello-world', 'GET');
        if (null !== $locale) {
            $request->query->set('_locale', $locale);
        }

        return $request;
    }
}
