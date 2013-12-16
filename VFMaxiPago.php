<?php
/**
 * MaxiPago-API Yii extension.
 *
 * @author Victor Freitas < victor@crayonstock.com >
 * @company CrayonStock < http://crayonstock.com >
 * @link https://github.com/victorfcm/maxipago-api
 * @package maxipago-api
 *
 * @version 0.1
 *
 * @TODO:
 *  Features
 *      - Debit Sale            <-- Important!
 *      - Delete CreditCard
 *      - Refund                <-- Important!
 *      - Reports               <-- Important!
 *      - Recurring             <-- Important!
 *      - Update Profile
 *
 *  Structure
 *      - Examples
 *      - Documentation
 *      - Put lib files into lib/ folder
 */

/**
 * MaxiPago-API is an application component used for interact with the MaxiPago <http://maxipago.com>
 *
 * You may configure it as below.  Check the public attributes and setter
 * methods of this class for more options.
 * <pre>
 * return array(
 * 	...
 * 	'import => array(
 * 		...
 * 		'ext.maxipago-api.*',
 * 	),
 * 	'components' => array(
 * 		'maxipago' => array(
 * 			'class' => 'ext.maxipago-api.VFMaxiPago',
 * 			'logging' => true,
 *          'logfile' => '/var/www/logs/MY_SITE/payment/',
 * 			'debug' => true,
 *          'enviroment' => 'TEST', // {TEST, LIVE}
 *          'storeId' => YOUR_STORE_ID,
 *          'storeKey' => YOUR_STORE_KEY,
 *          'processorId' => 1, // USE 1 FOR TEST, CONTACT THE MAXIPAGO SUPPORT FOR PRODUCTION ID
 * 		),
 * 		...
 * 	)
 * );
 * </pre>
 *
 * For full usage examples see: /maxipago-api/examples/ (coming soon)
 * Simple example usage:
 * <pre>
 *  $data = array(
 *       'referenceNum' => 'PRODUCT_INTERNAL_REFERENCE', // (REQUIRED) internal product control string.
 *       'authentication' => "", // Optional - Valid only for Cielo. Please see full documentation for more info
 *       'ipAddress' => '123.123.123.123', // Optional
 *       'chargeTotal' => 10.00,  // (REQUIRED) product total amount.
 *       'numberOfInstallments' => 2, // (OPTIONAL) number of installments ['parcelas']
 *       'chargeInterest' => 'N', // Optional - Charge interest flag (Y/N) ("com" e "sem" juros)
 *   );
 *
 *   $card = array(
 *       'number' => '4111111111111111', // (REQUIRED) - Full credit card number
 *       'expMonth' => '07', // REQUIRED - Credit card expiration month
 *       'expYear' => '2020', // REQUIRED - Credit card expiration year
 *       'cvvNumber' => '123', // Optional - Credit card verification number
 *   );
 *
 *  $payment = Yii::app()->maxipago;
 *
 *  $payment->setBillingData('address', 'Billing address');
 *  $payment->setBillingData('name', 'Billing Name');
 *  $payment->setShippingData('address', 'Shipping address');
 *
 *  $payment->setCard($card);
 *
 *  $payment->creditSale($data);
 *
 *  if ($payment->hasFailed())
 *  {
 *      echo "Transaction has failed<br>Error message: ".$payment->getMessage();
 *  }
 *  else
 *  {
 *      echo "Transaction Approved<br>Authorization code: ".$payment->getAuthCode();
 *  }
 * </pre>
 */

class VFMaxiPago extends CApplicationComponent
{

    //////////////////////////////
    //// System var definition
    //////////////////////////////
    /**
     * Instance of maxiPago class.
     * @var maxiPago $maxiInstance
     */
    private $maxiInstance;

    /**
     * If you don`t want to generate log, turn down!
     * @var bool
     */
    public $logging = true;

    /**
     * Logfile path, VERIFY THE PERMISSIONS!
     * @var bool
     */
    public $logfile = 'MY_APP_LOG';

    /**
     * If you don`t want to debug, turn down!
     * @var bool
     */
    public $debug = true;

    /**
     * The enviroment for the application.
     * @values { TEST , LIVE }
     * @var string
     */
    public $enviroment = 'TEST';

    /**
     * Your credentials.
     * @var string
     */
    public $storeId = 'YOUR_STORE_ID';
    public $storeKey = 'YOUR_STORE_KEY';

    /**
     * The processor ID, 1 is for TEST, for production values, contact your MaxiPago Support.
     * @var int
     */
    public $processorId = 1;

    /**
     * Billing information data.
     *
     * Fields:
     *  - name // RECOMMENDED
     *  - address
     *  - address2
     *  - city
     *  - state
     *  - postalcode
     *  - country // Customer country code per ISO 3166-2, (BR, US, EU)
     *  - phone
     *  - email
     *
     * @var array
     */
    private $_billing = array();

