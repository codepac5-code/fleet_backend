<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels\Driver;

enum DriverRedisKeies : string
{

//----------------------<< fleet >>---------------------------------------------
    case DriverServices = 'driver.{driver}-services';     


// ----------------------<< office >>--------------------------------------------




    /**
     * 
     *
     * @param array 
     * @return string
     */
    public function generateKey(array $variables): string
    {
        $template = $this->value;

        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }



}
