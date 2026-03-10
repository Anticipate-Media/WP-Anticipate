<?php
defined('ABSPATH') or die('Error: this file is not to be called separately.');

class AnticipateSettingsPage
{
    private $options;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings". Page has slug "anticipate-settings"
        add_options_page(
            'Anticipate settings',
            'Anticipate settings',
            'manage_options',
            'anticipate-settings',
            [$this, 'anticipate_settings_page']
        );
    }

    /**
     * Options page callback
     */
    public function anticipate_settings_page()
    {
        $this->options = get_option('anticipate_settings');
        ?>
        <div class="wrap">
            <h1>Additional code</h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('header_code');
                do_settings_sections('anticipate-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'header_code', // Option group
            'anticipate_settings', // Option name
            [$this, 'sanitize'] // Sanitize
        );

        add_settings_section(
            'header_code_section', // ID
            'Header code', // Title
            function () {
                print 'Voeg hier extra HTML/CSS/JS in die in de header wordt geplaats';
            },
            'anticipate-settings' // Page
        );

        add_settings_field(
            'additional_header_code', // ID
            'Additional Header Code', // Title
            [$this, 'additional_header_code_callback'], // Callback
            'anticipate-settings', // Page
            'header_code_section' // Section
        );

        add_settings_section(
            'favicomatic_section', // ID
            'Favicomatic', // Title
            function () {
                print 'Selecteer om automatisch Favicomatic code toe te voegen';
            },
            'anticipate-settings' // Page
        );

        add_settings_field(
            'favicomatic', // ID
            'Add code', // Title
            [$this, 'favicomatic_callback'], // Callback
            'anticipate-settings', // Page
            'favicomatic_section' // Section
        );

        add_settings_section(
            'laposta_section', // ID
            'Laposta', // Title
            function () {
                print 'Koppel aan Laposta account (Gutenberg of Divi block)';
            },
            'anticipate-settings' // Page
        );

        add_settings_field(
            'laposta_apikey', // ID
            'Laposta API-key', // Title
            [$this, 'laposta_apikey_callback'], // Callback
            'anticipate-settings', // Page
            'laposta_section' // Section
        );

        add_settings_section(
            'cookieyes_section', // ID
            'CookieYes', // Title
            function () {
                print 'Vul hier de ID in van de CookieYes banner om deze te tonen op de website.';
            },
            'anticipate-settings' // Page
        );

        add_settings_field(
            'cookieyes_id', // ID
            'CookieYes ID', // Title
            [$this, 'cookieyes_id_callback'], // Callback
            'anticipate-settings', // Page
            'cookieyes_section' // Section
        );



        add_settings_section(
            'versioninfo_section', // ID
            'Versies', // Title
            function () {
                
            },
            'anticipate-settings' // Page
        );


        add_settings_field(
            'versioninfo', // ID
            'Versie informatie', // Title
            [$this, 'versioninfo_callback'], // Callback
            'anticipate-settings', // Page
            'versioninfo_section' // Section
        );


    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = [];
        if (isset($input['additional_header_code'])) {
            $new_input['additional_header_code'] = $input['additional_header_code'];
        }
        if (isset($input['favicomatic'])) {
            $new_input['favicomatic'] = $input['favicomatic'];
        }
        if (isset($input['laposta_apikey'])) {
            $new_input['laposta_apikey'] = $input['laposta_apikey'];
            LapostaImplementatie::resetLapostaKoppeling();
        }
        if (isset($input['cookieyes_id'])) {
            $new_input['cookieyes_id'] = $input['cookieyes_id'];
        }
        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Voeg hier extra HTML/CSS/JS in die in de header wordt geplaats';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function additional_header_code_callback()
    {
        printf(
            '<textarea style="width: 700px; height: 200px; display: block" id="additional_header_code" name="anticipate_settings[additional_header_code]">%s</textarea>',
            isset($this->options['additional_header_code']) ? esc_attr($this->options['additional_header_code']) : ''
        );
    }

    public function favicomatic_callback()
    {
        printf(
            '<input type="checkbox" value="true" id="favicomatic_code" name="anticipate_settings[favicomatic]" %s />',
            (isset($this->options['favicomatic']) && $this->options['favicomatic'] == 'true') ? 'checked="checked"' : ''
        );
    }


  public function cookieyes_id_callback()
    {
        printf(
            '<input type="text" value="%s" id="cookieyes_id" name="anticipate_settings[cookieyes_id]" />',
            (isset($this->options['cookieyes_id'])) ? $this->options['cookieyes_id'] : ''
        );
    }

    public function laposta_apikey_callback()
    {
        printf(
            '<input type="text" value="%s" id="laposta_apikey" name="anticipate_settings[laposta_apikey]" />',
            (isset($this->options['laposta_apikey'])) ? $this->options['laposta_apikey'] : ''
        );

        if(isset($this->options['laposta_apikey'])){
            $lists = LapostaImplementatie::getLapostaLists();
            foreach($lists as $list){
                echo '<br />Formulier voor lijst "'.$list['list']['name'].'" shortcode: [ant_lapostaform list='.$list['list']['list_id'].']';
            }
        }
    }

    public function versioninfo_callback() {
        echo '<table>';
        echo '<tr><td>PHP versie:</td><td>'.phpversion().'</td></tr>';
        echo '<tr><td>WP versie:</td><td>'.get_bloginfo('version').'</td></tr>';
        echo '</table>';

    }

}

