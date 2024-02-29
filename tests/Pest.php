<?php

use Apiboard\Checks\Check;
use Apiboard\Checks\Results\Context;
use Apiboard\Checks\Results\Result;
use Apiboard\Logging\Logger;
use Apiboard\Reporting\Reporter;

function arrayLogger(): ArrayLogger
{
    return new ArrayLogger();
}

function testCheck(): TestCheck
{
    return new TestCheck();
}

class ArrayLogger implements Logger
{
    /**
     * @var array<string, array>
     */
    protected array $logged = [];

    public function assertNotEmpty(): void
    {
        expect($this->logged)->not->toBeEmpty();
    }

    public function recap(Reporter $reporter): int
    {
        return count($this->logged);
    }

    public function process(Context $context): void
    {
        $this->logged[] = $context;
    }

    public function trim(): void
    {
    }
}

class TestCheck implements Check
{
    protected array $results = [];

    public function addResult(Result $result): self
    {
        $this->results[] = $result;

        return $this;
    }

    public function id(): string
    {
        return 'test-check';
    }

    public function run(Context $context): Context
    {
        foreach ($this->results as $result) {
            $context->add($result);
        }

        return $context;
    }
}
