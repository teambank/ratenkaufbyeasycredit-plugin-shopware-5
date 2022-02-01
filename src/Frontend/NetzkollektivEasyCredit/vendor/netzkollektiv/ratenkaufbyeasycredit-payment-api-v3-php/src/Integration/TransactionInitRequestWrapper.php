<?php
namespace Teambank\RatenkaufByEasyCreditApiV3\Integration;

class TransactionInitRequestWrapper implements \ArrayAccess, \JsonSerializable
{
    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['transactionInitRequest'] = $data['transactionInitRequest'] ?? null;
        $this->container['invoiceFirstName'] = $data['invoiceFirstName'] ?? null;
        $this->container['invoiceLastName'] = $data['invoiceLastName'] ?? null;
        $this->container['company'] = $data['company'] ?? null;
    }

    /**
     * Sets transactionInitRequest
     *
     * @param string|null $transactionInitRequest invoice address firstname
     *
     * @return self
     */
    public function setTransactionInitRequest(TransactionInitRequest $transactionInitRequest)
    {
        $this->container['transactionInitRequest'] = $transactionInitRequest;

        return $this;
    }

    /**
     * Gets transactionInitRequest
     *
     * @return string|null
     */
    public function getTransactionInitRequest()
    {
        return $this->container['transactionInitRequest'];
    }

    /**
     * Gets transactionInitRequest
     *
     * @return string|null
     */
    public function getInvoiceFirstName()
    {
        return $this->container['invoiceFirstName'];
    }

    /**
     * Sets invoiceFirstName
     *
     * @param string|null $invoiceFirstName invoice address firstname
     *
     * @return self
     */
    public function setInvoiceFirstName($invoiceFirstName)
    {
        $this->container['invoiceFirstName'] = $invoiceFirstName;

        return $this;
    }

    /**
     * Gets invoiceLastName
     *
     * @return string|null
     */
    public function getInvoiceLastName()
    {
        return $this->container['invoiceLastName'];
    }

    /**
     * Sets invoiceLastName
     *
     * @param string|null $invoiceLastName invoice address lastname
     *
     * @return self
     */
    public function setInvoiceLastName($invoiceLastName)
    {
        $this->container['invoiceLastName'] = $invoiceLastName;

        return $this;
    }

    /**
     * Gets company
     *
     * @return string|null
     */
    public function getCompany()
    {
        return $this->container['company'];
    }

    /**
     * Sets company
     *
     * @param string|null $company invoice address lastname
     *
     * @return self
     */
    public function setCompany($company)
    {
        $this->container['company'] = $company;

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
}