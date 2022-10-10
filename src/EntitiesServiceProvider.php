<?php

namespace Phitech\Entities;

use Illuminate\Support\ServiceProvider;

class EntitiesServiceProvider extends ServiceProvider
{
	
	public function boot() {
        $this->loadRoutesFrom(__DIR__.'/../routes/console.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
	}
	
	public function register() {
		
	}
}