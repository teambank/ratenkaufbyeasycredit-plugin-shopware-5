<?php
/**
 * TransactionUpdate
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
 * OpenAPI Generator version: 5.4.0-SNAPSHOT
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
 * TransactionUpdate Class Doc Comment
 *
 * @category Class
 * @package  Teambank\RatenkaufByEasyCreditApiV3
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class TransactionUpdate implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'TransactionUpdate';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'orderValue' => 'float',
        'numberOfProductsInShoppingCart' => 'int',
        'orderId' => 'string',
        'shoppingCartInformation' => '\Teambank\RatenkaufByEasyCreditApiV3\Model\ShoppingCartInformationItem[]',
        'financingTerm' => 'int',
        'contact' => '\Teambank\RatenkaufByEasyCreditApiV3\Model\Contact',
        'bank' => '\Teambank\RatenkaufByEasyCreditApiV3\Model\Bank',
        'customerRelationship' => '\Teambank\RatenkaufByEasyCreditApiV3\Model\CustomerRelationship'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'orderValue' => null,
        'numberOfProductsInShoppingCart' => null,
        'orderId' => null,
        'shoppingCartInformation' => null,
        'financingTerm' => null,
        'contact' => null,
        'bank' => null,
        'customerRelationship' => null
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
        'orderValue' => 'orderValue',
        'numberOfProductsInShoppingCart' => 'numberOfProductsInShoppingCart',
        'orderId' => 'orderId',
        'shoppingCartInformation' => 'shoppingCartInformation',
        'financingTerm' => 'financingTerm',
        'contact' => 'contact',
        'bank' => 'bank',
        'customerRelationship' => 'customerRelationship'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'orderValue' => 'setOrderValue',
        'numberOfProductsInShoppingCart' => 'setNumberOfProductsInShoppingCart',
        'orderId' => 'setOrderId',
        'shoppingCartInformation' => 'setShoppingCartInformation',
        'financingTerm' => 'setFinancingTerm',
        'contact' => 'setContact',
        'bank' => 'setBank',
        'customerRelationship' => 'setCustomerRelationship'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'orderValue' => 'getOrderValue',
        'numberOfProductsInShoppingCart' => 'getNumberOfProductsInShoppingCart',
        'orderId' => 'getOrderId',
        'shoppingCartInformation' => 'getShoppingCartInformation',
        'financingTerm' => 'getFinancingTerm',
        'contact' => 'getContact',
        'bank' => 'getBank',
        'customerRelationship' => 'getCustomerRelationship'
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
        $this->container['orderValue'] = $data['orderValue'] ?? null;
        $this->container['numberOfProductsInShoppingCart'] = $data['numberOfProductsInShoppingCart'] ?? null;
        $this->container['orderId'] = $data['orderId'] ?? null;
        $this->container['shoppingCartInformation'] = $data['shoppingCartInformation'] ?? null;
        $this->container['financingTerm'] = $data['financingTerm'] ?? null;
        $this->container['contact'] = $data['contact'] ?? null;
        $this->container['bank'] = $data['bank'] ?? null;
        $this->container['customerRelationship'] = $data['customerRelationship'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if (!is_null($this->container['orderValue']) && ($this->container['orderValue'] > 10000)) {
            $invalidProperties[] = "invalid value for 'orderValue', must be smaller than or equal to 10000.";
        }

        if (!is_null($this->container['orderValue']) && ($this->container['orderValue'] < 199)) {
            $invalidProperties[] = "invalid value for 'orderValue', must be bigger than or equal to 199.";
        }

        if (!is_null($this->container['orderId']) && (mb_strlen($this->container['orderId']) > 50)) {
            $invalidProperties[] = "invalid value for 'orderId', the character length must be smaller than or equal to 50.";
        }

        if (!is_null($this->container['orderId']) && !preg_match("/[a-zA-Z0-9\\.:\\-_\/]*/", $this->container['orderId'])) {
            $invalidProperties[] = "invalid value for 'orderId', must be conform to the pattern /[a-zA-Z0-9\\.:\\-_\/]*/.";
        }

        if (!is_null($this->container['shoppingCartInformation']) && (count($this->container['shoppingCartInformation']) < 1)) {
            $invalidProperties[] = "invalid value for 'shoppingCartInformation', number of items must be greater than or equal to 1.";
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
     * Gets orderValue
     *
     * @return float|null
     */
    public function getOrderValue()
    {
        return $this->container['orderValue'];
    }

    /**
     * Sets orderValue
     *
     * @param float|null $orderValue Amount in €
     *
     * @return self
     */
    public function setOrderValue($orderValue)
    {

        if (!is_null($orderValue) && ($orderValue > 10000)) {
            throw new \InvalidArgumentException('invalid value for $orderValue when calling TransactionUpdate., must be smaller than or equal to 10000.');
        }
        if (!is_null($orderValue) && ($orderValue < 199)) {
            throw new \InvalidArgumentException('invalid value for $orderValue when calling TransactionUpdate., must be bigger than or equal to 199.');
        }

        $this->container['orderValue'] = $orderValue;

        return $this;
    }

    /**
     * Gets numberOfProductsInShoppingCart
     *
     * @return int|null
     */
    public function getNumberOfProductsInShoppingCart()
    {
        return $this->container['numberOfProductsInShoppingCart'];
    }

    /**
     * Sets numberOfProductsInShoppingCart
     *
     * @param int|null $numberOfProductsInShoppingCart numberOfProductsInShoppingCart
     *
     * @return self
     */
    public function setNumberOfProductsInShoppingCart($numberOfProductsInShoppingCart)
    {
        $this->container['numberOfProductsInShoppingCart'] = $numberOfProductsInShoppingCart;

        return $this;
    }

    /**
     * Gets orderId
     *
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->container['orderId'];
    }

    /**
     * Sets orderId
     *
     * @param string|null $orderId Shop transaction identifier (allows the shop to store its own reference for the transaction)
     *
     * @return self
     */
    public function setOrderId($orderId)
    {
        if (!is_null($orderId) && (mb_strlen($orderId) > 50)) {
            throw new \InvalidArgumentException('invalid length for $orderId when calling TransactionUpdate., must be smaller than or equal to 50.');
        }
        if (!is_null($orderId) && (!preg_match("/[a-zA-Z0-9\\.:\\-_\/]*/", $orderId))) {
            throw new \InvalidArgumentException("invalid value for $orderId when calling TransactionUpdate., must conform to the pattern /[a-zA-Z0-9\\.:\\-_\/]*/.");
        }

        $this->container['orderId'] = $orderId;

        return $this;
    }

    /**
     * Gets shoppingCartInformation
     *
     * @return \Teambank\RatenkaufByEasyCreditApiV3\Model\ShoppingCartInformationItem[]|null
     */
    public function getShoppingCartInformation()
    {
        return $this->container['shoppingCartInformation'];
    }

    /**
     * Sets shoppingCartInformation
     *
     * @param \Teambank\RatenkaufByEasyCreditApiV3\Model\ShoppingCartInformationItem[]|null $shoppingCartInformation shoppingCartInformation
     *
     * @return self
     */
    public function setShoppingCartInformation($shoppingCartInformation)
    {


        if (!is_null($shoppingCartInformation) && (count($shoppingCartInformation) < 1)) {
            throw new \InvalidArgumentException('invalid length for $shoppingCartInformation when calling TransactionUpdate., number of items must be greater than or equal to 1.');
        }
        $this->container['shoppingCartInformation'] = $shoppingCartInformation;

        return $this;
    }

    /**
     * Gets financingTerm
     *
     * @return int|null
     */
    public function getFinancingTerm()
    {
        return $this->container['financingTerm'];
    }

    /**
     * Sets financingTerm
     *
     * @param int|null $financingTerm ' Duration in months, depending on individual shop conditions and order value (please check your ratenkauf widget). Will be set to default value if not available. '
     *
     * @return self
     */
    public function setFinancingTerm($financingTerm)
    {
        $this->container['financingTerm'] = $financingTerm;

        return $this;
    }

    /**
     * Gets contact
     *
     * @return \Teambank\RatenkaufByEasyCreditApiV3\Model\Contact|null
     */
    public function getContact()
    {
        return $this->container['contact'];
    }

    /**
     * Sets contact
     *
     * @param \Teambank\RatenkaufByEasyCreditApiV3\Model\Contact|null $contact contact
     *
     * @return self
     */
    public function setContact($contact)
    {
        $this->container['contact'] = $contact;

        return $this;
    }

    /**
     * Gets bank
     *
     * @return \Teambank\RatenkaufByEasyCreditApiV3\Model\Bank|null
     */
    public function getBank()
    {
        return $this->container['bank'];
    }

    /**
     * Sets bank
     *
     * @param \Teambank\RatenkaufByEasyCreditApiV3\Model\Bank|null $bank bank
     *
     * @return self
     */
    public function setBank($bank)
    {
        $this->container['bank'] = $bank;

        return $this;
    }

    /**
     * Gets customerRelationship
     *
     * @return \Teambank\RatenkaufByEasyCreditApiV3\Model\CustomerRelationship|null
     */
    public function getCustomerRelationship()
    {
        return $this->container['customerRelationship'];
    }

    /**
     * Sets customerRelationship
     *
     * @param \Teambank\RatenkaufByEasyCreditApiV3\Model\CustomerRelationship|null $customerRelationship customerRelationship
     *
     * @return self
     */
    public function setCustomerRelationship($customerRelationship)
    {
        $this->container['customerRelationship'] = $customerRelationship;

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


