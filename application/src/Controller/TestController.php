<?php
namespace Lebran\App\Controller;

use Lebran\Core\Mvc\View;
use Lebran\Core\Mvc\Layout;

/**
 * Тестовый контроллер
 *
 * @author iToktor
 */
class TestController extends Layout
{    
    public function __construct()
    {
        $this->addMiddleware('test.test', array('only' => array('helloWorld')));
    }

    public function helloWorldAction()
    {
        $view = View::make('views/hello_world')->set('hello_world', 'Hello World!!!')->render();
        tmsg(View::make('views/hello_world'));
        $this->layout->index = $view;
    }
}
