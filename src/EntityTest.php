<?php

namespace Phitech\Entities;

class EntityTest extends Entity
{

    public function __construct($id = null) {
        parent::__construct('entity_test');
    }

    public function output_test_message() {
        dd("It works!");
    }
}