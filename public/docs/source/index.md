---
title: API Reference

language_tabs:
- bash
- javascript

includes:

search: true

toc_footers:
- <a href='http://github.com/mpociot/documentarian'>Documentation Powered by Documentarian</a>
---
<!-- START_INFO -->
# Info

Welcome to the generated API reference.
[Get Postman Collection](http://etupay.dev/docs/collection.json)

<!-- END_INFO -->

#Transaction management
APIs for managing transactions
<!-- START_da1b9a43a9a92a6dbfcbc69b4d7ac89f -->
## Update transaction parameters

> Example request:

```bash
curl -X PUT "http://etupay.dev/api/v1/transaction/1/selfUpdate" \
    -H "Content-Type: application/json" \
    -d '{"firstname":"molestiae","lastname":"ex","client_mail":"laborum"}'

```
```php

$client = new \GuzzleHttp\Client();
$response = $client->put("api/v1/transaction/1/selfUpdate", [
    'headers' => [
            "Content-Type" => "application/json",
        ],
    'json' => [
            "firstname" => "molestiae",
            "lastname" => "ex",
            "client_mail" => "laborum",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```

```javascript
const url = new URL("http://etupay.dev/api/v1/transaction/1/selfUpdate");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "firstname": "molestiae",
    "lastname": "ex",
    "client_mail": "laborum"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`PUT api/v1/transaction/{InitialisedTransactionUUID}/selfUpdate`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    firstname | string |  required  | Transaction user firstname
    lastname | string |  required  | Transaction user lastname
    client_mail | string |  required  | Transaction user email

<!-- END_da1b9a43a9a92a6dbfcbc69b4d7ac89f -->


