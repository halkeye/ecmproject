<?php  if ( ! defined('BASEPATH') && !defined('SYSPATH')) exit('No direct script access allowed');

/**
 * Data Mapper Class, OverZealous Edition
 *
 * Transforms database tables into objects.
 *
 * @license 	MIT License
 * @category	Models
 * @author  	Simon Stenhouse, Phil DeJarnett
 * @link    	http://www.overzealous.com/dmz/
 * @version 	1.4.0 ($Rev: 174 $) (Based on DataMapper 1.6.0)
 */

// --------------------------------------------------------------------------

/**
 * Autoload
 *
 * Autoloads object classes that are used with DataMapper.
 */
spl_autoload_register('DataMapper::autoload');

// --------------------------------------------------------------------------

/**
 * Data Mapper Class
 */
class DataMapper {
	
	static $config = array();
	static $common = array();
	static $global_extensions = array();

	var $error;
	var $stored;
	var $prefix = '';
	var $join_prefix = '';
	var $table = '';
    var $pk_key = 'id';
	var $model = '';
	var $error_prefix = '';
	var $error_suffix = '';
	var $created_field = '';
	var $updated_field = '';
	var $auto_transaction = FALSE;
	var $auto_populate_has_many = FALSE;
	var $auto_populate_has_one = FALSE;
	var $valid = FALSE;
	var $validated = FALSE;
	var $local_time = FALSE;
	var $unix_timestamp = FALSE;
	var $fields = array();
	var $all = array();
	var $parent = array();
	var $validation = array();
	var $has_many = array();
	var $has_one = array();
	var $query_related = array();
	var $production_cache = FALSE;
	var $extensions_path = '';
	var $extensions = NULL;
	var $free_result_threshold = 100;
	var $default_order_by = NULL;
	
	// If true before a related get(), any extra fields on the join table will be added.
	var $_include_join_fields = FALSE;
	// If true before a save, this will force the next save to be new.
	var $_force_save_as_new = FALSE;
	// If true, the next where statement will not be prefixed with an AND or OR.
	var $_where_group_started = FALSE;

