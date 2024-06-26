<?php

namespace Tests\Builders;

use Apiboard\OpenAPI\Endpoint;
use Apiboard\OpenAPI\Structure\Operation;
use Apiboard\OpenAPI\Structure\Parameter;
use Apiboard\OpenAPI\Structure\PathItem;
use Apiboard\OpenAPI\Structure\Response;
use Apiboard\OpenAPI\Structure\Schema;
use Apiboard\OpenAPI\Structure\Server;
use Apiboard\OpenAPI\Structure\Servers;
use Psr\Log\LoggerInterface;

class EndpointBuilder extends Builder
{
    protected ?LoggerInterface $logger = null;

    protected string $method = 'GET';

    protected string $uri = '/';

    protected array $path = [];

    protected array $operation = [];

    protected array $servers = [];

    public function method(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function uri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function deprecated(bool $deprecated = true): self
    {
        $this->operation['deprecated'] = $deprecated;

        return $this;
    }

    public function parameters(Parameter ...$parameters): self
    {
        foreach ($parameters as $parameter) {
            $this->operation['parameters'][] = $parameter->toArray();
        }

        return $this;
    }

    public function requestBody(string $contentType, Schema $schema): self
    {
        $this->operation['requestBody']['content'][$contentType] = [
            'schema' => $schema->toArray(),
        ];

        return $this;
    }

    public function responses(Response ...$responses): self
    {
        foreach ($responses as $response) {
            $this->operation['responses'][$response->statusCode()] = $response->jsonSerialize();
        }

        return $this;
    }

    public function servers(Server ...$servers): self
    {
        foreach ($servers as $server) {
            $this->servers[] = $server;
        }

        return $this;
    }

    public function make(): Endpoint
    {
        return new Endpoint(
            new Servers($this->servers),
            new PathItem($this->uri, $this->path),
            new Operation($this->method, $this->operation),
        );
    }
}
