<?php

namespace Apiboard\Reporting;

use Apiboard\Reporting\Reports\Report;

interface Reporter
{
    /**
     * @return array<array-key,Report>
     */
    public function reports(): array;

    public function write(Report $report): void;
}
