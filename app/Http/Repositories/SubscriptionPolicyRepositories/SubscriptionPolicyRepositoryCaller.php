<?php
namespace App\Http\Repositories\SubscriptionPolicyRepositories;

class SubscriptionPolicyRepositoryCaller{

    static public function createRepository(){return (new SubscriptionPolicyCreateRepository());}
    static public function readRepository(){return (new SubscriptionPolicyReadRepository());}
    static public function updateRepository(){return (new SubscriptionPolicyUpdateRepository());}
    static public function deleteRepository(){return (new SubscriptionPolicyDeleteRepository());}

}