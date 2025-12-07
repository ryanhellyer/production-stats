<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats\Tests\Unit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use RyanHellyer\ProductionStats\Http\Middleware\InjectLoadTime;
use Symfony\Component\HttpFoundation\Response;

class InjectLoadTimeTest extends TestCase
{
    private InjectLoadTime $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new InjectLoadTime();
    }

    public function testInjectsLoadTimeIntoHtmlResponse(): void
    {
        $result = $this->executeMiddleware('<html><body>Test content</body></html>', 'text/html; charset=utf-8');

        $this->assertStringContainsString('<!-- Page generated in', $result);
        $this->assertStringContainsString('ms at', $result);
        $this->assertStringContainsString('-->', $result);

        // Check if timestamp is present
        $pattern = '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/';
        $this->assertMatchesRegularExpression($pattern, $result);
    }

    public function testInjectsBeforeClosingBodyTag(): void
    {
        $result = $this->executeMiddleware('<html><body>Test content</body></html>', 'text/html');

        $this->assertStringContainsString('</body>', $result);
        $this->assertStringNotContainsString('</body></body>', $result);
    }

    public function testSkipsJsonResponse(): void
    {
        $jsonContent = '{"message":"Test"}';
        $result = $this->executeMiddleware($jsonContent, 'application/json');

        $this->assertStringNotContainsString('<!-- Page generated in', $result);
        $this->assertEquals($jsonContent, $result);
    }

    public function testSkipsResponseWithNoContentType(): void
    {
        $content = 'Some content';
        $result = $this->executeMiddleware($content, '');

        $this->assertStringNotContainsString('<!-- Page generated in', $result);
        $this->assertEquals($content, $result);
    }

    public function testHandlesEmptyResponse(): void
    {
        $result = $this->executeMiddleware('', 'text/html');

        $this->assertEquals('', $result);
    }

    /**
     * Execute middleware with given content and content type, returning the processed content.
     */
    private function executeMiddleware(string $content, string $contentType): string
    {
        $response = new Response($content);
        if ($contentType) {
            $response->headers->set('Content-Type', $contentType);
        }

        $result = $this->middleware->handle(new Request(), $this->createNextMiddleware($response));
        return $result->getContent();
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
