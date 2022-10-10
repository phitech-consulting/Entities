# Phitech Entities Library
Author: Phitech Consulting  
Package name: phitech/entities

Description: Provides methods for managing entities that consist two database tables: main and meta. It allows for an indiscrete set of attributes by storing them as key-value sets in the meta table, while the most important attributes and indexes are in the main table. This structure is inspired by the post/postmeta structure in Wordpress databases.

Important: This package requires MySQL DBMS. MariaDB won't do. The reason: 👇

*"All databases except SQL Server require the columns in the second argument of the upsert method to have a "primary" or "unique" index. In addition, the MySQL database driver ignores the second argument of the upsert method and always uses the "primary" and "unique" indexes of the table to detect existing records."* See also: https://laravel.com/docs/9.x/queries#upserts.
## Installation
This library is installed using composer.
```
$ composer require phitech/entities 
```
```
$ composer install
```
```
$ php artisan migrate
```
## Usage
To test if everything is installed correctly in your Laravel application, run:
```
$ php artisan entities:test
```
Create a new entity:
```
$ php artisan entities:make <entity name single> <entity name plural>
```
For instance 'order':
```
$ php artisan entities:make order orders
```
An entity definition will now automatically be placed in the entities table which looks like:

```
{
"entity_name":"order",
"main_db_table":"orders",
"meta_db_table":"orders_meta",
"meta_instance_id_column":"order_id",
"main_required_columns":["order_id"]
}
```
You should now make two tables by using database migrations. You must do this manually, because this application is not able to do this for you. Examples of the two database migration scripts are added below.
### Main table
```
$ php artisan migrate create_orders_table
```
Paste this script below into the newly created migration script. Edit the table name (here: *orders*). Add table attributes to your own preference and using the guidelines as described below.
```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
            /**
             * Here, define the main attributes of this entity. These should be the attributes that
             * are used as (composite) keys, indexes, foreign keys, or other attributes that are so
             * important that they should not be placed in the meta table.
             */
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};

```
### Meta table
```
$ php artisan migrate create_orders_meta_table
```
You should then paste this script below and only edit the table name (here: *orders_meta*), entity_id (here: *order_id*) parameters. Nothing else. 
```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_meta', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('cascade')->onDelete('cascade');
            $table->string('meta_key', 255)->nullable();
            $table->longText('meta_value')->nullable();
            $table->unique(['order_id', 'meta_key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders_meta');
    }
};

```