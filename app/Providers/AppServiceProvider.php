<?php

namespace App\Providers;

use App\Services\LogOtpSender;
use App\Services\NullOtpSender;
use App\Services\OtpSender;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OtpSender::class, function () {
            $driver = config('metanoia.otp.driver');

            return match ($driver) {
                'log'  => new LogOtpSender(),
                'null' => new NullOtpSender(),
                default => throw new InvalidArgumentException("Driver OTP [{$driver}] tidak didukung."),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
