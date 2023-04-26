<?php
namespace MyCore\Models\Traits;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 12/20/2018
 * Time: 3:35 PM
 */
trait InsertOrUpdateTrait
{
    public function insertOrUpdate(array $rows)
    {
        $table = \DB::getTablePrefix().with(new self)->getTable();


        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                    ).')';
            } , $rows )
        );

        $updates = implode( ',',
            array_map( function( $value ) { return "$value = VALUES($value)"; } , array_keys($first) )
        );

        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return \DB::statement( $sql );
    }


    public function insertOrUpdateSame(array $insertRows, $updateData)
    {
        if (empty($insertRows)) {
            return;
        }

        $table = \DB::getTablePrefix().with(new self)->getTable();


        $first = reset($insertRows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                    ).')';
            } , $insertRows )
        );

        $updates = [];
        foreach ($updateData as $key => $val) {
            $updates[] = "{$key}={$val}";
        }
        $updates = implode(', ', $updates);

        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return \DB::statement( $sql );
    }


    /**
     * Insert hoac update key
     *
     * @param array $rows
     * @param array $keyUpdate
     * @return mixed
     */
    public function insertOrUpdateKey(array $rows, array $keyUpdate)
    {
        $table = \DB::getTablePrefix().with(new self)->getTable();


        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                    ).')';
            } , $rows )
        );

        $updates = implode( ',',
            array_map( function( $value ) { return "$value = VALUES($value)"; } , $keyUpdate )
        );

        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return \DB::statement( $sql );
    }

    /**
     * Insert hoac update key
     *
     * @param array $rows
     * @param array $keyUpdate
     * @return mixed
     */
    public function insertOrUpdateKeyCalc(array $rows, array $keyUpdate)
    {
        $table = \DB::getTablePrefix().with(new self)->getTable();


        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                    ).')';
            } , $rows )
        );

        $updates = [];
        foreach ($keyUpdate as $key => $val) {
            if (is_numeric($key)) {
                $updates[] = "$val = VALUES($val)";
                continue;
            }

            $updates[] = "$key = $val";
        }
        $updates = implode( ',', $updates);

        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return \DB::statement( $sql );
    }
}