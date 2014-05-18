<?php

namespace app\aduro\authentication;

interface AuthenticationInterface
{

    public function verify($key, $secret);

}
