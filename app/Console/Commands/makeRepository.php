<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class makeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:repo {modelName} {--model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to make a new repository';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = '';
        $s = "\ ";


        $modelName = $this->argument('modelName');


        if (str_contains($modelName , '/')){
         $input = explode('/', $modelName);
         for($i=0 ; $i < count($input)-1 ;$i++){
             $path = $path.$s[0].$input[$i];
         }
         $modelName = $input[array_key_last($input)];
        }


    $content_create_repo = <<<EOT
    <?php
    namespace App\Http\Repositories{$path}{$s[0]}{$modelName}Repositories;
    use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
    use App\Models{$s[0]}{$modelName};

    class {$modelName}CreateRepository extends CreateRepository
    {
        public function __construct()
        {
            \$this->model = new {$modelName}();
        }
    }
    EOT;

    $content_update_repo = <<< EOT
    <?php
    namespace App\Http\Repositories{$path}{$s[0]}{$modelName}Repositories;
    use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
    use App\Models{$s[0]}{$modelName};

    class {$modelName}UpdateRepository extends UpdateRepository
    {
        public function __construct()
        {
            \$this->model = new {$modelName}();
        }

    }
    EOT;



    $content_read_repo =<<< EOT
    <?php
    namespace App\Http\Repositories{$path}{$s[0]}{$modelName}Repositories;
    use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
    use App\Models{$s[0]}{$modelName};

    class {$modelName}ReadRepository extends ReadRepository
    {
        public function __construct()
        {
            \$this->model = new {$modelName}();
        }

    }
    EOT;


    $content_delete_repo = <<<EOT
    <?php
    namespace App\Http\Repositories{$path}{$s[0]}{$modelName}Repositories;
    use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
    use App\Models{$s[0]}{$modelName};

    class {$modelName}DeleteRepository extends DeleteRepository
    {
        public function __construct()
        {
            \$this->model = new {$modelName}();
        }
    }
    EOT;




$content_caller_repo =<<<EOT
<?php
namespace App\Http\Repositories{$path}{$s[0]}{$modelName}Repositories;
use App\Models\{$modelName};

class {$modelName}RepositoryCaller{

    static public function createRepository(){return (new {$modelName}CreateRepository());}
    static public function readRepository(){return (new {$modelName}ReadRepository());}
    static public function updateRepository(){return (new {$modelName}UpdateRepository());}
    static public function deleteRepository(){return (new {$modelName}DeleteRepository());}
    static public function get_model() : {$modelName} {return (new {$modelName}());}


}
EOT;







$path_to_caller_repo ="app\Http\Repositories\RepositoryCaller.php";
$spacename = 'use App\Http\Repositories'.$s[0].$modelName.'Repositories'.$s[0].$modelName."RepositoryCaller; \n";
$function = 'static public function '.$modelName.'Repository(){return (new '.$modelName.'RepositoryCaller);}';





    $repositoryFolder = createDirectories("app\Http\Repositories".$path.$s[0].$modelName."Repositories", $s[0]);



     $read   = create_file_and_add_content($repositoryFolder.$modelName.'ReadRepository.php' , $content_read_repo ); // add service file
     $create = create_file_and_add_content($repositoryFolder.$modelName.'CreateRepository.php' , $content_create_repo ); // add service file
     $update = create_file_and_add_content($repositoryFolder.$modelName.'UpdateRepository.php' , $content_update_repo ); // add service file
     $delete = create_file_and_add_content($repositoryFolder.$modelName.'DeleteRepository.php' , $content_delete_repo ); // add service file
     $caller = create_file_and_add_content($repositoryFolder.$modelName.'RepositoryCaller.php' , $content_caller_repo ); // add service file



       if($read && $delete && $create && $update && $caller ) {

        
        if($this->option('model')){
        $path = str_replace('\\', '/', $path);
        print($path);
        Process::run("php artisan make:model ".$path.'/'.$modelName . ' -m'); // add model
        }

        add_spacename_and_function($path_to_caller_repo , $spacename ,$function );
        print("\n ------ Repositores created Successfully!\n");

    }
       else{
        print("\n ------ Sorry repositoris already exists!\n");
       }
    }
}
