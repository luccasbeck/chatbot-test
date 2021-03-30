<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;

class BotController extends BaseController
{
    public function getStatus(Request $request){
        $value = $request->session()->get('key');

        if(!empty($value)){
            echo json_encode(["result" => true]);
        }else{
            echo json_encode(["result" => false]);
        }
    }

    public function createAccount(Request $request){

        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        $currency = $request->currency;

        $user = User::create(['email' => $email,'name' => $name, 'password' => sha1($password)]);

        $account = $user->account()->create([
            'amount' => 0,
            'currency' => 'EUR',
        ]);

        echo json_encode(["result" => true]);

    }

    public function logIn(Request $request){

        $email = $request->email;
        $password = $request->password;

        $user=User::where(['email' => $email, 'password' => sha1($password)])->get()->toArray();

        if(!empty($user)){
            echo json_encode(["result" => true, "user_id" => $user[0]["id"], "name" => $user[0]["name"]]);
        }else {
            echo json_encode(["result" => false]);
        }

    }

    public function getBalance(Request $request){

        $email = $request->email;
        $password = $request->password;
        $currency = $request->balanceCurrency;

        $user=User::with('account')->where(['email' => $email, 'password' => sha1($password)])->get()->toArray()[0];

        $account = $user["account"];

        $latest = $this->getLatest();

        $account_converted = $account["amount"]/ $latest->rates->$currency;

        if(!empty($user)){
            echo json_encode(["result" => true, "balance" => $account_converted]);
        }else {
            echo json_encode(["result" => false]);
        }

    }

    public function deposit(Request $request){

        $email = $request->email;
        $password = $request->password;
        $currency = $request->depositCurrency;
        $amount = $request->depositAmount;

        $user=User::with('account')->where(['email' => $email, 'password' => sha1($password)])->get()->toArray()[0];

        $account = $user["account"];

        $latest = $this->getLatest();

        $prop = $account["currency"];

        $account_converted = $account["amount"]/ $latest->rates->$prop;

        $received_converted = $request->depositAmount/ $latest->rates->$currency;

        $total = $account_converted + $received_converted;

        $changedAccount = Account::where('id', $account["id"])->update(['amount' => $total]);

        $transaction = Transaction::create(['user_id' => $user["id"], 'amount' => $received_converted]);

        if(!empty($user)){
            echo json_encode(["result" => true]);
        }else {
            echo json_encode(["result" => false]);
        }

    }

    public function withdraw(Request $request){

        $email = $request->email;
        $password = $request->password;
        $currency = $request->withdrawCurrency;
        $amount = $request->withdrawAmount;

        $user=User::with('account')->where(['email' => $email, 'password' => sha1($password)])->get()->toArray()[0];

        $account = $user["account"];

        $latest = $this->getLatest();


        $prop = $account["currency"];

        $account_converted = $account["amount"]/ $latest->rates->$prop;

        $received_converted = $request->withdrawAmount/ $latest->rates->$currency;

        $total = $account_converted - $received_converted;

        if($total >= 0){
            $changedAccount = Account::where('id', $account["id"])->update(['amount' => $total]);

            $transaction = Transaction::create(['user_id' => $user["id"], 'amount' => $received_converted*-1]);

            echo json_encode(["result" => true]);
        }else{
            echo json_encode(["result" => false]);
        }

    }

    private function getLatest(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://data.fixer.io/api/latest?access_key=a5692aafdf4534dc62e55a342bd6ebaa',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlzcyI6Imh0dHA6XC9cL2Nocm9ub3NtYWlzLmNvbS5iciJ9.eyJ1c2VyIjp7ImluZF9pZCI6MzM1LCJpbmRfbmFtZSI6IkFyaWVsIEFxdWlsYSBaaW1iclx1MDBlM28iLCJ1c2VfaWQiOjM0MywidXNlX2lkX3BlbyI6Mzc1LCJ1c2VfaWRfcHJvIjozLCJ1c2VfY2l0aWVzIjpudWxsLCJ1c2VfbG9naW4iOiIzMDA0OSIsInVzZV9hcHAiOjAsInVzZV9jcmVhdGVkX2F0IjoiMjAyMC0wMS0yNyAwODo1MTozNSIsInNlY3RvcklkcyI6IjE1IiwiY2l0eUlkcyI6IjMzMDM0MDEiLCJwcm9faWQiOjMsInBlb19pZCI6Mzc1fSwibW9kdWxlcyI6WyJBdWRpdCIsIkJhbmtPZkhvdXJzIiwiQ2hlZXJEYXkiLCJEYXNoYm9hcmQiLCJEZXZpY2UiLCJFbXBsb3llZXMiLCJFeHBvcnRzIiwiSG9saWRheUNvbnRyb2wiLCJIb2xpZGF5UmVwb3J0IiwiT3ZlcnRpbWUiLCJSZXBvcnQiLCJTY2hlZHVsZSIsIlNlY3RvciIsIldvcmtDaXR5IiwiV29ya2RheSIsIldvcmtVbml0Il19.NTk0YWMzMjk2MjllYzdjOTdmZDQ3ZmQ0NDgwMWEyZmUzMTBhNzRiYTc2YjhlODFkOWIyYTUyOGRhMTAxODZmZA==',
            'Origin: http://chronosmais.com.br'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}
