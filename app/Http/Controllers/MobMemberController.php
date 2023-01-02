<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MobMemberController extends Controller
{
    public function member()
    {
        return view('mob_member.mob_membership');
    }
}
