<?php

namespace App\Listeners\Traits;

use const JSON_ERROR_NONE;

use Illuminate\Support\Str;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function strlen;
use function is_array;
use function is_resource;

trait LogsHttpClient
{
    public const ACTIVITY_LOG_NAME = 'http-client';

    /**
     * Determine if the content is within the set limits.
     *
     * @param  string $content
     * @return bool
     */
    public function contentWithinLimits($content)
    {
        $limit = $this->options['size_limit'] ?? 64;

        return mb_strlen($content) / 1_000 <= $limit;
    }

    /**
     * Format the given headers.
     *
     * @param  array $headers
     * @return array
     */
    protected function headers($headers)
    {
        $headerNames = collect($headers)->keys()->map(static fn ($headerName) => strtolower($headerName))->toArray();

        $headerValues = collect($headers)
            ->map(static fn ($header) => implode(', ', $header))
            ->all();

        $headers = array_combine($headerNames, $headerValues);

        return $this->hideParameters($headers,
            ['encAccountNumber', 'accountNumber']
        );
    }

    /**
     * Extract the input from the given request.
     *
     * @return array
     */
    protected function input(Request $request)
    {
        if (! $request->isMultipart()) {
            return $request->data();
        }

        return collect($request->data())->mapWithKeys(static function ($data) {
            if ($data['contents'] instanceof UploadedFile) {
                $value = [
                    'name' => $data['filename'] ?? $data['contents']->getClientOriginalName(),
                    'size' => ($data['contents']->getSize() / 1_000) . 'KB',
                    'headers' => $data['headers'] ?? [],
                ];
            } elseif (is_resource($data['contents'])) {
                $filesize = @filesize(stream_get_meta_data($data['contents'])['uri']);

                $value = [
                    'name' => $data['filename'] ?? null,
                    'size' => $filesize ? ($filesize / 1_000) . 'KB' : null,
                    'headers' => $data['headers'] ?? [],
                ];
            } elseif (false === json_encode($data['contents'])) {
                $value = [
                    'name' => $data['filename'] ?? null,
                    'size' => (strlen($data['contents']) / 1_000) . 'KB',
                    'headers' => $data['headers'] ?? [],
                ];
            } else {
                $value = $data['contents'];
            }

            return [$data['name'] => $value];
        })->toArray();
    }

    /**
     * Get the request duration in milliseconds.
     *
     * @return float|int|null
     */
    protected function duration(Response $response)
    {
        if ($response->transferStats && $response->transferStats->getTransferTime()) {
            return floor($response->transferStats->getTransferTime() * 1_000);
        }

        return null;
    }

    /**
     * Hide the given parameters.
     *
     * @return mixed
     */
    protected function hideParameters(array $data, array $sensitiveFields)
    {
        array_walk_recursive($data, function (&$value, $key) use ($sensitiveFields): void {
            if (in_array($key, $sensitiveFields, true)) {
                $value = '*****';
            }
        });

        return $data;
    }

    /**
     * Format the given response object.
     *
     * @return array|string
     */
    protected function response(Response $response)
    {
        $content = $response->body();

        $stream = $response->toPsrResponse()->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        if (is_array(json_decode($content, true))
            && JSON_ERROR_NONE === json_last_error()) {
            return $this->payload(json_decode($content, true));
        }

        if (Str::startsWith(strtolower($response->header('Content-Type')) ?: '', 'text/plain')) {
            return $content;
        }

        if ($response->redirect()) {
            return 'Redirected to ' . $response->header('Location');
        }

        if (empty($content)) {
            return 'Empty Response';
        }

        return 'HTML Response';
    }

    /**
     * Format the given payload.
     *
     * @param  array $payload
     * @return array
     */
    protected function payload($payload)
    {
        return $this->hideParameters($payload,
            [
                'encAccountNumber',
                'accountNumber',
            ]
        );
    }
}
