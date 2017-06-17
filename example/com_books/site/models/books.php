<?php
/**
 * @author		
 * @copyright	
 * @license		
 */

defined("_JEXEC") or die("Restricted access");

/**
 * List Model for books.
 *
 * @package     Books
 * @subpackage  Models
 */
class BooksModelBooks extends JModelList
{

  protected function getListQuery() {
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);
    // Select some fields
    $query->select('id,title,authors');
    $query->from('#__book');
    return $query;
  }

}
?>