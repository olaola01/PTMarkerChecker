<?php

namespace App\Providers;

use App\Interfaces\UserInfoRepositoryInterface;
use App\Repositories\UserInfoRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserInfoRepositoryInterface::class, UserInfoRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
