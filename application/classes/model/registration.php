<?php

class Model_Registration extends ORM 
{
    const STATUS_UNPROCESSED = 0; // Payment has not been sent yet (or received if mail-in)
    const STATUS_PROCESSING  = 1; // Waiting for Paypal to respond, mail-in/in-person payment is in limbo.
    const STATUS_NOT_ENOUGH  = 2; // Payment received is not enough to pay cost of pass.
    const STATUS_REFUNDED    = 97; // We've refunded the ticket. No longer valid
    const STATUS_FAILED      = 98; // Registration no longer valid (cancelled, refunded, etc).
    const STATUS_PAID        = 99; // Fully working and paid
    
    protected $_ignored_columns = array('agree_toc', 'unique_badge', 'comp_cid', 'comp_loc', 'comp_id');

    /* On unserialize never check the db */
    protected $_reload_on_wakeup = false;

    protected $_belongs_to = array(
        'convention' => array (
            'model' => 'convention', 
            'foreign_key' => 'convention_id'
        ),
        'pass' => array(
            'model' => 'pass', 
            'foreign_key' => 'pass_id',
        ),
        'account' => array(
            'model' => 'account', 
            'foreign_key' => 'account_id',
        ),
    );

    //protected $load_with = array('convention','pass', 'account');

    // Table primary key and value
    protected $_primary_key = 'id';

