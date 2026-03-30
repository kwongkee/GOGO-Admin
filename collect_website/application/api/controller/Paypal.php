<?php
namespace app\api\controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, application/json");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

use think\Controller;
use think\Db;
use think\Request;
use think\Validate;
use think\Log;

use PaypalServerSDKLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSDKLib\Environment;
use PaypalServerSDKLib\PaypalServerSdkClientBuilder;
use PaypalServerSDKLib\Models\Builders\MoneyBuilder;
use PaypalServerSDKLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSDKLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSDKLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSDKLib\Models\Builders\ShippingDetailsBuilder;
use PaypalServerSDKLib\Models\Builders\ShippingOptionBuilder;
use PaypalServerSDKLib\Models\ShippingType;


class Paypal extends Controller
{
    #sandbox
    public $api_key_sandbox = 'AQ90u7pDukqtRbKXXczVZusDEUU4OydoHQUpJmI5VaA9xPEUQMY5pd02zQ6cU55cw2ANTpMPTaozEGPv';
    public $key_sandbox = 'EJvNy4Qmbgt3XzxETNQBxXVoZPnpLxONmWy2P3avM2dMKal7jPoucjbtExQ24qOJ9uaCTUJkzBWHBveY';
    public $Username_sandbox = 'sb-d243th3780992@personal.example.com';
    public $Password_sandbox = 've:2Kfd)';

    #live
    public $api_key = 'Ac6L5MpzRfeUvYqDUknEzE-xtEAdpiI3s3Y3HU-o1pCTgHq8N0qOrtNIOaU_AgxeKL2UrZCxtLUiOdXs';
    public $key = 'ECDdA_1BnbJ3-hSaSj7aquw7HqfAMCoy_eqLOIrgRzbjzv_SXrgXkZXcEMH3ZSWS2W3EN1BdDfW7bEsM';
    public $Username = 'sb-op4mu3782546@business.example.com';
    public $Password = 'nb25!CC:';

    #应用id和应用sercret
    public $client_id = 'Ac6L5MpzRfeUvYqDUknEzE-xtEAdpiI3s3Y3HU-o1pCTgHq8N0qOrtNIOaU_AgxeKL2UrZCxtLUiOdXs';
    public $client_sercret = 'ECDdA_1BnbJ3-hSaSj7aquw7HqfAMCoy_eqLOIrgRzbjzv_SXrgXkZXcEMH3ZSWS2W3EN1BdDfW7bEsM';

    public function index(Request $request){
        $dat = json_decode(file_get_contents('php://input'), true);

        global $client;
        #准备初始化工作
        $client = PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    $this->client_id,
                    $this->client_sercret
                )
            )
            ->environment(Environment::PRODUCTION)
            ->build();

