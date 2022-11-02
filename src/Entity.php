<?php

namespace Phitech\Entities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Entity {
    public $main_db_table = "";
    public $meta_db_table = "";
    public $meta_entity_id = "instance_id";
    public $main_required = [];
    public $id = null;
    public $data = [];


    /**
     * @param $entity
     */
    public function __construct($entity, $id = null) {
		$this->id = $id;
        $definition = DB::table("entities")->where("name", $entity)->get()->first();
        if($definition) {
            $definition = json_decode($definition->definition, true);
            $this->main_db_table = $definition['main_db_table'];
            $this->meta_db_table = $definition['meta_db_table'];
            $this->meta_entity_id = $definition['meta_instance_id'] ?? $this->meta_entity_id;
            $this->main_required = $definition['main_required_columns'];
        } else {
            abort(500, "Missing entity definition for: " . $entity);
        }
        if($id) {
            $this->get_single_instance(["id" => $id]);
        }
    }


    /**
     * Find one specific instance and return all columns and metadata in the form of a table.
     * If the instance was not found, return null.
     * @param array $pk
     * @return array|null
     */
    public function get_single_instance(array $pk = []) {
        $entity_data = [];
        $query = DB::table($this->main_db_table);
        foreach($pk as $y => $x) {
            $query = $query->where($y, $x);
        }
        $instance = $query->get('*')->first();
        if(!isset($instance->id)) {
            return null;
        }
        foreach($instance as $key => $value) {
            $entity_data[$key] = $value;
        }
        $meta = DB::table($this->meta_db_table)->where($this->meta_entity_id, $instance->id)->select('meta_key', 'meta_value')->get();
        foreach($meta as $meta_item) {
            $entity_data[$meta_item->meta_key] = $meta_item->meta_value;
        }
        $this->data = $entity_data;
        return $entity_data;
    }


    /**
     * Given an array of specific IDs, return a table of all data for these rows. Automatically transform
     * key-meta values from meta table to columns.
     * @param array|null $ids
     * @return array
     */
    public function get_entity_matrix(array $ids = null) {
        $entity_matrix = [];
        $query_data = DB::table($this->main_db_table);
        $query_meta = DB::table($this->meta_db_table);
        if(isset($ids)) {
            $query_data->whereIn("id", $ids);
            $query_meta->whereIn($this->meta_entity_id, $ids);
        }
        $entity_data = $query_data->select('*')->get();
        $entity_meta = $query_meta->select($this->meta_entity_id, 'meta_key', 'meta_value')->get();
        foreach($entity_data as $item) {
            foreach($item as $key => $value) {
                $entity_matrix[$item->id][$key] = $value;
            }
        }
        foreach($entity_meta as $meta_item) {
            $entity_matrix[$meta_item->{$this->meta_entity_id}][$meta_item->meta_key] = $meta_item->meta_value;
        }
        return $entity_matrix;
    }


    /**
     * @param array $pk ## One column as private key: [["column_name" => "value"]], multiple columns as private_key: [["column_name_1" => "value_1"],["column_name_2" => "value_2"]]
     * @param array $main ## Structure: ["column_name_1" => "value_1", "column_name_2" => "value_2", etc...]
     * @param array|null $meta ## Structure: [0 => [str $meta_key, str $meta_value], 1 => [etc...]]
     * @return mixed
     */
    public function upsert_instance(array $pk, array $main, array $meta = null) {
        $meta ??= [];
        $insert_meta_query = [];

        /* Look up the instance_id by the key that was given */
        $id_query = DB::table($this->main_db_table);
        foreach($pk as $y => $x) {
            $id_query = $id_query->where($y, $x);
        }
        $instance = $id_query->get('id')->first();
        $main['id'] = $instance->id ?? null; // If no instance was found, assign null

        /* Update or insert the main entity data, keep the instance_id for later use */
        $query_data = DB::table($this->main_db_table);
        $columns = array_keys($main);
        $query_data->upsert($main, 'id', $columns);
        $instance_id = DB::getPdo()->lastInsertId() ?: $main['id']; // Proceed with ID from update or insert, otherwise proceed with ID that was set earlier

        /* Update or insert the entity meta data */
        $query_meta = DB::table($this->meta_db_table);
        foreach($meta as $set) {
            $insert_meta_query[] = ["meta_key" => $set[0], "meta_value" => $set[1], $this->meta_entity_id => $instance_id];
        }
        $query_meta->upsert($insert_meta_query, [$this->meta_entity_id, "meta_key"], [$this->meta_entity_id, "meta_key", "meta_value"]);

        /* Return the database ID of the instance that was inserted or updated */
        return $this->get_single_instance($instance_id);
    }


    /**
     * It can be necessary to get a list of all distinct meta keys. For instance to compare the list of existing
     * meta keys with a list of meta keys in a HTTP-call (like a WooCommerce webhook). You can then distinguish
     * which meta keys exist in the database that don't exist in the HTTP-call, so that you can delete those.
     * @param $entity_id
     * @return DB | false
     */
    public function get_distinct_meta_keys($entity_id) {
        return DB::table($this->meta_db_table)->where($this->meta_entity_id, $entity_id)->distinct()->get();
    }


    /**
     * Very unsafe function to retrieve an entity ID by meta key and value. It's unsafe, because meta_key and
     * meta_value together are by no means necessarily unique. You should only use this function when you know
     * the rest of your program guarantees meta_key plus meta_value are unique.
     * @param $key
     * @param $value
     * @return integer|false
     */
    public function find_by_key_value(string $key, string $value) {
        $result = DB::table($this->meta_db_table)->where("meta_key", $key)->where("meta_value", $value)->first();
        return $result->{$this->meta_entity_id} ?? false;
    }


    /**
     * Get value of meta-key for this specific entity. Note that ID and meta_key are together
     * unique, so the ->first() method is used safely.
     * @param string $key
     * @return string ## The meta_value if found, empty string otherwise
     */
    public function get_meta_value(string $key) {
		$result = DB::table($this->meta_db_table)->where("meta_key", $key)->where($this->meta_entity_id, $this->id)->first();
		return $result->meta_value ?? "";
    }


	/**
	 * Add one key-value set for this specific entity.
     * @param $key
     * @param $value
     * @return false ## Just always return false, regardless of outcomes fail, update or insert.
     */
    public function add_meta_value($key, $value) {
        if($this->id) {
			$data = [$this->meta_entity_id => $this->id, 'meta_key' => $key, 'meta_value' => $value];
			DB::table($this->meta_db_table)->upsert($data, [$this->meta_entity_id, 'meta_key'], [$this->meta_entity_id, "meta_key", "meta_value"]);
        }
		return false;
    }


    /**
     * Helper method to create a new entity definition in 'entities' table.
     * @param $definition ## Array containing definition to later convert to JSON
     * @return void
     */
    public static function register_new(array $definition) {
        if(Schema::hasTable('entities')) {
            DB::table('entities')->insert([
                'name' => $definition['entity_name'],
                'definition' => json_encode($definition)
            ]);
        } else {
            abort(500, "Cannot register entity because there's no entities table. Did you run 'php artisan migrate'?");
        }
    }
}
