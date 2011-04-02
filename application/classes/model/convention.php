<?php

class Model_Convention extends ORM
{

    /* On unserialize never check the db */
    protected $_reload_on_wakeup = false;

    protected $_has_many = array(
        'registration' => array()
    );

    // Table primary key and value
    protected $_primary_key = 'id';

    // Model table information

    protected $_table_columns = array (
            'id'            => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
            'name'          => array ( 'type' => 'string', 'length' => '100' ),
            'start_date'    => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, ),
            'end_date'      => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, ),
			'location'          => array ( 'type' => 'string', 'length' => '150' ),
    );

	public $default_fields = array(
            'name' => array( 'type'  => 'text', 'label' => 'Convention Name', 'required'=>true ),
            'start_date' => array( 'type'  => 'date', 'label' => 'Start Date', 'required'=>true    ),
            'end_date' => array( 'type'  => 'date', 'label' => 'End Date', 'required'=>true    ),
            'location' => array( 'type'  => 'text', 'label' => 'Location', 'required'=>true    )
    );

    public function filters()
    {
        $filters = parent::filters();
        $filters['*'] = array('trim');
        return $filters;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules['name'] = array(
            array('not_empty'),
        );
        $rules['location'] = array(
            array('not_empty'),
        );
        //Non-set start date will be set to today
        $rules['start_date'] = array(
            array(array($this, '_valid_date'))
        );
        //Non-set end date will be set to convention end.
        $rules['end_date'] = array(
            array(array($this, '_valid_date'))
        );
        $rules['valid_range'] = array(
            array(
                array($this, '_valid_range'),
                array(':validation', 'start_date', 'end_date')
            )
        );
        return $rules;
    }

    public function save_admin(array & $array)
	{
        var_dump($array);
		$array = Validation::factory($array);
        $array->label('start_date', 'Start Date');

		$array->rule('name', 'not_empty');
		$array->rule('location', 'not_empty');
		$array->rule('start_date', array($this, '_valid_date')); //Non-set start date will be set to today
		$array->rule('end_date', array($this, '_valid_date')); //Non-set end date will be set to convention end.
        $array->rule('valid_range', array($this, '_valid_range'), array(':validation', 'start_date', 'end_date'));
		return $this->loaded() ? $this->update($array) : $this->create($array);
	}

	/* Have some utility library instead of duplicating this across models? */
	public function _valid_date($date)
	{
		/* If date validation failed (not a date string) or date does not match expected... */
		if (!$date || date("Y-m-d", strtotime($date)) != $date)
            return 0;
        return 1;
	}

	/* This is cheap (since I hardcoded stuff) But it works :) */
	public function _valid_range(Validation $array, $field1, $field2)
	{
		$start = strtotime($array[$field1]);
		$end = strtotime($array[$field2]);

		if (!$start || !$end || $start >= $end)
            return 0;
        return 1;
	}

    public function getCurrentConvention()
    {
        //return $this->where(time().' BETWEEN start_date AND end_date')->find(); //If start dates are set to actual convention start dates, this goes boom.
		return $this->where('start_date >', time())->orderby('start_date', 'asc')->find();
    }

	public static function getTotalConventions()
	{
        $query = DB::query(Database::SELECT, 'SELECT COUNT(*) as count FROM conventions');
        $row = $query->execute();
        return (int) $row[0]['count'];
	}
	public function validConvention($cid)
	{
		if (!is_numeric($cid) || $cid == -1)
			return false;

		$res = ORM::factory('Convention')->find($cid);
		if ($res->loaded())
			return true;
		else
			return false;
	}
}
