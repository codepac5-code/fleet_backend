<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet;


enum FleetWalletRedisKeies : string
{

    case  Balance = '{status}-balance';






//------------------------- <<< >>>> ------------------
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
 