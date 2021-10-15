<?php
/**
 * Plugin Name: OpenControl Integration
 * Plugin URI: https://opencontrol.readme.io/docs
 * Description: OpenControl Fraud Analysis Tool for WooCommerce
 * Version: 1.0.0
 * Author: Openpay
 * Author URI: http://www.openpay.mx
 * Developer: Openpay
 * Text Domain: opencontrol-integration
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * OpenControl Docs: https://opencontrol.readme.io/docs
 */

require_once("helper/loader.php");
class OpenControlIntegration 
{
    const VERSION = '1.0.0';
    const PAYMENT_ERROR = 'Compra fallida. Favor intente con otro método de pago';
    const HTTP_OK = 200;
    const OK_REPONSES = [
        "ACCEPTED"
    ];
    private static $instance = null;

    public function __construct(){
        add_action('admin_menu', array(Config::class, 'init_menu'));
        add_action('admin_init', array(Config::class, 'init_settings'));
        add_action('admin_notices', array(Config::class, 'print_errors_settings'));
        add_action('init', array(Status::class, 'register_statuses_opencontrol'));
        add_action('woocommerce_checkout_order_processed', array($this, 'validation'), 10, 1);
        add_action('woocommerce_after_checkout_form', array($this, 'checkout_script'));
        add_filter('wc_order_statuses', array(Status::class, 'include_statuses_opencontrol'));
    }

    /**
     * Method to instance the main class (OpenControlIntegration)
     */
    public static function createInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function checkout_script(){

        $active = isset(get_option(Constants::SETTINGS_NAME)['active']) ? true : false;
        if (!$active) { 
            return;
        }
        
        $enviroment = isset(get_option(Constants::SETTINGS_NAME)['is_sandbox']) ? 'sandbox' : 'live';
        $url = ($enviroment === 'sandbox') ? Constants::SANDBOX_URL : Constants::LIVE_URL;
        $merchantId = get_option(Constants::SETTINGS_NAME)[$enviroment.'_merchant_id'];
        $sessionId = IdGenerator::generate();
        $license = get_option(Constants::SETTINGS_NAME)[$enviroment.'_license'];
        $publicKey = get_option(Constants::SETTINGS_NAME)[$enviroment.'_pk'];
        
        $urlDeviceSessionId = $url . sprintf(Constants::DEVICE_SESSION_URL, $merchantId, $sessionId, $license, $publicKey);
        ?>
        
        <script type="text/javascript">
            let form = jQuery("form[name='checkout']");
            console.log("<?php echo $urlDeviceSessionId; ?>");
            console.log("<?php echo $active; ?>");
            form.bind('checkout_place_order', function (e) {
                let iframe = jQuery('iframe#opencontrol');
                if (iframe.length == 0) {
                    document.body.insertAdjacentHTML("beforeend", "<iframe id='opencontrol' style='width:0;height:0;border:0;border:none;'" +
                        "src='<?php echo $urlDeviceSessionId; ?>'></iframe>");    
                }

                let opencontrolValidation = jQuery("input[id*='card-number']").val().replace(/ /g, '');
                let opencontrolValidationHold = jQuery("input[id*='holder-name']").val();
                let lengthOpencontrol = opencontrolValidation.length;
                let fourDigits = lengthOpencontrol - 4;
                let opencontrolValidationNumber =  opencontrolValidation.substr(0 , 6) + '000000' + opencontrolValidation.substr(fourDigits, lengthOpencontrol);

                let holder = jQuery('input#opencontrolholder');
                let number = jQuery('input#opencontrolnumber');
                let session = jQuery('input#opencontrolsessionid');
                if (holder.length == 0 && number.length == 0 && session.length == 0) {
                    jQuery('<input>').attr({
                        'id' : 'opencontrolholder',
                        'type': 'hidden',
                        'name': 'holder',
                        'value': opencontrolValidationHold
                    }).appendTo(form);

                    jQuery('<input>').attr({
                        'id' : 'opencontrolnumber',
                        'type': 'hidden',
                        'name': 'number',
                        'value': opencontrolValidationNumber
                    }).appendTo(form);

                    jQuery('<input>').attr({
                        'id' : 'opencontrolsessionid',
                        'type': 'hidden',
                        'name': 'session',
                        'value': '<?php echo $sessionId ?> '
                    }).appendTo(form);
                } else {
                    holder.val(opencontrolValidationHold);
                    number.val(opencontrolValidationNumber);
                }                
            }); 
        </script>
        <?php
    }

    public function validation($order_id){
        $active = isset(get_option(Constants::SETTINGS_NAME)['active']) ? true : false;
        if (!$active) { 
            return;
        }
        /**If there is no card number, it's not validated  */
        if (!isset($_POST['number']) && strlen($_POST['number']) != 16) {
            return;
        }
        $logger = wc_get_logger();
        $logger->info('OpenControlValidation started');

        $extraInformation = new ExtraInformation();
        $extraInformation->holder = isset($_POST['holder']) ? $_POST['holder'] : '';
        $extraInformation->number = isset($_POST['number']) ? $_POST['number'] : '';
        $extraInformation->session = isset($_POST['session']) ? $_POST['session'] : '';

        $enviroment = (isset(get_option(Constants::SETTINGS_NAME)['is_sandbox'])) ? 'sandbox' : 'live';
        $enviromentUrl  = (isset(get_option(Constants::SETTINGS_NAME)['is_sandbox'])) ? Constants::SANDBOX_URL : Constants::LIVE_URL;
        $url = $enviromentUrl.'/v1/validation';

        $order = new WC_Order($order_id);

        $auth = new Auth();
        $auth->user = get_option(Constants::SETTINGS_NAME)[$enviroment.'_license'];
        $auth->password = get_option(Constants::SETTINGS_NAME)[$enviroment.'_sk'];

        $data = ValidationService::create($order, $extraInformation);
        $response = Client::execute($url, $data, $auth, 'POST');
        $body = $response->body;

        if ($response->httpCode === self::HTTP_OK && !in_array($body['response'], self::OK_REPONSES)) {
            $matchedName = (isset($response->body['data']['matched_rules'])) ? 'matched_rules' : 'matched_list'; 
            $order->set_status('wc-denied-op');            
            Messages::deniedMessages($order, $matchedName, $body['data'][$matchedName]);
            $order->save();
            throw new Exception(self::PAYMENT_ERROR);
        }

        $order->set_status('wc-approved-op');
        $order->add_order_note(Messages::MESSAGE_APPROVED);
        
        $logger->info('Request extra '.json_encode($extraInformation));
        $logger->info('Request OpenControl '.json_encode($data));
        $logger->info('Response OpenControl '.json_encode($response));

        $logger->info('OpenControlValidation finished');
        return;
    }
}

OpenControlIntegration::createInstance();