    /**
     * Shipping information data.
     *
     * Fields:
     *  - address
     *  - address2
     *  - city
     *  - state
     *  - postalcode
     *  - country // Customer country code per ISO 3166-2, (BR, US, EU)
     *  - phone
     *  - email
     *
     * @var array
     */
    private $_shipping = array();

    /**
     * Customer information data.
     *
     * Fields:
     *  - customerIdExt // REQUIRED default time()
     *  - firstName     // REQUIRED
     *  - lastName      // REQUIRED
     *  - address
     *  - address2
     *  - state
     *  - zip
     *  - country // Customer country code per ISO 3166-2, (BR, US, EU)
     *  - phone
     *  - email
     *
     * @var array
     */
    private $_customer = array();

    /**
     * Boleto information data.
     *
     * Fields:
     *  - processorID       // use 12 for development, in production, contact your support team.
     *  - referenceNum
     *  - chargeTotal
     *  - bname
     *  - number            // use time() as default
     *  - expirationDate    // YYYY-MM-DD format
     *  - instructions      // Instructions to be printed with the boleto. Use ";" to break lines
     *
     * @var array
     */
    private $_boleto = array();

    /**
     * CreditCard information data.
     *
     * Fields:
     *  - number
     *  - expMonth
     *  - expYear
     *  - cvvNumber
     *
     * // Below are the REQUIRED commands to save a card automatically
     *  - saveOnFile    // boolean
     *  - customerId    // integer
     *
     * @var array
     */
    private $_creditCard = array();

    //////////////////////////////
    //// System functions.
    //// Change at your own risk.
    //////////////////////////////
    /**
     * Init the system.
     */
    public function init()
    {
        // initialize the parameters.
        parent::init();

        // set the maxiPago class instance.
        $this->getInstance();

        if($this->logging)
            $this->setLoggable();

        if($this->debug)
            $this->maxiInstance->setDebug();

        $this->maxiInstance->setEnvironment($this->enviroment);
        $this->setCredentials();
    }

    /**
     * Magically gets the MaxiPago instance functions.
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->maxiInstance->$name($arguments);
    }

    /**
     * Get the maxiPago instance.
     * @return maxiPago
     */
    public function getInstance()
    {
        if(!isset($this->maxiInstance))
        {
            $this->maxiInstance = new maxiPago();
        }

        return $this->maxiInstance;
    }

    /**
     * Connect to the MaxiPago! server.
     * @throws CException
     */
    public function setCredentials()
    {
        try
        {
            $this->maxiInstance->setCredentials($this->storeId, $this->storeKey);
        }
        catch(CException $e)
        {
            throw new CException('Invalid credentials '.$e->getFile().' - '.$e->getLine());
        }
    }

    /**
     * Try to write in the logfile, if it works, set loggable.
     * @throws CException
     */
    public function setLoggable()
    {
        if(is_writable($this->logfile))
        {
            $this->maxiInstance->setLogger($this->logfile);
        }
        else
        {
            throw new CException('Logfile is not writable');
        }
    }

    /**
     * Check if the transaction has failed.
     * @return bool
     */
    public function hasFailed()
    {
        return ($this->maxiInstance->isErrorResponse() || $this->maxiInstance->getResponseCode() != 0);
    }

    /**
     * Set the specified field on the billing information.
     * @param $field
     * @param $data
     *
     * @return mixed
     */
    public function setBillingData($field, $data)
    {
        return $this->_billing[$field] = $data;
    }

    /**
     * Set the entire billing information array data.
     * @param array $data
     *
     * @return array
     */
    public function setBilling(Array $data)
    {
        return $this->_billing = $data;
    }

    /**
     * Get the billing data.
     * @return array
     * @throws Exception
     */
    private function getBilling()
    {
        try
        {
            if(!empty($this->_billing))
            {
                $return = array();

                foreach($this->_billing as $field => $data)
                {
                    $field = 'b'.$field;
                    $return[$field] = $data;
                }

                return $return;
            }

            return array();
        }
        catch(CException $e)
        {
            throw new CException('Error when try to get the billing information'.$e->getFile().' - '.$e->getLine());
        }
    }

    /**
     * Set the specified field on the shipping information.
     * @param $field
     * @param $data
     *
     * @return mixed
     */
    public function setShippingData($field, $data)
    {
        return $this->_shipping[$field] = $data;
    }

    /**
     * Set the entire shipping information array data.
     * @param array $data
     *
     * @return array
     */
    public function setShipping(Array $data)
    {
        return $this->_shipping = $data;
    }

    /**
     * Get the shipping data.
     * @return array
     * @throws Exception
     */
    private function getShipping()
    {
        try
        {
            if(!empty($this->_shipping))
            {
                $return = array();

                foreach($this->_shipping as $field => $data)
                {
                    $field = 's'.$field;
                    $return[$field] = $data;
                }

                return $return;
            }

            return array();
        }
        catch(CException $e)
        {
            throw new CException('Error when try to get the shipping information '.$e->getFile().' - '.$e->getLine());
        }
    }

