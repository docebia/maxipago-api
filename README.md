maxipago-api
============

MaxiPago! and Yii Framework integration.


MaxiPago-API is an application component used for interact with the MaxiPago <http://maxipago.com>

You may configure it as below.  Check the public attributes and setter
methods of this class for more options.

##Instalation
```php
    return array(
        'import' => array(
            'ext.maxipago-api.*',
        ),
        'components' => array(
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
        )
    );
```

##Example
```php
    $data = array(
        'referenceNum' => 'PRODUCT_INTERNAL_REFERENCE', // (REQUIRED) internal product control string.
        'authentication' => "", // Optional - Valid only for Cielo. Please see full documentation for more info
        'ipAddress' => '123.123.123.123', // Optional
        'chargeTotal' => 10.00,  // (REQUIRED) product total amount.
        'numberOfInstallments' => 2, // (OPTIONAL) number of installments ['parcelas']
        'chargeInterest' => 'N', // Optional - Charge interest flag (Y/N) ("com" e "sem" juros)
    );

    $card = array(
        'number' => '4111111111111111', // (REQUIRED) - Full credit card number
        'expMonth' => '07', // REQUIRED - Credit card expiration month
        'expYear' => '2020', // REQUIRED - Credit card expiration year
        'cvvNumber' => '123', // Optional - Credit card verification number
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