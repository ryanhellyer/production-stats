<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use RyanHellyer\ProductionStats\Core\HtmlResponseInjector;
use Symfony\Component\HttpFoundation\Response;

class HtmlResponseInjectorTest extends TestCase
{
    private HtmlResponseInjector $injector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->injector = new HtmlResponseInjector();
    }


    public function testInjectsCommentIntoHtmlResponse(): void
    {
        $response = $this->injector->inject(
            new Response('<html><body>Test</body></html>', 200, ['Content-Type' => 'text/html']),
            42.3
        );

        $content = (string) $response->getContent();
        $this->assertStringContainsString('<!-- Page generated in 42 ms at', $content);
        $this->assertStringContainsString('-->', $content);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $content);
    }

    public function testInjectsBeforeClosingBodyTag(): void
    {
        $response = $this->injector->inject(
            new Response('<html><body>Test</body></html>', 200, ['Content-Type' => 'text/html']),
            0
        );

        $content = (string) $response->getContent();
        $this->assertStringContainsString('</body>', $content);
        $this->assertStringNotContainsString('</body></body>', $content);
    }

    public function testSkipsJsonResponse(): void
    {
        $json = '{"message":"Test"}';
        $response = $this->injector->inject(
            new Response($json, 200, ['Content-Type' => 'application/json']),
            0
        );

        $this->assertSame($json, $response->getContent());
    }

    public function testSkipsXmlResponse(): void
    {
        $xml = '<root><item>Test</item></root>';
        $response = $this->injector->inject(
            new Response($xml, 200, ['Content-Type' => 'application/xml']),
            0
        );

        $this->assertSame($xml, $response->getContent());
    }

    public function testSkipsResponseWithNoContentType(): void
    {
        $content = 'Some content';
        $response = $this->injector->inject(new Response($content), 0);

        $this->assertSame($content, $response->getContent());
    }

    public function testHandlesEmptyContent(): void
    {
        $response = $this->injector->inject(new Response('', 200, ['Content-Type' => 'text/html']), 0);

        $this->assertSame('', $response->getContent());
    }

    public function testHtmlResponseWithCharset(): void
    {
        $response = $this->injector->inject(
            new Response('<html><body>x</body></html>', 200, ['Content-Type' => 'text/html; charset=utf-8']),
            5.1
        );

        $this->assertStringContainsString('<!-- Page generated in 5 ms at', (string) $response->getContent());
    }

    public function testIsHtmlResponse(): void
    {
        $this->assertTrue($this->injector->isHtmlResponse(
            new Response('', 200, ['Content-Type' => 'text/html'])
        ));
        $this->assertTrue($this->injector->isHtmlResponse(
            new Response('', 200, ['Content-Type' => 'text/html; charset=utf-8'])
        ));
        $this->assertFalse($this->injector->isHtmlResponse(
            new Response('', 200, ['Content-Type' => 'application/json'])
        ));
        $this->assertFalse($this->injector->isHtmlResponse(new Response('')));
    }

    public function testFormatComment(): void
    {
        $comment = $this->injector->formatComment(123.7);

        $this->assertStringContainsString('Page generated in 123 ms at', $comment);
        $this->assertStringContainsString('-->', $comment);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $comment);
    }
}
