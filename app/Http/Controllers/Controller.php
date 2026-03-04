<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function formatValidationErrors($validator)
    {
        return $validator->errors()->first();
    }
}
