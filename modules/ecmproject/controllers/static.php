<?php

class Static_Controller extends Controller_Core
{
    public function css()
    {
        if (expires::check(300) === FALSE) expires::set(300);

        header('Content-Type: text/css');
        $view = new View('css/main.css');
        $view->render(TRUE);
        #Last-Modified: Fri, 18 Dec 2009 08:13:06 GMT
        #Etag: "1104d1-16ec-47afc4f340880"

    }

}
