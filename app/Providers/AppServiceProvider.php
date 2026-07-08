<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\StorageService::class, function ($app) {
            return new \App\Services\StorageService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
   {
    if (Schema::hasTable('smtp_settings')) {
    $smtp = SmtpSetting::first();
    if ($smtp) {
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => $smtp->mailer,
            'host' => $smtp->host,
            'port' => $smtp->port,
            'encryption' => $smtp->encryption == 'none' ? null : $smtp->encryption,
            'username' => $smtp->username,
            'password' => $smtp->password,
        ]);
        Config::set('mail.from.address', $smtp->from_address);
        Config::set('mail.from.name', $smtp->from_name);
    }
}

  }

}
