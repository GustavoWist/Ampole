<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(){

        return view('/prohibited');

    }

    public function logout(){

        // logout from the application

        session()->forget('user');
        return redirect()->to('/prohibited');

    }

    public function loginSubmit(Request $request){

        // form validation
        $request->validate(
            // rules
            [
                'login-username' => 'required',
                'login-password' => 'required'
            ], 
            // error messages
            [
                'login-username.required' => 'O username é obrigatório',
                'login-password.required' => 'A senha é obrigatória',
                'login-username.email' => 'Não é um email válido',
                'login-password.min' => 'A senha deve ter pelo menos :min caracteres',
                'login-password.max' => 'A senha deve ter no máximo :max caracteres'

            ]
        );

        // get user input 

        $username = $request->input('login-username');
        $password = $request->input('login-password');
    
        // get all the users from the database
        //$users= User::all()->toArray();

        $userModel= new User();
        $users = $userModel->all()->toArray();
        

        // check if user exists

        $user = User::where('username', $username)
                        ->first();
        

        if(!$user){
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('loginError', 'Usuário ou Senha incorretos.');
        }

        // check if password is correct

        if(!password_verify($password, $user->password)){
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('loginError', 'Senha incorreta.');
        }

        // update last login

        $user->save();

        // login user

        session([
            'user' => [
                'id' => $user->id,
                'username' => $user->username
            ]
            ]);

        // redirect

        return redirect()->to('/');
            
    }
}
