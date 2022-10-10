<?php

namespace Phitech\Entities;

class EntityTest extends Entity
{
    public function output_test_message() {
        $messages = "";
        if(Schema::hasTable('entities')) {
            $messages .= "Table 'entities' is present. You are set to go.\n\n";
        } else {
            $messages .= "Table 'entities' not found. You should run 'artisan migrate'.\n\n";
        }
        echo "\n" . $messages;
    }
}