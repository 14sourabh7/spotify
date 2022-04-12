
  
<?php

use Phalcon\Mvc\Model;

class Users extends Model
{
    public $user_id;
    public $name;
    public $email;
    public $password;
    public $refresh;
    public $token;

    /**
     * function to validate user
     *
     */
    public function checkUser($email, $password)
    {
        return  Users::find("email = '" . $email . "' AND password = '" . $password . "'");
    }

    /**
     * function to add user
     */
    public function addUser($name, $email, $password)
    {
        $user = new Users();
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $result = $user->save();
        return $result;
    }


    /**
     * adding both the tokens to db
     *
     */
    public function addTokens($user, $access, $refresh)
    {
        $user = Users::find("user_id = '" . $user . "'")[0];
        $user->token = $access;
        $user->refresh_token = $refresh;
        $dbresult = $user->update();
        return $dbresult;
    }
}
