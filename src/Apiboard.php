<?php

namespace Apiboard;

use Apiboard\Checks\Checks;
use Apiboard\Checks\DeprecatedOperation;
use Apiboard\Checks\DeprecatedParameters;
use Apiboard\Logging\Logger;
use Apiboard\Logging\NullLogger;
use Apiboard\Logging\Sampler;
use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Apiboard
{
    protected array $apis;

    protected bool $enabled = true;

    protected Closure $beforeRunningChecks;

    protected Closure $runChecksCallback;

    protected Closure $logResolverCallback;

    public function __construct(array $apis)
    {
        $this->apis = $apis;
        $this->beforeRunningChecks = fn () => null;
        $this->runChecksCallback = fn (Checks $checks) => $checks->__invoke();
        $this->logResolverCallback = fn () => new NullLogger;
    }

    public function beforeRunningChecks(Closure $beforeRunningChecks): void
    {
        $this->beforeRunningChecks = $beforeRunningChecks;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function isDisabled(): bool
    {
        return $this->enabled === false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled === true;
    }

    public function runChecksUsing(Closure $callback): void
    {
        $this->runChecksCallback = $callback;
    }

    public function resolveLoggerUsing(Closure $callback): void
    {
        $this->logResolverCallback = $callback;
    }

    public function inspect(string $name, RequestInterface $request, ?ResponseInterface $response = null): void
    {
        if ($this->isDisabled()) {
            return;
        }

        $config = $this->apis[$name];

        $api = new Api($name, $config['openapi']);

        $checks = new Checks(
            $api,
            $this->resolveLogger($config['logger'] ?? null),
            $request,
            $response,
        );

        $checks->add(new DeprecatedOperation);
        $checks->add(new DeprecatedParameters);

        ($this->beforeRunningChecks)($checks);

        $sampler = new Sampler($config['sample_rate'] ?? 1, $this->runChecksCallback);

        $sampler->__invoke($checks);
    }

    protected function resolveLogger(string|Logger|null $logger): Logger
    {
        $logResolver = $this->logResolverCallback;

        return match (true) {
            $logger instanceof Logger => $logger,
            default => $logResolver($logger),
        };
    }
}
