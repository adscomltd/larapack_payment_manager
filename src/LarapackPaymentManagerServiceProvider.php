<?php

namespace Adscom\LarapackPaymentManager;

use Illuminate\Support\ServiceProvider;

class LarapackPaymentManagerServiceProvider extends ServiceProvider
{
  public function boot(): void
  {
    // PaymentManager
    $this->app->singleton('PaymentManager', PaymentManager::class);

    if ($this->app->runningInConsole()) {
//
//      $this->publishes([
//        __DIR__.'/../config/currency.php' => config_path('currency.php'),
//      ], 'config');
//
//       // Export the migration
//      if (! class_exists('CreateCurrencyTable')) {
//        $this->publishes([
//          __DIR__ . '/../database/migrations/create_currency_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_currency_table.php'),
//          // you can add any number of migrations here
//        ], 'migrations');
//      }
    }
  }
}
