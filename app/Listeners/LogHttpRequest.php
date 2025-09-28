<?php

namespace App\Listeners;

use const JSON_ERROR_NONE;

use Illuminate\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Foundation\Http\Events\RequestHandled;

use function defined;
use function in_array;
use function is_array;
use function is_object;
use function is_string;

class LogHttpRequest
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(RequestHandled $event): void
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $event->request->server('REQUEST_TIME_FLOAT');

        if (! app()->runningUnitTests()) {
            logs('stderr')
                ->info('http request/response', [
                    'ip_address' => $event->request->ip(),
                    'uri' => str_replace($event->request->root(), '', $event->request->fullUrl()) ?: '/',
                    'method' => $event->request->method(),
                    'controller_action' => optional($event->request->route())->getActionName(),
                    'middleware' => array_values(optional($event->request->route())->gatherMiddleware() ?? []),
                    'headers' => $this->headers($event->request->headers->all()),
                    'payload' => $this->payload($this->input($event->request)),
                    'session' => $this->payload($this->sessionVariables($event->request)),
                    'response_headers' => $this->headers($event->response->headers->all()),
                    'response_status' => $event->response->getStatusCode(),
                    'response' => $this->response($event->response),
                    'duration' => $startTime ? floor((microtime(true) - $startTime) * 1_000) : null,
                    'memory' => round(memory_get_peak_usage(true) / 1_024 / 1_024, 1),
                ]);
        }
    }

    /**
     * Determine if the content is within the set limits.
     *
     * @param  string $content
     * @return bool
     */
    public function contentWithinLimits($content)
    {
        $limit = $this->options['size_limit'] ?? 64;

        return intdiv(mb_strlen($content), 1_000) <= $limit;
    }

    /**
     * Determine if the request should be ignored based on its method.
     *
     * @param  mixed $event
     * @return bool
     */
    protected function shouldIgnoreHttpMethod($event)
    {
        return in_array(
            strtolower($event->request->method()),
            collect($this->options['ignore_http_methods'] ?? [])->map(static fn ($method) => strtolower($method))->all()
        );
    }

    /**
     * Determine if the request should be ignored based on its status code.
     *
     * @param  mixed $event
     * @return bool
     */
    protected function shouldIgnoreStatusCode($event)
    {
        return in_array(
            $event->response->getStatusCode(),
            $this->options['ignore_status_codes'] ?? []
        );
    }

    /**
     * Format the given headers.
     *
     * @param  array $headers
     * @return array
     */
    protected function headers($headers)
    {
        $headers = collect($headers)
            ->map(static fn ($header) => implode(', ', $header))
            ->all();

        return $this->hideParameters($headers,
            []
        );
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
            []
        );
    }

    /**
     * Hide the given parameters.
     *
     * @param  array $data
     * @param  array $hidden
     * @return mixed
     */
    protected function hideParameters($data, $hidden)
    {
        foreach ($hidden as $parameter) {
            if (Arr::get($data, $parameter)) {
                Arr::set($data, $parameter, '********');
            }
        }

        return $data;
    }

    /**
     * Format the given response object.
     *
     * @return array|string
     */
    protected function response(\Symfony\Component\HttpFoundation\Response $response)
    {
        $content = $response->getContent();

        if (is_string($content)) {
            if (is_array(Json::decode($content))
                && JSON_ERROR_NONE === json_last_error()) {
                return $this->contentWithinLimits($content)
                        ? $this->hideParameters(Json::decode($content), [])
                        : 'Purged By Telescope';
            }

            if (Str::startsWith(strtolower($response->headers->get('Content-Type') ?? ''), 'text/plain')) {
                return $this->contentWithinLimits($content) ? $content : 'Purged By Telescope';
            }
        }

        if ($response instanceof RedirectResponse) {
            return 'Redirected to ' . $response->getTargetUrl();
        }

        if ($response instanceof Response && $response->getOriginalContent() instanceof View) {
            return [
                'view' => $response->getOriginalContent()->getPath(),
                'data' => $this->extractDataFromView($response->getOriginalContent()),
            ];
        }

        if (is_string($content) && empty($content)) {
            return 'Empty Response';
        }

        return 'HTML Response';
    }

    /**
     * Extract the data from the given view in array form.
     *
     * @param  \Illuminate\View\View $view
     * @return array
     */
    protected function extractDataFromView($view)
    {
        return collect($view->getData())->map(static function ($value) {
            if (is_object($value)) {
                return [
                    'class' => $value::class,
                    'properties' => method_exists($value, 'formatForTelescope')
                        ? $value->formatForTelescope()
                        : Json::decode(Json::encode($value)),
                ];
            } else {
                return Json::decode(Json::encode($value));
            }
        })->toArray();
    }

    /**
     * Extract the session variables from the given request.
     *
     * @return array
     */
    private function sessionVariables(Request $request)
    {
        return $request->hasSession() ? $request->session()->all() : [];
    }

    /**
     * Extract the input from the given request.
     *
     * @return array
     */
    private function input(Request $request)
    {
        $files = $request->files->all();

        array_walk_recursive($files, static function (&$file): void {
            $file = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->isFile() ? ($file->getSize() / 1_000) . 'KB' : '0',
            ];
        });

        return array_replace_recursive($request->input(), $files);
    }
}
