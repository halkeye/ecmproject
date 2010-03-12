<?php

class Static_Controller extends Controller_Core
{
    public function css($key = 'main.css')
    {
#        if (expires::check(300) === FALSE) expires::set(300);

        header('Content-Type: text/css');
        $view = new View('css/'.basename($key));
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
    
    public function img($filename)
    {
        if (expires::check(300) === FALSE) expires::set(300);
        $info = pathinfo($filename);
        $file = Kohana::find_file('views/images', basename($info['basename'], '.'.$info['extension']), true, $info['extension']);

        if ($info['extension'] == 'png')
            header('Content-Type: Content-Type: image/png');
        if ($info['extension'] == 'jpg')
            header('Content-Type: Content-Type: image/jpeg');
        if ($info['extension'] == 'gif')
            header('Content-Type: Content-Type: image/gif');
        if ($info['extension'] == 'ico')
            header('Content-Type: Content-Type: image/vnd.microsoft.icon');

        header('Content-Length: ' . filesize($file));
        
        ob_clean();
        flush();
        readfile($file);

    }

}
