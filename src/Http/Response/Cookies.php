<?php
namespace Lebran\Http\Response;

use Lebran\Utils\Storage;
use Lebran\Di\Injectable;
use Lebran\Di\InjectableInterface;

/**
 * The helper class for a cookies. Supports setting of the array.
 * It is used notation dot to access multidimensional arrays.
 *
 * @package    Http
 * @subpackage Response
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Cookies extends Storage implements InjectableInterface
{
    use Injectable;

    /**
     * Store cookies.
     *
     * @var array
     */
    protected $bag = [];

    /**
     * Registered in response or not.
     *
     * @var bool
     */
    protected $registered = false;

    /**
     * Parameters for cookies.
     *
     * @var array
     */
    protected $params = [
        // The time when the term of the cookie expires
        'expiration' => 0,
        // The path to the directory on the server from which the cookie will be available
        'path'       => '',
        // Domain, which is available cookie
        'domain'     => null,
        // It indicates that the cookie should be transferred from the customer via a secure HTTPS connection
        'secure'     => false,
        // If set to true, cookie will only be available via Http protocol
        'httponly'   => false
    ];

    /**
     * Initialisation.
     *
     *      $cookies = new Cookies(['secure' => true]));
     *
     * @param array $params Cookies parameters.
     */
    public function __construct(array $params = [])
    {
        array_walk_recursive($_COOKIE, 'trim');
        parent::__construct($_COOKIE);
        $this->params = array_merge($this->params, $params);
    }

    /**
     * Set the cookie(s) value or an array of key.
     *
     *      $cookie->set('global.post', $_POST, ['expiration' => 32000]);
     *
     * @param string $name   Cookie(s) name.
     * @param mixed  $value  Cookie(s) value.
     * @param array  $params Cookie(s) parameters.
     *
     * @return object Cookies object.
     * @throws \Lebran\Http\Response\Exception
     */
    public function set($name, $value, array $params = [])
    {
        foreach ($this->params as $key => $val) {
            if (empty($params[$key])) {
                $params[$key] = $val;
            }
        }
        if ($params['expiration'] !== 0) {
            $params['expiration'] += time();
        }

        $temp_name = explode('.', trim($name));
        $name      = array_shift($temp_name);
        if (0 !== count($temp_name)) {
            $name = $name.'['.implode('][', $temp_name).']';
        }

        if (is_array($value)) {
            $value = $this->setHelper($name, $value);
            array_walk(
                $value,
                function (&$item) use ($params) {
                    $item = array_merge($params, ['value' => $item]);
                }
            );
        } else {
            $value = [$name => array_merge($params, ['value' => $value])];
        }

        $this->bag = array_merge($this->bag, $value);

        if (!$this->registered) {
            if (!is_object($this->di) && !$this->di->has('response')) {
                throw new Exception('A dependency injection object is required to access the "response" service');
            }
            $this->di->get('response')->setCookies($this);
            $this->registered = true;
        }

        return $this;
    }

    /**
     * Removes a cookie(s) on a key with the specified parameters.
     *
     * @param string $name   Cookie(s) name.
     * @param array  $params Parameters which announces cookie(s).
     *
     * @return object Cookies object.
     * @throws \Lebran\Http\Response\Exception
     */
    public function delete($name, array $params = [])
    {
        if ((($delete = $this->get($name)) !== null)) {
            if (is_array($delete)) {
                array_walk_recursive(
                    $delete,
                    function (&$item) {
                        $item = '';
                    }
                );
            } else {
                $delete = '';
            }

            $params = array_merge($params, ['expiration' => 1]);
            $this->set($name, $delete, $params);
        }

        return $this;
    }

    /**
     * Send Cookies from bag.
     *
     * @return object Cookies object.
     */
    public function send()
    {
        foreach ($this->bag as $name => $cookie) {
            setcookie(
                $name,
                $cookie['value'],
                $cookie['expiration'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }

        return $this;
    }

    /**
     * Recursively converts into a single array.
     *
     *      $this->setHelper('test', $test);
     *
     *      Incoming array:
     *          [
     *              "level11" => "value1",
     *              "level12" => [
     *                  "level21" => "value2"
     *              ]
     *          ]
     *
     *      Outgoing array:
     *          [
     *              "test[level11]" => "value1",
     *              "test[level12][level21]" => "value2",
     *          ]
     *
     * @param array  $array Convertible array.
     * @param string $name  The name that will be added at the beginning.
     *
     * @return array Processed array.
     */
    final protected function setHelper($name, array $array)
    {
        $temp = [];
        foreach ($array as $key => $value) {
            $new_name = (string)(($name !== '')?$name.'['.$key.']':$key);
            if (is_array($value)) {
                $temp = array_merge($temp, $this->setHelper($new_name, $value));
            } else {
                $temp[$new_name] = $value;
            }
        }
        return $temp;
    }
}