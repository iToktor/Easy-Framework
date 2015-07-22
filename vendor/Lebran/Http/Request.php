<?php
namespace Lebran\Http;

use Lebran\Http\Request\File;

/**
 * It implements a wrapper for Http request with extended functionality.
 *
 *      - Easy access to request headers
 *      - Get sanitizing values from the global arrays
 *      - Methods of checking the type of request
 *      - Easy access to files obtained via the form as array of objects
 *
 * @package    Http
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Request
{
    /**
     * Storage files.
     *
     * @var array
     */
    protected $files = [];

    /**
     * Initialisation.
     */
    public function __construct()
    {
        $files = [];
        foreach ($_FILES as $key => $file) {
            if (!is_array($file['name'])) {
                $files[$key] = $file;
            } else {
                $files[$key] = $this->smoothFiles(
                    $file['name'],
                    $file['type'],
                    $file['tmp_name'],
                    $file['size'],
                    $file['error']
                );
            }
        }
        $this->fileHelper($files, $this->files);
        array_walk_recursive($_POST, 'trim');
        array_walk_recursive($_GET, 'trim');
        array_walk_recursive($_SERVER, 'trim');
    }

    /**
     * If you do not pass, sends array.
     * When sending the key, sends the value with the same key.
     *
     * @param array  $array   Array which will return value.
     * @param string $key     The key to which will go search.
     * @param mixed  $default Value that will be sent if no search results.
     *
     * @return mixed Array, the value of a key or default.
     */
    protected function getHelper(array $array, $key, $default)
    {
        return ($key)?(isset($array[$key])?$array[$key]:$default):$array;
    }

    /**
     * If you do not pass, sends array of POST.
     * When sending the key, sends the value with the same key.
     *
     * @param string $key     The key to which will go search.
     * @param mixed  $default Value that will be sent if no search results.
     *
     * @return mixed An array of POST, the value of a key or default.
     */
    public function getPost($key = null, $default = null)
    {
        return $this->getHelper($_POST, $key, $default);
    }

    /**
     * If you do not pass, sends array of GET.
     * When sending the key, sends the value with the same key.
     *
     * @param string $key     The key to which will go search.
     * @param mixed  $default Value that will be sent if no search results.
     *
     * @return mixed An array of GET, the value of a key or default.
     */
    public function getQuery($key = null, $default = null)
    {
        return $this->getHelper($_GET, $key, $default);
    }

    /**
     * If you do not pass, sends array of SERVER.
     * When sending the key, sends the value with the same key.
     *
     * @param string $key     The key to which will go search.
     * @param mixed  $default Value that will be sent if no search results.
     *
     * @return mixed An array of SERVER, the value of a key or default.
     */
    public function getServer($key = null, $default = null)
    {
        return $this->getHelper($_SERVER, $key, $default);
    }

    /**
     * Sends the name of a method derived from the request headers.
     *
     * @return string Request method.
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     * Sends uri from the request.
     *
     * @return string Request uri.
     */
    public function getUri()
    {
        return trim($this->getServer('REQUEST_URI'), '/');
    }

    /**
     * Sends header value of the request.
     *
     * @param string $header Header name.
     *
     * @return mixed The value of the title, if no search results - false.
     */
    public function getHeader($header)
    {
        $header = strtoupper(strtr($header, "-", "_"));

        if (isset($_SERVER[$header])) {
            return $_SERVER[$header];
        } else if (isset($_SERVER['HTTP_'.$header])) {
            return $_SERVER['HTTP_'.$header];
        } else {
            return false;
        }
    }

    /**
     * Checks whether the 'post' request method.
     *
     * @return bool true, if the method is POST, then - false.
     */
    public function isPost()
    {
        return 'POST' == $this->getMethod();
    }

    /**
     * Checks whether the 'get' request method.
     *
     * @return bool true, if the method is GET, then - false.
     */
    public function isGet()
    {
        return 'GET' == $this->getMethod();
    }

    /**
     * Checks whether the request is asynchronous.
     *
     * @return bool true, if the method XmlHttpRequest, then - false.
     */
    public function isXMLHttpRequest()
    {
        return 'XMLHttpRequest' == $this->getHeader('X_REQUESTED_WITH');
    }

    /**
     * Checks whether the user is using a secure connection. (HTTPS)
     *
     * @return bool true, если HTTPS, иначе - false.
     */
    public function isHttps()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * It sends files obtained via the form as array of objects.
     *
     * @param string $name Name file or files.
     *
     * @return mixed An array of File objects.
     */
    public function getFiles($name = null)
    {
        return $this->getHelper($this->files, $name, false);
    }

    /**
     * Smooth out $_FILES to have plain array with all files uploaded.
     *
     * @param array $names     File names.
     * @param array $types     File types.
     * @param array $tmp_names File tmp_names.
     * @param array $sizes     File sizes.
     * @param array $errors    File errors.
     *
     * @return array Smooth files.
     */
    final protected function smoothFiles(array $names, array $types, array $tmp_names, array $sizes, array $errors)
    {
        $files = [];
        foreach ($names as $key => $name) {
            if (is_string($name)) {
                $files[$key] = [
                    'name'     => $name,
                    'type'     => $types[$key],
                    'tmp_name' => $tmp_names[$key],
                    'size'     => $sizes[$key],
                    'error'    => $errors[$key]
                ];
            }
            if (is_array($name)) {
                $parentFiles = $this->smoothFiles(
                    $names[$key],
                    $types[$key],
                    $tmp_names[$key],
                    $sizes[$key],
                    $errors[$key]
                );
                foreach ($parentFiles as $key1 => $file) {
                    $files[$key][$key1] = $file;
                }
            }
        }
        return $files;
    }

    /**
     * File helper.
     *
     * @param array $file  Array of files.
     * @param array $array Where an array of record.
     *
     * @return void
     */
    final protected function fileHelper(array $file, &$array)
    {
        foreach ($file as $key => $value) {
            if (is_array($value)) {
                $this->fileHelper($value, $array[$key]);
            } else {
                if (UPLOAD_ERR_OK == $file['error'] && !empty($file)) {
                    $array = new File($file);
                }
                break;
            }
        }
    }
}
