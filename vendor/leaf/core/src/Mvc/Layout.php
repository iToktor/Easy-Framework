<?php
namespace Leaf\Core\Mvc;

/**
 * Класс помощник для создания шаблонов,
 * состоящих из нескольких маленьких(видов),
 * реализует принцип обвертки.
 * 
 * @package    Core
 * @subpackage Utils
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
abstract class Layout extends Controller {
    
    /**
     * Имя обвертки.
     *
     * @var string 
     */
    public $layout = 'index';

    /**
     * Путь к папке templates.
     *
     * @var string
     */
    public $template_path = false;
    
    /**
     * Включить или выключить авто-рендеринг.
     *
     * @var bool 
     */
    public $render = true;
        
    /**
     * Если включен авто-рендеринг, загружаем базовый шаблон(обвертки).
     * При переопределении обязательно вызывать(parent::first()).
     *
     * @return void
     */
    public function before() {
        parent::before();
        if ($this->render === true) {
            $this->layout = View::make($this->layout, $this->template, $this->template_path);
        } 
    }
    
    /**
     * Если включен авто-рендеринг, загрузка в ответ отрендериного базового шаблона(обвертки).
     * При переопределении обязательно вызывать(parent::first()).
     * 
     * @return void
     */
    public function after() {
        parent::after();
        if ($this->render === true) {
            $this->app->response->setBody($this->layout->render());
        }   
    }

    /**
     * Редирект
     *
     * @param string $uri Ури редиректа.
     * @param int $code Статус код.
     * @return void
     */
    public function redirect( $uri = '', $code = 302) {
        $this->render = false;
        parent::redirect($uri, $code);
    }
}    