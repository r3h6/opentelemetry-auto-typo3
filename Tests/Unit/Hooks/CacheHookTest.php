<?php

declare(strict_types=1);

namespace R3H6\Opentelemetry\Tests\Unit\Hooks;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

class CacheHookTest extends AbstractTestCase
{
    #[Test]
    public function cObjGetSingleIsInstrumented(): void
    {
        $expected = [
            'cache.key' => 'test-id',
            'cache.identifier' => 'test-cache',
            'cache.operation' => 'set',
            // 'cache.tags' => 'set',
        ];
        $subject = self::createMock(FrontendInterface::class);
        $subject->method('getIdentifier')->willReturn('test-cache');
        $subject->set('test-id', 'test-value', ['tag1', 'tag2'], 3600);
        $span = $this->storage->offsetGet(0);
        self::assertSame('cache::set', $span->getName());
        self::assertArraySubset($expected, $span->getAttributes()->toArray());
    }
}
