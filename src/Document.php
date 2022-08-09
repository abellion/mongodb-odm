<?php

namespace Xenus;

use Iterator;
use ArrayAccess;
use JsonSerializable;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;

class Document implements Iterator, ArrayAccess, JsonSerializable, Serializable, Unserializable
{
    use Concerns\HasCollection;
    use Concerns\HasId;
    use Concerns\HasRelationships;

    use Document\ArrayAccess;
    use Document\ArrayIterator;
    use Document\CamelCaseAccessor;
    use Document\MongoAccess;

    use Document\Serializers\JsonSerializer;
    use Document\Serializers\NativeSerializer;

    protected $withId = false;
    protected $document = [];

    /**
     * Return the document's values for debugging
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->document;
    }

    /**
     * @param array $document The values to put in the document
     */
    public function __construct($document = [])
    {
        if ($this->withId && !isset($document['_id'])) {
            self::setFromSetter('_id', new ObjectID());
        }

        self::fillFromSetter(($document instanceof self) ? $document->document : $document);
    }

    /**
     * Get the value of the given key
     *
     * @param  string $offset  The key
     * @param  mixed  $default The default value to return
     *
     * @return mixed
     */
    public function get(string $offset, $default = null)
    {
        if (isset($this->document[$offset])) {
            return $this->document[$offset];
        }

        return $default;
    }

    /**
     * Get whether the given key exists or not
     *
     * @param  string  $offset The key
     *
     * @return boolean
     */
    public function has(string $offset)
    {
        return array_key_exists($offset, $this->document);
    }

    /**
     * Return a new document only with the specified fields
     *
     * @param  array  $fields The keys to keep
     *
     * @return Xenus\Document
     */
    public function with(array $fields)
    {
        $document = new static();
        $document->document = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $this->document)) {
                $document->document[$field] = $this->document[$field];
            }
        }

        return $document;
    }

    /**
     * Return a new document without the specified fields
     *
     * @param  array  $fields The keys to drop
     *
     * @return Xenus\Document
     */
    public function without(array $fields)
    {
        $document = new static();
        $document->document = [];

        foreach ($this->document as $key => $value) {
            if (!in_array($key, $fields)) {
                $document->document[$key] = $this->document[$key];
            }
        }

        return $document;
    }

    /**
     * Fill the document whith the given values
     *
     * @param  array  $document The values
     *
     * @return self
     */
    public function merge(array $document)
    {
        return self::fillFromSetter($document);
    }

    /**
     * Return the document as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->document;
    }

    /**
     * Get a value from a getter if it exists, or fallback on the internal array
     *
     * @param  string $offset The key
     *
     * @return mixed
     */
    public function getFromGetter(string $offset)
    {
        $getter = $this->getterIze($offset);

        if (method_exists($this, $getter)) {
            return call_user_func([$this, $getter]);
        }

        return self::get($offset);
    }

    /**
     * Set the given value on the offset
     *
     * @param string $offset The key to retrieve the value
     * @param mixed  $value  The value
     *
     * @return self
     */
    public function set(string $offset, $value)
    {
        $this->document[$offset] = $value;

        return $this;
    }

    /**
     * Set the given value through a setter
     *
     * @param string $offset The key to retrieve the value
     * @param mixed  $value  The value
     *
     * @return self
     */
    public function setFromSetter(string $offset, $value)
    {
        $setter = $this->setterIze($offset);

        if (method_exists($this, $setter)) {
            return call_user_func([$this, $setter], $value);
        }

        return self::set($offset, $value);
    }

    /**
     * Fill the document with the given array
     *
     * @param  array  $document The array
     *
     * @return self
     */
    public function fill(array $document)
    {
        foreach ($document as $offset => $value) {
            self::set($offset, $value);
        }

        return $this;
    }

    /**
     * Fill the document, though the setters, with the given array
     *
     * @param  array  $document The array
     *
     * @return self
     */
    public function fillFromSetter(array $document)
    {
        foreach ($document as $offset => $value) {
            self::setFromSetter($offset, $value);
        }

        return $this;
    }
}
