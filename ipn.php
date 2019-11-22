<?php
#autoload the classes
function BPC_autoloader($class)
{
    if (strpos($class, 'BPC_') !== false):
        if (!class_exists('BitPayLib/' . $class, false)):
            #doesnt exist so include it
            include 'BitPayLib/' . $class . '.php';
        endif;
    endif;
}
spl_autoload_register('BPC_autoloader');

//sample incoming ipn
/*
{
    "event": {
        "code": 1005,
        "name": "invoice_confirmed"
    },
    "data": {
        "id": "123456",
        "orderId": "1a9e2efa-555b-433f-8780-55eeae1f0f45",
        "url": "https:/test.bitpay.com/invoice?id=123456",
        "status": "confirmed",
        "btcPrice": "0.002600",
        "price": 10.5,
        "currency": "USD",
        "invoiceTime": 1507729941907,
        "expirationTime": 1507730841907,
        "currentTime": 1507730718246,
        "btcPaid": "0.002600",
        "btcDue": "0.000000",
        "rate": 4037.92,
        "exceptionStatus": false,
        "buyerFields": {
            "buyerName": "Satoshi Nakamoto",
            "buyerAddress1": "140 E 46th St",
            "buyerAddress2": "",
            "buyerCountry": "US",
            "buyerEmail": "test@email.com",
            "buyerPhone": "555-0042",
            "buyerNotify": true
        }
    }
}
*/
// modify the following to meet your requirements, this example takes an incoming json post
// Access the incoming data
$json = file_get_contents('php://input');
// decodes to object
$data = json_decode($json);
$invoice_data = $data->data;
#print_r($invoice_data);

$invoiceID = $invoice_data->id;
$ipn_status = $invoice_data->status;

// you should verify the status of the IPN by sending a GET request to BitPay
// http://test.bitpay.com/invoices/$invoiceID (or http://www.bitpay.com/invoices/$invoiceID)

//create a basic object to send to BitPay
$bitpay_checkout_token = "your api token";
$env = 'test'; // or prod
$config = new BPC_Configuration($bitpay_checkout_token, $env);

//create a class that will contain all the parameters to send to bitpay
$params = new stdClass();
$params->invoiceID = $invoiceID;

$item = new BPC_Item($config, $params);

$invoice = new BPC_Invoice($item); //this creates the invoice with all of the config params
$orderStatus = json_decode($invoice->BPC_checkInvoiceStatus($invoiceID));

$invoiceData = $orderStatus->data;
$invoiceStatus = $invoiceData->status;
// here is where you will need to do a comparison, and update your system
// this compares the incoming IPN status to the actual status from the GET, and if they match, then proceed
//example

if($ipn_status == $invoiceStatus):
switch ($invoiceStatus){
    case 'paid':
        //invoice has been paid, not confirmed.  
    break;
    case 'confirmed':
        //invoice has been confirmed via 6 confirmation.  If shipping physical goods, this status should be used  
    break;
    case 'completed':
        //invoice has been completed 
    break;
    case 'expired':
        //invoice has expired, user most likely never paid
    break;

}
endif;


