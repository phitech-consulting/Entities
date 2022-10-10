<?php

use Phitech\Entities\EntityTest

Artisan::command('entities:test', function () {

    $entity_test = new EntityTest();
    $entity_test->output_test_message();

})->purpose('Find if Entities library is installed correctly.');