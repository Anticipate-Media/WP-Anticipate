<?php

class LapostaImplementatie{
    public static function form($lijst='', $attributes=[]){
        if($lijst==''){
            return '';
        }
        if(!isset($attributes['submit'])){$attributes['submit']='Versturen';}
        if(!isset($attributes['display'])){$attributes['display']='placeholders';}
        $lapostaFields = LapostaImplementatie::getLapostaFields($lijst);
        if(!is_array($lapostaFields)){
            return '';
        }
        $return = '';
        $return .= '<form method="post" accept-charset="utf-8" class="lapostaform">';
        $return .= '<div class="lapostamessage"></div>';
        $return .= '<input type="hidden" name="lapostalistid" value="'.$lijst.'" />';

        foreach($lapostaFields['data'] as $lapostaField){
            if(intval($lapostaField['field']['in_form'])==1){
                switch($lapostaField['field']['datatype']) {
                    case 'text':
                        $return .= '<label class="textlabel" for="'.$lapostaField['field']['field_id'].'">';
                        $required='';
                        if(intval($lapostaField['field']['required'])==1){
                            $required=' required';
                        }
                        if($attributes['display']=='labels'){
                            $return .= $lapostaField['field']['name'];
                            if(intval($lapostaField['field']['required'])==1){
                                $return .= '&nbsp;*';
                            }    
                        }
                        $placeholder='';
                        if($attributes['display']=='placeholders'){
                            $placeholder = $lapostaField['field']['name'];
                            if(intval($lapostaField['field']['required'])==1){
                                $placeholder.= ' *';
                            }    
                        }
                        $return .= '<input type="text" placeholder="'.$placeholder.'" name="'.$lapostaField['field']['field_id'].'" id="id-'.$lapostaField['field']['field_id'].'" '.$required.'/>';
                        $return .= '</label>';
                        break;

                    case 'select_single':
                        $return .= '<label class="checkboxlabel" for="'.$lapostaField['field']['field_id'].'">';
                        $required='';
                        if(intval($lapostaField['field']['required'])==1){
                            $required=' required';
                        }
                        $return .= '<input type="checkbox" value="'.$lapostaField['field']['options'][0].'" name="'.$lapostaField['field']['field_id'].'" id="id-'.$lapostaField['field']['field_id'].'" '.$required.'/>';
                        $return .= $lapostaField['field']['options'][0];
                        if(intval($lapostaField['field']['required'])==1){
                            $return .= '&nbsp;*';
                        }
                        $return .= '</label>';
                        break;
    
                }
            }

        }
        $return .= '<input type="submit" class="lapostasubmit" value="'.$attributes['submit'].'" />';
        $return .= '</form>';
        return $return;
    }


    public static function resetLapostaKoppeling(){
        global $wpdb;
        $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_anticipate_laposta%')" );
    }

    public static function getLapostaLists(){
    $lapostaLists = get_transient('anticipate_lapostalists');
    if(!$lapostaLists){
        $lapostaLists=[];
        $options = get_option('anticipate_settings');
        if(isset($options['laposta_apikey']) && $options['laposta_apikey']!==''){
            Laposta::setApiKey($options['laposta_apikey']);
            $list = new Laposta_List();
            $lijsten = $list->all();
            $lapostaLists = $lijsten['data'];
            set_transient('anticipate_lapostalists', $lapostaLists);
        }    
    }
    return $lapostaLists;
}

public static function getLapostaFields($listId){
    $lapostaFields = get_transient('anticipate_lapostafields_'.$listId);
    if(!$lapostaFields){
        $options = get_option('anticipate_settings');
        if(isset($options['laposta_apikey']) && $options['laposta_apikey']!==''){
            Laposta::setApiKey($options['laposta_apikey']);
            $lapostaField = new Laposta_Field($listId);
            try{
                $lapostaFields = $lapostaField->all();
            }  catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            set_transient('anticipate_lapostafields_'.$listId, $lapostaFields);
        }
    }
    return $lapostaFields;
}
}


add_action( 'wp_ajax_laposta_submit', 'lapostasubmit');
add_action( 'wp_ajax_nopriv_laposta_submit', 'lapostasubmit');

function lapostasubmit(){
    $lapostaFields = LapostaImplementatie::getLapostaFields($_POST['lapostalistid']);

    $email='';
    $custom_fields=[];
    foreach($lapostaFields['data'] as $lapostaField){
        if(intval($lapostaField['field']['in_form'])==1){
            if(!isset($_POST[$lapostaField['field']['field_id']])){
                // overslaan
            } else {
                if(intval($lapostaField['field']['is_email'])==1){
                    $email = $_POST[$lapostaField['field']['field_id']];
                } else {
                    $custom_fields[str_replace(['{','}'],'',$lapostaField['field']['tag'])] = $_POST[$lapostaField['field']['field_id']];
                }
            }
        }  
    }

    $options = get_option('anticipate_settings');
    Laposta::setApiKey($options['laposta_apikey']);

    $member = new Laposta_Member($_POST['lapostalistid']);
    try{
        $result = $member->create(array(
            'ip' => getUserIP(),
            'email' => $email,
            'source_url' => home_url(),
            'custom_fields' => $custom_fields
            )
        );
    } catch(Exception $e) {
        if($e->getMessage()=='API error: Email address exists'){
            $error = new WP_Error( 'lp_ee', 'This email address already exists on this mailinglist.', $email );
            wp_send_json_error($error);
        } elseif (strpos($e->getMessage(),'cannot be empty')>0){
            $error = new WP_Error( 'lp_ef', 'Please check your input.', $email );
            wp_send_json_error($error);
        } else {
            $error = new WP_Error( 'lp_ge', $e->getMessage() );
            wp_send_json_error($error);    
        }
    }
    wp_send_json_success( 'Thank you for your registration', 200 );   
    
    
	wp_die(); 

}


function getUserIP() {
    if( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
            $addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($addr[0]);
        } else {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

