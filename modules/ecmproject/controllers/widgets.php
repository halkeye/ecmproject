<?php

class Widgets_Controller extends Controller 
{
    public function index()
    {
        $this->regList();
    }

    public function regList()
    {
        $this->view->heading    = "Pre-registration List";
        $this->view->subheading = "";
		
        $data = array();
        $rows = ORM::Factory('Registration')
                ->where('status', Registration_Model::STATUS_PAID)
				->orderBy(array('badge' => 'ASC','gname'=>'ASC'))
				->find_all();
        foreach ($rows as $row)
        {
            $badge = $row->badge;
            if (!$badge) { $row->gname; }
            $letter = strtoupper($badge{0});
            if (!ctype_alpha($letter)) $letter = '#';

            $data['preRegs'][$letter][] = $badge;
        }
        /* Our "checkout template" */
        $this->view->content = new View('widgets/regList', $data);
    }

}
