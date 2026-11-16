<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProductionUrlsTest extends TestCase
{
    protected function tearDown(): void
    {
        URL::forceScheme(null);

        parent::tearDown();
    }

    public function test_production_with_an_https_app_url_generates_https_links(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://bijghor.infinityfreeapp.com']);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', route('home'));
        $this->assertStringStartsWith('https://', asset('favicon.svg'));
    }

    public function test_local_development_keeps_plain_http(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('http://', route('home'));
    }
}
