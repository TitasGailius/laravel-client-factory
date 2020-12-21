<?php

namespace TitasGailius\LaravelClientFactory;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @mixin \Illuminate\Http\Client\PendingRequest
 */
abstract class Client
{
    use ForwardsCalls;

    /**
     * Configured pending request instance.
     *
     * @var array
     */
    protected static $configured = [];

    /**
     * Configuring pending request.
     *
     * @var \Illuminate\Http\Client\PendingRequest|null
     */
    protected $configuring;

    /**
     * Configure the http client.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    abstract public function configure($app);

    /**
     * Create a new pending request.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function newPendingRequest()
    {
        if (isset(static::$configured[static::class])) {
            return clone static::$configured[static::class];
        }

        if ($this->configuring) {
            return $this->configuring;
        }

        return $this->configurePendingRequest(Http::asJson());
    }

    /**
     * Configure a given pending request.
     *
     * @param  \Illuminate\Http\Client\PendingRequest $request
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function configurePendingRequest(PendingRequest $request)
    {
        $this->configuring = $request;

        $this->configure(app());

        static::$configured[static::class] = $request;

        $this->configuring = null;

        return clone static::$configured[static::class];
    }

    /**
     * Dynamically proxy calls to Laravel's HTTP client.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters = [])
    {
        return $this->forwardCallTo($this->newPendingRequest(), $method, $parameters);
    }
}
