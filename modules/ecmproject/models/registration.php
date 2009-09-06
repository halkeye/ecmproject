<?php

class Registration_Model extends ORM 
{
    /* On unserialize never check the db */
    protected $reload_on_wakeup = false;

    // Current relationships
    public $has_and_belongs_to_many = array('accounts_usergroups' => 'usergroups');

    // Table primary key and value
    protected $primary_key = 'id';

    // Model table information
    protected $table_columns = array (
            'id'          => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
            'email'       => array ( 'type' => 'string', 'length' => '55'                                              ),
            'password'    => array ( 'type' => 'string', 'length' => '40'                                              ),
            'salt'        => array ( 'type' => 'string', 'length' => '10',                                             ),
            'status'      => array ( 'type' => 'int',    'max' => 127,        'unsigned' => false,                     ),
            'created'     => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => false,                     ),
            'login'       => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => false, 'null' => true,     ),
            /*
CREATE TABLE registrations(
   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   convention_id INT UNSIGNED NOT NULL,
   pass_id INT UNSIGNED NOT NULL,
   account_id INT UNSIGNED, -- Took out NOT NULL requirement for SET NULL trigger to work.
   gname VARCHAR(55) NOT NULL, -- Given name
   sname VARCHAR(55) NOT NULL, -- Surname
   badge VARCHAR(55),
   dob DATE NOT NULL,
   phone VARCHAR(15) NOT NULL,
   cell VARCHAR(15) NOT NULL,
   address TEXT,
   email VARCHAR(55) NOT NULL, -- Account email can be the same as this one...
   econtact VARCHAR(55) NOT NULL,
   ephone VARCHAR(15) NOT NULL,
   heard_from TEXT,
   attendance_reason TEXT,
   */
    );

    var $validation = array(
        array(
            'field' => 'dob',
            'label' => 'Date Of Birth',
            'rules' => array('xss_clean', 'required', 'trim', 'valid_date'),
        ),
        array(
            'field' => 'cell',
            'label' => 'Cell Phone Number',
            'rules' => array('xss_clean', 'trim', /*'valid_phone_number'*/),
        ),
        array(
            'field' => 'address',
            'label' => 'Address',
            'rules' => array('xss_clean', 'trim'),
        ),
        array(
            'field' => 'econtact',
            'label' => 'Emergency Contact',
            'rules' => array('xss_clean', 'required', 'trim', 'max_length' => 55, 'alpha_dash_dot'),
        ),
        array(
            'field' => 'ephone',
            'label' => 'Emergency Contact Phone',
            'rules' => array('xss_clean', 'trim', /*'valid_phone_number'*/),
        ),
    );

    /**
     * @param $accountId Account Id
     * @param $conventionId [optional] Convention id, defaults to most recent one
     * @return array of registrations
     */
    public static function getByAccount($accountId, $conventionId = null)
    {
        $db = Database::instance();

        $vars = array($accountId);
        if ($conventionId)
        {
            $conventionWhere = 'c.conventionId = ?';
            $vars[] = $conventionId;
        }
        else 
        {
            $conventionWhere = '? BETWEEN c.start_date AND c.end_date';
            $vars[] = time();
        }

        $result = $db->query("
                SELECT 
                    *
                FROM 
                    registrations r
                LEFT JOIN 
                    conventions c ON (r.convention_id=c.id)
                LEFT JOIN 
                    passes p ON (r.pass_id=p.id)
                WHERE
                    account_id = ? AND $conventionWhere
                ", $vars);
        return $result;
    }

}

/* End of file user.php */
/* Location: ./application/models/registration.php */ 
