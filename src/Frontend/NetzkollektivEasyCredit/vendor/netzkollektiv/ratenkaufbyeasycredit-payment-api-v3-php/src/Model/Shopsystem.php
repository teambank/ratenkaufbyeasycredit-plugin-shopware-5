<?php
/**
 * Shopsystem
 *
 * PHP version 7.3
 *
 * @category Class
 * @package  Teambank\RatenkaufByEasyCreditApiV3
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Transaction-V3 API Definition
 *
 * Transaction-V3 API for Merchant Portal
 *
 * The version of the OpenAPI document: V3.84.0
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.0.0-SNAPSHOT
 */
/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Teambank\RatenkaufByEasyCreditApiV3\Model;

use \ArrayAccess;
use \Teambank\RatenkaufByEasyCreditApiV3\ObjectSerializer;

/**
 * Shopsystem Class Doc Comment
 *
 * @category Class
 * @description technischeShopparameter
 * @package  Teambank\RatenkaufByEasyCreditApiV3
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class Shopsystem implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'Shopsystem';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'shopSystemManufacturer' => 'string',
        'shopSystemModuleVersion' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'shopSystemManufacturer' => null,
        'shopSystemModuleVersion' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'shopSystemManufacturer' => 'shopSystemManufacturer',
        'shopSystemModuleVersion' => 'shopSystemModuleVersion'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'shopSystemManufacturer' => 'setShopSystemManufacturer',
        'shopSystemModuleVersion' => 'setShopSystemModuleVersion'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'shopSystemManufacturer' => 'getShopSystemManufacturer',
        'shopSystemModuleVersion' => 'getShopSystemModuleVersion'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }


    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['shopSystemManufacturer'] = $data['shopSystemManufacturer'] ?? null;
        $this->container['shopSystemModuleVersion'] = $data['shopSystemModuleVersion'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if (!is_null($this->container['shopSystemManufacturer']) && (mb_strlen($this->container['shopSystemManufacturer']) > 255)) {
            $invalidProperties[] = "invalid value for 'shopSystemManufacturer', the character length must be smaller than or equal to 255.";
        }

        if (!is_null($this->container['shopSystemModuleVersion']) && (mb_strlen($this->container['shopSystemModuleVersion']) > 255)) {
            $invalidProperties[] = "invalid value for 'shopSystemModuleVersion', the character length must be smaller than or equal to 255.";
        }

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets shopSystemManufacturer
     *
     * @return string|null
     */
    public function getShopSystemManufacturer()
    {
        return $this->container['shopSystemManufacturer'];
    }

    /**
     * Sets shopSystemManufacturer
     *
     * @param string|null $shopSystemManufacturer Shop system manufacturer
     *
     * @return self
     */
    public function setShopSystemManufacturer($shopSystemManufacturer)
    {
        if (!is_null($shopSystemManufacturer) && (mb_strlen($shopSystemManufacturer) > 255)) {
            throw new \InvalidArgumentException('invalid length for $shopSystemManufacturer when calling Shopsystem., must be smaller than or equal to 255.');
        }

        $this->container['shopSystemManufacturer'] = $shopSystemManufacturer;

        return $this;
    }

    /**
     * Gets shopSystemModuleVersion
     *
     * @return string|null
     */
    public function getShopSystemModuleVersion()
    {
        return $this->container['shopSystemModuleVersion'];
    }

    /**
     * Sets shopSystemModuleVersion
     *
     * @param string|null $shopSystemModuleVersion Shop system module version
     *
     * @return self
     */
    public function setShopSystemModuleVersion($shopSystemModuleVersion)
    {
        if (!is_null($shopSystemModuleVersion) && (mb_strlen($shopSystemModuleVersion) > 255)) {
            throw new \InvalidArgumentException('invalid length for $shopSystemModuleVersion when calling Shopsystem., must be smaller than or equal to 255.');
        }

        $this->container['shopSystemModuleVersion'] = $shopSystemModuleVersion;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}

