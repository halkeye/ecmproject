<?php

class Model_Registration extends ORM 
{
    const STATUS_UNPROCESSED = 0; // Payment has not been sent yet (or recieved if mail-in)
    const STATUS_PROCESSING  = 1; // Waiting for Paypal to respond, mail-in/in-person payment is in limbo.
	const STATUS_NOT_ENOUGH	 = 2; // Payment recieved is not enough to pay cost of pass.
	const STATUS_FAILED		 = 98; //Registration no longer valid (cancelled, refunded, etc).
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
			'gname'			=> array ( 'type' => 'string', 'length' => 55												 ),
			'sname'			=> array ( 'type' => 'string', 'length' => 55												 ),
			'email'			=> array ( 'type' => 'string', 'length' => 55												 ),
			'phone'			=> array ( 'type' => 'string', 'length' => 25												 ),
            'account_id'    => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true,                      ),
            'reg_id'        => array ( 'type' => 'string', 'length' => 25,                                               ),
            'status'      	=> array ( 'type' => 'int',    'max' => 127,        'unsigned' => false,                     ),
    );

    public $formo_defaults = array(
            'pass_id' 	=> array( 'type'  => 'select', 	'label' => 'Pass', 			'required'	=> true, 'adminRequired'=>true    ),            
            'gname' 	=> array( 'type'  => 'text', 	'label' => 'Given Name', 	'required'	=> true, 'adminRequired'=>true 	  ),
            'sname' 	=> array( 'type'  => 'text', 	'label' => 'Surname', 		'required'	=> true, 'adminRequired'=>true    ),
			'phone' 	=> array( 'type'  => 'text', 	'label' => 'Phone', 													  ),
			'status'	=> array( 'type'  => 'select',  'label' => 'Status',		'required'	=> true							  ),
			
			/*
            'badge' => array( 'type'  => 'text', 'label' => 'Badge', 'required'=>true    ),
            'dob'   => array( 'type'  => 'date', 'label' => 'Date of Birth', 'required'=>true ),
            'email' => array( 'type'  => 'text', 'label' => 'Email', 'required'=>true ),           
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
        $filters['pass_id'] = array(
            array(array($this, '__update_convention_from_pass')),
        );
		$filter['account_id'] = array(
			array(array($this, '__resolve_account')),
		);
        return $filters;
    }   
	
	/* Validation callbacks */
    public function __valid_pass($value) {
		return (bool) ORM::Factory('Pass', $value)->where('convention_id', '=', $this->convention_id)->count_all();
    }
	public function __check_regID_availability($value) {
		return ! (bool) ORM::Factory('Registration')->where('reg_id', '=', $value)->count_all();
	} 

	/* Filter callbacks */
	public function __update_convention_from_pass($value) {
        $pass = ORM::Factory('Pass')->where('passes.id','=',$value)->find();
        $this->convention_id = $pass->convention_id;
        return $value;
    }
	public function __determine_associated_account($value) {
		if ( isset($this->email) && !empty($this->email) )
		{
			return (bool) false;
		}
		
		return (bool) true;
	}
	public function __resolve_account($value) {
	
	}
	  
	/* Ticket allocation methods */	
	public function reserveTickets($amount = 1) {
		DB::query(NULL, 'START TRANSACTION')->execute();
		$query = DB::query(Database::SELECT, 'SELECT * FROM ticket_counters WHERE pass_id = :pass_id FOR UPDATE'); 
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
	public function finalizeTickets($amount = 1) {
		$alloc = ($amount <= $this->ticket_reserved) ? $amount : $this->ticket_reserved; //Don't "allocate" more than what was originally reserved.
	
		//If reserveTickets reserved 0, this query will change nothing. ticket_reserved/ticket_next_id fields are inaccessible from anywhere outside this class.
		$allocate_query = DB::query(Database::UPDATE, 'UPDATE ticket_counters SET tickets_assigned = tickets_assigned + :alloc, next_id = next_id + :next_id');
		$allocate_query->param(':alloc', $alloc);
		$allocate_query->param(':next_id', $alloc); 
		print "Rows changed: " . $allocate_query->execute();
	
		print (string)$allocate_query;
	
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
	public static function getAllRegistrationsByConvention($account_id)	{
		
		return ORM::Factory('Registration')->where('account_id', '=', $account_id)->order_by('convention_id', 'DESC')->find_all();	
	}	
	
	public function statusToString() 
    {
		if ($this->status == Model_Registration::STATUS_UNPROCESSED)
			return 'UNPROCESSED';
		else if ($this->status == Model_Registration::STATUS_PROCESSING )
			return 'PROCESSING';
		else if ($this->status == Model_Registration::STATUS_PAID)
			return 'PAID';
		else if ($this->status == Model_Registration::STATUS_NOT_ENOUGH)
			return 'PARTIAL PAYMENT';
		else if ($this->status == Model_Registration::STATUS_FAILED)
			return 'CANCELLED';
		else
			return 'IN LIMBO';
	}
	
	public function getColumns()
	{
		//return implode(",", array_keys($this->table_columns));
		$keys = array_keys($this->table_columns);
		$columns = array();
		
		foreach($keys as $key):
			$columns[$key] = __('convention.registration_field_' . $key);					
		endforeach;
		
		return implode(",", $columns);
	}
	
	private function sendConfirmationEmail()
	{		
		$conv = ORM::Factory('Convention',$this->convention_id)->find();
		$pass = ORM::Factory('Pass',$this->pass_id)->find();
		$acct = ORM::Factory('Account',$this->account_id)->find();
		
		if (!$conv->loaded || !$pass->loaded)
		{
			die('Unexpected error encountered! Press back and try again else contact the system administrators');
		}
		
		/* Prevent spamming the user twice. Ignore upper/lowercase? */
		if ($this->email == $acct->email)
			$email = $this->email;
		else
			$email = $this->email . ',' . $acct->email;
		
        $emailVars = array(
                'email'                    => $email,
                'reg'            		   => $this,
				'conv'					   => $conv,
				'pass'					   => $pass
			);

        $to      = $emailVars['email'];
        $from    = __('ecmproject.outgoing_email_name') . ' <' . __('ecmproject.outgoing_email_address') . '>';
        $subject = __('ecmproject.registration_subject');
 
        $view = new View('user/register_confirmation', $emailVars);
        $message = $view->render();
		
        email::send($to, $from, $subject, $message, TRUE);    
	}
	
	/* Non-used validation callbacks */
	public function _valid_date($value)	{
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
}

/* End of file user.php */
/* Location: ./application/models/registration.php */ 