    //////////////////////////////
    //// CreditCard functions
    //////////////////////////////
    /**
     * CreditCard default Sale.
     *
     * Required $data fields:
     *  - referenceNum
     *  - chargeTotal
     *
     * Optional $data fields:
     *  - numberOfInstallments
     *  - chargeInterest
     *  - currencyCode
     *
     * @param array $data
     * @throws Exception
     */
    public function creditSale(Array $data)
    {
        try
        {
            $data = array_merge($data, $this->getShipping());
            $data = array_merge($data, $this->getBilling());
            $data = array_merge($data, $this->getCard());
            $data['processorID'] = $this->processorId;

            $this->maxiInstance->creditCardSale($data);
        }
        catch(CException $e)
        {
            throw new CException('Error on creditSale '.$e->getFile().' - '.$e->getLine());
        }
    }

    /**
     * @link see the parameter $_creditCard for more details
     * @param array $data
     */
    public function setCard(Array $data)
    {
        $this->_creditCard = $data;
    }

    /**
     * @link see the parameter $_creditCard for more details
     * @param string $field
     * @param mixed $data
     */
    public function setCardData($field, $data)
    {
        $this->_creditCard[$field] = $data;
    }

    /**
     * @return array
     */
    public function getCard()
    {
        return $this->_creditCard;
    }

    //////////////////////////////
    //// 'Boleto' functions
    //////////////////////////////
    /**
     * Boleto default Sale.
     * @param array $data
     * @throws Exception
     */
    public function boletoSale(Array $data)
    {
        try
        {
            if(!isset($data['processorID']))
                $data['processorID'] = 12;

            if(!isset($data['number']))
                $data['number'] = time();

            $data = array_merge($data, $this->getShipping());
            $data = array_merge($data, $this->getBilling());

            var_dump($data); die;
            $this->maxiInstance->boletoSale($data);
        }
        catch(CException $e)
        {
            throw new CException('Error on boleto sale '.$e->getFile().' - '.$e->getLine());
        }
    }

    //////////////////////////////
    //// Customer functions
    //////////////////////////////
    /**
     * @param string $field
     * @param mixed $data
     */
    public function setCustomerField($field, $data)
    {
        $this->_customer[$field] = $data;
    }

    /**
     * @param array $data
     *
     * @return string
     * @throws CException
     */
    public function addCustomer(Array $data)
    {
        try
        {
            if(!isset($data['customerIdExt']))
                $data['customerIdExt'] = time();

            $this->_customer = $data;

            $this->maxiInstance->addProfile($this->_customer);
        }
        catch(CException $e)
        {
            throw new CException('Error on add costumer '.$e->getFile().' - '.$e->getLine());
        }

        return $this->getCustomerID();
    }

    /**
     * Required values:
     *  - creditCardNumber
     *  - expirationMonth
     *  - expirationYear
     *
     * @param array $creditCard
     * @return String creditCardToken
     * @throws CException
     */
    public function addCreditCard(Array $creditCard)
    {
        foreach($this->getBilling() as $field => $data)
        {
            $__d = explode('b', $field);
            $__d = ucfirst($__d[1]);
            $__d = 'billing'.$__d;

            $creditCard[$__d] = $data;
        }

        $creditCard['customerId'] = $this->getCustomerID();

        try
        {
            $this->maxiInstance->addCreditCard($creditCard);
        }
        catch(CException $e)
        {
            throw new CException('Error on add credit card '.$e->getFile().' - '.$e->getLine());
        }

        return $this->maxiInstance->getToken();
    }

    //////////////////////////////
    ///// Recurring functions
    //////////////////////////////
    /**
     * @param String $orderId
     * @throws CException
     */
    public function cancelRecurring($orderId)
    {
        try
        {
            $this->maxiInstance->cancelRecurring(array('orderID' => $orderId));
        }
        catch(CException $e)
        {
            throw new CException('Error on remove recurring '.$e->getFile().' - '.$e->getLine());
        }
    }

    /**
     * Required Values:
     *  - startDate         // YYYY-MM-DD
     *  - frequency         // Payment frequency
     *  - period            // Interval of payment: 'daily', 'weekly', 'monthly'
     *  - installments      // Total number of payments before the order is completed
     *  - failureThreshold  // Number of declines before email notification
     *
     * @param array $data
     * @throws CException
     */
    public function createRecurring(Array $data)
    {
        $data = array_merge($data, $this->getShipping());
        $data = array_merge($data, $this->getBilling());
        $data = array_merge($data, $this->getCard());
        $data['processorID'] = $this->processorId;

        try
        {
            $this->maxiInstance->createRecurring($data);
        }
        catch(CException $e)
        {
            throw new CException('Error on create recurring '.$e->getFile().' - '.$e->getLine());
        }
    }
}