<?php
declare(strict_types=1);

namespace App\SimClient\Support;

use Kernel\Util\Session;

class SessionContext
{
    public static function id(): string
    {
        Session::start();
        $id = session_id();
        if (!$id) {
            $id = md5(uniqid('sim', true));
            $_SESSION['_sim_client_sid'] = $id;
        } elseif (!isset($_SESSION['_sim_client_sid'])) {
            $_SESSION['_sim_client_sid'] = $id;
        } else {
            $id = (string)$_SESSION['_sim_client_sid'];
        }
        Session::end();
        return $id;
    }
}
