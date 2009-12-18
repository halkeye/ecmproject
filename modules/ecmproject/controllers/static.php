<?php

class Static_Controller extends Controller_Core
{
    public function css()
    {
        if (expires::check(300) === FALSE) expires::set(300);

        header('Content-Type: text/css');
        $view = new View('css/main.css');
        $view->render(TRUE);
    }
    public function js($keys)
    {
        if (expires::check(300) === FALSE) expires::set(300);
        header('Content-Type: application/x-javascript');
        foreach (explode(',', $keys) as $key)
        {
            $view = null;
            if ($key == 'loginOrRegister')
                $view = new View('js/loginOrRegister.js');

            if (!$view)
                throw Kohana_exception("No such file or directory");
            $view->render(TRUE);
        }

    }

}
