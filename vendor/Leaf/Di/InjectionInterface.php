<?php
namespace Leaf\Di;

/**
 * This interface must be implemented in those classes that need internal dependency injection container.
 *
 * @package    Di
 * @version    2.1
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
interface InjectableInterface
{
    /**
     * Sets the dependency injection container.
     *
     * @param object $di Container object.
     *
     * @return void
     */
    public function setDi(Container $di);

    /**
     * Returns the dependency injection container.
     *
     * @return object Container object.
     */
    public function getDi();
}