    // Model table information  
    protected $_table_columns = array (
            'id'            => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
            'convention_id' => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true,                      ),
            'pass_id'       => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true,                      ),
            'gname'         => array ( 'type' => 'string', 'length' => 55                                                ),
            'sname'         => array ( 'type' => 'string', 'length' => 55                                                ),
            'email'         => array ( 'type' => 'string', 'length' => 55                                                ),
            'phone'         => array ( 'type' => 'string', 'length' => 25                                                ),
            'account_id'    => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true,                      ),
            'reg_id'        => array ( 'type' => 'string', 'length' => 25,                                               ),
            'status'        => array ( 'type' => 'int',    'max' => 127,        'unsigned' => false,                     ),
            'pickupStatus'  => array ( 'type' => 'int',    'max' => 127,        'unsigned' => false,                     ),
			'dob'  			=> array ( 'type' => 'date',   																 ),
    );

    public $formo_defaults = array(
            'pass_id'   => array( 'type'  => 'select',  'label' => 'Pass',          'required'  => true, 'adminRequired'=>true    ),            
            'gname'     => array( 'type'  => 'text',    'label' => 'Given Name',    'required'  => true, 'adminRequired'=>true    ),
            'sname'     => array( 'type'  => 'text',    'label' => 'Surname',       'required'  => true, 'adminRequired'=>true    ),
            'phone'     => array( 'type'  => 'text',    'label' => 'Phone',                                                       ),
            'status'    => array( 'type'  => 'select',  'label' => 'Status',        'required'  => true                           ),
            'pickupStatus'  => array( 'type'  => 'select_noblank','label' => 'Pickup Status',       'required'  => true           ),
            'email'     => array( 'type'  => 'text',    'label' => 'Email',                                                       ), 
			'dob'   => array( 'type'  => 'date', 'label' => 'Date of Birth', 		'required'=>false 							  ),			
            /*
            'badge' => array( 'type'  => 'text', 'label' => 'Badge', 'required'=>true    ),
            
             
            'cell'  => array( 'type'  => 'text', 'label' => 'Cell Phone', 'required' => false),
            'city'  => array( 'type'  => 'text', 'label' => 'City', 'required' => true),
            'prov'  => array( 'type'  => 'text', 'label' => 'Province', 'required' => true),
            'econtact'  => array( 'type'  => 'text', 'label' => 'Emergency Contact Name', 'required' => true),
            'ephone'  => array( 'type'  => 'text', 'label' => 'Emergency Contact Phone', 'required' => true),
            */
    );
    
    /* Used for ticket allocation */
    private $ticket_reserved = 0;
    private $ticket_next_id = 0;
    
    public function rules()
    {
        $rules = parent::rules();
        $rules['phone'] = array(            
            array('phone'),
        );
        $rules['agree_toc'] = array(array('range', array(':value',0,1))); //This field is not triggered?
        $rules['pass_id'] = array(
            array(array($this, '__valid_pass')),
        );
        $rules['reg_id'] = array(
            array('not_empty'),
            array(array($this, '__check_regID_availability')),
        );
        $rules['email'] = array(
            array('email')
        );
		$rules['dob'] = array(
			array('date')
		);
                
        foreach ($this->formo_defaults as $field => $fieldData) {
            if (!isset($rules[$field])) $rules[$field] = array();
            if (isset($fieldData['required']) && $fieldData['required'])
            {
                array_push($rules[$field], array('not_empty'));
            }
            else
            {
                array_push($rules[$field], array('min_length', array(':value', 0)));
            }
            array_push($rules[$field], array('max_length', array(':value', 255)));
        }
                
        return $rules;
    }   
    	
	public function values(array $values, array $expected = NULL)
	{
		if ( !empty($values['dob-year']) && !empty($values['dob-month']) && !empty($values['dob-day']) )
		{
			$values['dob'] = $values['dob-year'] . '-' . $values['dob-month'] . '-' . $values['dob-day'];
		}
			
		return parent::values($values, $expected);
	}	
		
    public function save(Validation $validation = NULL) {
        /* Re-resolve account id if email changes. Alternately, check for empty(account_id) && $this->_changed['email'] for a permanent lock once associated. */
        if (@$this->_changed['email']) {
            $this->__resolve_account($this->email);
        }
		
		/* If pickup status is unset, set pickup status to not picked up (0). */
		if ( !empty($this->pickupStatus) || !is_numeric($this->pickupStatus) ) {
			$this->pickupStatus = 0;
		}
		
        return parent::save($validation);
    }
    
    public function filters()
    {
        $filters = parent::filters();
        $filters[TRUE] = array(
            array('trim')
        );
        $filters['startDate'] = array(
            array('strtotime')
        );
        $filters['endDate'] = array(
            array('strtotime'),
        );
        return $filters;
    }   
    
    public function delete()
    {
        $pass_id = $this->pass_id;
        $result = parent::delete();
        
        if ($result) {
            $this->deleteTicket($pass_id);
        }
        
        return $result;
    }
    
    /* Validation callbacks */
    public function __valid_pass($value) {
        return (bool) ORM::Factory('Pass')->where('passes.id','=',$value)->where('convention_id', '=', $this->convention_id)->count_all();
    }
    public function __check_regID_availability($value) {
        $changed = $this->_changed;
        if ( empty($changed['reg_id'])) {
            return true;
        }
        
        return ! (bool) ORM::Factory('Registration')->where('reg_id', '=', $value)->count_all();
    } 

    public function __determine_associated_account($value) {
        if ( isset($this->email) && !empty($this->email) )
        {
            return (bool) false;
        }
        
        return (bool) true;
    }
    public function __resolve_account($value) {     
        $acct = ORM::Factory('Account')->where('email', '=', $value)->find();
        if ( $acct->loaded() ) {
            $this->account_id = $acct->id;
        }       
        
        return true;
    }
      
    /* Ticket allocation methods */ 
    public function reserveTickets($amount = 1) {
        DB::query(NULL, 'START TRANSACTION')->execute();
        $query = DB::query(Database::SELECT, 'SELECT * FROM ticketcounters WHERE pass_id = :pass_id FOR UPDATE'); 
        $query->param(':pass_id', $this->pass_id);              
        $result = $query->execute();
        
        /* No ticket counter, non-numeric reservation amount, amount less than zero */
        if ( !$result->count() || !is_numeric($amount) || $amount <= 0 ) {
            DB::query(NULL, 'ROLLBACK');
            return 0;
        }
        /* No limit, or enough tickets available */
        else if ( $result->get('tickets_total', -1) == -1 || $result->get('tickets_total') - $result->get('tickets_assigned') >= $amount) { //No limit.
            $this->ticket_reserved = $amount;
            $this->ticket_next_id  = $result->get('next_id');       
        }
        else {
            DB::query(NULL, 'ROLLBACK');
            return 0;
        }

        return $this->ticket_next_id;
    }
    public function finalizeTickets($amount = 1, $incNextID = false) {
        //Reserve tickets is not open. Do nothing (or throw an exception)
        if (!$this->ticket_reserved) {
            return;
        }
    
        $alloc = ($amount <= $this->ticket_reserved) ? $amount : $this->ticket_reserved; //Don't "allocate" more than what was originally reserved.
    
        //If reserveTickets reserved 0, this query will change nothing. ticket_reserved/ticket_next_id fields are inaccessible from anywhere outside this class.
        $allocate_query = DB::query(Database::UPDATE, 'UPDATE ticketcounters SET tickets_assigned = tickets_assigned + :alloc WHERE pass_id = :pass_id');
        $allocate_query->param(':alloc', $alloc);
        $allocate_query->param(':next_id', $incNextID ? $alloc : 0); 
        $allocate_query->param(':pass_id', $this->pass_id);
        $allocate_query->execute();
		
		//(TEMP) Fix issue #17. Ticket registration needs to be re-thought of. 
		//Lock-less approach: select next_id, create reg ID, attempt insert. If failure, repeat process. 
		$allocate_query = DB::query(Database::UPDATE, 
			'UPDATE ticketcounters SET next_id = next_id + :next_id WHERE pass_id IN (SELECT id from passes where convention_id = :convention_id)');
		$allocate_query->param(':next_id', $incNextID ? $alloc : 0);
		$allocate_query->param(':convention_id', $this->convention_id);
		$allocate_query->execute();
        
        DB::query(NULL, 'COMMIT')->execute();
        $this->ticket_reserved = 0;
        $this->ticket_next_id = 0;
    }
    public function releaseTickets() {
        if ($this->ticket_reserved) {
            DB::query(NULL, 'ROLLBACK')->execute(); //Incidentally, if a save() actually got through but then an exception gets thrown while in the same block, everything gets rolled back.            
        }
        
        $this->ticket_reserved = 0;
        $this->ticket_next_id = 0;
    }   
    private function deleteTicket($pass_id) {
        if ($this->ticket_reserved) {
            return;
        }
        
        DB::query(NULL, 'START TRANSACTION')->execute();
        $query = DB::query(Database::SELECT, 'SELECT * FROM ticketcounters WHERE pass_id = :pass_id FOR UPDATE'); 
        $query->param(':pass_id', $pass_id);                
        $result = $query->execute();
        
        $allocate_query = DB::query(Database::UPDATE, 'UPDATE ticketcounters SET tickets_assigned = tickets_assigned - 1 WHERE pass_id = :pass_id');
        $allocate_query->param(':pass_id', $pass_id);
        $allocate_query->execute();
        DB::query(NULL, 'COMMIT')->execute();   
    }
    
    /* Utility methods */
    public static function getTotalRegistrations($cid) {
        return ORM::Factory('Registration')->where('convention_id','=',$cid)->count_all();
    }
    public static function getStatusValues() {
        $status_values = array();
        $status_values[Model_Registration::STATUS_UNPROCESSED] = 'UNPROCESSED';
        $status_values[Model_Registration::STATUS_PROCESSING] = 'PROCESSING';
        $status_values[Model_Registration::STATUS_PAID] = 'PAID';
        $status_values[Model_Registration::STATUS_NOT_ENOUGH] = 'PARTIAL PAYMENT';
        $status_values[Model_Registration::STATUS_FAILED] = 'FAILED';
        $status_values[Model_Registration::STATUS_REFUNDED] = 'REFUNDED';
        return $status_values;
    }
    public function toString() {
        return $this->pass . ' - ' . $this->badge;
    }
    
    /* Unsorted methods. TODO: CLEANUP. */
        
    /**
     * @param $accountId Account Id
     * @param $conventionId [optional] Convention id, defaults to most recent one
     * @return array of registrations
     */
    public static function getByAccount($accountId, $conventionId = null)
    {
        $vars = array(':account_id' => $accountId);
        if ($conventionId)
        {
            $conventionWhere = 'c.conventionId = :conventionId';
            $vars[':conventionId'] = $conventionId;
        }
        else 
        {
            //$conventionWhere = ':startTime BETWEEN c.start_date AND c.end_date';
            $conventionWhere = '1=1';
            //$vars[':startTime'] = time();
        }

        $query = DB::query(Database::SELECT, "
                SELECT 
                    r.*,
                    c.name as convention_name,
                    c.location as convention_location,
                    p.name as pass_name
                FROM 
                    registrations r
                LEFT JOIN 
                    conventions c ON (r.convention_id=c.id)
                LEFT JOIN 
                    passes p ON (r.pass_id=p.id)
                WHERE
                    account_id = :account_id AND $conventionWhere
                ");
        $query->parameters($vars);
        return $query->execute();
    }
    public function getPossiblePassesQuery()
    {
        return ORM::Factory('pass')
            ->where('enddate', '>=',   time())
            ->where('startdate', '<=', time())
            ->where('isPurchasable', '=', 1);
            #->where('convention_id', $this->convention_id);
    }

    public function getForAccount($account_id)
    {
        /* FIXME - Maybe limit to this convention also, so any outstanding entries will be ignored */
        return $this 
            ->with('convention')
            ->with('pass')
            ->where('account_id', '=', $account_id)
            ->where('status', 'IN', array(
                        Model_Registration::STATUS_UNPROCESSED, /* Only grab one we havn't heard back from yet */
                        Model_Registration::STATUS_NOT_ENOUGH
                )
            )
            ->find_all();
    }
    
    /*
    * getAllRegistrationsByConvention
    *
    * Returns all conventions for a particular account ordered by convention_id in newest first (DESC order) Used for displaying 
    * the history of registrations for a particular user.
    */
    public static function getAllRegistrationsByConvention($account_id) {
        
        return ORM::Factory('Registration')->where('account_id', '=', $account_id)->order_by('convention_id', 'DESC')->find_all();  
    }   
    
    public function statusToString() 
    {
        $values = $this->getStatusValues();
        if (isset($values[$this->status])) { return $values[$this->status]; };
        return 'IN LIMBO';
    }

    public function pickupToString() 
    {
        if ($this->pickupStatus)
            return __("Picked Up"); 
        return __("Not Picked Up"); 
    }
    
    public function getColumns()
    {
        //return implode(",", array_keys($this->table_columns));
        $keys = array_keys($this->_table_columns);
        $columns = array();
        
        foreach($keys as $key):
            $columns[$key] = __($key);                  
        endforeach;
        
        return implode(",", $columns);
    }
    
    public function sendConfirmationEmail()
    {       
        $config = Kohana::config('ecmproject');
        
        if (!$this->convention->loaded() || !$this->pass->loaded())
        {
            die('Unexpected error encountered! Press back and try again else contact the system administrators');
        }
        
        $emails = array();
        if ($this->email) $emails[] = $this->email;
        /* Prevent spamming the user twice. Ignore upper/lowercase? */
        if ($this->email != $this->account->email) $emails[] = $this->account->email;
        
        $emailVars = array(
                'reg'                      => $this,
                'conv'                     => $this->convention,
                'pass'                     => $this->pass
            );

        $view = new View('user/register_confirmation', $emailVars);
        $message = $view->render();

        ### FIXME - MAKE SURE TO ADD non html version too
        $email = Email::factory($config['registration_subject']);
        $email->message($message,'text/html');
        foreach ($emails as $rcpt)
        {
            $email->to($rcpt);
        }
        $email->from($config['outgoing_email_address'], $config['outgoing_email_name']);
        $email->send();
    }
    
    /* Non-used validation callbacks */
    public function _valid_date($value) {
        $date = strtotime($value);

        if (!$date || date("Y-m-d", $date) != $value)
            return FALSE;
        return TRUE;
    }
    public function _valid_birthdate($value) {
        $date = strtotime($value);
        /* if someone isn't born yet, they can't have a badge (mostly because they don't have a birthday) */
        if ($date > time())
            return FALSE;
        return TRUE;
    }
    public function _unique_badge(Validation $array) {
        $query = ORM::Factory('registration');
        // TODO: use config
        // TODO: switch this to be a config. name bool, and badge bool, so name and badge can be enforced unique
        $query->where('gname', '=',$array['gname']);
        $query->where('sname', '=',$array['sname']);
        $query->where('account_id', '=',$array['account_id']);
        if ($this->loaded) 
            $query->where('id','!=', $this->id);

        return ((bool)$query->count_all());
    }
    
    /*
    * Builds a registration identifier given a post, valid locations and a unique id number.
    *
    * array $post 
    * array $locations
    * int $convention_id 
    * returns an empty array on success or an array with error messages on failure. 
    */
    public function build_regID($post, $locations, $convention_id) {
        $validate = Validation::Factory($post)
            ->rule('comp_loc', 'in_array', array(':value', array_values($locations)))
            ->rule('comp_id', 'numeric')
            ->rule('comp_id', 'not_empty');
                    
        $errors = array();  
        if ( !$validate->check() || !is_numeric($convention_id) )
        {               
            foreach($validate->errors('reg_id') as $error_msg) 
            {
                array_push($errors, $error_msg);
            }
            
            $this->reg_id = '';
        }
        else {
            $this->reg_id = sprintf('%s-%02s-%04s', $post['comp_loc'], $convention_id, $post['comp_id']);
        }
                    
        return $errors;
    }
}

/* End of file user.php */
/* Location: ./application/models/registration.php */ 
