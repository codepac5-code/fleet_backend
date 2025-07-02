<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels\OfficeWallet;


enum OfficeWalletRedisKeies : string
{

    case  Balance = '{status}-balance.{officeId}';






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
 