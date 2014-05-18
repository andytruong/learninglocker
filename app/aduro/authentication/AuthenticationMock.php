<?php

namespace app\aduro\authentication;

class AuthenticationMock implements AuthenticationInterface
{

    public function verify($key, $secret)
    {
        return;
    }

}
