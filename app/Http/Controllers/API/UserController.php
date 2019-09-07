<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use Illuminate\Mail\Mailer;
use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\ApiServices\UserServices;
use Mail;

class UserController extends Controller
{
	public $userservices;
	public function __construct(UserServices $UserServices)
    {
		$this->userservices = $UserServices;        
    }

    public function getData(Request $request){
    	$data = $this->userservices->getData($request);
        if(isset($data) && !empty($data)){            
            $amount = $this->calcSalary($data);
            $last_working_day = $this->checkDay($request['month']); 
            $bonus_day = $this->findBonusDay($request['month']);
            $new_data= array(
                            'month' => date('M', $request['month']),
                            'bonus_day' => $bonus_day,
                            'salaries_payment_day' => $last_working_day,
                            'salaries_total'=> $amount['total_salary'],
                            'bonus_total'=> $amount['total_bonus'],
                            'payment_total'=> $amount['total_payment'],
                ); 
            return response()->json($new_data);
         }else{
            return response()->json(array('error' => 'True',  
                                        'code' => '403',
                                        'message' => 'User Unauthorized'
                                          )
                                    );
        }
    } 

    public function calcSalary($data){
        $total_salary = 0;
        $total_bonus = 0;
        $total_payment = 0;
        foreach ($data as $key => $value) {
            foreach ($value as $k => $v) {
                if($k == 'salary'){
                    $total_salary = $total_salary + $v; 
                }
                if($k == 'bonus'){
                    $total_bonus = $total_bonus + $v; 
                }
           }
        }
        $total_payment = $total_salary + $total_bonus;
        return array('total_payment'=> $total_payment,
                    'total_salary' => $total_salary,
                    'total_bonus' => $total_bonus
                    );
    }

    public function checkDay($month){
        $getUnixTime = strtotime('1-'.$month.'-'. 2019);
        $total_number_days = date('t', $getUnixTime);
        
        $month_last_date = strtotime($total_number_days. '-' .$month.'-'. '2019');
        $day = date('l', $month_last_date);
        $last_working_day ='';
       
        if($day == 'Sunday'){
           $last_working_day= $total_number_days-2;
        }
        elseif($day=='Saturday'){
            $last_working_day= $total_number_days-1;
        }else{
            $last_working_day= $total_number_days;
        }
        return $last_working_day.'-'. $month.'-2019';
    }

    public function findBonusDay($month){
        $getUnixTime = strtotime('1-'.$month.'-'. 2019);
        $total_number_days = date('t', $getUnixTime);
        $month_last_date = strtotime($total_number_days. '-' .$month.'-'. '2019');
        $day = date('l', $month_last_date);

        if($day == 'Saturday' || $day == 'Sunday'){
            for ($i = 1; $i < 7; $i++) {
                 $unixTime= strtotime($total_number_days . '-' . $month . '-2019'  . " +" . (15 + $i)." days");
                 $dayOfWeek = date("l", $unixTime);
                 if($dayOfWeek == "Thursday") {
                     return $i + 15;
                 }
            }
        }else{
            return 15;
        }
    }
   
    public function sendReminder(Request $request){
        $data = $this->userservices->getData($request);
        $month = date('m');
        $amount = $this->calcSalary($data);
        $new_data = array('salaries_total'=> $amount['total_salary'],                          
                            'payment_total'=> $amount['total_payment'],
                            );

        Mail::send('mail', $new_data, function($message) {
        $message->to('apoorva@mailinator.com', 'Reminder')
                ->subject('Salary Reminder');

        });
        echo "Reminder Sent.";
    }


}

