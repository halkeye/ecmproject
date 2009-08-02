<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Language extends CI_Language
{
    function MY_Language()
    {
        parent::CI_Language();
    }

	/**
	 * Fetch a single line of text from the language array
	 *
	 * @access	public
	 * @param	string	$line 	the language line
     * @param   hash    $replacements Placeholders to replace
	 * @return	string
	 */
	function line($line = '', $replacements = array())
	{
		$line = ($line == '' OR ! isset($this->language[$line])) ? $line : $this->language[$line];
		return strtr($line, $replacements);;
	}

}

