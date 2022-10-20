<?php

namespace Phitech\Entities;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EntityTest extends Entity
{
    public function __construct($id = null) {
        if(Schema::hasTable('entities')) {
            if(!DB::table('entities')->where("name", "entity_test")->first()) {
                $this->set_up_entity_test();
            }
            parent::__construct("entity_test", $id);
        }
    }

    public function output_test_message() {
        $messages = "";
        if(Schema::hasTable('entities')) {
            $messages .= "Table 'entities' is present. You are set to go.\n\n";
        } else {
            $messages .= "Table 'entities' not found. You should run 'artisan migrate'.\n\n";
        }
        echo "\n" . $messages;
    }

    public function set_up_entity_test() {
        $single = 'entity_test';
        $plural = 'entity_tests';
        $entity_definition = [
            "entity_name" => $single,
            "main_db_table" => $plural,
            "meta_db_table" => $plural . "_meta",
            "meta_instance_id" => $single . "_id",
            "main_required_columns" => [$single . "_id"],
        ];
        Entity::register_new($entity_definition);
    }
}