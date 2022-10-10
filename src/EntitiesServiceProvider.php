<?php

namespace Phitech\Entities;

use Illuminate\Support\ServiceProvider;

class EntitiesServiceProvider extends ServiceProvider
{
	
	public function boot() {
		dd("it works!");
	}
	
	public function register() {
		
	}
}