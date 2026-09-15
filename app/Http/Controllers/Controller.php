<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /** ভ্যালিডেশন বার্তা বাংলায় দেখানোর জন্য ফ্ল্যাশ করে রিডাইরেক্ট */
    protected function backWithErrors($errors, array $with = [])
    {
        return back()->withErrors($errors)->withInput()->with($with);
    }
}
