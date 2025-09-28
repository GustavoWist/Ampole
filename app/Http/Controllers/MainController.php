<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
   public function index(){
    //show home view
    return view('home');
   
    }

    public function newNote(){

        echo "I'm am creating a new note";

    }
}
