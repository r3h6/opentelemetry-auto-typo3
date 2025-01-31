<?php

declare(strict_types=1);

namespace R3H6\Opentelemetry\Tests\Unit\Hooks;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ContentObjectHookTest extends AbstractTestCase
{
    #[Test]
    public function cObjGetSingleIsInstrumented(): void
    {
        $expected = [
            'cObj.type' => 'TEXT',
            'cObj.key' => '__',
            'cObj.content' => 'content',
        ];
        $subject = self::createMock(ContentObjectRenderer::class);
        $subject->method('cObjGetSingle')->willReturn('content');
        $subject->cObjGetSingle('TEXT', ['value' => 'content']);
        $span = $this->storage->offsetGet(0);
        self::assertSame(get_class($subject) . '::cObjGetSingle', $span->getName());
        self::assertArraySubset($expected, $span->getAttributes()->toArray());
    }
}
