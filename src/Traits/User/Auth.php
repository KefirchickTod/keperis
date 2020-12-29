<?php


namespace src\Traits\User;


use App\Models\User\UserModel;

/**
 * Trait Auth
 * @package App\src\Traits\User
 * @property $db DB
 * @property attributes array
 */
trait Auth
{

    public function auth(){

        $password = valid($this->attributes, 'password');
        $login = valid($this->attributes, 'login');
        return $this->check($login, $password);
    }

    private function check($login, $password){

        $password = UserModel::password($password);
        $sql = \db()->selectSql(
            'bc_user',
            '*',
            "bc_user_email = '$login' AND bc_user_password = '" . $password . "'"
        );

        if (!$sql || $sql[0]['bc_user_delete']) {
            return false;
        }

        $_SESSION['BCUserLogged'] = true;
        $_SESSION['BCUserId'] = $sql[0]['bc_user_id'];



        return true;
    }
}