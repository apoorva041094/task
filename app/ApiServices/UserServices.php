<?php 
namespace App\ApiServices;
use Illuminate\Support\Facades\DB;

class UserServices
{
    public function getData($data)
    {
    	if($data->api_key == '12345' && $data->role == 'admin'){
	        $users = DB::table('users')
	        			->join('emp_sal', 'emp_sal.user_id', '=', 'users.id' )
	        			->get();
    		return $users;
    	}else{
    		return false;
    	}
    }
}

?>