if (is_admin()) {
    $anticipate_settings_page = new AnticipateSettingsPage();
}

add_action('wp_head', function () {
    $anticipate_settings = get_option('anticipate_settings');
    if (isset($anticipate_settings['additional_header_code']) ) {
        echo $anticipate_settings['additional_header_code'];
    }

    if (isset($anticipate_settings['favicomatic']) && $anticipate_settings['favicomatic'] == 'true') {
        $path = get_home_url();

        echo '<link rel="apple-touch-icon-precomposed" sizes="57x57" href="' . $path . '/apple-touch-icon-57x57.png" />' . "\n";
        echo '<link rel="apple-touch-icon-precomposed" sizes="114x114" href="' . $path . '/apple-touch-icon-114x114.png" />' . "\n";
        echo '<link rel="apple-touch-icon-precomposed" sizes="72x72" href="' . $path . '/apple-touch-icon-72x72.png" />' . "\n";
        echo '<link rel="apple-touch-icon-precomposed" sizes="144x144" href="' . $path . '/apple-touch-icon-144x144.png" />' . "\n";
        echo '<link rel="apple-touch-icon-precomposed" sizes="60x60" href="' . $path . '/apple-touch-icon-60x60.png" />' . "\n";
        echo '<link rel="apple-touch-icon-precomposed" sizes="120x120" href="' . $path . '/apple-touch-icon-120x120.png" />' . "\n";
        echo '<link rel="apple-touch-icon-precomposed" sizes="76x76" href="' . $path . '/apple-touch-icon-76x76.png" />' . "\n";
        echo '<link rel="apple-touch-icon-precomposed" sizes="152x152" href="' . $path . '/apple-touch-icon-152x152.png" />' . "\n";
        echo '<link rel="icon" type="image/png" href="' . $path . '/favicon-196x196.png" sizes="196x196" />' . "\n";
        echo '<link rel="icon" type="image/png" href="' . $path . '/favicon-96x96.png" sizes="96x96" />' . "\n";
        echo '<link rel="icon" type="image/png" href="' . $path . '/favicon-32x32.png" sizes="32x32" />' . "\n";
        echo '<link rel="icon" type="image/png" href="' . $path . '/favicon-16x16.png" sizes="16x16" />' . "\n";
        echo '<link rel="icon" type="image/png" href="' . $path . '/favicon-128.png" sizes="128x128" />' . "\n";
        echo '<meta name="application-name" content=" "/>' . "\n";
        echo '<meta name="msapplication-TileColor" content="#FFFFFF" />' . "\n";
        echo '<meta name="msapplication-TileImage" content="' . $path . '/mstile-144x144.png" />' . "\n";
        echo '<meta name="msapplication-square70x70logo" content="' . $path . '/mstile-70x70.png" />' . "\n";
        echo '<meta name="msapplication-square150x150logo" content="' . $path . '/mstile-150x150.png" />' . "\n";
        echo '<meta name="msapplication-wide310x150logo" content="' . $path . '/mstile-310x150.png" />' . "\n";
        echo '<meta name="msapplication-square310x310logo" content="' . $path . '/mstile-310x310.png" />';
    }
});

