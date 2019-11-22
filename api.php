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

//sample incoming
/*
{
"total": "5.00",
"order_number":"123-456",
"currency":"USD",
"user_name":"Satoshi Nakamoto",
"user_email":"Satoshi@Nakamoto.com"
}
*/
// modify the following to meet your requirements, this example takes an incoming json post
// Access the incoming data
$json = file_get_contents('php://input');
// decodes to object
$data = json_decode($json);

//create a request to pass to BitPay
$bitpay_checkout_token = "your api token";
$env = 'test'; // or prod
$config = new BPC_Configuration($bitpay_checkout_token, $env);

//create a class that will contain all the parameters to send to bitpay
$params = new stdClass();

$params->extension_version = "My_Plugin_1.0";
$params->price = $data->total;
$params->currency = $data->currency; //set as needed

//if there is user info, pass it along 
if ($data->user_email):
    $buyerInfo = new stdClass();
    $buyerInfo->name = $data->user_name;
    $buyerInfo->email = $data->user_email;
    $params->buyer = $buyerInfo;
endif;

//if you would like to redirect a user after they make a payment, add it here, with any other GET parametrs
$params->redirectURL = "http://www.myredirecturl.com";
//the notification url (IPN) will need to be configured on your setup to handle incoming data when the status changes (ipn.php )
$params->notificationURL = "http://www.ipnurl.com";
$params->extendedNotifications = true;

//create an item with all of the parameters
$item = new BPC_Item($config, $params);
$invoice = new BPC_Invoice($item);

$invoice->BPC_createInvoice();

// if you would like to view the raw data, use the following
// an example would be if you a mobile device was using this middleware, you would need to send back the following for it to monitor the status
// http://test.bitpay.com/invoices/$decoded_invoice->id  ( or http://www.bitpay.com/invoices/$decoded_invoice->id)
// you would be monitoring $decoded_invoice->status for the paid/confirmed status to change and handle it in-app

/*
$decoded_invoice = $invoice->BPC_getInvoiceRaw();
print_r($decoded_invoice);
*/

//this url is created by BitPay.  You will need to display or redirect so the user can pay the invoice
$invoiceUrl = $invoice->BPC_getInvoiceURL();