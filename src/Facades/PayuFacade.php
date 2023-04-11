<?php

namespace Morawcik\LunarPayu\Facades;

use Illuminate\Support\Facades\Facade;

class PayuFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'lunar:payu';
    }

}