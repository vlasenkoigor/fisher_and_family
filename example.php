<?php
require_once('../PPBootStrap.php');
pg_connect("host=***.***.***.*** port=5432 dbname=*** user=*** password=****");

/*Some tests on fgtest database*/
//echo Utils::getAccountByName('Flames Group SIA_EUR'); //32
//echo "\n";
//echo Utils::getContractorByName('Test Test'); //15
//echo "\n";
//echo Utils::getClientByAccount('elecrow100@gmail.com'); //380
//echo "\n";
//echo Utils::getCategoryByClient( Utils::getClientByAccount('elecrow100@gmail.com') );//182
//echo "\n";
//var_dump(Utils::getChain(182)); //182,4
//echo "\n";


/* `Start Date` - The earliest transaction date at which to start the
search.
*/

//start finding transactions
Paypal_erp::getTransactions();

//start process transactions
Paypal_erp::ProcessTransactions();


Class TransactionErp {
    public $id;
    public $time;
    public $type;
    public $payer;
    public $payer_name;
    public $transaction_id;
    public $status;
    public $gross_amount;
    public $fee_amount;
    public $net_amount;
    public $gross_currency;
    public $fee_currency;
    public $net_currency;
    public $parsed;

    public function save() {
        $q = "INSERT INTO finance.payment_transaction_paypal_classic (time, type, payer, payer_name, transaction_id, status, gross_amount, fee_amount, net_amount, gross_currency, fee_currency, net_currency, parsed)
        VALUES ('$this->time', '$this->type', '$this->payer', '$this->payer_name', '$this->transaction_id', '$this->status', $this->gross_amount, $this->fee_amount, $this->net_amount, '$this->gross_currency', '$this->fee_currency', '$this->net_currency', false)";
        pg_query($q);
    }

}

class Payment
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $account_id;

    /**
     *
     * @var integer
     */
    public $account_term_id;

    /**
     *
     * @var integer
     */
    public $client_id;

    /**
     *
     * @var integer
     */
    public $client_term_id;

    /**
     *
     * @var integer
     */
    public $user_id;

    /**
     *
     * @var double
     */
    public $amount;

    /**
     *
     * @var string
     */
    public $client_name;

    /**
     *
     * @var string
     */
    public $comment;

    /**
     *
     * @var string
     */
    public $add_date;

    /**
     *
     * @var string
     */
    public $bank_detail;
    /**
     *
     * @var integer
     */
    public $type;

    public $rate;

    public $sys_cur_amount;

    public $reference_number;

    public $is_salary;

    public $date_for;


    public function save()
    {
        $q = "INSERT INTO finance.payment
              (account_id, account_term_id, client_id, client_term_id, user_id, amount, comment, add_date, bank_detail, type, rate, sys_cur_amount, reference_number, client_account_id, date_for, is_salary)
              VALUES
              ($this->account_id, $this->account_term_id, $this->client_id, $this->client_term_id, $this->user_id,
              $this->amount, '$this->comment,', '$this->add_date', '$this->bank_detail', $this->type, $this->rate, $this->sys_cur_amount, NULL, NULL, '$this->add_date', false)
              RETURNING id";

        $res = pg_query($q);
        $res = pg_fetch_assoc($res);
        return $res['id'];
    }
}

