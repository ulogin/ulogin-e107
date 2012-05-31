global $tp, $pref, $class2, $row;
if (!USER){
    $redirect_url = urlencode(SITEURL.e_PLUGIN.'ulogin/ulogin.php');
    $text = '<script src="http://ulogin.ru/js/ulogin.js"></script>';
    $text.= '<div style="float: right; margin: 7px 55px;">';
    $text.= '<a href="#" id="uLogin" x-ulogin-params="display=window;';
    $text.= 'fields=first_name,last_name,photo,nickname,email;';
    $text.= 'redirect_uri='.$redirect_url.'">';
    $text.= '<img src="http://ulogin.ru/img/button.png" width=187 height=30/></a>';
    $text.= '</div>';
}    
return $text;
