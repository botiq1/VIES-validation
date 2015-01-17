<?php

class VIESPayload
{
  protected $state;
  protected $errors;
  protected $method;

  protected $countryCode;
  protected $vatNumber;
  protected $requesterCountryCode;
  protected $requesterVatNumber;

  public function __construct()
  {
    $this->reset();
  }

  protected function reset()
  {
    $this->state = false;
    $this->errors = array();
    $this->method = '';

    $this->countryCode = null;
    $this->vatNumber = null;
    $this->requesterCountryCode = null;
    $this->requesterVatNumber = null;
  }

  public function setSimple($countryCode, $vatNumber)
  {
    $this->reset();
    $this->state = true;

    $countryCode = strtoupper($countryCode);

    if(!$this->checkCountryCode($countryCode))
    {
      $this->errors[] = 'Invalid countryCode';
      $this->state = false;
    }

    if(!$this->checkVatNumber($vatNumber))
    {
      $this->errors[] = 'Invalid vatNumber';
      $this->state = false;
    }

    if($this->state)
    {
      $this->countryCode = $countryCode;
      $this->vatNumber = $vatNumber;

      $this->method = 'checkVat';
    }
  }

  public function setApprox($countryCode, $vatNumber, $requesterCountryCode, $requesterVatNumber)
  {
    $this->setSimple($countryCode, $vatNumber);

    $requesterCountryCode = strtoupper($requesterCountryCode);

    if(!$this->checkCountryCode($requesterCountryCode))
    {
      $this->errors[] = 'Invalid requesterCountryCode';
      $this->state = false;
    }

    if(!$this->checkVatNumber($requesterVatNumber))
    {
      $this->errors[] = 'Invalid requesterVatNumber';
      $this->state = false;
    }

    if($this->state)
    {
      $this->requesterCountryCode = $requesterCountryCode;
      $this->requesterVatNumber = $requesterVatNumber;

      $this->method = 'checkVatApprox';
    }
  }

  public function getMethod()
  {
    return $this->method;
  }

  public function isValid()
  {
    if(!$this->state)
      return $this->errors;

    return $this->state;
  }

  public function toArray()
  {
    if(!$this->state)
      return $this->errors;

    $array = array(
      'countryCode' => $this->countryCode,
      'vatNumber' => $this->vatNumber
    );

    if(!is_null($this->requesterCountryCode) && !is_null($this->requesterVatNumber))
    {
      $array['requesterCountryCode'] = $this->requesterCountryCode;
      $array['requesterVatNumber'] = $this->requesterVatNumber;
    }

    return $array;
  }

  public function checkCountryCode($countryCode)
  {
    if(!preg_match('/^[A-Z]{2}$/', $countryCode))
      return false;
    return true;
  }

  public function checkVatNumber($vatNumber)
  {
    if(!preg_match('/^[0-9A-Za-z\+\*\.]{2,12}$/', $vatNumber))
      return false;
    return true;
  }

}

class VIESRequest
{
  const service_wsdl = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

  const OK = 'OK';
  const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
  const INVALID_PAYLOAD = 'INVALID_PAYLOAD';
  const INVALID_INPUT = 'INVALID_INPUT';
  const MS_UNAVAILABLE = 'MS_UNAVAILABLE'; // member state unavailable
  const TIMEOUT = 'TIMEOUT';
  const SERVER_BUSY = 'SERVER_BUSY';

  protected $result = false;
  protected $data = null;

  function __construct($payload)
  {
    if(!($payload instanceof VIESPayload) || $payload->isValid()!==true)
    {
      $this->result = self::INVALID_PAYLOAD;
      return;
    }

    try
    {
      $soap = new SoapClient(self::service_wsdl);
      $this->data = $soap->{$payload->getMethod()}($payload->toArray());
      $this->result = self::OK;
    }
    catch(SoapFault $e)
    {
      $this->result = $e->getMessage();
    }
  }

  function getResult()
  {
    return $this->result;
  }

  function getData()
  {
    return $this->data;
  }
}
