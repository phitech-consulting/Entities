<?php

use Phitech\Entities\EntityTest;
use Phitech\Entities\Entity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

Artisan::command('entities:test', function () {

    $entity_test = new EntityTest();
    $entity_test->output_test_message();

})->purpose('Find if Entities library is installed correctly');

Artisan::command('entities:make {single} {plural}', function ($single, $plural) {
    $entity_definition = [
        "entity_name" => $single,
        "main_db_table" => $plural,
        "meta_db_table" => $plural . "_meta",
        "meta_instance_id" => $single . "_id",
        "main_required_columns" => [$single . "_id"],
    ];
    Entity::register_new($entity_definition);
})->purpose('Register a new entity definition');


Artisan::command('entities:get_entity_meta {entity} {id} {key}', function ($entity, $id, $key) {

    $entity_test = new Entity(entity: $entity, id: $id);
    echo $entity_test->get_meta_value($key);

})->purpose('Get meta-value by ID and meta_key');


Artisan::command('entities:add_entity_meta {entity} {id} {key} {value}', function ($entity, $id, $key, $value) {

    $entity_test = new Entity(entity: $entity, id: $id);
    echo $entity_test->add_meta_value($key, $value);

})->purpose('Insert meta-value for an instance');


Artisan::command('entities:get_single_instance {id}', function ($id) {

    $entity_test = new Entity(entity: 'entity_test', id: $id);
    print_r($entity_test->data);

})->purpose('Get all data of single instance by ID');