<?php
use \JCC\Controller\BaseController;
use \Twig;

defined("_JEXEC") or die("Restricted access");

class BooksController extends  \JCC\Controller\BaseController {

  protected $component = 'Books';
  protected $default_view = 'books';
  protected $routes = [
      [ 
        'pattern' => 'books/:id',
				'action' => 'book',
				'rules' => [
					'id' => '(\d+)'
				]
			],
      [ 
        'pattern' => 'books',
				'action' => 'books'
			]
  ];

  function action_book($vars) {
    $this->model = $this->getModel('book');
/*
    if ($this->layout=='edit') {
    } else {
       $this->model->getItem($vars['id']);
       $this->view('book')->display();
    }
 */
     if (!isset($vars['id'])) action_books($vars);
     else {
       echo $this->twig(JPATH_COMPONENT_SITE.'/twig')
         ->render('view.html.twig', array(
                    'book' => $this->model->getItem($vars['id'])
        ));
     }
  }

  function action_books($vars) {
    $this->model = $this->getModel('books');    
//    $this->view('books')->display();
    echo $this->twig(JPATH_COMPONENT_SITE.'/twig')
       ->render('list.html.twig', array(
        'books' => $this->model->getItems(),
        'navigation' => '',
        ));
  }

}
?>