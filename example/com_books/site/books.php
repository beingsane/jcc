<?php
defined("_JEXEC") or die("Restricted access");

if (!class_exists('\JCC\Loader')) {
  JLoader::register('JCC\Loader', JPATH_LIBRARIES . '/jcc/Loader.php');
  if (class_exists('\JCC\Loader')) {
    JCC\Loader::setup();
  } else {
    $app=JFactory::getApplication();
//	  if ($app->isAdmin()) {
      $app->enqueueMessage(
        JText::sprintf('LIBRARY_JU_MISSING', 'JCC', 'warning'	)
      );
  //}
  }
}

include_once('BooksController.php');

try {
  $controller	 = new BooksController();
  $controller->execute();
} catch (Exception $e) {
  $controller->redirection(JURI::base(), $e->getMessage(), 'error');
}