//        $endpoint = $_SERVER["REQUEST_URI"];
        $endpoint = $dat['type'];
        if ($endpoint === "/")
        {
            try
            {
                $response = ["message" => "Server is running",];
                header("Content-Type: application/json");
                echo json_encode($response);
            }
            catch(Exception $e) {
                echo json_encode(["error" => $e->getMessage()]);
                http_response_code(500);
            }
        }

        if ($endpoint === "orders") {
            $data = json_decode(file_get_contents("php://input"), true);
            $cart = $data["info"];
            header("Content-Type: application/json");
            try {
                $orderResponse = $this->createOrder($cart);

                echo json_encode($orderResponse["jsonResponse"]);
            } catch (Exception $e) {
                echo json_encode(["error" => $e->getMessage()]);
                http_response_code(500);
            }
        }

        if (str_ends_with($endpoint, "/capture")) {
            $urlSegments = explode("/", $endpoint);
            end($urlSegments); // Will set the pointer to the end of array
            $orderID = prev($urlSegments);
            header("Content-Type: application/json");
            try {
                $captureResponse = $this->captureOrder($orderID,$dat['paymentSource']);
                echo json_encode($captureResponse["jsonResponse"]);
            } catch (Exception $e) {
                echo json_encode(["error" => $e->getMessage()]);
                http_response_code(500);
            }
        }

        if (str_ends_with($endpoint, "/authorize")) {
            $urlSegments = explode("/", $endpoint);
            end($urlSegments); // Will set the pointer to the end of array
            $orderID = prev($urlSegments);
            header("Content-Type: application/json");
            try {
                $authorizeResponse = $this->authorizeOrder($orderID);
                echo json_encode($authorizeResponse["jsonResponse"]);
            } catch (Exception $e) {
                echo json_encode(["error" => $e->getMessage()]);
                http_response_code(500);
            }
        }

        if (str_ends_with($endpoint, "/captureAuthorize")) {
            $urlSegments = explode("/", $endpoint);
            end($urlSegments); // Will set the pointer to the end of array
            $authorizationId = prev($urlSegments);
            header("Content-Type: application/json");
            try {
                $captureAuthResponse = $this->captureAuthorize($authorizationId);
                echo json_encode($captureAuthResponse["jsonResponse"]);
            } catch (Exception $e) {
                echo json_encode(["error" => $e->getMessage()]);
                http_response_code(500);
            }
        }
    }

    public function handleResponse($response)
    {
        $jsonResponse = json_decode($response->getBody(), true);
        return [
            "jsonResponse" => $jsonResponse,
            "httpStatusCode" => $response->getStatusCode(),
        ];
    }

    /**
     * Create an order to start the transaction.
     * 创建订单
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_create
     */
    public function createOrder($cart)
    {
        global $client;

        #1、订单的总金额+支付方式手续费
        $true_money = $cart['true_money'] + $cart['handing_fee'];

        #2、将金额转换成美元，按表里的汇率
        $rate = Db::name('website_exchange_rate')->where(['id'=>1])->find();
        $true_money = round($true_money * $rate['rate'],2);

        #3、发送给paypal创建订单号
        $orderBody = [
            "body" => OrderRequestBuilder::init("CAPTURE", [
                PurchaseUnitRequestBuilder::init(
//                    AmountWithBreakdownBuilder::init("USD", "2.55")->build()
                    AmountWithBreakdownBuilder::init("USD",$true_money)->build()
                )->build(),
            ])->build(),
        ];

        #4、paypal返回订单号信息
        $apiResponse = $client->getOrdersController()->ordersCreate($orderBody);

        $apiResponse2 = $this->handleResponse($apiResponse);
        Log::info('PayPal创建订单ID：'.$apiResponse2['jsonResponse']['id'].' ； PayPal支付回调：'.json_encode($apiResponse2,true));
        if($apiResponse2['httpStatusCode'] == 201){
            #paypal返回状态“已创建”订单
            $orderCurrency = Db::name('centralize_currency')->where(['currency_symbol_standard'=>$rate['symbol']])->find();

            $order = Db::name('website_order_list')->where(['id' => $cart['id']])->find();
            $user = Db::name('website_user')->where(['id'=>$order['user_id']])->find();

            #5.1、修改订购清单的“订单编号、支付编号、最终支付金额”
            $new_ordersn = get_ordersn(3);#支付单编号

            #6、记录平台支付订单记录
            Db::name('core_paylog')->insert([
                'uniacid'=>3,
                'openid'=>$user['openid'],
                'tid'=>$new_ordersn,
                'fee'=>$true_money,
                'status'=>0,
                'module'=>'sz_yi',
            ]);
            #7、记录国外支付订单记录
            $pay_id = Db::name('website_pay_list')->insertGetId([
                'order_id'=>$cart['id'],
                'pay_sn'=>$apiResponse2['jsonResponse']['id'],
                'currency'=>$orderCurrency['id'],
                'money'=>$true_money,
                'status'=>0,
                'createtime'=>time()
            ]);

            #5.2、
            Db::name('website_order_list')->where(['id' => $cart['id']])->update([
                'pay_id'=>$pay_id,//记录首个支付id
                'ordersn' => $new_ordersn,
                'other_paysn'=>$apiResponse2['jsonResponse']['id'],
                'final_money' => $true_money,
            ]);
        }

        return $this->handleResponse($apiResponse);
    }


    /**
     * Capture payment for the created order to complete the transaction.
     * 获取已创建订单的付款以完成交易。
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_capture
     */
    function captureOrder($orderID,$paymentSource='paypal')
    {
        global $client;

        $captureBody = [
            "id" => $orderID,
        ];

        $apiResponse = $client->getOrdersController()->ordersCapture($captureBody);

        $apiResponse2 = $this->handleResponse($apiResponse);
        #1、记录paypal支付日志
        Log::info('PayPal订单ID：'.$orderID.' ； 支付方式：'.$paymentSource.' ； PayPal支付回调：'.json_encode($apiResponse2,true));
        if($apiResponse2['httpStatusCode'] == 201){
            #paypal支付成功
            $order = Db::name('website_order_list')->where(['other_paysn'=>$apiResponse2['jsonResponse']['id']])->find();
            #2、修改订单状态、2=国外支付
            Db::name('website_order_list')->where(['other_paysn'=>$apiResponse2['jsonResponse']['id']])->update([
                'status'=>1,#已付款
                'pay_method'=>2,
            ]);
            #3、记录平台支付订单记录
            Db::name('core_paylog')->where(['tid'=>$order['ordersn']])->update([
                'type'=>$paymentSource,
                'status'=>1,
                'reqRes'=>json_encode($apiResponse2,true)
            ]);
            #4、记录国外支付订单记录
            Db::name('website_pay_list')->where(['order_id'=>$order['id'],'pay_sn'=>$apiResponse2['jsonResponse']['id']])->update([
                'pay_type'=>$paymentSource,
                'status'=>1,
                'paytime'=>time()
            ]);
        }

        return $this->handleResponse($apiResponse);
    }

    /**
     * Authorizes payment for an order.
     * 授权订单付款。
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_authorize
     */
    function authorizeOrder($orderID)
    {
        global $client;

        $authorizeBody = [
            "id" => $orderID,
        ];

        $apiResponse = $client
            ->getOrdersController()
            ->ordersAuthorize($authorizeBody);

        return $this->handleResponse($apiResponse);
    }

    /**
     * Captures an authorized payment, by ID.
     * 按ID捕获授权付款。
     * @see https://developer.paypal.com/docs/api/payments/v2/#authorizations_capture
     */
    function captureAuthorize($authorizationId)
    {
        global $client;

        $captureAuthorizeBody = [
            "authorizationId" => $authorizationId,
        ];

        $apiResponse = $client
            ->getPaymentsController()
            ->authorizationsCapture($captureAuthorizeBody);

        return $this->handleResponse($apiResponse);
    }
}