Class Utils {


    public static function getContractorByName($name) {
        $q = "SELECT id FROM core.contractor WHERE name = '$name' LIMIT 1";
        $res = pg_query($q);
        if (pg_num_rows($res) == 1){
            $res = pg_fetch_assoc($res);
            return $res['id'];
        } else {
            return 0;
        }
    }

    public static function getAccountByName($name) {
        $q = "SELECT id FROM finance.account WHERE name = '$name' LIMIT 1";
        $res = pg_query($q);
        if (pg_num_rows($res) == 1){
            $res = pg_fetch_assoc($res);
            return $res['id'];
        } else {
            return 0;
        }
    }

    public static function getClientByName($name) {
        $q = "SELECT id FROM core.client WHERE name = '$name' LIMIT 1";
        $res = pg_query($q);
        if (pg_num_rows($res) == 1){
            $res = pg_fetch_assoc($res);
            return $res['id'];
        } else {
            return 0;
        }
    }

    public static function getClientByAccount($name) {
        $q = "SELECT c.id
                FROM core.client c
                LEFT JOIN core.client_account ca ON ca.client_id = c.id
                WHERE ca.number = '$name'
                LIMIT 1";
        $res = pg_query($q);
        if (pg_num_rows($res) == 1){
            $res = pg_fetch_assoc($res);
            return $res['id'];
        } else {
            return 0;
        }
    }

    public static function getCategoryByClient($id) {
        $q = "SELECT cat.id
                FROM core.client c
                LEFT JOIN finance.category cat ON cat.id = c.category_id
                WHERE c.id = $id
                LIMIT 1";
        $res = pg_query($q);
        if (pg_num_rows($res) == 1){
            $res = pg_fetch_assoc($res);
            return $res['id'];
        } else {
            return 0;
        }
    }

    public static function getChain($id) {
        $a = array();

        $c = self::getCategory($id);

        do {
            $a[] = $c['id'];
            $c = self::getCategory($c['parent_category_id']);
            if ($c === false) {
                break;
            }
        } while(true);


        return $a;
    }

    public static function getCategory($id){
        $q = "SELECT * FROM finance.category WHERE id = $id LIMIT 1";
        $res = pg_query($q);
        if (pg_num_rows($res) == 1) {
            return  pg_fetch_assoc($res);
        } else {
            return false;
        }
    }

    public static function DateConvert($timestamp){
        //2014-11-12T13:14:45Z
        return Date('Y-m-d', $timestamp)."T".Date('H:i:s')."Z";
    }

    public static function markTransactionAsParsed($id){
        $q = "UPDATE finance.payment_transaction_paypal_classic SET parsed = TRUE WHERE id = ".$id;
        pg_query($q);
    }
    public static function linkCategories($payment_id, $categories){
        foreach ($categories as $c) {
            $q = "INSERT INTO finance.category_payment
                  (category_id, payment_id, amount, chain)
                  VALUES
                  ($c,$payment_id, 0, FALSE)";
            pg_query($q);
        }
    }
}

