<?php
/**
 * @author		
 * @copyright	
 * @license		
 */

defined("_JEXEC") or die("Restricted access");

/**
 * Books helper class.
 *
 * @package     Books
 * @subpackage  Helpers
 */
class BooksHelper
{
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_BOOKS_SUBMENU_BOOK'), 
			'index.php?option=com_books&view=books', 
			$vName == 'books'
		);

		JHtmlSidebar::addEntry(
			JText::_('JCATEGORIES'), 
			'index.php?option=com_categories&extension=com_books', 
			$vName == 'categories'
		);
	}
	
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_books';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
	

}