<?php

namespace Apiboard;

use Apiboard\Checks\Results\Result;
use Apiboard\OpenAPI\Endpoint;
use DateTime;

class Context
{
    protected Api $api;

    protected ?Endpoint $endpoint;

    protected DateTime $initialisedAt;

    protected array $results = [];

    public function __construct(Api $api, ?Endpoint $endpoint, ?DateTime $initialisedAt = null)
    {
        $this->api = $api;
        $this->endpoint = $endpoint;
        $this->initialisedAt = $initialisedAt ?? new DateTime;
    }

    public function hash(): string
    {
        $normalisedEndpoint = json_encode(
            $this->normaliseArray(
                $this->endpoint()?->jsonSerialize() ?? [],
                'title',
                'description',
                'summary',
                'tags',
                'operationId',
                'requestBody',
                'externalDocs',
                'example',
                'examples',
                'schema',
                'parameters',
                'x-*',
                '$*',
            ),
        );

        return md5("{$this->api()->id()}:{$normalisedEndpoint}");
    }

    public function api(): Api
    {
        return $this->api;
    }

    public function endpoint(): ?Endpoint
    {
        return $this->endpoint;
    }

    public function initialisedAt(): DateTime
    {
        return $this->initialisedAt;
    }

    public function add(Result ...$results): void
    {
        foreach ($results as $result) {
            $this->results[] = $result;
        }
    }

    /**
     * @return array<array-key,Result>
     */
    public function results(): array
    {
        return $this->results;
    }

    protected function normaliseArray(array $data, string ...$keysToRemove): array
    {
        ksort($data);

        foreach ($data as $key => $value) {
            foreach ($keysToRemove as $keyToRemove) {
                if (fnmatch($keyToRemove, $key)) {
                    unset($data[$key]);

                    continue;
                }
            }

            if (is_array($value)) {
                $data[$key] = $this->normaliseArray($value, ...$keysToRemove);
            }
        }

        return $data;
    }
}
