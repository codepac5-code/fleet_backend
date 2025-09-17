<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneralPurposeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $className;
    public $method;
    public $params;

    /**
     * Create a new job instance.
     *
     * @param string 
     * @param string
     * @param array 
     */
    public function __construct(string $className, string $method, array $params = [])
    {
        $this->className = $className;
        $this->method = $method;
        $this->params = $params;
    }

    public function handle()
    {
        if (class_exists($this->className) && method_exists($this->className, $this->method)) {
            call_user_func_array([new $this->className, $this->method], $this->params);
        }
    }
}
