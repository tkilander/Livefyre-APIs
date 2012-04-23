<?php

if ( !defined( 'LF_DEFAULT_TLD' ) ) {
    define( 'LF_DEFAULT_TLD', 'livefyre.com' );
}
if ( !defined( 'LF_DEFAULT_PROFILE_DOMAIN' ) ) {
    define( 'LF_DEFAULT_PROFILE_DOMAIN', 'livefyre.com' );
}
define( 'LF_NETWORK_COOKIE_PREFIX', 'livefyre_token_' );
include("User.php");
include("Site.php");

class Livefyre_Domain {
    private $host;
    private $key;
    
    public function __construct($host, $key=null, $http_api=null) {
        $this->host = $host;
        $this->key = $key;
        if ( defined('LF_DEFAULT_HTTP_LIBRARY') ) {
            $httplib = LF_DEFAULT_HTTP_LIBRARY;
            $this->http = new $httplib;
        } else {
            include_once("Http.php");
            $this->http = new Livefyre_http; 
        }
    }

    public function get_host() {
        return $this->host;
    }
    
    public function get_key() {
        return $this->key;
    }
    
    public function user($uid, $display_name = null) {
        return new Livefyre_User($uid, $this, $display_name);
    }

    function authenticate_js( $token_url, $cookie_path = '/' ) {
    
        $cookie_name = LF_NETWORK_COOKIE_PREFIX . $this->get_host();
        ?>
            <script type="text/javascript">
                // This script is being rendered because it appears the user is logged in
                // Now we attempt to fetch Livefyre credentials from a cookie,
                // falling back to ajax as needed.
                LF.ready(function(){
                    var lfCookieName = '<?php echo $cookie_name; ?>';
                    if (!$jl.cookie(lfCookieName)) {
                        // fetch via ajax
                        $jl.ajax({
                            url: '<?php echo $token_url; ?>',
                            type: 'json',
                            success: function(json){
                                LF.login(json);
                                $jl.cookie(lfCookieName, $jl.JSON.stringify(json), {expires:1, path:'<?php echo $cookie_path ?>'});
                            },
                            error: function(a, b){
                                console.log("There was some problem fetching a livefyre token. ", a, b);
                            }
                        });
                    } else {
                        try {
                            var lfLoginConfig = eval('('+$jl.cookie(lfCookieName)+')');
                            LF.login(lfLoginConfig);
                        } catch (e) {
                            console.log("Error attempting to parse & login with ", lfCookieName, " cookie value!", $jl.cookie(lfCookieName), " ", e);
                        }
                    }
                });
            </script>
        <?php
    
    }

    
    public function site($site_id) {
        return new Livefyre_Site($site_id, $this);
    }

    public function validate_server_token($token) {
        return lftokenValidateServerToken($token, $this->key);
    }
}

?>
