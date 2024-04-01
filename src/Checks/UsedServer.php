<?php

namespace Apiboard\Checks;

use Apiboard\Checks\Concerns\AcceptsRequest;
use Apiboard\Checks\Results\ServerUsed;
use Apiboard\Context;
use Apiboard\OpenAPI\Concerns\MatchesStrings;

class UsedServer implements Check
{
    use AcceptsRequest;
    use MatchesStrings;

    public function run(Context $context): Context
    {
        $endpoint = $context->endpoint();

        if ($endpoint?->servers() === null) {
            return $context;
        }

        foreach ($endpoint->servers() as $server) {
            $usedServer = $this->matchingUriPattern(
                $server->url().$endpoint->path()->uri(),
                $this->request->getUri()->__toString(),
            );

            if ($usedServer) {
                $context->add(new ServerUsed($server->url()));
            }
        }

        return $context;
    }
}
