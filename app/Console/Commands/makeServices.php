<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class makeServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:service {serviceName}  {--all} {--c} {--r} {--cr} {--management} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create a new service in fleet app';



    /**
     * Execute the console command.
     */
    public function handle()
    {
       // print($this->option('o'));

   $path = '';
   $s = "\ ";
   $serviceName = $this->argument('serviceName');
   if (str_contains($serviceName , '/')){
    $input = explode('/', $serviceName);
    for($i=0 ; $i < count($input)-1 ;$i++){
        $path = $path.$s[0].$input[$i];
    }
    $serviceName = $input[array_key_last($input)];
   }


    if(!$this->option('management') ){

   //Process::run("php artisan make:controller ".$path.'Controllers/'.$serviceName); // add controller

$content_service = <<<EOT
<?php
namespace App\Http\Services{$path}{$s[0]}{$serviceName}\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class {$serviceName}Logic implements Service {

    private RepositoryCaller \$repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private {$serviceName}Input \$input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        \$this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        // write your logic code..

        \$response  = new {$serviceName}Output([] , '');
        return \$response->send_as_array();
   }
}
EOT;



// ------------------------


 $content_controller_r = <<<EOT
 <?php
 namespace App\Http\Services{$path}{$s[0]}{$serviceName}\Controller;

 use App\Http\Services{$path}{$s[0]}{$serviceName}\Logic{$s[0]}{$serviceName}Input;
 use App\Http\Services{$path}{$s[0]}{$serviceName}\Logic{$s[0]}{$serviceName}Logic;
 use App\Http\Controllers\Controller;
 use App\Http\Core\Response\SendResponse;
 use App\Http\Services{$path}{$s[0]}{$serviceName}{$s[0]}Request{$s[0]}{$serviceName}Request;

 class {$serviceName}Controller extends Controller
 {
     public function __invoke({$serviceName}Request \$request)
     {
         // validate input data and pass it to the service..
         \$input = new {$serviceName}Input(\$request->validated());

         \$service = new {$serviceName}Logic(\$input); // call the service's logic

         // execute service and get result..
         \$result = \$service->execute();

         return SendResponse::sendSuccessResponse(\$result); // send response..
     }
 }
 EOT;


 $content_controller = <<<EOT
 <?php
 namespace App\Http\Services{$path}{$s[0]}{$serviceName}\Controller;

 use App\Http\Services{$path}{$s[0]}{$serviceName}\Logic{$s[0]}{$serviceName}Input;
 use App\Http\Services{$path}{$s[0]}{$serviceName}\Logic{$s[0]}{$serviceName}Logic;
 use App\Http\Controllers\Controller;
 use App\Http\Core\Response\SendResponse;
 use Illuminate\Http\Request;

 class {$serviceName}Controller extends Controller
 {
     public function __invoke(Request \$request)
     {
         // validate input data and pass it to the service..
         \$input = new {$serviceName}Input(\$request->all());

         \$service = new {$serviceName}Logic(\$input); // call the service's logic

         // execute service and get result..
         \$result = \$service->execute();

         return SendResponse::sendSuccessResponse(\$result); // send response..
     }
 }
 EOT;


 // ------------------


 $content_input = <<<EOT
<?php
namespace App\Http\Services{$path}{$s[0]}{$serviceName}\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class {$serviceName}Input implements InputServiceInterface
{
    public function __construct( array \$input)
    {}

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}
EOT;



//-----------------


$content_output = <<<EOT
<?php
namespace App\Http\Services{$path}{$s[0]}{$serviceName}\Logic;

use App\Http\Core\InternalInterface\OutputServiceInterface;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class {$serviceName}Output implements OutputServiceInterface
{

    public function __construct(private \$data , private string \$message , private string \$viewPath ='' ){}

        public function send_as_array(): ResponseModel {
        return (new ResponseModel(
            data:
            [
                ''
            ],
            message:\$this->message,
            status:200,
            viewPath:\$this->viewPath
       ));
    }

    public function send_as_object():ResponseModel { return (new ResponseModel(
        data:\$this->data,
        message:\$this->message,
        status:200,
        viewPath:\$this->viewPath
   ));
}

}
EOT;


//----------------------


$content_request = <<<EOT
<?php
namespace App\Http\Services{$path}{$s[0]}{$serviceName}\Request;

use App\Http\Core\Request\BaseRequest;

class {$serviceName}Request extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return
        [
            // write your rules here..
        ];
    }

}

EOT;


// -------------------------
//print($path);

    $serviceFolder = createDirectories("app\Http\Services".$path.'\\'.$serviceName, $s[0]);
    $logicFolder = createDirectories($serviceFolder.'Logic' , $s[0]);

//    print($serviceFolder);
//     return null;

   $status = create_file_and_add_content($logicFolder.$serviceName.'Logic.php' , $content_service ); // add service file
   create_file_and_add_content ($logicFolder.$serviceName.'Input.php' , $content_input  ); // add controller file
   create_file_and_add_content ($logicFolder.$serviceName.'Output.php' , $content_output ); // add controller file

   if($this->option('r') || $this->option('all')||  $this->option('cr')){
    $requestFolder = createDirectories($serviceFolder.'Request' , $s[0]);
    create_file_and_add_content ($requestFolder.$serviceName.'Request.php' , $content_request ); // add controller file
    $content_controller = $content_controller_r;
   }
   if($this->option('c') || $this->option('all') ||  $this->option('cr')){
    $controllerFolder = createDirectories($serviceFolder.'Controller' , $s[0]);
    create_file_and_add_content ($controllerFolder.$serviceName.'Controller.php' , $content_controller ); // add controller file
   }


  $status ? print("\n ------ Service created Successfully!\n"): print("\n ------ Sorry service already exists!\n");
    }
    else{

        createDirectories("app\Http\Services".$path.$s[0].$serviceName.'Management',$s[0]);
        $path = substr($path, 1);

        if( $path !== '')
        {
            $path = $path.$s[0];
        }
        $path = str_replace($s[0], '/', $path);

        //  print($path);
        //  return null;

        Process::run("php artisan file:service ".$path.$serviceName.'Management'.'/Add'.$serviceName.' --all' ); // add model
        Process::run("php artisan file:service ".$path.$serviceName.'Management'.'/Show'.$serviceName .' --all'); // add model
        Process::run("php artisan file:service ".$path.$serviceName.'Management'.'/Delete'.$serviceName.' --all'); // add model
        Process::run("php artisan file:service ".$path.$serviceName.'Management'.'/Edit'.$serviceName.' --all' ); // add model
        Process::run("php artisan file:service ".$path.$serviceName.'Management'.'/View'.$serviceName .' --all'); // add model

        print("\n ------ Service Management created Successfully!\n");

        }
    }
}
