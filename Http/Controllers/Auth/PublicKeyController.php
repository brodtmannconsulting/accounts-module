<?php

namespace Modules\Accounts\Http\Controllers\AuthController;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounts\Entities\Auth\TokenKey;

class PublicKeyController extends Controller
{
    public function show($time){
        $date = date("Y/m/d H:i", $time);
        return response()->json(TokenKey::where('valid_from', '<=', $date)
                                ->orderBy('valid_from', 'desc')
                                ->firstOrFail()->public_key);
    }
}
