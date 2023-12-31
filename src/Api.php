<?php

namespace Apiboard;

use Apiboard\Checks\Check;
use Apiboard\Logging\Logger;
use Apiboard\OpenAPI\Endpoint;
use Apiboard\OpenAPI\EndpointMatcher;
use Apiboard\OpenAPI\OpenAPI;
use Apiboard\OpenAPI\Structure\Document;
use Closure;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class Api
{
    protected string $id;

    protected string $openapi;

    protected LoggerInterface $logger;

    protected Closure $checkRunner;

    protected ?Document $document = null;

    public function __construct(string $id, string $openapi, LoggerInterface $logger, Closure $checkRunner)
    {
        $this->id = $id;
        $this->openapi = $openapi;
        $this->logger = $logger;
        $this->checkRunner = $checkRunner;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function openapi(): string
    {
        return $this->openapi;
    }

    public function specification(): Document
    {
        return $this->document ??= (new OpenAPI())->parse($this->openapi);
    }

    public function log(): Logger
    {
        return new Logger($this->logger);
    }

    public function inspect(MessageInterface $message): void
    {
        if ($message instanceof RequestInterface) {
            $endpoint = $this->matchingEndpoint($message);

            foreach ($endpoint?->checksFor($message) ?? [] as $check) {
                $this->runCheck($check);
            }
        }
    }

    public function matchingEndpoint(RequestInterface $request): ?Endpoint
    {
        $endpoint = new EndpointMatcher($this);

        return $endpoint->matchingIn($request);
    }

    protected function runCheck(Check $check): void
    {
        ($this->checkRunner)($check);
    }
}
