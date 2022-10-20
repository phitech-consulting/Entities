<?php

use Phitech\Entities\EntityTest;
use Illuminate\Support\Facades\DB;

Artisan::command('entities:test', function () {

    $entity_test = new EntityTest();
    $entity_test->output_test_message();

})->purpose('Find if Entities library is installed correctly');

Artisan::command('entities:make {single} {plural}', function ($single, $plural) {
    $entity_definition = json_encode([
        "entity_name" => $single,
        "main_db_table" => $plural,
        "meta_db_table" => $plural . "_meta",
        "meta_instance_id" => $single . "_id",
        "main_required_columns" => [$single . "_id"],
    ]);
    DB::table('entities')->insert([
        'name' => $single,
        'definition' => $entity_definition
    ]);
})->purpose('Register a new entity definition');


Artisan::command('entities:get_entity_meta {id} {key}', function ($id, $key) {

    $entity_test = new EntityTest();
    echo $entity_test->get_meta_value($key);

})->purpose('Get meta-value by ID and meta_key');