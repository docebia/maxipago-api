maxipago-api
============

MaxiPago-API is an application component used for interact with the MaxiPago <http://maxipago.com>

##In this extension, you can do:
    * Credit sale
    * Boleto sale
    * Recurring sale
    * OneClick sale
    * Create profiles

##In this extension, you can't do (well, not yet):
    * Debit sale
    * Refound transaction
    * Capture transaction (need to be done, as soon as possible)

##What's coming?
Well, I want to do something about CronJobs here, using MaxiPago! caputre methods. This would be awesome.

##Instalation
In your config/main.php (or equivalent), put this configurations, remember to change the variables to what you need to.
```php
    return array(
        ...
        'import' => array(
            ...
            'ext.maxipago-api.*',
        ),
        ...
        'components' => array(
            ...
            'maxipago' => array(
                'class' => 'ext.maxipago-api.VFMaxiPago',
                'logging' => true,
                'logfile' => '/var/www/logs/MY_SITE/payment/',
                'debug' => true,
                'enviroment' => 'TEST', // {TEST, LIVE}
                'storeId' => YOUR_STORE_ID,
                'storeKey' => YOUR_STORE_KEY,
                'processorId' => 1, // USE 1 FOR TEST, CONTACT THE MAXIPAGO SUPPORT FOR PRODUCTION ID
            ),
        ...
        )
    );
```

##Example 1
Simple example, to do a credit card sale.

```php
    $data = array(
        'referenceNum' => 'PRODUCT_INTERNAL_REFERENCE',     // (REQUIRED) internal product control string.
        'authentication' => "",                             // (OPTIONAL) - Valid only for Cielo. Please see full documentation for more info
        'ipAddress' => '123.123.123.123',
        'chargeTotal' => 10.00,                             // (REQUIRED) product total amount.
        'numberOfInstallments' => 2,                        // (OPTIONAL) number of installments ['parcelas']
        'chargeInterest' => 'N',                            // (OPTIONAL) - Charge interest flag (Y/N) ("com" e "sem" juros)
    );

    $card = array(
        'number' => '4111111111111111',                     // (REQUIRED) - Full credit card number
        'expMonth' => '07',                                 // (REQUIRED) - Credit card expiration month
        'expYear' => '2020',                                // (REQUIRED) - Credit card expiration year
        'cvvNumber' => '123',                               // (OPTIONAL) - Credit card verification number
    );

    $payment = Yii::app()->maxipago;

    $payment->setBillingData('address', 'Billing address');
    $payment->setBillingData('name', 'Billing Name');
    $payment->setShippingData('address', 'Shipping address');

    $payment->setCard($card);
    $payment->creditSale($data);

    if ($payment->hasFailed())
    {
       echo "Transaction has failed<br>Error message: ".$payment->getMessage();
    }
    else
    {
       echo "Transaction Approved<br>Authorization code: ".$payment->getAuthCode();
    }
```

##Example 2
A little bit more complicated example, that saves the user credit card on the MaxiPago! system. That's very useful to
OneClick buy, the request returns a token, that can be used later to make another sale, without another confirmation.

```php
    $data = array(
        'referenceNum' => 'PRODUCT_INTERNAL_REFERENCE',     // (REQUIRED) internal product control string.
        'authentication' => "",                             // (OPTIONAL) - Valid only for Cielo. Please see full documentation for more info
        'ipAddress' => '123.123.123.123',
        'chargeTotal' => 10.00,                             // (REQUIRED) product total amount.
        'numberOfInstallments' => 2,                        // (OPTIONAL) number of installments ['parcelas']
        'chargeInterest' => 'N',                            // (OPTIONAL) - Charge interest flag (Y/N) ("com" e "sem" juros)
    );

    $card = array(
        'number' => '4111111111111111',                     // (REQUIRED) - Full credit card number
        'expMonth' => '07',                                 // (REQUIRED) - Credit card expiration month
        'expYear' => '2020',                                // (REQUIRED) - Credit card expiration year
        'cvvNumber' => '123',                               // (OPTIONAL) - Credit card verification number
    );

    $customer = array(
        'firstName' => 'Fulano',
        'lastName' => 'de tal'
    );

    $payment = Yii::app()->maxipago;
    $payment->addCustomer($customer);

    $payment->setBillingData('address', 'Billing address');
    $payment->setBillingData('name', 'Billing Name');
    $payment->setShippingData('address', 'Shipping address');

    $payment->setCard($card);

    // there is the trick!
    $payment->setCardData('saveOnFile', 1);
    $payment->setCardData('customerId', $payment->getCustomerID());

    $payment->creditSale($data);

    if ($payment->hasFailed())
    {
       echo "Transaction has failed<br>Error message: ".$payment->getMessage();
    }
    else
    {
       echo "Transaction Approved<br>Authorization code: ".$payment->getAuthCode().' <br /> Token: '.$payment->getToken();
    }
```

##Example 3
Using a token to do a transaction.

```php
    $data = array(
        'referenceNum' => 'PRODUCT_INTERNAL_REFERENCE',     // (REQUIRED) internal product control string.
        'authentication' => "",                             // (OPTIONAL) - Valid only for Cielo. Please see full documentation for more info
        'ipAddress' => '123.123.123.123',
        'chargeTotal' => 10.00,                             // (REQUIRED) product total amount.
        'numberOfInstallments' => 2,                        // (OPTIONAL) number of installments ['parcelas']
        'chargeInterest' => 'N',                            // (OPTIONAL) - Charge interest flag (Y/N) ("com" e "sem" juros)
    );

    $card = array(
        'token' => 'USER_SAVED_TOKEN',                     // (REQUIRED) - token of the credit card
        'customerId' => 'USER_SAVED_ID',
    );

    $payment = Yii::app()->maxipago;

    $payment->setBillingData('address', 'Billing address');
    $payment->setBillingData('name', 'Billing Name');
    $payment->setShippingData('address', 'Shipping address');

    $payment->setCard($card);
    $payment->creditSale($data);

    if ($payment->hasFailed())
    {
       echo "Transaction has failed<br>Error message: ".$payment->getMessage();
    }
    else
    {
       echo "Transaction Approved<br>Authorization code: ".$payment->getAuthCode();
    }
```