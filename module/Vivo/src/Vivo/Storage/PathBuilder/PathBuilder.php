<?php
namespace Vivo\Storage\PathBuilder;

use Vivo\Storage\Exception;

/**
 * PathBuilder
 * Storage paths manipulation
 */
class PathBuilder implements PathBuilderInterface
{
    /**
     * Character used as a separator for paths in storage
     * @var string
     */
    protected $separator;

    /**
     * Character used to replace illegal path characters
     * @var string
     */
    protected $replacementChar    = '-';

    /**
     * Constructor
     * @param string $separator Path components separator
     * @throws \Vivo\Storage\Exception\InvalidArgumentException
     */
    public function __construct($separator)
    {
        if (strlen($separator) != 1) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Only single character separators supported; '%s' given", __METHOD__, $separator));
        }
        $this->separator    = $separator;
    }

    /**
     * Returns character used as a separator in storage paths
     * @return string
     */
    public function getStoragePathSeparator()
    {
        return $this->separator;
    }

    /**
     * Builds storage path from submitted elements
     * @param array $elements
     * @param bool $absolute If true, builds an absolute path starting with the storage path separator
     * @return string
     */
    public function buildStoragePath(array $elements, $absolute = true)
    {
        $components = array();
        $separator  = $this->getStoragePathSeparator();
        //Get atomic components
        foreach ($elements as $element) {
            $elementComponents  = $this->getStoragePathComponents($element);
            $components         = array_merge($components, $elementComponents);
        }
        $path   = implode($separator, $components);
        if ($absolute) {
            $path    = $separator . $path;
        }
        $path   = $this->removeIllegalCharacters($path);
        return $path;
    }

    /**
     * Returns an array of 'atomic' storage path components
     * @param string $path
     * @return array
     */
    public function getStoragePathComponents($path)
    {
        $components = explode($this->getStoragePathSeparator(), $path);
        foreach ($components as $key => $value) {
            $value  = trim($value);
            $components[$key]   = $value;
            if ($value == '' || is_null($value)) {
                unset($components[$key]);
            }
        }
        //Reset array indices
        $components = array_values($components);
        return $components;
    }

    /**
     * Returns sanitized path (trimmed, no double separators, etc.)
     * @param string $path
     * @return string
     */
    public function sanitize($path)
    {
        $absolute   = $this->isAbsolute($path);
        $components = $this->getStoragePathComponents($path);
        $separator  = $this->getStoragePathSeparator();
        $sanitized  = implode($separator, $components);
        if ($absolute) {
            $sanitized  = $separator . $sanitized;
        }
        $sanitized  = $this->removeIllegalCharacters($sanitized);
        return  $sanitized;
    }

    /**
     * Returns directory name for the given path
     * If there is no parent directory for the given $path, returns storage path separator
     * @param string $path
     * @return string
     */
    public function dirname($path)
    {
        $components = $this->getStoragePathComponents($path);
        array_pop($components);
        if (count($components) > 0) {
            $absolute   = $this->isAbsolute($path);
            $dir        = $this->buildStoragePath($components, $absolute);
        } else {
            $dir        = $this->getStoragePathSeparator();
        }
        return $dir;
    }

    /**
     * Returns trailing component of the path
     * @param string $path
     * @return string
     */
    public function basename($path)
    {
        $components = $this->getStoragePathComponents($path);
        $basename   = array_pop($components);
        return $basename;
    }

    /**
     * Returns true when the $path denotes an absolute path
     * @param string $path
     * @return boolean
     */
    public function isAbsolute($path)
    {
        $path       = trim($path);
        $firstChar  = substr($path, 0, 1);
        return $firstChar == $this->getStoragePathSeparator();
    }

    /**
     * Returns $path with all illegal characters removed and replaced with replacement character
     * @param string $path
     * @return string
     */
    protected function removeIllegalCharacters($path)
    {
        $cleaned            = '';
        $len                = strlen($path);
        for ($i = 0; $i < $len; $i++) {
            $cleaned    .= (stripos('abcdefghijklmnopqrstuvwxyz0123456789-_/.', $path{$i}) !== false)
                            ? $path[$i] : $this->replacementChar;
        }
        return $cleaned;
    }
}
