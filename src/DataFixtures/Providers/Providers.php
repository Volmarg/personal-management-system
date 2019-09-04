<?php
namespace App\DataFixtures\Providers;


use Exception;

abstract class Providers {

    const KEY_PRICE_RANGE_MIN = 'price_range_min';
    const KEY_PRICE_RANGE_MAX = 'price_range_max';


    /**
     * @var integer $amountOfSetsToCreateForMonth
     */
    private $amountOfSetsToCreateForMonth;

    /**
     * @var boolean $areGroups
     */
     private $areGroups = false;

    /**
     * @param array $all
     * @param string $group_name
     * @return array
     * @throws Exception
     */
    public function getGroup(array $all, string $group_name): array {

        if(
                $this->areGroups
            &&  array_key_exists($group_name, $all)
        ){
            return $all[$group_name];
        }

        throw new Exception("Group {$group_name} was not found in array {$all}." );
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function getRandom(array $data) {
        $array_index = array_rand($data);
        $random_data = $data[$array_index];
        return $random_data;
    }

    /**
     * @param array $data
     * @param int $count
     * @return array
     */
    public function getNonRepeatingRandoms(array $data, int $count) {

        $randoms = [];

        for($x = 0; $x <= $count; $x++) {

            $array_index = array_rand($data);
            $randoms[]   = $data[$array_index];

            unset($data[$array_index]);

            if( empty ($data) ) {
                break;
            }

        }

        return $randoms;
    }

    /**
     * @param array $data
     * @param string $group_name
     * @param int $count
     * @return array|null
     */
    public function getNonRepeatingRandomsFromGroup(array $data, string $group_name, int $count): array {

        if( !$this->areGroups || array_key_exists($group_name, $data) ){
            return null;
        }

        $group_data = $data[$group_name];
        $randoms    = $this->getNonRepeatingRandoms($group_data, $count);

        return $randoms;

    }

}