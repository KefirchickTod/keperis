<?php

if (!function_exists('isLogin')) {
    function isLogin()
    {

        $result = \App\Models\User\UserModel::select('checkLoggedUser');
        return $result;
    }
}