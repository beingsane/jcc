<?php
/**
 * @author		
 * @copyright	
 * @license		
 */

defined("_JEXEC") or die("Restricted access");

/**
 * Details item controller class.
 *
 * @package     Books
 * @subpackage  Controllers
 */
class BooksControllerDetails extends JControllerForm
{
	/**
	 * The URL view item variable.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $view_item = 'Details';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $view_list = 'Books';

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = array())
	{
		$user       = JFactory::getUser();
		$categoryId = JArrayHelper::getValue($data, 'catid', $this->input->getInt('catid'), 'int');

		if ($categoryId)
		{
			// If the category has been passed in the data or URL check it.
			return $user->authorise('core.create', 'com_books.category.'.$categoryId);
		}

		return parent::allowAdd();
	}
	
	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Details', 'BooksModel', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_books&view=books' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
}
?>