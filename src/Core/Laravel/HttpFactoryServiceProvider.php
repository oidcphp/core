<?php

namespace LaravelBridge\Slim\Providers;

use Illuminate\Support\ServiceProvider;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Diactoros\UriFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Default service provider for Laravel
 */
class HttpFactoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ResponseFactoryInterface::class, ResponseFactory::class);
        $this->app->singleton(RequestFactoryInterface::class, RequestFactory::class);
        $this->app->singleton(ServerRequestFactoryInterface::class, ServerRequestFactory::class);
        $this->app->singleton(StreamFactoryInterface::class, StreamFactory::class);
        $this->app->singleton(UploadedFileFactoryInterface::class, UploadedFileFactory::class);
        $this->app->singleton(UriFactoryInterface::class, UriFactory::class);
    }
}
