# VIES-validation
EU VAT number validation using VIES SOAP API.

If you want to be able to prove to a Tax Administration of a Member State that you have checked a given VAT number at a given time, and obtained a given validation reply, please keep this consultation number ($data->requestIdentifier) in your archives. Please note, however, that the confirmation of the validity of a VAT number and of its attribution to a given taxable person is only ONE of the elements of evidence supporting the VAT exemption of intra-community supplies of goods (article 138, paragraph 1 of Council Directive 2006/112/EC) or the non-application of VAT on a supply of services due to its localisation in another Member State.

# Usage
payloadSimple example
```php
$payloadSimple = new VIESPayload();
$payloadSimple->setSimple('SK', '1111111111'); // countryCode, vatNumber

$request = new VIESRequest($payloadSimple);
if($request->getResult()==VIESRequest::OK) // SOAP request succeeded
{
  $data = $request->getData();
  if($data->valid) // VAT number is valid
  {
    // $data->countryCode
    // $data->vatNumber
    // $data->requestDate
    // $data->valid
    // $data->name
    // $data->address
  }
}
```

payloadApprox example (only requester information supported)
```php
$payloadApprox = new VIESPayload();
$payloadApprox->setApprox('SK', '1111111111', 'SK', '2222222222'); // countryCode, vatNumber, requesterCountryCode, requesterVatNumber

// request example
$request = new VIESRequest($payloadApprox);
if($request->getResult()==VIESRequest::OK) // SOAP request succeeded
{
  $data = $request->getData();
  if($data->valid) // VAT number is valid
  {
    // $data->countryCode
    // $data->vatNumber
    // $data->requestDate
    // $data->valid
    // $data->traderName
    // $data->traderCompanyType
    // $data->traderAddress
    // $data->requestIdentifier - store this
  }
}
```

possible $request->getResult() values
```php
const OK = 'OK';
const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
const INVALID_PAYLOAD = 'INVALID_PAYLOAD';
const INVALID_INPUT = 'INVALID_INPUT';
const MS_UNAVAILABLE = 'MS_UNAVAILABLE'; // member state unavailable
const TIMEOUT = 'TIMEOUT';
const SERVER_BUSY = 'SERVER_BUSY';
```