	/**
	 * Constructor
	 *
	 * Initialize DataMapper.
	 */
	function DataMapper($id = NULL)
	{
		$this->_assign_libraries();
	
		$this->_load_languages();

		$this->_load_helpers();

		$common_key = singular(get_class($this));
		
		// Determine model name
		if (empty($this->model))
		{
			$this->model = $common_key;
		}

		// Load stored config settings by reference
		foreach (array_keys(DataMapper::$config) as $key)
		{
			// Only if they're not already set
			if (empty($this->{$key}))
			{
				$this->{$key} =& DataMapper::$config[$key];
			}
		}

		// Load model settings if not in common storage
		if ( ! array_key_exists($common_key, DataMapper::$common))
		{
			// If model is 'datamapper' then this is the initial autoload by CodeIgniter
			if ($this->model == 'datamapper')
			{
				// Load config settings
				$this->config->load('datamapper', TRUE, TRUE);

				// Get and store config settings
				DataMapper::$config = $this->config->item('datamapper');
				
				DataMapper::_load_extensions(DataMapper::$global_extensions, DataMapper::$config['extensions']);
				unset(DataMapper::$config['extensions']);

				return;
			}
			
			$loaded_from_cache = FALSE;
			
			// Load in the production cache for this model, if it exists
			if( ! empty(DataMapper::$config['production_cache']))
			{
				// attempt to load the production cache file
				$cache_folder = APPPATH . DataMapper::$config['production_cache'];
				if(file_exists($cache_folder) && is_dir($cache_folder) && is_writeable($cache_folder))
				{
					$cache_file = $cache_folder . '/' . $common_key . EXT;
					if(file_exists($cache_file))
					{
						include($cache_file);
						if(isset($cache))
						{
							DataMapper::$common[$common_key] =& $cache;
							unset($cache);
			
							// allow subclasses to add initializations
							if(method_exists($this, 'post_model_init'))
							{
								$this->post_model_init(TRUE);
							}
							
							// Load extensions (they are not cacheable)
							if(!empty($this->extensions))
							{
								$extensions = $this->extensions;
								$this->extensions = array();
								DataMapper::_load_extensions($this->extensions, $extensions);
								DataMapper::$common[$common_key]['extensions'] = $this->extensions;
							}
							
							$loaded_from_cache = TRUE;
						}
					}
				}
			}
			
			if(! $loaded_from_cache)
			{

				// Determine table name
				if (empty($this->table))
				{
					$this->table = plural(get_class($this));
				}
	
				// Add prefix to table
				$this->table = $this->prefix . $this->table;
	
				// Convert validation into associative array by field name
				$associative_validation = array();
	
				foreach ($this->validation as $name => $validation)
				{
					if(is_string($name)) {
						$validation['field'] = $name;
					} else {
						$name = $validation['field'];
					}
					
					// clean up possibly missing fields
					if( ! isset($validation['rules']))
					{
						$validation['rules'] = array();
					}
					if( ! isset($validation['label']))
					{
						$validation['label'] = $name;
					}
					// TODO: enable Localization of label
					
					// Populate associative validation array
					$associative_validation[$name] = $validation;
				}
				
				// set up id column, if not set
				if(!isset($associative_validation[$this->pk_key]))
				{
					$associative_validation[$this->pk_key] = array(
						'field' => $this->pk_key,
						'label' => 'Identifier',
						'rules' => array('integer')
					);
				}
	
				$this->validation = $associative_validation;
	
				// Get and store the table's field names and meta data
				$fields = $this->db->field_data($this->table);
	
				// Store only the field names and ensure validation list includes all fields
				foreach ($fields as $field)
				{
					// Populate fields array
					$this->fields[] = $field->name;
	
					// Add validation if current field has none
					if ( ! array_key_exists($field->name, $this->validation))
					{
						$this->validation[$field->name] = array('field' => $field->name, 'label' => '', 'rules' => array());
					}
				}
				
				// convert simple has_one and has_many arrays into more advanced ones
				foreach(array('has_one', 'has_many') as $arr)
				{
					$new = array();
					foreach ($this->{$arr} as $key => $value)
					{
						// allow for simple (old-style) associations
						if (is_int($key))
						{
							$key = $value;
						}
						// convert value into array if necessary
						if ( ! is_array($value))
						{
							$value = array('class' => $value);
						} else if ( ! isset($value['class']))
						{
							// if already an array, ensure that the class attribute is set
							$value['class'] = $key;
						}
						if( ! isset($value['other_field']))
						{
							// add this model as the model to use in queries if not set
							$value['other_field'] = $this->model;
						}
						if( ! isset($value['join_self_as']))
						{
							// add this model as the model to use in queries if not set
							$value['join_self_as'] = $value['other_field'];
						}
						if( ! isset($value['join_other_as']))
						{
							// add the key as the model to use in queries if not set
							$value['join_other_as'] = $key;
						}
						$new[$key] = $value;
					}
					// replace the old array
					$this->{$arr} = $new;
				}
				
				// allow subclasses to add initializations
				if(method_exists($this, 'post_model_init'))
				{
					$this->post_model_init(FALSE);
				}
	
				// Store common model settings
				foreach (array('table', 'fields', 'validation', 'has_one', 'has_many') as $item)
				{
					DataMapper::$common[$common_key][$item] = $this->{$item};
				}
				
				// if requested, store the item to the production cache
				if( ! empty(DataMapper::$config['production_cache']))
				{
					// attempt to load the production cache file
					$cache_folder = APPPATH . DataMapper::$config['production_cache'];
					if(file_exists($cache_folder) && is_dir($cache_folder) && is_writeable($cache_folder))
					{
						$cache_file = $cache_folder . '/' . $common_key . EXT;
						$cache = "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); \n";
						
						$cache .= '$cache = ' . var_export(DataMapper::$common[$common_key], TRUE) . ';';
				
						if ( ! $fp = @fopen($cache_file, 'w'))
						{
							show_error('Error creating production cache file: ' . $cache_file);
						}
						
						flock($fp, LOCK_EX);	
						fwrite($fp, $cache);
						flock($fp, LOCK_UN);
						fclose($fp);
					
						@chmod($cache_file, FILE_WRITE_MODE);
					}
				}
				
				// Load extensions last, so they aren't cached.
				if(!empty($this->extensions))
				{
					$extentions = $this->extensions;
					$this->extensions = array();
					DataMapper::_load_extensions($this->extensions, $extensions);
					DataMapper::$common[$common_key]['extensions'] = $this->extensions;
				}
			}
		}

		// Load stored common model settings by reference
		foreach (array_keys(DataMapper::$common[$common_key]) as $key)
		{
			$this->{$key} =& DataMapper::$common[$common_key][$key];
		}

		// Clear object properties to set at default values
		$this->clear();
		
		if( ! empty($id) && is_numeric($id))
		{
			$this->get_by_id(intval($id));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Autoload
	 *
	 * Autoloads object classes that are used with DataMapper.
	 *
	 * Note:
	 * It is important that they are autoloaded as loading them manually with
	 * CodeIgniter's loader class will cause DataMapper's __get and __set functions
	 * to not function.
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	static function autoload($class)
	{
		// Don't attempt to autoload CI_ or MY_ prefixed classes
		if (in_array(substr($class, 0, 3), array('CI_', 'MY_')))
		{
			return;
		}

		// Prepare class
		$class = strtolower($class);

		// Prepare path
		$path = APPPATH . 'models';

		// Prepare file
		$file = $path . '/' . $class . EXT;

		// Check if file exists, require_once if it does
		if (file_exists($file))
		{
			require_once($file);
		}
		else
		{
			// Do a recursive search of the path for the class
			DataMapper::recursive_require_once($class, $path);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Recursive Require Once
	 *
	 * Recursively searches the path for the class, require_once if found.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	static function recursive_require_once($class, $path)
	{
		if ($handle = opendir($path))
		{
			while (FALSE !== ($dir = readdir($handle)))
			{
				// If dir does not contain a dot
				if (strpos($dir, '.') === FALSE)
				{
					// Prepare recursive path
					$recursive_path = $path . '/' . $dir;

					// Prepare file
					$file = $recursive_path . '/' . $class . EXT;

					// Check if file exists, require_once if it does
					if (file_exists($file))
					{
						require_once($file);

						break;
					}
					else if (is_dir($recursive_path))
					{
						// Do a recursive search of the path for the class
						DataMapper::recursive_require_once($class, $recursive_path);
					}
				}
			}

			closedir($handle);
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Loads in any extensions used by this class or globally.
	 * @return 
	 * @param object $extensions
	 * @param object $name
	 */
	static function _load_extensions(&$extensions, $names)
	{
		foreach($names as $name => $options)
		{
			if( ! is_string($name))
			{
				$name = $options;
				$options = NULL;
			}
			// only load an extension once per class or once globally
			if(isset($extensions[$name]))
			{
				return;
			}
			else if(isset(DataMapper::$global_extensions[$name]))
			{
				return;
			}
			
			if( ! isset($extensions['_methods']))
			{
				$extensions['_methods'] = array();
			}
			
			// determine the file name and class name
			if(strpos($name, '/') === FALSE)
			{
				$file = APPPATH . DataMapper::$config['extensions_path'] . '/' . $name . EXT;
				$ext = $name;
			}
			else
			{
				$file = APPPATH . $name . EXT;
				$ext = array_pop(explode('/', $name));
			}
			
			if(!file_exists($file))
			{
				show_error('DataMapper Error: loading extension ' . $name . ': File not found.');
			}
			
			// load class
			include_once($file);
			
			// create class
			if(is_null($options))
			{
				$o = new $ext();
			}
			else
			{
				$o = new $ext($options);
			}
			$extensions[$name] =& $o;
			
			// figure out which methods can be called on this class.
			$methods = get_class_methods($ext);
			foreach($methods as $m)
			{
				// do not load private methods or methods already loaded.
				if($m[0] !== '_' &&
						is_callable(array($o, $m)) &&
						! array_key_exists($m, $extensions['_methods'])
						) {
					// store this method.
					$extensions['_methods'][$m] = $name;
				}
			}
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Dynamically load an extension when needed.
	 * @param object $name Name of the extension (or array of extensions).
	 */
	function load_extension($name, $options = NULL)
	{
		if( ! is_array($name))
		{
			if( ! is_null($options))
			{
				$name = array($name => $options);
			}
			else
			{
				$name = array($name);
			}
		}
		
		DataMapper::_load_extensions(DataMapper::$global_extensions, $name);
	}

	// --------------------------------------------------------------------
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *                                                                   *
	 * Magic methods                                                     *
	 *                                                                   *
	 * The following are methods to override the default PHP behaviour.  *
	 *                                                                   *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	// --------------------------------------------------------------------

	/**
	 * Get
	 *
	 * Returns the value of the named property.
	 * If named property is a related item, instantiate it first.
	 *
	 * @access	overload
	 * @param	string
	 * @return	object
	 */
	function __get($name)
	{
		// Special case to get form_validation when first accessed
		if($name == 'form_validation')
		{
			$CI =& get_instance();
			if( ! isset($CI->form_validation))
			{
				$CI->load->library('form_validation');
			}
			$this->form_validation = $CI->form_validation;
			$this->lang->load('form_validation');
			return $this->form_validation;
		}

		$has_many = isset($this->has_many[$name]);
		$has_one = isset($this->has_one[$name]);

		// If named property is a "has many" or "has one" related item
		if ($has_many OR $has_one)
		{
			$related_properties = $has_many ? $this->has_many[$name] : $this->has_one[$name];
			// Instantiate it before accessing
			$class = $related_properties['class'];
			$this->{$name} = new $class();

			// Store parent data
			$this->{$name}->parent = array('model' => $related_properties['other_field'], $this->pk_key => $this->{$this->pk_key});

			// Check if Auto Populate for "has many" or "has one" is on
			if (($has_many && $this->auto_populate_has_many) OR ($has_one && $this->auto_populate_has_one))
			{
				$this->{$name}->get();
			}

			return $this->{$name};
		}
		
		$name_single = singular($name);
		if($name_single !== $name) {
			// possibly return single form of name
			$test = $this->{$name_single};
			if(is_object($test)) {
				return $test;
			}
		}

		return NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Call
	 *
	 * Calls the watched method.
	 *
	 * @access	overload
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	function __call($method, $arguments)
	{
		
		// List of watched method names
		$watched_methods = array('save_', 'delete_', 'get_by_related_', 'get_by_related', 'get_by_', '_related_', '_related', '_join_field');

		foreach ($watched_methods as $watched_method)
		{
			// See if called method is a watched method
			if (strpos($method, $watched_method) !== FALSE)
			{
				$pieces = explode($watched_method, $method);
				if ( ! empty($pieces[0]) && ! empty($pieces[1]))
				{
					// Watched method is in the middle
					return $this->{'_' . trim($watched_method, '_')}($pieces[0], array_merge(array($pieces[1]), $arguments));
				}
				else
				{
					// Watched method is a prefix or suffix
					return $this->{'_' . trim($watched_method, '_')}(str_replace($watched_method, '', $method), $arguments);
				}
			}
		}
		
		// attempt to call an extension
		$ext = NULL;
		if($this->_extension_method_exists($method, 'local'))
		{
			$name = $this->extensions['_methods'][$method];
			$ext = $this->extensions[$name];
		}
		else if($this->_extension_method_exists($method, 'global'))
		{
			$name = DataMapper::$global_extensions['_methods'][$method];
			$ext = DataMapper::$global_extensions[$name];
		}
		if( ! is_null($ext))
		{
			array_unshift($arguments, $this);
			return call_user_func_array(array($ext, $method), $arguments);
		}
		
		// show an error, for debugging's sake.
		throw new Exception("Unable to call the method \"$method\" on the class " . get_class($this));
	}
	
	/**
	 * Returns TRUE or FALSE if the method exists in the extensions.
	 * @return TRUE if the method can be called.
	 * @param object $method Method to look for.
	 * @param object $which[optional] One of 'both', 'local', or 'global'
	 */
	function _extension_method_exists($method, $which = 'both') {
		$found = FALSE;
		if($which != 'global') {
			$found =  ! empty($this->extensions) && array_key_exists($method, $this->extensions['_methods']);
		}
		if( ! $found && $which != 'local' ) {
			$found =  ! empty(DataMapper::$global_extensions) && array_key_exists($method, DataMapper::$global_extensions['_methods']);
		}
		return $found;
	}

	// --------------------------------------------------------------------

	/**
	 * Clone
	 *
	 * Allows for a less shallow clone than the default PHP clone.
	 *
	 * @access	overload
	 * @return	void
	 */
	function __clone()
	{
		foreach ($this as $key => $value)
		{
			if (is_object($value))
			{
				$this->{$key} = clone($value);
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * To String
	 *
	 * Converts the current object into a string.
	 *
	 * @access	overload
	 * @return	void
	 */
	function __toString()
	{
		return ucfirst($this->model);
	}

	// --------------------------------------------------------------------

	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *                                                                   *
	 * Main methods                                                      *
	 *                                                                   *
	 * The following are methods that form the main                      *
	 * functionality of DataMapper.                                      *
	 *                                                                   *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


	// --------------------------------------------------------------------

	/**
	 * Get
	 *
	 * Get objects.
	 *
	 * @access	public
	 * @param	integer
	 * @param	integer
	 * @return	object
	 */
	function get($limit = NULL, $offset = NULL)
	{
		// Check if this is a related object and if so, perform a related get
		if ( ! empty($this->parent))
		{
			// Set limit and offset
			$this->limit($limit, $offset);

			$has_many = array_key_exists($this->parent['model'], $this->has_many);
			$has_one = array_key_exists($this->parent['model'], $this->has_one);

			// If this is a "has many" or "has one" related item
			if ($has_many || $has_one)
			{
				$this->_get_relation($this->parent['model'], $this->parent[$this->pk_key]);
			}

			// For method chaining
			return $this;
		}

		// Check if object has been validated
		if ($this->validated)
		{
			// Reset validated
			$this->validated = FALSE;

			// Use this objects properties
			$data = $this->_to_array(TRUE);

			if ( ! empty($data))
			{
				// Clear this object to make way for new data
				$this->clear();
				
				// Set up default order by (if available)
				$this->_handle_default_order_by();

				// Get by objects properties
				$query = $this->db->get_where($this->table, $data, $limit, $offset);

				if ($query->num_rows() > 0)
				{
					// Populate all with records as objects
					$this->all = $this->_to_object($query->result(), get_class($this));

					// Populate this object with values from first record
					foreach ($query->row() as $key => $value)
					{
						$this->{$key} = $value;
					}
				}
				
				if($query->num_rows() > $this->free_result_threshold)
				{
					$query->free_result();
				}
			}
		}
		else
		{
			// Clear this object to make way for new data
			$this->clear();
				
			// Set up default order by (if available)
			$this->_handle_default_order_by();

			// Get by built up query
			$query = $this->db->get($this->table, $limit, $offset);

			if ($query->num_rows() > 0)
			{
				// Populate all with records as objects
				$this->all = $this->_to_object($query->result(), get_class($this));

				// Populate this object with values from first record
				foreach ($query->row() as $key => $value)
				{
					$this->{$key} = $value;
				}
			}
			
			if($query->num_rows() > $this->free_result_threshold)
			{
				$query->free_result();
			}
		}

		$this->_refresh_stored_values();

		// For method chaining
		return $this;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Forces this object to be INSERTed, even if it has an ID.
	 * 
	 * @param object $object [optional]  See save.
	 * @param object $related_field [optional] See save.
	 * @return Result of the save.
	 */
	function save_as_new($object = '', $related_field = '')
	{
		$this->_force_save_as_new = TRUE;
		return $this->save($object, $related_field);
	}

	// --------------------------------------------------------------------

	/**
	 * Save
	 *
	 * Saves the current record.
	 * If object is supplied, saves relations between this object and the supplied object(s).
	 *
	 * @access	public
	 * @param	mixed
	 * @return	bool
	 */
	function save($object = '', $related_field = '')
	{
		// Temporarily store the success/failure
		$result = array();

		// Validate this objects properties
		$this->validate($object, $related_field);

		// If validation passed
		if ($this->valid)
		{
			// Get current timestamp
			$timestamp = ($this->local_time) ? date('Y-m-d H:i:s O') : gmdate('Y-m-d H:i:s O');

			// Check if unix timestamp
			$timestamp = ($this->unix_timestamp) ? strtotime($timestamp) : $timestamp;

			// Check if object has a 'created' field
			if (in_array($this->created_field, $this->fields))
			{
				// If created datetime is empty, set it
				if (empty($this->{$this->created_field}))
				{
					$this->{$this->created_field} = $timestamp;
				}
			}

			// Check if object has an 'updated' field
			if (in_array($this->updated_field, $this->fields))
			{
				// Update updated datetime
				$this->{$this->updated_field} = $timestamp;
			}
			
			// SmartSave: if there are objects being saved, and they are stored
			// as in-table foreign keys, we can save them at this step.
			if( ! empty($object))
			{
				if(is_array($object))
				{
					$this->_save_itfk($object, $related_field);
				}
				else
				{
					// single objects are saved separately to allow clearing of $object.
					$rf = empty($related_field) ? $object->model : $related_field;
					if(array_key_exists($rf, $this->has_one) && in_array($rf . '_id', $this->fields))
					{
						// ITFK: store on the table
						$this->{$rf . '_id'} = $object->{$object->pk_key};
						$object = '';
					}
				}
			}

			// Convert this object to array
			$data = $this->_to_array();

			if ( ! empty($data))
			{
				if ( ! $this->_force_save_as_new && ! empty($data[$this->pk_key]))
				{
					// Prepare data to send only changed fields
					foreach ($data as $field => $value)
					{
						// Unset field from data if it hasn't been changed
						if ($this->{$field} === $this->stored->{$field})
						{
							unset($data[$field]);
						}
					}

					// Check if only the 'updated' field has changed, and if so, revert it
					if (count($data) == 1 && isset($data[$this->updated_field]))
					{
						// Revert updated
						$this->{$this->updated_field} = $this->stored->{$this->updated_field}; 

						// Unset it
						unset($data[$this->updated_field]);
					}

					// Only go ahead with save if there is still data
					if ( ! empty($data))
					{
						// Begin auto transaction
						$this->_auto_trans_begin();

						// Update existing record
						$this->db->where($this->pk_key, $this->{$this->pk_key});
						$this->db->update($this->table, $data);

						// Complete auto transaction
						$this->_auto_trans_complete('save (update)');
					}

					// Reset validated
					$this->validated = FALSE;

					$result[] = TRUE;
				}
				else
				{
					// Prepare data to send only populated fields
					foreach ($data as $field => $value)
					{
						// Unset field from data
						if ( ! isset($value))
						{
							unset($data[$field]);
						}
					}

					// Begin auto transaction
					$this->_auto_trans_begin();

					// Create new record
					$this->db->insert($this->table, $data);

					if( ! $this->_force_save_as_new)
					{
						// Assign new ID
						$this->{$this->pk_key} = $this->db->insert_id();
					}

					// Complete auto transaction
					$this->_auto_trans_complete('save (insert)');

					// Reset validated
					$this->validated = FALSE;

					$result[] = TRUE;
				}
			}

			$this->_refresh_stored_values();

			// Check if a relationship is being saved
			if ( ! empty($object))
			{
				// Begin auto transaction
				$this->_auto_trans_begin();
				
				// save recursively
				$this->_save_related_recursive($object, $related_field);
				
				// Complete auto transaction
				$this->_auto_trans_complete('save (relationship)');
			}
		}
		
		$this->force_save_as_new = FALSE;

		// If no failure was recorded, return TRUE
		return ( ! empty($result) && ! in_array(FALSE, $result));
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Recursively saves arrays of objects if they are In-Table Foreign Keys. 
	 * @param object $objects Objects to save.  This array may be modified.
	 * @param object $related_field Related Field name (empty is OK)
	 */
	function _save_itfk( &$objects, $related_field)
	{
		foreach($objects as $index => $o)
		{
			if(is_int($index))
			{
				$rf = $related_field;
			}
			else
			{
				$rf = $index;
			}
			if(is_array($o))
			{
				$this->_save_itfk($o, $rf);
			}
			else
			{
				if(empty($rf)) {
					$rf = $o->model;
				}
				if(array_key_exists($rf, $this->has_one) && in_array($rf . '_id', $this->fields))
				{
					// ITFK: store on the table
					$this->{$rf . '_id'} = $o->{$o->pk_key};
					
					// unset, so that it doesn't get re-saved later.
					unset($objects[$index]);
				}
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Recursively saves arrays of objects.
	 * 
	 * @param object $object Array of objects to save, or single object
	 * @param object $related_field Default related field name (empty is OK)
	 * @return TRUE or FALSE if an error occurred.
	 */
	function _save_related_recursive($object, $related_field)
	{
		if(is_array($object))
		{
			$success = TRUE;
			foreach($object as $rk => $o)
			{
				if(is_int($rk))
				{
					$rk = $related_field;
				}
				$rec_success = $this->_save_related_recursive($o, $rk);
				$success = $success && $rec_success;
			}
			return $success;
		}
		else
		{
			return $this->_save_relation($object, $related_field);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * _Save
	 *
	 * Used by __call to process related saves.
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @return	bool
	 */
	function _save($related_field, $arguments)
	{
		return $this->save($arguments[0], $related_field);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Deletes the current record.
	 * If object is supplied, deletes relations between this object and the supplied object(s).
	 *
	 * @access	public
	 * @param	mixed
	 * @return	bool
	 */
	function delete($object = '', $related_field = '')
	{
		if (empty($object) && ! is_array($object))
		{
			if ( ! empty($this->{$this->pk_key}))
			{
				// Begin auto transaction
				$this->_auto_trans_begin();

				// Delete this object
				$this->db->where($this->pk_key, $this->{$this->pk_key});
				$this->db->delete($this->table);

				// Delete all "has many" and "has one" relations for this object
				foreach (array('has_many', 'has_one') as $type) {
					foreach ($this->{$type} as $model => $properties)
					{
						// Prepare model
						$class = $properties['class'];
						$object = new $class();
						
						$this_model = $properties['join_self_as'];
	
						// Determine relationship table name
						$relationship_table = $this->_get_relationship_table($object, $model);
						
						if ($relationship_table == $object->table)
						{
							$data = array($this_model . '_id' => NULL);
							
							// Update table to remove relationships
							$this->db->where($this_model . '_id', $this->{$this->pk_key});
							$this->db->update($object->table, $data);
						}
						else if ($relationship_table != $this->table)
						{
	
							$data = array($this_model . '_id' => $this->{$this->pk_key});
		
							// Delete relation
							$this->db->delete($relationship_table, $data);
						}
						// Else, no reason to delete the relationships on this table
					}
				}

				// Complete auto transaction
				$this->_auto_trans_complete('delete');

				// Clear this object
				$this->clear();

				return TRUE;
			}
		}
		else if (is_array($object))
		{
			// Begin auto transaction
			$this->_auto_trans_begin();

			// Temporarily store the success/failure
			$result = array();

			foreach ($object as $rel_field => $obj)
			{
				if (is_int($rel_field))
				{
					$rel_field = $related_field;
				}
				if (is_array($obj))
				{
					foreach ($obj as $r_f => $o)
					{
						if (is_int($r_f))
						{
							$r_f = $rel_field;
						}
						$result[] = $this->_delete_relation($o, $r_f);
					}
				}
				else
				{
					$result[] = $this->_delete_relation($obj, $rel_field);
				}
			}

			// Complete auto transaction
			$this->_auto_trans_complete('delete (relationship)');

			// If no failure was recorded, return TRUE
			if ( ! in_array(FALSE, $result))
			{
				return TRUE;
			}
		}
		else
		{
			// Begin auto transaction
			$this->_auto_trans_begin();

			// Temporarily store the success/failure
			$result = $this->_delete_relation($object, $related_field);

			// Complete auto transaction
			$this->_auto_trans_complete('delete (relationship)');

			return $result;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * _Delete
	 *
	 * Used by __call to process related saves.
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @return	bool
	 */
	function _delete($related_field, $arguments)
	{
		$this->delete($arguments[0], $related_field);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete All
	 *
	 * Deletes all records in this objects all list.
	 *
	 * @access	public
	 * @return	bool
	 */
	function delete_all()
	{
		if ( ! empty($this->all))
		{
			foreach ($this->all as $item)
			{
				if ( ! empty($item->{$item->pk_key}))
				{
					$item->delete();
				}
			}

			$this->clear();

			return TRUE;
		}

		return FALSE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Refresh All
	 *
	 * Removes any empty objects in this objects all list.
	 * Only needs to be used if you are looping through the all list
	 * a second time and you have deleted a record the first time through.
	 *
	 * @access	public
	 * @return	bool
	 */
	function refresh_all()
	{
		if ( ! empty($this->all))
		{
			$all = array();

			foreach ($this->all as $item)
			{
				if ( ! empty($item->{$item->pk_key}))
				{
					$all[] = $item;
				}
			}

			$this->all = $all;

			return TRUE;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate
	 *
	 * Validates the value of each property against the assigned validation rules.
	 *
	 * @access	public
	 * @param	mixed
	 * @return	object
	 */
	function validate($object = '', $related_field = '')
	{
		// Return if validation has already been run
		if ($this->validated)
		{
			// For method chaining
			return $this;
		}

		// Set validated as having been run
		$this->validated = TRUE;

		// Clear errors
		$this->error = new stdClass();
		$this->error->all = array();
		$this->error->string = '';

		foreach ($this->fields as $field)
		{
			$this->error->{$field} = '';
		}

		// Loop through each property to be validated
		foreach ($this->validation as $validation)
		{
			// Get validation settings
			$field = $validation['field'];
			$label = ( ! empty($validation['label'])) ? $validation['label'] : $field;
			$rules = $validation['rules'];

			// Will validate differently if this is for a related item
			$related = (array_key_exists($field, $this->has_many) OR array_key_exists($field, $this->has_one));

			// Check if property has changed since validate last ran
			if ($related OR ! isset($this->stored->{$field}) OR $this->{$field} !== $this->stored->{$field})
			{
				// Only validate if field is related or required or has a value
				if ( ! $related && ! in_array('required', $rules))
				{
					if ( ! isset($this->{$field}) OR $this->{$field} === '')
					{
						continue;
					}
				}

				// Loop through each rule to validate this property against
				foreach ($rules as $rule => $param)
				{
					// Check for parameter
					if (is_numeric($rule))
					{
						$rule = $param;
						$param = '';
					}

					// Clear result
					$result = '';

					// Check rule exists
					if ($related)
					{
						// Prepare rule to use different language file lines
						$rule = 'related_' . $rule;
						
						$arg = $object;
						if( ! empty($related_field)) {
							$arg = array($related_field => $object);
						}

						if (method_exists($this, '_' . $rule))
						{
							// Run related rule from DataMapper or the class extending DataMapper
							$result = $this->{'_' . $rule}($arg, $field, $param);
						}
						else if($this->_extension_method_exists('rule_' . $rule))
						{
							$result = $this->{'rule_' . $rule}($arg, $field, $param);
						}
					}
					else if (method_exists($this, '_' . $rule))
					{
						// Run rule from DataMapper or the class extending DataMapper
						$result = $this->{'_' . $rule}($field, $param);
					}
					else if($this->_extension_method_exists('rule_' . $rule))
					{
						// Run an extension-based rule.
						$result = $this->{'rule_' . $rule}($field, $param);
					}
					else if (method_exists($this->form_validation, $rule))
					{
						// Run rule from CI Form Validation
						$result = $this->form_validation->{$rule}($this->{$field}, $param);
					}
					else if (function_exists($rule))
					{
						// Run rule from PHP
						$this->{$field} = $rule($this->{$field});
					}

					// Add an error message if the rule returned FALSE
					if ($result === FALSE)
					{
						// Get corresponding error from language file
						if (FALSE === ($line = $this->lang->line($rule)))
						{
							$line = 'Unable to access an error message corresponding to your rule name: '.$rule.'.';
						}

						// Check if param is an array
						if (is_array($param))
						{
							// Convert into a string so it can be used in the error message
							$param = implode(', ', $param);

							// Replace last ", " with " or "
							if (FALSE !== ($pos = strrpos($param, ', ')))
							{
								$param = substr_replace($param, ' or ', $pos, 2);
							}
						}

						// Check if param is a validation field
						if (array_key_exists($param, $this->validation))
						{
							// Change it to the label value
							$param = $this->validation[$param]['label'];
						}

						// Add error message
						$this->error_message($field, sprintf($line, $label, $param));
						
						// Escape to prevent further error checks
						break;
					}
				}
			}
		}

		// Set whether validation passed
		$this->valid = empty($this->error->all);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Skips validation for the next call to save.
	 * Note that this also prevents the validation routine from running until the next get.
	 * 
	 * @return $this
	 * @param object $skip[optional] If FALSE, re-enables validation.
	 */
	function skip_validation($skip = TRUE)
	{
		$this->validated = $skip;
		$this->valid = $skip;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Clear
	 *
	 * Clears the current object.
	 *
	 * @access	public
	 * @return	void
	 */
	function clear()
	{
		// Clear the all list
		$this->all = array();

		// Clear errors
		$this->error = new stdClass();
		$this->error->all = array();
		$this->error->string = '';

		// Clear this objects properties and set blank error messages in case they are accessed
		foreach ($this->fields as $field)
		{
			$this->{$field} = NULL;
			$this->error->{$field} = '';
		}
		
		// Clear the auto transaction error
		if($this->auto_transaction) {
			$this->error->transaction = '';
		}

		// Clear this objects "has many" related objects
		foreach ($this->has_many as $related => $properties)
		{
			unset($this->{$related});
		}

		// Clear this objects "has one" related objects
		foreach ($this->has_one as $related => $properties)
		{
			unset($this->{$related});
		}

		// Clear the query related list
		$this->query_related = array();

		// Clear and refresh stored values
		$this->stored = new stdClass();

		$this->_refresh_stored_values();
	}

	// --------------------------------------------------------------------

	/**
	 * Count
	 *
	 * Returns the total count of the objects records.
	 * If on a related object, returns the total count of related objects records.
	 *
	 * @access	public
	 * @return	integer
	 */
	function count()
	{
		// Check if related object
		if ( ! empty($this->parent))
		{
			// Prepare model
			$related_field = $this->parent['model'];
			$related_properties = $this->_get_related_properties($related_field);
			$class = $related_properties['class'];
			$other_model = $related_properties['join_other_as'];
			$this_model = $related_properties['join_self_as'];
			$object = new $class();

			// Determine relationship table name
			$relationship_table = $this->_get_relationship_table($object, $related_field);
			if($relationship_table == $object->table) {
				// has_one join on the other object's table
				$this->db->where($this->pk_key, $this->parent[$this->pk_key])->where($this_model . '_id IS NOT NULL');
			} else {
				$this->db->where($other_model . '_id', $this->parent[$this->pk_key]);
			}
			$this->db->from($relationship_table);

			// Return count
			return $this->db->count_all_results();
		}
		else
		{
			$this->db->from($this->table);

			// Return count
			return $this->db->count_all_results();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Exists
	 *
	 * Returns TRUE if the current object has a database record.
	 *
	 * @access	public
	 * @return	bool
	 */
	function exists()
	{
		return ( ! empty($this->{$this->pk_key}));
	}

	// --------------------------------------------------------------------

	/**
	 * Query
	 *
	 * Runs the specified query and populates the current object with the results.
	 *
	 * Warning: Use at your own risk.  This will only be as reliable as your query.
	 *
	 * @access	public
	 * @access	string
	 * @access	array
	 * @return	void
	 */
	function query($sql, $binds = FALSE)
	{
		// Get by objects properties
		$query = $this->db->query($sql, $binds);

		if ($query->num_rows() > 0)
		{
			// Populate all with records as objects
			$this->all = $this->_to_object($query->result(), get_class($this));

			// Populate this object with values from first record
			foreach ($query->row() as $key => $value)
			{
				$this->{$key} = $value;
			}
		}
		
		if($query->num_rows() > $this->free_result_threshold)
		{
			$query->free_result();
		}

		$this->_refresh_stored_values();

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Check Last Query
	 * Renders the last DB query performed.
	 * 
	 * @return formatted last db query as a string.
	 */
	function check_last_query($delims = array('<pre>', '</pre>'), $return_as_string = FALSE) {
		$q = wordwrap($this->db->last_query(), 100, "\n\t");
		if(!empty($delims)) {
			$q = implode($q, $delims);
		}
		if($return_as_string === FALSE) {
			echo $q;
		}
		return $q;
	}

	// --------------------------------------------------------------------

	/**
	 * Error Message
	 *
	 * Adds an error message to this objects error object.
	 *
	 * @access	public
	 * @access	string
	 * @access	string
	 * @return	void
	 */
	function error_message($field, $error)
	{
		if ( ! empty($field) && ! empty($error))
		{
			// Set field specific error
			$this->error->{$field} = $this->error_prefix . $error . $this->error_suffix;

			// Add field error to errors all list
			$this->error->all[] = $this->error->{$field};

			// Append field error to error message string
			$this->error->string .= $this->error->{$field};
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get Clone
	 *
	 * Returns a clone of the current object.
	 *
	 * @access	public
	 * @return	object
	 */
	function get_clone()
	{
		return clone($this);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Copy
	 *
	 * Returns an unsaved copy of the current object.
	 *
	 * @access	public
	 * @return	object
	 */
	function get_copy()
	{
		$copy = clone($this);

		$copy->{$copy->pk_key} = NULL;

		return $copy;
	}

	// --------------------------------------------------------------------

	/**
	 * Get By
	 *
	 * Gets objects by specified field name and value.
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	function _get_by($field, $value = array())
	{
		if (isset($value[0]))
		{
			$this->where($field, $value[0]);
		}

		return $this->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Get By Related
	 *
	 * Gets objects by specified related object and optionally by field name and value.
	 *
	 * @access	private
	 * @param	string
	 * @param	mixed
	 * @return	object
	 */
	function _get_by_related($model, $arguments = array())
	{
		if ( ! empty($model))
		{
			// Add model to start of arguments
			$arguments = array_merge(array($model), $arguments);
		}

		$this->_related('where', $arguments);

		return $this->get();
	}

	// --------------------------------------------------------------------


	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *                                                                   *
	 * Active Record methods                                             *
	 *                                                                   *
	 * The following are methods used to provide Active Record           *
	 * functionality for data retrieval.                                 *
	 *                                                                   *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


	// --------------------------------------------------------------------

	/**
	 * Add Table Name
	 *
	 * Adds the table name to a field if necessary
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	object
	 */
	function add_table_name($field, $only_if_no_parent = FALSE)
	{
		$empty = empty($this->parent) && empty($this->query_related);
		if( ! ($only_if_no_parent && empty($this->parent) && empty($this->query_related)) )
		{
			// only add table if the field doesn't contain a dot (.), open parentheses, or comma
			if (preg_match('/[\.\(]/', $field) == 0)
			{
				// split string into parts, add field
				$field_parts = explode(',', $field);
				$field = '';
				foreach ($field_parts as $part)
				{
					if ( ! empty($field))
					{
						$field .= ', ';
					}
					$part = trim($part);
					// handle comparison operators on where
					$subparts = explode(' ', $part, 2);
					if (in_array($subparts[0], $this->fields))
					{
						$field .= $this->table  . '.' . $part;
					}
					else
					{
						$field .= $part;
					}
				}
			}
		}
		return $field;
	}

	// --------------------------------------------------------------------

	/**
	 * Select
	 *
	 * Sets the SELECT portion of the query.
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	object
	 */
	function select($select = '*', $escape = NULL)
	{
		if ($escape !== FALSE)
		{
			$select = $this->add_table_name($select, TRUE);
		}
		$this->db->select($select, $escape);
		
		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Select Max
	 *
	 * Sets the SELECT MAX(field) portion of a query.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	function select_max($select = '', $alias = '')
	{
		// Check if this is a related object
		if ( ! empty($this->parent))
		{
			$alias = ($alias != '') ? $alias : $select;
		}
		$this->db->select_max($this->add_table_name($select, TRUE), $alias);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Select Min
	 *
	 * Sets the SELECT MIN(field) portion of a query.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	function select_min($select = '', $alias = '')
	{
		// Check if this is a related object
		if ( ! empty($this->parent))
		{
			$alias = ($alias != '') ? $alias : $select;
		}
		$this->db->select_min($this->add_table_name($select, TRUE), $alias);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Select Avg
	 *
	 * Sets the SELECT AVG(field) portion of a query.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	function select_avg($select = '', $alias = '')
	{
		// Check if this is a related object
		if ( ! empty($this->parent))
		{
			$alias = ($alias != '') ? $alias : $select;
		}
		$this->db->select_avg($this->add_table_name($select, TRUE), $alias);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Select Sum
	 *
	 * Sets the SELECT SUM(field) portion of a query.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	function select_sum($select = '', $alias = '')
	{
		// Check if this is a related object
		if ( ! empty($this->parent))
		{
			$alias = ($alias != '') ? $alias : $select;
		}
		$this->db->select_sum($this->add_table_name($select, TRUE), $alias);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Distinct
	 *
	 * Sets the flag to add DISTINCT to the query.
	 *
	 * @access	public
	 * @param	bool
	 * @return	object
	 */
	function distinct($value = TRUE)
	{
		$this->db->distinct($value);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Where
	 *
	 * Get items matching the where clause.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function get_where($where = array(), $limit = NULL, $offset = NULL)
	{
		$this->where($where);

		return $this->get($limit, $offset);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Starts a query group.
	 * @param object $prefix [optional]  (Internal use only)
	 * @return $this for chaining.
	 */
	function group_start($not = '', $type = 'AND ')
	{
		// in case groups are being nested
		$type = $this->_get_prepend_type($type);
		
		$prefix = (count($this->db->ar_where) == 0 AND count($this->db->ar_cache_where) == 0) ? '' : $type;
		$this->db->ar_where[] = $prefix . $not .  ' (';
		$this->_where_group_started = TRUE;
		return $this;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Starts a query group, but ORs the group
	 * @return $this for chaining.
	 */
	function or_group_start()
	{
		return $this->group_start('', 'OR ');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Starts a query group, but NOTs the group
	 * @return $this for chaining.
	 */
	function not_group_start()
	{
		return $this->group_start('NOT ', 'OR ');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Starts a query group, but OR NOTs the group
	 * @return $this for chaining.
	 */
	function or_not_group_start()
	{
		return $this->group_start('NOT ', 'OR ');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Ends a query group.
	 * @return $this for chaining. 
	 */
	function group_end()
	{
		$this->db->ar_where[] = ')';
		$this->_where_group_started = FALSE;
		return $this;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Private function to convert the AND or OR prefix to '' when starting
	 * a group.
	 * @param object $type Current type value
	 * @return New type value
	 */
	function _get_prepend_type($type)
	{
		if($this->_where_group_started)
		{
			$type = '';
			$this->_where_group_started = FALSE;
		}
		return $type;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Where
	 *
	 * Sets the WHERE portion of the query.
	 * Separates multiple calls with AND.
	 *
	 * Called by get_where()
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function where($key, $value = NULL, $escape = TRUE)
	{
		return $this->_where($key, $value, 'AND ', $escape);
	}

	// --------------------------------------------------------------------

	/**
	 * Or Where
	 *
	 * Sets the WHERE portion of the query.
	 * Separates multiple calls with OR.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function or_where($key, $value = NULL, $escape = TRUE)
	{
		return $this->_where($key, $value, 'OR ', $escape);
	}

	// --------------------------------------------------------------------

	/**
	 * Where
	 *
	 * Called by where() or or_where().
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @param	bool
	 * @return	object
	 */
	function _where($key, $value = NULL, $type = 'AND ', $escape = NULL)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
		foreach ($key as $k => $v)
		{
			$new_k = $this->add_table_name($k, TRUE);
			if ($new_k != $k)
			{
				$key[$new_k] = $v;
				unset($key[$k]);
			}
		}
		
		$type = $this->_get_prepend_type($type);
		
		$this->db->_where($key, $value, $type, $escape);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Where In
	 *
	 * Sets the WHERE field IN ('item', 'item') SQL query joined with
	 * AND if appropriate.
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	object
	 */
	function where_in($key = NULL, $values = NULL)
	{
	 	return $this->_where_in($key, $values);
	}

	// --------------------------------------------------------------------

	/**
	 * Or Where In
	 *
	 * Sets the WHERE field IN ('item', 'item') SQL query joined with
	 * OR if appropriate.
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	object
	 */
	function or_where_in($key = NULL, $values = NULL)
	{
	 	return $this->_where_in($key, $values, FALSE, 'OR ');
	}

	// --------------------------------------------------------------------

	/**
	 * Where Not In
	 *
	 * Sets the WHERE field NOT IN ('item', 'item') SQL query joined with
	 * AND if appropriate.
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	object
	 */
	function where_not_in($key = NULL, $values = NULL)
	{
		return $this->_where_in($key, $values, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Or Where Not In
	 *
	 * Sets the WHERE field NOT IN ('item', 'item') SQL query joined wuth
	 * OR if appropriate.
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	object
	 */
	function or_where_not_in($key = NULL, $values = NULL)
	{
		return $this->_where_in($key, $values, TRUE, 'OR ');
	}

	// --------------------------------------------------------------------

	/**
	 * Where In
	 *
	 * Called by where_in(), or_where_in(), where_not_in(), or or_where_not_in().
	 *
	 * @access	private
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @param	string
	 * @return	object
	 */
	function _where_in($key = NULL, $values = NULL, $not = FALSE, $type = 'AND ')
	{	
		$type = $this->_get_prepend_type($type);
		
	 	$this->db->_where_in($this->add_table_name($key, TRUE), $values, $not, $type);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Like
	 *
	 * Sets the %LIKE% portion of the query.
	 * Separates multiple calls with AND.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	function like($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'AND ', $side);
	}

	// --------------------------------------------------------------------

	/**
	 * Not Like
	 *
	 * Sets the NOT LIKE portion of the query.
	 * Separates multiple calls with AND.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	function not_like($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'AND ', $side, 'NOT');
	}

	// --------------------------------------------------------------------

	/**
	 * Or Like
	 *
	 * Sets the %LIKE% portion of the query.
	 * Separates multiple calls with OR.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	function or_like($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'OR ', $side);
	}

	// --------------------------------------------------------------------

	/**
	 * Or Not Like
	 *
	 * Sets the NOT LIKE portion of the query.
	 * Separates multiple calls with OR.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	function or_not_like($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'OR ', $side, 'NOT');
	}

	// --------------------------------------------------------------------

	/**
	 * ILike
	 *
	 * Sets the case-insensitive %LIKE% portion of the query.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	function ilike($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'AND ', $side, '', TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Not ILike
	 *
	 * Sets the case-insensitive NOT LIKE portion of the query.
	 * Separates multiple calls with AND.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	function not_ilike($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'AND ', $side, 'NOT', TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Or Like
	 *
	 * Sets the case-insensitive %LIKE% portion of the query.
	 * Separates multiple calls with OR.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	function or_ilike($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'OR ', $side, '', TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Or Not Like
	 *
	 * Sets the case-insensitive NOT LIKE portion of the query.
	 * Separates multiple calls with OR.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	function or_not_ilike($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'OR ', $side, 'NOT', TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * _Like
	 *
	 * Private function to do actual work.
	 * NOTE: this does NOT use the built-in ActiveRecord LIKE function.
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '', $no_case = FALSE)
	{
		if ( ! is_array($field))
		{
			$field = array($field => $match);
		}

		foreach ($field as $k => $v)
		{
			$new_k = $this->add_table_name($k, TRUE);
			if ($new_k != $k)
			{
				$field[$new_k] = $v;
				unset($field[$k]);
			}
		}
		
		// Taken from CodeIgniter's Active Record because (for some reason)
		// it is stored separately that normal where statements.
 	
		foreach ($field as $k => $v)
		{
			if($no_case)
			{
				$k = 'UPPER(' . $this->db->_protect_identifiers($k) .')';
				$v = strtoupper($v);
			}
			$f = "$k $not LIKE";

			if ($side == 'before')
			{
				$m = "%{$v}";
			}
			elseif ($side == 'after')
			{
				$m = "{$v}%";
			}
			else
			{
				$m = "%{$v}%";
			}
			
			$this->_where($f, $m, $type);
		}

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Group By
	 *
	 * Sets the GROUP BY portion of the query.
	 *
	 * @access	public
	 * @param	string
	 * @return	object
	 */
	function group_by($by)
	{
		$this->db->group_by($this->add_table_name($by, TRUE));

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Having
	 *
	 * Sets the HAVING portion of the query.
	 * Separates multiple calls with AND.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	object
	 */
	function having($key, $value = '', $escape = TRUE)
	{
		return $this->_having($key, $value, 'AND ', $escape);
	}

	// --------------------------------------------------------------------

	/**
	 * Or Having
	 *
	 * Sets the OR HAVING portion of the query.
	 * Separates multiple calls with OR.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	object
	 */
	function or_having($key, $value = '', $escape = TRUE)
	{
		return $this->_having($key, $value, 'OR ', $escape);
	}

	// --------------------------------------------------------------------

	/**
	 * Having
	 *
	 * Sets the HAVING portion of the query.
	 * Separates multiple calls with AND.
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	object
	 */
	function _having($key, $value = '', $type = 'AND ', $escape = TRUE)
	{	
		$this->db->_having($this->add_table_name($key, TRUE), $value, $type, $escape);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Order By
	 *
	 * Sets the ORDER BY portion of the query.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	function order_by($orderby, $direction = '')
	{
		$this->db->order_by($this->add_table_name($orderby, TRUE), $direction);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Adds in the defaut order_by items, if there are any, and
	 * order_by hasn't been overridden. 
	 */
	function _handle_default_order_by()
	{
		if(empty($this->default_order_by))
		{
			return;
		}
		$sel = $this->table . '.' . '*';
		$sel_protect = $this->db->_protect_identifiers($sel);
		// only add the items if there isn't an existing order_by,
		// AND the select statement is empty or includes * or table.* or `table`.*
		if(empty($this->db->ar_orderby) &&
			(
				empty($this->db->ar_select) ||
				in_array('*', $this->db->ar_select) ||
				in_array($sel_protect, $this->db->ar_select) ||
			 	in_array($sel, $this->db->ar_select)
			 	
			))
		{
			foreach($this->default_order_by as $k => $v) {
				if(is_int($k)) {
					$k = $v;
					$v = '';
				}
				$k = $this->add_table_name($k, TRUE);
				$this->order_by($k, $v);
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Limit
	 *
	 * Sets the LIMIT portion of the query.
	 *
	 * @access	public
	 * @param	integer
	 * @param	integer
	 * @return	object
	 */
	function limit($value, $offset = '')
	{
		$this->db->limit($value, $offset);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Offset
	 *
	 * Sets the OFFSET portion of the query.
	 *
	 * @access	public
	 * @param	integer
	 * @return	object
	 */
	function offset($offset)
	{
		$this->db->offset($offset);

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Start Cache
	 *
	 * Starts AR caching.
	 *
	 * @access	public
	 * @return	void
	 */		
	function start_cache()
	{
		$this->db->start_cache();
	}

	// --------------------------------------------------------------------

	/**
	 * Stop Cache
	 *
	 * Stops AR caching.
	 *
	 * @access	public
	 * @return	void
	 */		
	function stop_cache()
	{
		$this->db->stop_cache();
	}

	// --------------------------------------------------------------------

	/**
	 * Flush Cache
	 *
	 * Empties the AR cache.
	 *
	 * @access	public
	 * @return	void
	 */	
	function flush_cache()
	{	
		$this->db->flush_cache();
	}

	// --------------------------------------------------------------------

	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *                                                                   *
	 * Transaction methods                                               *
	 *                                                                   *
	 * The following are methods used for transaction handling.          *
	 *                                                                   *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


	// --------------------------------------------------------------------

	/**
	 * Trans Off
	 *
	 * This permits transactions to be disabled at run-time.
	 *
	 * @access	public
	 * @return	void		
	 */	
	function trans_off()
	{
		$this->db->trans_enabled = FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Trans Strict
	 *
	 * When strict mode is enabled, if you are running multiple groups of
	 * transactions, if one group fails all groups will be rolled back.
	 * If strict mode is disabled, each group is treated autonomously, meaning
	 * a failure of one group will not affect any others.
	 *
	 * @access	public
	 * @param	bool
	 * @return	void		
	 */	
	function trans_strict($mode = TRUE)
	{
		$this->db->trans_strict($mode);
	}

	// --------------------------------------------------------------------

	/**
	 * Trans Start
	 *
	 * Start a transaction.
	 *
	 * @access	public
	 * @param	bool
	 * @return	void
	 */	
	function trans_start($test_mode = FALSE)
	{	
		$this->db->trans_start($test_mode);
	}

	// --------------------------------------------------------------------

	/**
	 * Trans Complete
	 *
	 * Complete a transaction.
	 *
	 * @access	public
	 * @return	bool		
	 */	
	function trans_complete()
	{
		return $this->db->trans_complete();
	}

	// --------------------------------------------------------------------

	/**
	 * Trans Begin
	 *
	 * Begin a transaction.
	 *
	 * @access	public
	 * @param	bool
	 * @return	bool
	 */	
	function trans_begin($test_mode = FALSE)
	{	
		return $this->db->trans_begin($test_mode);
	}

	// --------------------------------------------------------------------

	/**
	 * Trans Status
	 *
	 * Lets you retrieve the transaction flag to determine if it has failed.
	 *
	 * @access	public
	 * @return	bool		
	 */	
	function trans_status()
	{
		return $this->_trans_status;
	}

	// --------------------------------------------------------------------

	/**
	 * Trans Commit
	 *
	 * Commit a transaction.
	 *
	 * @access	public
	 * @return	bool
	 */	
	function trans_commit()
	{
		return $this->db->trans_commit();
	}

	// --------------------------------------------------------------------

	/**
	 * Trans Rollback
	 *
	 * Rollback a transaction.
	 *
	 * @access	public
	 * @return	bool
	 */	
	function trans_rollback()
	{
		return $this->db->trans_rollback();
	}

	// --------------------------------------------------------------------

	/**
	 * Auto Trans Begin
	 *
	 * Begin an auto transaction if enabled.
	 *
	 * @access	public
	 * @param	bool
	 * @return	bool
	 */	
	function _auto_trans_begin()
	{
		// Begin auto transaction
		if ($this->auto_transaction)
		{
			$this->trans_begin();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Auto Trans Complete
	 *
	 * Complete an auto transaction if enabled.
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function _auto_trans_complete($label = 'complete')
	{
		// Complete auto transaction
		if ($this->auto_transaction)
		{
			// Check if successful
			if (!$this->trans_complete())
			{
				$rule = 'transaction';

				// Get corresponding error from language file
				if (FALSE === ($line = $this->lang->line($rule)))
				{
					$line = 'Unable to access the ' . $rule .' error message.';
				}

				// Add transaction error message
				$this->error_message($rule, sprintf($line, $label));

				// Set validation as failed
				$this->valid = FALSE;
			}
		}
	}

	// --------------------------------------------------------------------

	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *                                                                   *
	 * Related methods                                                   *
	 *                                                                   *
	 * The following are methods used for managing related records.      *
	 *                                                                   *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	// --------------------------------------------------------------------

	/**
	 * get_related_properties
	 *
	 * Located the relationship properties for a given field or model
	 * Can also optionally attempt to convert the $related_field to
	 * singular, and look up on that.  It will modify the $related_field if
	 * the conversion to singular returns a result.
	 * 
	 * $related_field can also be a deep relationship, such as
	 * 'post/editor/group', in which case the $related_field will be processed
	 * recursively, and the return value will be $user->has_NN['group']; 
	 *
	 * @access	private
	 * @param	string
	 * @return	object
	 */
	function _get_related_properties(&$related_field, $try_singular = FALSE)
	{
		// Handle deep relationships
		if(strpos($related_field, '/') !== FALSE)
		{
			$rfs = explode('/', $related_field);
			$last = $this;
			$prop = NULL;
			foreach($rfs as &$rf)
			{
				$prop = $last->_get_related_properties($rf, $try_singular);
				if(is_null($prop))
				{
					break;
				}
				$last = $last->{$rf};
			}
			if( ! is_null($prop))
			{
				// update in case any items were converted to singular.
				$related_field = implode('/', $rfs);
			}
			return $prop;
		}
		else
		{
			if (isset($this->has_many[$related_field]))
			{
				return $this->has_many[$related_field];
			}
			else if (isset($this->has_one[$related_field]))
			{
				return $this->has_one[$related_field];
			}
			else
			{
				if($try_singular)
				{
					$rf = singular($related_field);
					$ret = $this->_get_related_properties($rf);
					if( is_null($ret))
					{
						show_error("Unable to relate {$this->model} with $related_field.");
					}
					else
					{
						$related_field = $rf;
						return $ret;
					}
				}
				else
				{
					// not related
					return NULL;
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Add Related Table
	 *
	 * Adds the table of a related item, and joins it to this class.
	 * Returns the name of that table for further queries.
	 * 
	 * If $related_field is deep, then this adds all necessary relationships
	 * to the query.
	 *
	 * @access	private
	 * @param mixed $object The object (or related field) to look up.
	 * @param string $related_field [optional] Related field anem for object
	 * @param array $query_related [optional] Private, do not use.
	 * @param string $name_prepend [optional] Private, do not use.
	 * @param string $this_table [optional] Private, do not use.
	 * @return String Name of the related table
	 */
	function _add_related_table($object, $related_field = '', &$query_related = NULL, $name_prepend = '', $this_table = NULL)
	{
		if ( is_string($object))
		{
			// only a model was passed in, not an object
			$related_field = $object;
			$object = NULL;
		}
		else if (empty($related_field))
		{
			// model was not passed, so get the Object's native model
			$related_field = $object->model;
		}
		
		$related_field = strtolower($related_field);
		
		// Handle deep relationships
		if(strpos($related_field, '/') !== FALSE)
		{
			$rfs = explode('/', $related_field);
			$last = $this;
			$prepend = '';
			$object_as = NULL;
			foreach($rfs as $rf)
			{
				$object_as = $last->_add_related_table($rf, '', $this->query_related, $prepend, $object_as);
				$prepend .= $rf . '_'; 
				$last = $last->{$rf};
			}
			return $object_as;
		}
		
		$related_properties = $this->_get_related_properties($related_field);
		$class = $related_properties['class'];
		$this_model = $related_properties['join_self_as'];
		$other_model = $related_properties['join_other_as'];
		
		if (empty($object))
		{
			// no object was passed in, so create one
			$object = new $class();
		}
		
		if(is_null($query_related))
		{
			$query_related =& $this->query_related;
		}
		
		if(is_null($this_table))
		{
			$this_table = $this->table;
		}
		
		// Determine relationship table name
		$relationship_table = $this->_get_relationship_table($object, $related_field);
		
		// only add $related_field to the table name if the 'class' and 'related_field' aren't equal
		// and the related object is in a different table
		if ( ($class == $related_field) && ($this->table != $object->table) )
		{
			$object_as = $name_prepend . $object->table;
			$relationship_as = $name_prepend . $relationship_table;
		}
		else
		{
			$object_as = $name_prepend . $related_field . '_' . $object->table;
			$relationship_as = $name_prepend . $related_field . '_' . $relationship_table;
		}
		
		$other_column = $other_model . '_id';
		$this_column = $this_model . '_id' ;
		

		// Force the selection of the current object's columns
		if (empty($this->db->ar_select))
		{
			$this->db->select($this->table . '.*');
		}
		
		// the extra in_array column check is for has_one self references
		if ($relationship_table == $this->table && in_array($other_column, $this->fields))
		{
			// has_one relationship without a join table
			if ( ! in_array($object_as, $query_related))
			{
				$this->db->join($object->table . ' as ' .$object_as, $object_as . '.id = ' . $this_table . '.' . $other_column, 'LEFT OUTER');
				$query_related[] = $object_as;
			}
			$this_column = NULL;
		}
		// the extra in_array column check is for has_one self references
		else if ($relationship_table == $object->table && in_array($this_column, $object->fields))
		{
			// has_one relationship without a join table
			if ( ! in_array($object_as, $query_related))
			{
				$this->db->join($object->table . ' as ' .$object_as, $this_table . '.id = ' . $object_as . '.' . $this_column, 'LEFT OUTER');
				$query_related[] = $object_as;
			}
			$other_column = NULL;
		}
		else
		{
			// has_one or has_many with a normal join table
			
			// Add join if not already included
			if ( ! in_array($relationship_as, $query_related))
			{
				$this->db->join($relationship_table . ' as ' . $relationship_as, $this_table . '.id = ' . $relationship_as . '.' . $this_column, 'LEFT OUTER');
				
				if($this->_include_join_fields) {
					$fields = $this->db->field_data($relationship_table);
					foreach($fields as $key => $f) {
						if($f->name == $this->pk_key || $f->name == $this_column || $f->name == $other_column)
						{
							unset($fields[$key]);
						}
					}
					// add all other fields
					$selection = '';
					foreach ($fields as $field)
					{
						$new_field = 'join_'.$field->name;
						if (!empty($selection))
						{
							$selection .= ', ';
						}
						$selection .= $relationship_as.'.'.$field->name.' AS '.$new_field;
					}
					$this->db->select($selection);
					
					// now reset the flag
					$this->_include_join_fields = FALSE;
				}
	
				$query_related[] = $relationship_as;
			}
	
			// Add join if not already included
			if ( ! in_array($object_as, $query_related))
			{
				$this->db->join($object->table . ' as ' . $object_as, $object_as . '.id = ' . $relationship_as . '.' . $other_column, 'LEFT OUTER');

				$query_related[] = $object_as;
			}
		}
		
		return $object_as;
	}

	// --------------------------------------------------------------------

	/**
	 * Related
	 *
	 * Sets the specified related query.
	 *
	 * @access	private
	 * @param	string
	 * @param	mixed
	 * @return	object
	 */
	function _related($query, $arguments = array())
	{
		if ( ! empty($query) && ! empty($arguments))
		{
			$object = $field = $value = $option = NULL;

			// Prepare model
			if (is_object($arguments[0]))
			{
				$object = $arguments[0];
				$related_field = $object->model; 

				// Prepare field and value
				$field = (isset($arguments[1])) ? $arguments[1] : $object->pk_key;
				$value = (isset($arguments[2])) ? $arguments[2] : $object->{$object->pk_key};
			}
			else
			{
				$related_field = $arguments[0];
				// the TRUE allows conversion to singular
				$related_properties = $this->_get_related_properties($related_field, TRUE);
				$class = $related_properties['class'];
				// enables where_related_{model}($object)
				if(isset($arguments[1]) && is_object($arguments[1]))
				{
					$object = $arguments[1];
					// Prepare field and value
					$field = (isset($arguments[2])) ? $arguments[2] : $object->pk_key;
					$value = (isset($arguments[3])) ? $arguments[3] : $object->{$object->pk_key};
				}
				else
				{
					$object = new $class();
					// Prepare field and value
					$field = (isset($arguments[1])) ? $arguments[1] : $object->pk_key;
					$value = (isset($arguments[2])) ? $arguments[2] : NULL;
				}
			}

			// Determine relationship table name, and join the tables
			$object_table = $this->_add_related_table($object, $related_field);

			// Add query clause
			$this->{$query}($object_table . '.' . $field, $value);
		}

		// For method chaining
		return $this;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Include Related
	 *
	 * Joins specified values of a has_one object into the current query
	 * If $fields is NULL or '*', then all columns are joined (may require instantiation of the other object)
	 * If $fields is a single string, then just that column is joined.
	 * Otherwise, $fields should be an array of column names.
	 * 
	 * $append_name can be used to override the default name to append, or set it to FALSE to prevent appending.
	 *
	 * @return $this
	 * @param object $related_field The related object or field name
	 * @param object $fields[optional] The fields to join (NULL or '*' means all fields, or use a single field or array of fields)
	 * @param object $append_name[optional] The name to use for joining (with '_'), or FALSE to disable.
	 */
	function include_related($related_field, $fields = NULL, $append_name = TRUE) {
		if(is_null($fields) || $fields == '*') {
			if(is_object($related_field)) {
				$fields = $related_field->fields;
			} else {
				$fields = $this->{$related_field}->fields;
			}
		}
		if ( ! is_array($fields))
		{
			$fields = array($fields);
		}
		
		if (is_object($related_field))
		{
			$object = $related_field;
			$related_field = $object->model;
			$related_properties = $this->_get_related_properties($related_field);
		}
		else
		{
			// the TRUE allows conversion to singular
			$related_properties = $this->_get_related_properties($related_field, TRUE);
			$class = $related_properties['class'];
			$object = new $class();
		}
		
		$rfs = explode('/', $related_field);
		$last = $this;
		foreach($rfs as $rf)
		{
			if ( ! array_key_exists($rf, $last->has_one) )
			{
				show_error("Invalid request to include_related: $rf is not a has_one relationship to {$last->model}.");
			}
			$last = $last->{$rf};
		}
		
		$table = $this->_add_related_table($object, $related_field);
		
		$append = '';
		if($append_name !== FALSE) {
			if($append_name === TRUE) {
				$append = str_replace('/', '_', $related_field);
			} else {
				$append = $append_name;
			}
			$append .= '_';
		}
		
		// now add fields
		$selection = '';
		foreach ($fields as $field)
		{
			$new_field = $append . $field;
			// prevent collisions
			if(in_array($new_field, $this->fields)) {
				continue;
			}
			if (!empty($selection))
			{
				$selection .= ', ';
			}
			$selection .= $table.'.'.$field.' AS '.$new_field;
		}
		$this->db->select($selection);
		
		// For method chaining
		return $this;
	}
	
	/**
	 * Legacy version of include_related
	 * DEPRECATED: Will be removed by 2.0
	 * @deprecated Please use include_related
	 */
	function join_related($related_field, $fields = NULL, $append_name = TRUE) {
		return $this->include_related($related_field, $fields, $append_name);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Relation
	 *
	 * Finds all related records of this objects current record.
	 *
	 * @access	private
	 * @param	string
	 * @param	integer
	 * @return	void
	 */
	function _get_relation($related_field, $id)
	{
		// No related items
		if (empty($related_field) OR empty($id))
		{
			// Reset query
			$this->db->_reset_select();

			return;
		}
		
		// To ensure result integrity, group all previous queries
		if( ! empty($this->db->ar_where))
		{
			array_unshift($this->db->ar_where, '( ');
			$this->db->ar_where[] = ' )';
		}
		
		// query all items related to the given model
		$this->where_related($related_field, $this->pk_key, $id);
				
		// Set up default order by (if available)
		$this->_handle_default_order_by();

		$query = $this->db->get($this->table);

		// Clear this object to make way for new data
		$this->clear();

		if ($query->num_rows() > 0)
		{
			// Populate all with records as objects
			$this->all = $this->_to_object($query->result(), get_class($this));

			// Populate this object with values from first record
			foreach ($query->row() as $key => $value)
			{
				$this->{$key} = $value;
			}
		}
		
		if($query->num_rows() > $this->free_result_threshold)
		{
			$query->free_result();
		}

		$this->_refresh_stored_values();
	}

	// --------------------------------------------------------------------

	/**
	 * Save Relation
	 *
	 * Saves the relation between this and the other object.
	 *
	 * @access	private
	 * @param	object
	 * @return	bool
	 */
	function _save_relation($object, $related_field)
	{
		if (empty($related_field))
		{
			$related_field = $object->model;
		}
		
		// the TRUE allows conversion to singular
		$related_properties = $this->_get_related_properties($related_field, TRUE);
		
		if ( ! empty($related_properties) && $this->exists() && $object->exists())
		{
			$this_model = $related_properties['join_self_as'];
			$other_model = $related_properties['join_other_as'];
			$other_field = $related_properties['other_field'];
			
			// Determine relationship table name
			$relationship_table = $this->_get_relationship_table($object, $related_field);

			if($relationship_table == $this->table)
			{
				// FIXME: should this re-query the existing object?
				$this->{$other_model . '_id'} = $object->{$object->pk_key};
				return $this->save();
			}
			else if($relationship_table == $object->table)
			{
				// FIXME: should this re-query the existing object?
				$object->{$this_model . '_id'} = $this->{$object->pk_key};
				return $object->save();
			}
			else
			{
				$data = array($this_model . '_id' => $this->{$this->pk_key}, $other_model . '_id' => $object->id);
	
				// Check if relation already exists
				$query = $this->db->get_where($relationship_table, $data, NULL, NULL);
	
				if ($query->num_rows() == 0)
				{
					// If this object has a "has many" relationship with the other object
					if (array_key_exists($related_field, $this->has_many))
					{
						// If the other object has a "has one" relationship with this object
						if (array_key_exists($other_field, $object->has_one))
						{
							// And it has an existing relation
							$query = $this->db->get_where($relationship_table, array($other_model . '_id' => $object->id), 1, 0);
	
							if ($query->num_rows() > 0)
							{
								// Find and update the other objects existing relation to relate with this object
								$this->db->where($other_model . '_id', $object->id);
								$this->db->update($relationship_table, $data);
							}
							else
							{
								// Add the relation since one doesn't exist
								$this->db->insert($relationship_table, $data);
							}
	
							return TRUE;
						}
						else if (array_key_exists($other_field, $object->has_many))
						{
							// We can add the relation since this specific relation doesn't exist, and a "has many" to "has many" relationship exists between the objects
							$this->db->insert($relationship_table, $data);
	
							return TRUE;
						}
					}
					// If this object has a "has one" relationship with the other object
					else if (array_key_exists($related_field, $this->has_one))
					{
						// And it has an existing relation
						$query = $this->db->get_where($relationship_table, array($this_model . '_id' => $this->{$this->pk_key}), 1, 0);
							
						if ($query->num_rows() > 0)
						{
							// Find and update the other objects existing relation to relate with this object
							$this->db->where($this_model . '_id', $this->{$this->pk_key});
							$this->db->update($relationship_table, $data);
						}
						else
						{
							// Add the relation since one doesn't exist
							$this->db->insert($relationship_table, $data);
						}
	
						return TRUE;
					}
				}
				else
				{
					// Relationship already exists
					return TRUE;
				}
			}
		}
		else
		{
			if( ! $object->exists())
			{
				$msg = 'dm_save_rel_noobj';
			}
			else if( ! $this->exists())
			{
				$msg = 'dm_save_rel_nothis';
			}
			else
			{
				$msg = 'dm_save_rel_failed';
			}
			$msg = $this->lang->line($msg);
			$this->error_message($related_field, sprintf($msg, $related_field));
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Relation
	 *
	 * Deletes the relation between this and the other object.
	 *
	 * @access	private
	 * @param	object
	 * @return	bool
	 */
	function _delete_relation($object, $related_field)
	{
		if (empty($related_field))
		{
			$related_field = $object->model;
		}
		
		// the TRUE allows conversion to singular
		$related_properties = $this->_get_related_properties($related_field, TRUE);
		
		if ( ! empty($related_properties) && ! empty($this->{$this->pk_key}) && ! empty($object->id))
		{
			$this_model = $related_properties['join_self_as'];
			$other_model = $related_properties['join_other_as'];
			
			// Determine relationship table name
			$relationship_table = $this->_get_relationship_table($object, $related_field);

			if ($relationship_table == $this->table)
			{
				$this->{$other_model . '_id'} = NULL;
				$this->save();
			}
			else if ($relationship_table == $object->table)
			{
				$object->{$this_model . '_id'} = NULL;
				$object->save();
			}
			else
			{
				$data = array($this_model . '_id' => $this->{$this->pk_key}, $other_model . '_id' => $object->id);

				// Delete relation
				$this->db->delete($relationship_table, $data);
			}

			// Clear related object so it is refreshed on next access
			unset($this->{$related_field});

			return TRUE;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Relationship Table
	 *
	 * Determines the relationship table.
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _get_relationship_table($object, $related_field)
	{
		$prefix = $object->prefix;
		$table = $object->table;
		
		if (empty($related_field))
		{
			$related_field = $object->model;
		}
		
		$related_properties = $this->_get_related_properties($related_field);
		$this_model = $related_properties['join_self_as'];
		$other_model = $related_properties['join_other_as'];
		$other_field = $related_properties['other_field'];
		
		if (array_key_exists($related_field, $this->has_one))
		{
			// see if the relationship is in this table
			if (in_array($other_model . '_id', $this->fields))
			{
				return $this->table;
			}
		}
		
		if (array_key_exists($other_field, $object->has_one))
		{
			// see if the relationship is in this table
			if (in_array($this_model . '_id', $object->fields))
			{
				return $object->table;
			}
		}

		$relationship_table = '';
		
 		// Check if self referencing
		if ($this->table == $table)
		{
			// use the model names from related_properties
			$p_this_model = plural($this_model);
			$p_other_model = plural($other_model);
			$relationship_table = ($p_this_model < $p_other_model) ? $p_this_model . '_' . $p_other_model : $p_other_model . '_' . $p_this_model;
		}
		else
		{
			$relationship_table = ($this->table < $table) ? $this->table . '_' . $table : $table . '_' . $this->table;
		}

		// Remove all occurances of the prefix from the relationship table
		$relationship_table = str_replace($prefix, '', str_replace($this->prefix, '', $relationship_table));

		// So we can prefix the beginning, using the join prefix instead, if it is set
		$relationship_table = (empty($this->join_prefix)) ? $this->prefix . $relationship_table : $this->join_prefix . $relationship_table;

		return $relationship_table;
	}

	// --------------------------------------------------------------------

	/**
	 * Count Related
	 *
	 * Returns the number of related items in the database and in the related object.
	 *
	 * @access	private
	 * @param	string
	 * @param	mixed
	 * @return	integer
	 */
	function _count_related($related_field, $object = '')
	{
		$count = 0;
		
		// lookup relationship info
		// the TRUE allows conversion to singular
		$rel_properties = $this->_get_related_properties($related_field, TRUE);
		$class = $rel_properties['class'];
		
		$ids = array();
		
		if ( ! empty($object))
		{
			$count = $this->_count_related_objects($related_field, $object, '', $ids);
			$ids = array_unique($ids);
		}

		if ( ! empty($related_field) && ! empty($this->{$this->pk_key}))
		{
			$one = array_key_exists($related_field, $this->has_one);
			
			// don't bother looking up relationships if this is a $has_one and we already have one.
			if( (!$one) || empty($ids))
			{
				// Prepare model
				$object = new $class();
	
				// Store parent data
				$object->parent = array('model' => $rel_properties['other_field'], $this->pk_key => $this->{$this->pk_key});
				
				if( ! empty($ids)) {
					$object->where_not_in($this->pk_key, $ids);
				}
				
				$count += $object->count();
			}
		}

		return $count;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Private recursive function to count the number of objects
	 * in a passed in array (or a single object)
	 * 
	 * @return # of items
	 * @param object $compare related field (model) to compare to
	 * @param object $object Object or array to count
	 * @param object $related_field[optional] related field of $object
	 */
	function _count_related_objects($compare, $object, $related_field, &$ids)
	{
		$count = 0;
		if (is_array($object))
		{
			// loop through array to check for objects
			foreach ($object as $rel_field => $obj)
			{
				if ( ! is_string($rel_field))
				{
					// if this object doesn't have a related field, use the parent related field
					$rel_field = $related_field;
				}
				$count += $this->_count_related_objects($compare, $obj, $rel_field, $ids);
			}
		}
		else
		{
			// if this object doesn't have a related field, use the model
			if (empty($related_field))
			{
				$related_field = $object->model;
			}
			// if this object is the same relationship type, it counts
			if ($related_field == $compare && $object->exists())
			{
				$ids[] = $object->id;
				$count++;
			}
		}
		return $count;
	}

	// --------------------------------------------------------------------

	/**
	 * Include Join Fields
	 *
	 * If TRUE, the any extra fields on the join table will be included
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function include_join_fields($include = TRUE)
	{
		$this->_include_join_fields = $include;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Join Field
	 *
	 * Sets the value on a join table based on the related field
	 * If $related_field is an array, then the array should be
	 * in the form $related_field => $object or array($object)
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function set_join_field($related_field, $field, $value = NULL, $object = NULL)
	{
		$related_ids = array();
		
		if (is_array($related_field))
		{
			// recursively call this on the array passed in.
			foreach ($related_field as $key => $object)
			{
				$this->set_join_field($key, $field, $value, $object);
			}
			return;
		}
		else if (is_object($related_field))
		{
			$object = $related_field;
			$related_field = $object->model; 
			$related_ids[] = $object->id;
			$related_properties = $this->_get_related_properties($related_field);
		}
		else
		{
			// the TRUE allows conversion to singular
			$related_properties = $this->_get_related_properties($related_field, TRUE);
			if (is_null($object))
			{
				$class = $related_properties['class'];
				$object = new $class();
			}
		}
		
		// Determine relationship table name
		$relationship_table = $this->_get_relationship_table($object, $related_field);
		
		if (empty($object))
		{
			// no object was passed in, so create one
			$class = $related_properties['class'];
			$object = new $class();
		}
		
		$this_model = $related_properties['join_self_as'];
		$other_model = $related_properties['join_other_as'];
		
		if (! is_array($field))
		{
			$field = array( $field => $value );
		}
		
		if ( ! is_array($object))
		{
			$object = array($object);
		}
		
		if (empty($object))
		{
			$this->db->where($this_model . '_id', $this->{$this->pk_key});
			$this->db->update($relationship_table, $field);
		}
		else
		{
			foreach ($object as $obj)
			{
				$this->db->where($this_model . '_id', $this->{$this->pk_key});
				$this->db->where($other_model . '_id', $obj->id);
				$this->db->update($relationship_table, $field);
			}
		}
		
		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Join Field
	 *
	 * Adds a query of a join table's extra field
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function _join_field($query, $arguments)
	{
		if ( ! empty($query) && count($arguments) >= 3)
		{
			$object = $field = $value = $option = NULL;

			// Prepare model
			if (is_object($arguments[0]))
			{
				$object = $arguments[0];
				$related_field = $object->model; 
			}
			else
			{
				$related_field = $arguments[0];
				// the TRUE allows conversion to singular
				$related_properties = $this->_get_related_properties($related_field, TRUE);
				$class = $related_properties['class'];
				$object = new $class();
			}
			

			// Prepare field and value
			$field = $arguments[1];
			$value = $arguments[2];

			// Determine relationship table name, and join the tables
			$rel_table = $this->_get_relationship_table($object, $related_field);

			// Add query clause
			$this->db->{$query}($rel_table . '.' . $field, $value);
		}

		// For method chaining
		return $this;
	}

	// --------------------------------------------------------------------

	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *                                                                   *
	 * Related Validation methods                                        *
	 *                                                                   *
	 * The following are methods used to validate the                    *
	 * relationships of this object.                                     *
	 *                                                                   *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


	// --------------------------------------------------------------------

	/**
	 * Related Required (pre-process)
	 *
	 * Checks if the related object has the required related item
	 * or if the required relation already exists.
	 *
	 * @access	private
	 * @param	mixed
	 * @param	string
	 * @return	bool
	 */	
	function _related_required($object, $model)
	{
		return ($this->_count_related($model, $object) == 0) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Related Min Size (pre-process)
	 *
	 * Checks if the value of a property is at most the minimum size.
	 * 
	 * @access	private
	 * @param	mixed
	 * @param	string
	 * @param	integer
	 * @return	bool
	 */
	function _related_min_size($object, $model, $size = 0)
	{
		return ($this->_count_related($model, $object) < $size) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Related Max Size (pre-process)
	 *
	 * Checks if the value of a property is at most the maximum size.
	 * 
	 * @access	private
	 * @param	mixed
	 * @param	string
	 * @param	integer
	 * @return	bool
	 */
	function _related_max_size($object, $model, $size = 0)
	{
		return ($this->_count_related($model, $object) > $size) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *                                                                   *
	 * Validation methods                                                *
	 *                                                                   *
	 * The following are methods used to validate the                    *
	 * values of this objects properties.                                *
	 *                                                                   *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


	// --------------------------------------------------------------------

	/**
	 * Alpha Dash Dot (pre-process)
	 *
	 * Alpha-numeric with underscores, dashes and full stops.
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */	
	function _alpha_dash_dot($field)
	{
		return ( ! preg_match('/^([\.-a-z0-9_-])+$/i', $this->{$field})) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha Slash Dot (pre-process)
	 *
	 * Alpha-numeric with underscores, dashes, forward slashes and full stops.
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */	
	function _alpha_slash_dot($field)
	{
		return ( ! preg_match('/^([\.\/-a-z0-9_-])+$/i', $this->{$field})) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Matches (pre-process)
	 *
	 * Match one field to another.
	 * This replaces the version in CI_Form_validation.
	 * 
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function _matches($field, $other_field)
	{
		return ($this->{$field} !== $this->{$other_field}) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Min Date (pre-process)
	 *
	 * Checks if the value of a property is at least the minimum date.
	 * 
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function _min_date($field, $date)
	{
		return (strtotime($this->{$field}) < strtotime($date)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Max Date (pre-process)
	 *
	 * Checks if the value of a property is at most the maximum date.
	 * 
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function _max_date($field, $date)
	{
		return (strtotime($this->{$field}) > strtotime($date)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Min Size (pre-process)
	 *
	 * Checks if the value of a property is at least the minimum size.
	 * 
	 * @access	private
	 * @param	string
	 * @param	integer
	 * @return	bool
	 */
	function _min_size($field, $size)
	{
		return ($this->{$field} < $size) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Max Size (pre-process)
	 *
	 * Checks if the value of a property is at most the maximum size.
	 * 
	 * @access	private
	 * @param	string
	 * @param	integer
	 * @return	bool
	 */
	function _max_size($field, $size)
	{
		return ($this->{$field} > $size) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Unique (pre-process)
	 *
	 * Checks if the value of a property is unique.
 	 * If the property belongs to this object, we can ignore it.
 	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	function _unique($field)
	{
		if ( ! empty($this->{$field}))
		{
			$query = $this->db->get_where($this->table, array($field => $this->{$field}), 1, 0);

			if ($query->num_rows() > 0)
			{
				$row = $query->row();

				// If unique value does not belong to this object
				if ($this->{$this->pk_key} != $row->{$row->pk_key})
				{
					// Then it is not unique
					return FALSE;
				}
			}
		}

		// No matches found so is unique
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Unique Pair (pre-process)
	 *
	 * Checks if the value of a property, paired with another, is unique.
 	 * If the properties belongs to this object, we can ignore it.
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function _unique_pair($field, $other_field = '')
	{
		if ( ! empty($this->{$field}) && ! empty($this->{$other_field}))
		{
			$query = $this->db->get_where($this->table, array($field => $this->{$field}, $other_field => $this->{$other_field}), 1, 0);

			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				
				// If unique pair value does not belong to this object
				if ($this->{$this->pk_key} != $row->{$row->pk_key})
				{
					// Then it is not a unique pair
					return FALSE;
				}
			}
		}

		// No matches found so is unique
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Date (pre-process)
	 *
	 * Checks whether the field value is a valid DateTime.
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	function _valid_date($field)
	{
		// Ignore if empty
		if (empty($this->{$field}))
		{
			return TRUE;
		}

		$date = date_parse($this->{$field});

		return checkdate($date['month'], $date['day'],$date['year']);
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Date Group (pre-process)
	 *
	 * Checks whether the field value, grouped with other field values, is a valid DateTime.
	 *
	 * @access	private
	 * @param	string
	 * @param	array
	 * @return	bool
	 */
	function _valid_date_group($field, $fields = array())
	{
		// Ignore if empty
		if (empty($this->{$field}))
		{
			return TRUE;
		}

		$date = date_parse($this->{$fields['year']} . '-' . $this->{$fields['month']} . '-' . $this->{$fields['day']});

		return checkdate($date['month'], $date['day'],$date['year']);
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Match (pre-process)
	 *
	 * Checks whether the field value matches one of the specified array values.
	 *
	 * @access	private
	 * @param	string
	 * @param	array
	 * @return	bool
	 */
	function _valid_match($field, $param = array())
	{
		return in_array($this->{$field}, $param);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Boolean (pre-process)
	 * 
	 * Forces a field to be either TRUE or FALSE.
	 * Uses PHP's built-in boolean conversion.
	 * 
	 * @param object $field 
	 */
	function _boolean($field)
	{
		$this->{$field} = (boolean)$this->{$field};
	}

	// --------------------------------------------------------------------

	/**
	 * Encode PHP Tags (prep)
	 *
	 * Convert PHP tags to entities.
	 * This replaces the version in CI_Form_validation.
	 *
	 * @access	private
	 * @param	string
	 * @return	void
	 */	
	function _encode_php_tags($field)
	{
		$this->{$field} = encode_php_tags($this->{$field});
	}

	// --------------------------------------------------------------------

	/**
	 * Prep for Form (prep)
	 *
	 * Converts special characters to allow HTML to be safely shown in a form.
	 * This replaces the version in CI_Form_validation.
	 *
	 * @access	private
	 * @param	string
	 * @return	void
	 */	
	function _prep_for_form($field)
	{
		$this->{$field} = $this->form_validation->prep_for_form($this->{$field});
	}

	// --------------------------------------------------------------------

	/**
	 * Prep URL (prep)
	 *
	 * Adds "http://" to URLs if missing.
	 * This replaces the version in CI_Form_validation.
	 *
	 * @access	private
	 * @param	string
	 * @return	void
	 */	
	function _prep_url($field)
	{
		$this->{$field} = $this->form_validation->prep_url($this->{$field});
	}

	// --------------------------------------------------------------------

	/**
	 * Strip Image Tags (prep)
	 *
	 * Strips the HTML from image tags leaving the raw URL.
	 * This replaces the version in CI_Form_validation.
	 *
	 * @access	private
	 * @param	string
	 * @return	void
	 */	
	function _strip_image_tags($field)
	{
		$this->{$field} = strip_image_tags($this->{$field});
	}

	// --------------------------------------------------------------------

	/**
	 * XSS Clean (prep)
	 *
	 * Runs the data through the XSS filtering function, described in the Input Class page.
	 * This replaces the version in CI_Form_validation.
	 *
	 * @access	private
	 * @param	string
	 * @param	bool
	 * @return	void
	 */	
	function _xss_clean($field, $is_image = FALSE)
	{
		$this->{$field} = xss_clean($this->{$field}, $is_image);
	}

	// --------------------------------------------------------------------

	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *                                                                   *
	 * Common methods                                                    *
	 *                                                                   *
	 * The following are common methods used by other methods.           *
	 *                                                                   *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


	// --------------------------------------------------------------------

	/**
	 * To Array
	 *
	 * Converts this objects current record into an array for database queries.
	 * If validate is TRUE (getting by objects properties) empty objects are ignored.
	 *
	 * @access	private
	 * @param	bool
	 * @return	array
	 */
	function _to_array($validate = FALSE)
	{
		$data = array();

		foreach ($this->fields as $field)
		{
			if (empty($this->{$field}) && $validate)
			{
				continue;
			}
			
			$data[$field] = $this->{$field};
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * To Object
	 *
	 * Converts the query result into an array of objects.
	 *
	 * @access	private
	 * @param	array
	 * @param	string
	 * @return	array
	 */
	function _to_object($result, $model)
	{
		$items = array();

		foreach ($result as $row)
		{
			$item = new $model();

			// Populate this object with values from first record
			foreach ($row as $key => $value)
			{
				$item->{$key} = $value;
			}

			foreach ($this->fields as $field)
			{
				if (! isset($row->{$field}))
				/*{
					$item->{$field} = $row->{$field};
				}
				else */
				{
					$item->{$field} = NULL;
				}
			}

			$item->_refresh_stored_values();

			$items[$item->{$item->pk_key}] = $item;
		}

		return $items;
	}

	// --------------------------------------------------------------------

	/**
	 * Refresh Stored Values
	 *
	 * Refreshes the stored values with the current values.
	 *
	 * @access	private
	 * @return	void
	 */
	function _refresh_stored_values()
	{
		// Update stored values
		foreach ($this->fields as $field)
		{
			$this->stored->{$field} = $this->{$field};
		}

		// Check if there is a "matches" validation rule
		foreach ($this->validation as $validation)
		{
			// If there is, match the field value with the other field value
			if (array_key_exists('matches', $validation['rules']))
			{
				$this->{$validation['field']} = $this->stored->{$validation['field']} = $this->{$validation['rules']['matches']};
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Assign Libraries
	 *
	 * Assigns required CodeIgniter libraries to DataMapper.
	 *
	 * @access	private
	 * @return	void
	 */
	function _assign_libraries()
	{
		if ($CI =& get_instance())
		{
			$this->lang = $CI->lang;
			$this->load = $CI->load;
			$this->db = $CI->db;
			$this->config = $CI->config;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Load Languages
	 *
	 * Loads required language files.
	 *
	 * @access	private
	 * @return	void
	 */
	function _load_languages()
	{

		// Load the DataMapper language file
		$this->lang->load('datamapper');
	}

	// --------------------------------------------------------------------

	/**
	 * Load Helpers
	 *
	 * Loads required CodeIgniter helpers.
	 *
	 * @access	private
	 * @return	void
	 */
	function _load_helpers()
	{
		// Load inflector helper for singular and plural functions
		$this->load->helper('inflector');

		// Load security helper for prepping functions
		$this->load->helper('security');
	}
}

/* End of file datamapper.php */
/* Location: ./application/models/datamapper.php */
