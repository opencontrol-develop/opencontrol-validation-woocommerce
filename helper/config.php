<?php
class Config
{
    const PLUGIN = 'opencontrol_plugin';
    const SECTION = 'opencontrol_section';
    const TEXTS_FIELDS = [
        [
            'label' => 'Sandbox Licencia',
            'field_name' => 'sandbox_license',
            'type'=> 'text'
        ],
        [
            'label' => 'Sandbox Merchant Id',
            'field_name' => 'sandbox_merchant_id',
            'type'=> 'text'
        ],
        [
            'label' => 'Sandbox Llave Secreta',
            'field_name' => 'sandbox_sk',
            'type'=> 'password'
        ],
        [
            'label' => 'Sandbox Llave Pública',
            'field_name' => 'sandbox_pk',
            'type'=> 'text' 
        ],
        [
            'label' => 'Producción Licencia',
            'field_name' => 'live_license',
            'type'=> 'text'
        ],
        [
            'label' => 'Producción Merchant Id',
            'field_name' => 'live_merchant_id',
            'type'=> 'text'
        ],
        [
            'label' => 'Producción Llave Secreta',
            'field_name' => 'live_sk',
            'type'=> 'password'
        ],
        [
            'label' => 'Producción Llave Pública',
            'field_name' => 'live_pk',
            'type'=> 'text'
        ],
    ];




    public static function init_menu() {
        add_menu_page(
            'OpenControl',
            'OpenControl',
            'manage_options',
            Constants::SETTINGS_NAME,
            [self::class, 'template_settings'],
            'dashicons-admin-page',
            75
        );   
    }

    public static function template_settings() {
        plugins_url()
        ?>
        <h1>OpenControl</h1>
        <form action="options.php" method="post">
            <?php 
            settings_fields(Constants::SETTINGS_NAME);
            do_settings_sections(self::PLUGIN);
            ?>
            <input
            type="submit"
            name="submit"
            class="button button-primary"
            value="<?php esc_attr_e('Save'); ?>"
            />
        </form>
        <script src="<?php echo plugins_url("opencontrol-integration/assets/js/jquery.min.js");;?>"></script>
        <?php
        include('config_script.php');
    }

    public static function init_settings() {
        register_setting(
            Constants::SETTINGS_NAME,
            Constants::SETTINGS_NAME,
            [self::class, 'validation_inputs']
        );

        add_settings_section(
            self::SECTION,
            'Configuración OpenControl',
            [self::class, 'header'],
            self::PLUGIN
        );
        
        add_settings_field(
            'active',
            'Habilitar',
            [self::class, 'boolean_field'],
            self::PLUGIN,
            self::SECTION,
            [
                'field_name' => 'active',
                'id' => 'active'
            ]
        );

        add_settings_field(
            'is_sandbox',
            'Sandbox',
            [self::class, 'boolean_field'],
            self::PLUGIN,
            self::SECTION,
            [
                'field_name' => 'is_sandbox',
                'id' => 'is_sandbox'
            ]
        );

        self::initiate_field_texts();
    }

    public static function header() {
        echo '<p>Ingrese sus credenciales de OpenControl para configurar el plugin</p>';
    }

    public static function print_errors_settings() {
        settings_errors(Constants::SETTINGS_NAME);
    }

    public static function text_field($args){

        $options = get_option( Constants::SETTINGS_NAME );
        $value = isset($options[$args['field_name']]) ? $options[$args['field_name']] : "";
        printf(
            '<input style="width:%s" type="%s" name="%s" value="%s" />',
            esc_attr('65%'),
            esc_attr($args['type']),
            esc_attr(Constants::SETTINGS_NAME.'['.$args['field_name'].']'),
            esc_attr($value)
        );
    }

    public static function boolean_field($args){
        $options = get_option( Constants::SETTINGS_NAME);
        $value = checked(1, isset($options[$args['field_name']]), false);
        printf(
            '<input type="checkbox" id="%s" name="%s" value="1" %s />',
            esc_attr($args['id']),
            esc_attr(Constants::SETTINGS_NAME.'['.$args['field_name'].']'),
            esc_attr( $value)
        );
    }

   

    public static function validation_inputs($inputs) {
        
        $data = ['session_id', 'Check_configuration'];
        $enviroment = (isset(get_option(Constants::SETTINGS_NAME)['is_sandbox'])) ? 'sandbox' : 'live';
        $enviromentUrl  = (isset(get_option(Constants::SETTINGS_NAME)['is_sandbox'])) ? Constants::SANDBOX_URL : Constants::LIVE_URL;
        $url = $enviromentUrl.'/v1/validation';
        $auth = new Auth();
        $auth->user = $inputs[$enviroment.'_license'];
        $auth->password = $inputs[$enviroment.'_sk'];

        $response = Client::execute($url, $data, $auth, 'POST');
        if ($response->httpCode !== 400) {
            add_settings_error(Constants::SETTINGS_NAME, esc_attr( 'settings_updated' ), 'Llave privada o licencia errónea.Por favor corrobore los datos.');
            return $inputs;
        }

        add_settings_error(Constants::SETTINGS_NAME, esc_attr( 'settings_updated' ), 'Configuración guardada exitosamente', 'success');
        return $inputs;
    }

    private static function initiate_field_texts() {
        foreach(self::TEXTS_FIELDS as $field) {
            add_settings_field(
                $field['field_name'],
                $field['label'],
                [self::class, 'text_field'],
                self::PLUGIN,
                self::SECTION,
                [
                    'field_name'=>$field['field_name'],
                    'type' => $field['type']
                ]
            );
        }
    }
}