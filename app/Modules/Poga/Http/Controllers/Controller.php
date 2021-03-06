<?php

namespace Raffles\Modules\Poga\Http\Controllers;

use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, RedirectsUsers, ValidatesRequests;
}
