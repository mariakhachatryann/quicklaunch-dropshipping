<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 13.04.2019
 * Time: 10:57
 */

namespace backend\models;



class LoginForm extends \common\models\LoginForm
{
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Admin::findByUsername($this->username);
        }

        return $this->_user;
    }
}