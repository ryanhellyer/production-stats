<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats\Tests\Unit\Laravel;

use Closure;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use RyanHellyer\ProductionStats\Core\HtmlResponseInjector;
use RyanHellyer\ProductionStats\Laravel\Http\Middleware\InjectLoadTime;
use Symfony\Component\HttpFoundation\Response;

class InjectLoadTimeTest extends TestCase
{
    private HtmlResponseInjector $injector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->injector = new HtmlResponseInjector();
    }

    public function testDelegatesToCoreInjector(): void
    {
        $middleware = new InjectLoadTime($this->injector);

        $result = $this->executeMiddleware(
            $middleware,
            '<html><body>Test content</body></html>',
            'text/html; charset=utf-8'
        );

        $this->assertStringContainsString('<!-- Page generated in', $result);
        $this->assertStringContainsString('ms at', $result);
        $this->assertStringContainsString('-->', $result);
    }

    public function testInjectsBeforeClosingBodyTag(): void
    {
        $middleware = new InjectLoadTime($this->injector);

        $result = $this->executeMiddleware(
            $middleware,
            '<html><body>Test content</body></html>',
            'text/html'
        );

        $this->assertStringContainsString('</body>', $result);
        $this->assertStringNotContainsString('</body></body>', $result);
    }

    public function testSkipsJsonResponse(): void
    {
        $middleware = new InjectLoadTime($this->injector);

        $jsonContent = '{"message":"Test"}';
        $result = $this->executeMiddleware($middleware, $jsonContent, 'application/json');

        $this->assertStringNotContainsString('<!-- Page generated in', $result);
        $this->assertEquals($jsonContent, $result);
    }

    public function testSkipsResponseWithNoContentType(): void
    {
        $middleware = new InjectLoadTime($this->injector);

        $content = 'Some content';
        $result = $this->executeMiddleware($middleware, $content, '');

        $this->assertStringNotContainsString('<!-- Page generated in', $result);
        $this->assertEquals($content, $result);
    }

    public function testHandlesEmptyResponse(): void
    {
        $middleware = new InjectLoadTime($this->injector);

        $result = $this->executeMiddleware($middleware, '', 'text/html');

        $this->assertEquals('', $result);
    }

    /**
     * Execute middleware with given content and content type, returning the processed content.
     */
    private function executeMiddleware(InjectLoadTime $middleware, string $content, string $contentType): string
    {
        $response = new Response($content);
        if ($contentType) {
            $response->headers->set('Content-Type', $contentType);
        }

        $result = $middleware->handle(new Request(), $this->createNextMiddleware($response));
        return (string) $result->getContent();
    }

    /**
     * Create a mock next middleware that returns the given response.
     */
    private function createNextMiddleware(Response $response): Closure
    {
        return function ($request) use ($response) {
            return $response;
        };
    }
}
