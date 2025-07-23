<?php
namespace App\Http\Repositories\EmployeeRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Employee;

class EmployeeReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Employee();
    }



    
    public function dataTableEmployee( $filter){

        $auth = auth()->user();

        $select = select_by_language(
            [    
                'id',
            'firstName',
            'lastName',
            'email',
            'photo',
            'gender',
            'officeId',
            'address',
            'country',
            'city',
            'region',
            'isActive',
            'isOnline',
            'phoneNumber',
            'employeeJobName_ar as employeeJobName',
            'job_description_ar as job_description'],
            [
                'id',
                'firstName',
                'lastName',
                'email',
                'photo',
                'gender',
                'officeId',
                'address',
                'country',
                'city',
                'region',
                'isActive',
                'phoneNumber',
                'isOnline',
                'employeeJobName_en as employeeJobName',
                'job_description_en as job_description'
            ]);

            $query = $this->model->scopeForCurrentUser()
            ->select($select);
            
      

        if ($filter != null) {
            if (isset($filter['column_status'])) {
                $query->where('isConected', $filter['column_status']);
            }
        }

       return $query->orderBy('created_at','desc');
    }

}