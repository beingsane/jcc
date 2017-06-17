<?php
namespace JCC\Controller;

use Joomla\Registry\Registry;
use JCC\Router\Router;


class BaseController  {
  
  protected $default_view = 'default';
  protected $routes = null;

  public function redirection($redirect, $message = null, $messageType = 'message') {
    if ($redirect) {
      $app = \JFactory::getApplication();
      if ($message) $app->enqueueMessage($message, $messageType);
      $app->redirect($redirect);
    }
  }

  function view($view_name, $format=null){
    if ($format==null) $format=$this->options['format'];
    $view = $this->getView($view_name, $format);
    if (isset($this->model)) $view->setModel($this->model, true);		
    $view->setLayout($this->layout);
    $view->document = $this->document;
    return $view;
  }

  function twig($template_dir) {
    $loader = new \Twig\Loader\FilesystemLoader($template_dir);
    $twig = new \Twig\Environment($loader, array(
     'cache' => JPATH_ROOT.'/cache',
    ));
   return $twig;
  }

  function getView($name) {
    require_once JPATH_COMPONENT_SITE.'/views/'.$name.'/view.html.php';
    $class=$this->component.'View'.$name;
    if (class_exists($class)) return new $class();
    return null;
  }

  function getModel($name) {
    require_once JPATH_COMPONENT_SITE.'/models/'.$name.'.php';
    $class=$this->component.'Model'.$name;
//$db = isset($config['dbo']) ? $config['dbo'] : \JFactory::getDbo();
//$class($db);   
    if (class_exists($class)) return new $class();
    else {
      $class=$this->component.$name;
      if (class_exists($class)) return new $class();
    }
    return null;
  }


  function default_display(){
    $this->view($this->view_name)->display();
  }

  function action_default($vars) {
    try {
      $this->model = $this->getModel($this->view_name);
      $this->default_display();
    } catch (Exception $e) {
      $this->setRedirect(JURI::base(), $e->getMessage(), 'error');
    }
  }

  function getUri(){
    $uri = \JUri::getInstance();
    return $uri->toString(array('path', 'query'));
  }

  function execute() {
    $this->app=\JFactory::getApplication();
    // Obiekt dokumentu
    $this->document = \JFactory::getDocument();
    $this->input = $this->app->input;
    $this->options=[];
    $this->options['format'] = $this->document->getType(); // html ? 
    $this->view_name = $this->input->getCmd('view', 'default');
    if ($this->view_name === 'error') {
      $this->view_name = $this->default_view;
    }
    // layout
    $this->layout = $this->input->getCmd('layout', 'default');
    // Push document object into <th></th>e view.

    if (isset($this->routes)) {
      $router = new Router;
      $router->addRoutes($this->routes);
      $path  = preg_replace('~^/.*?index.php/?~', '', $this->getUri());
      $route = $router->parseRoute($path);
      $action  = $route['action'];
      $vars  = $route['vars'];
      $method='action_'.$action;
    } else {
      $vars=[];
      $vars['id'] = $this->input->get('id');
      $vars['task'] = $this->input->get('task');
      $method='action_'.$this->view_name;
    }
    if (method_exists($this, $method)) $this->$method($vars);
    else $this->action_default($vars);
  }
  
}
?>
  
