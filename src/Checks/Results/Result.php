<?php

namespace Apiboard\Checks\Results;

use Apiboard\Checks\Check;
use DateTime;

class Result
{
    protected Check $check;

    protected array $data;

    protected DateTime $date;

    private function __construct(Check $check, array $data)
    {
        $this->check = $check;
        $this->data = $data;
        $this->date = new DateTime();
    }

    public static function new(Check $check, array $details = []): self
    {
        return new self($check, $details);
    }

    public function check(): Check
    {
        return $this->check;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function date(): DateTime
    {
        return $this->date;
    }
}
