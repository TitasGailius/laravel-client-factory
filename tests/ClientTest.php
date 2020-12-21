<?php

namespace TitasGailius\LaravelClientFactory\Tests;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use TitasGailius\LaravelClientFactory\Client;

class ClientTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('services.forge.base_uri', 'https://forge.com');
    }

    public function testConfigure()
    {
        Http::fake([
            'https://forge.com/api/servers' => Http::response(['status' => 'created'], 200)
        ]);

        $response = (new Forge)->createServer([
            'title' => 'My First Server',
        ]);

        $this->assertEquals(['status' => 'created'], $response);
    }
}

class Forge extends Client
{
    /**
     * Configure the http client.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function configure($app)
    {
        $this->baseUrl($app['config']['services.forge.base_uri']);
    }

    /**
     * Create a new forge server.
     *
     * @param  array  $server
     * @return mixed
     */
    public function createServer(array $server)
    {
        return $this->post('api/servers', $server)->json();
    }
}
