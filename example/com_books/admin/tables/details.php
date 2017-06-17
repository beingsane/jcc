<?php
/**
 * @author		
 * @copyright	
 * @license		
 */

defined("_JEXEC") or die("Restricted access");

/**
 * Details table class.
 *
 * @package     Books
 * @subpackage  Tables
 */
class BooksTableDetails extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__book', 'id', $db);
	}

	/**
     * Overloaded check function
     */
    public function check()
	{
        //If there is an ordering column and this is a new row then get the next ordering value
        if (property_exists($this, 'ordering') && $this->id == 0) {
            $this->ordering = self::getNextOrder();
        }

		
		return parent::store($updateNulls);
	}
}
?>