Class Paypal_erp {

    public static function getTransactions(){

        //last parse 14-11-2014
        // $start_date = mktime(0,0,0,10,1,2014);
        $start_date = time()-(60*60*24);
        $interval = (60*60*24);

        do {
            echo "Parsing for ".Date("Y-m-d");
            $transactionSearchRequest = new TransactionSearchRequestType();
            $transactionSearchRequest->StartDate = Utils::DateConvert($start_date);
            $transactionSearchRequest->EndDate =  Utils::DateConvert($start_date + $interval);
            $tranSearchReq = new TransactionSearchReq();
            $tranSearchReq->TransactionSearchRequest = $transactionSearchRequest;

            /*
             * 		 ## Creating service wrapper object
            Creating service wrapper object to make API call and loading
            Configuration::getAcctAndConfig() returns array that contains credential and config parameters
            */

            $paypalService = new PayPalAPIInterfaceServiceService(Configuration::getAcctAndConfig());

            try {
                /* wrap API method calls on the service object with a try catch */
                $transactionSearchResponse = $paypalService->TransactionSearch($tranSearchReq);
            } catch (Exception $ex) {
                include_once("../Error.php");
                exit;
            }

            if(isset($transactionSearchResponse)) {
                echo " Found ".count($transactionSearchResponse->PaymentTransactions)." transactions:\n";
                foreach($transactionSearchResponse->PaymentTransactions as $tr) {
                    $t = new TransactionErp();
                    echo "Saving ".$tr->TransactionID."\n";
                    $t->time  = $tr->Timestamp;
                    $t->type  = $tr->Type;
                    $t->payer  = $tr->Payer;
                    $t->payer_name  = $tr->PayerDisplayName;
                    $t->transaction_id  = $tr->TransactionID;
                    $t->status  = $tr->Status;
                    $t->gross_amount  = $tr->GrossAmount->value ? $tr->GrossAmount->value : 0 ;
                    $t->fee_amount  = $tr->FeeAmount->value ? $tr->FeeAmount->value : 0;
                    $t->net_amount  = $tr->NetAmount->value ? $tr->NetAmount->value : 0;
                    $t->gross_currency  = $tr->GrossAmount->currencyID ? $tr->GrossAmount->currencyID : '';
                    $t->fee_currency  = $tr->FeeAmount->currencyID ? $tr->FeeAmount->currencyID : '';
                    $t->net_currency  = $tr->NetAmount->currencyID ? $tr->NetAmount->currencyID : '';
                    $t->save();
                }
            } else {
                echo "Nothing found\n";
            }
            $start_date +=$interval;
        } while ( $start_date < time() );
    }

    /*
     * Process nee transactions and save to the payments
     * */
    public static function ProcessTransactions(){

        /* setting */
        //paypal account prefix

        $prefix_ = "paypal_"; //paypal_USD, paypal_EUR
        $default_client = "Default";
        $credit_card = '***** *************';

        $user_id = 24;//24; //id менеджера, который проводит операции (Paypal)

        $default_client = Utils::getClientByName( $default_client );

        $conversions = array(); //массив, хранящий транзакции по обмену валют, которые были уже обработанны

        //get new transactions
        // parsed = FALSE AND
        $q = "SELECT * FROM finance.payment_transaction_paypal_classic WHERE parsed = FALSE AND status IN ('Refunded', 'Completed', 'Paid') ";
        $transactions_res = pg_query($q);

        if (pg_num_rows($transactions_res) <=0 ) { return false; }

        //main transactions loop
        while ($row = pg_fetch_assoc($transactions_res)){
            if (in_array($row['transaction_id'], $conversions)) { continue; };
            echo $row['type']. ' '. ($row['payer'] ? $row['payer']: $row['payer_name']).' '.$row['net_amount'].' '.$row['net_currency']."\n";

            $p = new Payment();
            $client_id = 0;
            $p->user_id = $user_id;
            $p->add_date = $row['time'];
            $p->bank_detail = '';

            $types = array('Payment', 'Recurring Payment', 'Bill', 'Donation','Recurring Payment','Refund');
            if (in_array($row['type'],$types)) { // для платежей between clients
                $p->comment = $row['type'].$row['net_amount'].' '.$row['net_currency']." Client: ".$row['payer'].' Transaction ID :'.$row['transaction_id'];
                $p->type = 1;
                $amount = $row['net_amount'];
                //определяем направление платежа

                if ($amount < 0) { //outgoing
                    $p->client_id = 1;
                    $p->client_term_id = Utils::getClientByAccount( $row['payer'] );
                    if ($p->client_term_id == 0) {
                        $p->client_term_id = $default_client;
                    } else {
                        $client_id = $p->client_term_id;
                    }
                    $p->account_id = Utils::getAccountByName($prefix_.$row['net_currency']);
                    $p->account_term_id = 0;
                } else if ($amount >= 0 ){ //incoming
                    $p->client_term_id = 1;
                    $p->client_id = Utils::getClientByAccount( $row['payer'] );
                    if ($p->client_id ==0){
                        $p->client_id = $default_client;
                    } else {
                        $client_id = $p->client_id;
                    }
                    $p->account_term_id = Utils::getAccountByName($prefix_.$row['net_currency']);
                    $p->account_id = 0;
                }

                //  сумма в обсолютном значении
                $p->amount = abs($amount);

                // если сумма в долларах, то в системной валюте столько же,
                // если нет, то находим курс

                if ( $row['net_currency'] == 'USD' ){
                    $p->sys_cur_amount = abs($amount);
                    $p->rate = 1;
                    $p->comment.= ' Rate = 1';
                }  else {
                    $q_curr = "SELECT
                    net_amount, net_currency
                    FROM finance.payment_transaction_paypal_classic
                    WHERE   type ILIKE '%Currency Conversion%'
                    AND time = '".$row['time']."'::timestamp
                    ";

                    $res_cur = pg_query($q_curr);
                    if ( pg_num_rows($res_cur) != 2 ) {
                        //No currency conversion transactions
                        echo "No currency conversion transactions";
                        $p->comment.= " No currency conversion transactions; Rate = 1";
                        $p->rate = 1;
                        $p->sys_cur_amount = abs( $amount );
                    } else {

                        $row_c_1 = pg_fetch_assoc($res_cur);
                        $row_c_2 = pg_fetch_assoc($res_cur);

                        if ($row_c_1['net_currency'] =='USD') {
                            $amount_usd = $row_c_1['net_amount'];
                            $amount_another = $row_c_2['net_amount'];
                        } else{
                            $amount_usd = $row_c_2['net_amount'];
                            $amount_another = $row_c_1['net_amount'];
                        }

                        //берем по модулю
                        $amount_usd = abs($amount_usd);
                        $amount_another = abs($amount_another);

                        $rate = $amount_usd / $amount_another;
                        echo "Rate ".$rate. " System = ".abs($amount) * $rate."\n";
                        $p->comment .= ' Rate = '.$rate;
                        $p->rate = $rate;
                        $p->sys_cur_amount = abs($amount) * $rate;
                    }
                }
            } else if (preg_match('/Currency Conversion/', $row['type'])){ //дальше идут транзацкии, которые попадают под тип Between accounts

                $p->type = 2;
                $p->client_term_id = 1;
                $p->client_id = 1;
                $currency = $row['net_currency'];
                $p->comment = 'Currency conversion';

                //Находим связанную транзакцию по обмену валюты
                $q_curr = "SELECT
                    net_amount, net_currency, transaction_id
                    FROM finance.payment_transaction_paypal_classic
                    WHERE   type ILIKE '%Currency Conversion%'
                    AND time = '".$row['time']."'::timestamp
                    AND net_currency <> '".$currency."'
                    LIMIT 1
                    ";
                $res_cur = pg_query($q_curr);

                if (pg_num_rows($res_cur) == 1 ){
                    $row_2 = pg_fetch_assoc($res_cur);

                    $conversions[] = $row_2['transaction_id'];
                    $conversions[] = $row['transaction_id'];

                    //определяем направление между счетами
                    if ( $row['net_amount'] < 0 ) {
                        $p->sys_cur_amount = abs($row_2['net_amount']);
                        $p->amount = abs($row['net_amount']);
                        $p->rate = $p->sys_cur_amount / $p->amount;

                        $p->account_id = Utils::getAccountByName($prefix_.$row['net_currency']);
                        $p->account_term_id = Utils::getAccountByName($prefix_.$row_2['net_currency']);
                    } else {
                        $p->amount = abs($row_2['net_amount']);
                        $p->sys_cur_amount = abs($row['net_amount']);
                        $p->rate = $p->sys_cur_amount / $p->amount;

                        $p->account_term_id = Utils::getAccountByName($prefix_.$row['net_currency']);
                        $p->account_id = Utils::getAccountByName($prefix_.$row_2['net_currency']);
                    }
                    $p->comment .= ' FROM '.$p->amount.' '.$row['net_currency'].' TO '.$row_2['net_amount'].' '.$row_2['net_currency'];
                } else {
                    //no currency conversion transaction
                    $p->amount = abs($row['amount']);
                    $p->rate = 1;
                    $p->sys_cur_amount = abs($row['amount']);
                    $p->comment .= 'no currency conversion transaction';
                }
                $p->comment .= ' Transaction ID :'.$row['transaction_id'];
            } else if (in_array($row['type'], array('Withdraw', 'Transfer'))){
                $p->comment = $row['type'].' '.$row['net_amount'].' '.$row['net_currency'];
                $p->client_term_id = 1;
                $p->client_id = 1;
                $p->type = 2;
                //Находим связанную транзакцию по обмену валюты
                $q_curr = "SELECT
                    net_amount, net_currency, transaction_id
                    FROM finance.payment_transaction_paypal_classic
                    WHERE   type ILIKE '%Currency Conversion%'
                    AND time = '".$row['time']."'::timestamp
                    LIMIT 2
                    ";
                $res_cur = pg_query($q_curr);

                if ( pg_num_rows($res_cur) == 2 ){

                    $row_c_1 = pg_fetch_assoc($res_cur);
                    $row_c_2 = pg_fetch_assoc($res_cur);

                    if ($row_c_1['net_currency'] ==$row['net_currency']) {
                        $amount_main = $row_c_1['net_amount'];
                        $amount_another = $row_c_2['net_amount'];
                    } else{
                        $amount_main = $row_c_2['net_amount'];
                        $amount_another = $row_c_1['net_amount'];
                    }

                    if ( $row['type'] == 'Withdraw' ) {
                        $p->account_id = Utils::getAccountByName($prefix_.$row['net_currency']);
                        $p->account_term_id = Utils::getAccountByName($credit_card);
                        $p->amount = abs($row['net_amount']);
                        $p->rate = abs($amount_another/$amount_main);
                        $p->sys_cur_amount = $p->amount * abs($p->rate);

                    } elseif($row['type'] == 'Transfer'){
                        $p->account_term_id = Utils::getAccountByName($prefix_.$row['net_currency']);
                        $p->account_id = Utils::getAccountByName($credit_card);
                        $p->sys_cur_amount = abs($row['net_amount']);
                        $p->rate = abs($amount_main/$amount_another);
                        $p->amount = abs($p->sys_cur_amount / $p->rate);
                    }
                    $p->comment .= ' Rate = '.$p->rate;
                } else{
                    //no currency conversion transaction
                    $p->comment .= 'no currency conversion transaction';
                    $p->amount = abs($row['amount']);
                    $p->rate = 1;
                    $p->sys_cur_amount = abs($row['amount']);

                    if ( $row['type'] == 'Withdraw' ) {
                        $p->account_id = Utils::getAccountByName($prefix_.$row['net_currency']);
                        $p->account_term_id = Utils::getAccountByName($credit_card);
                    } else if ( $row['type'] == 'Transfer' ){
                        $p->account_term_id = Utils::getAccountByName($prefix_.$row['net_currency']);
                        $p->account_id = Utils::getAccountByName($credit_card);
                    }
                }

                $p->comment .=' Transaction ID : '.$row['transaction_id'];
            }
            $payment_id = $p->save();
            Utils::markTransactionAsParsed($row['id']);
            if ($client_id) {
                Utils::linkCategories($payment_id, Utils::getChain( Utils::getCategoryByClient($client_id) ));
            }
        }
    }
}