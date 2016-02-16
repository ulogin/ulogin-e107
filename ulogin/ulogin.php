<?php
$iso = array(
                "?"=>"YE","?"=>"I","?"=>"G","?"=>"i","?"=>"#","?"=>"ye","?"=>"g",
                "?"=>"A","?"=>"B","?"=>"V","?"=>"G","?"=>"D",
                "?"=>"E","?"=>"YO","?"=>"ZH",
                "?"=>"Z","?"=>"I","?"=>"J","?"=>"K","?"=>"L",
                "?"=>"M","?"=>"N","?"=>"O","?"=>"P","?"=>"R",
                "?"=>"S","?"=>"T","?"=>"U","?"=>"F","?"=>"X",
                "?"=>"C","?"=>"CH","?"=>"SH","?"=>"SHH","?"=>"'",
                "?"=>"Y","?"=>"","?"=>"E","?"=>"YU","?"=>"YA",
                "?"=>"a","?"=>"b","?"=>"v","?"=>"g","?"=>"d",
                "?"=>"e","?"=>"yo","?"=>"zh",
                "?"=>"z","?"=>"i","?"=>"j","?"=>"k","?"=>"l",
                "?"=>"m","?"=>"n","?"=>"o","?"=>"p","?"=>"r",
                "?"=>"s","?"=>"t","?"=>"u","?"=>"f","?"=>"x",
                "?"=>"c","?"=>"ch","?"=>"sh","?"=>"shh","?"=>"",
                "?"=>"y","?"=>"","?"=>"e","?"=>"yu","?"=>"ya","�"=>"","�"=>"","?"=>"-"
            );

function random($length = 10)
{
    $random = '';
		
    for ($i = 0; $i < $length; $i++){
        $random += chr(rand(48, 57));
    }
    return $random;
}

function uploadPhoto($url, $filename){
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_HEADER, 1); 
        $result = curl_exec($ch);
        if (!$result)
            return false;

        preg_match('/Content-Type: \w+(\/)(?<value>\w+)/', $result, $value);
        $ext = $value['value'] == 'jpeg' ? 'jpg' : $value['value'];
        
        $savepath = dirname(dirname(dirname(__FILE__ ))).'/e107_images/avatars/'.$filename.'.'.$ext;
        
        while (file_exists($savepath)){
            $savepath = e_IMAGE.$filename.random().'.'.$ext;
        }
        
        $from = fopen($url,'rb'); 
        $to = fopen($savepath, "wb");
        $size = 0;
        if ($from && $to){
            while(!feof($from)) {
                $size += fwrite($to, fread($from, 1024 * 8 ), 1024 * 8 );
            }
        } else 
            return false;

        fclose($from); 
        fclose($to);
        
        return $filename.'.'.$ext;
}

require_once('../../class2.php');

if(USER){
   header('Location: '. SITEURL); 
}

if (isset($_POST['token'])){
     $token = $_POST['token'];
     
     if (function_exists('file_get_contents')){
        $data = json_decode(file_get_contents('http://ulogin.ru/token.php?token='.$token.'&host='.  urlencode(SITEURL)) , true);
        if (!isset($data['error'])){
             
            $user_id = false;
            $exist = false;
            $email = strip_tags($data['email']);
            
             //достаем привязку аккаунта соцсети из нашей доп. таблицы
            $sql->db_Select("ulogin_user", "uid", "identity = '".$data['identity']."'");
            $row = $sql->db_Fetch();
            if (isset($row['uid'])){
                $user_id = $row['uid'];
                $exist = true;
            }

            //если ранее нашли привязку, пытаемся достать пользователя с указанным user_id
            if($user_id !== false){
                $sql->db_Select("user", "user_id", "user_id = '".$user_id."'");
                $row = $sql->db_Fetch();
                if (isset($row['user_id'])){
                    $user_id = $row['user_id'];
                }else{
                    $user_id = false;
                }
            }

            //если через привязку не нашли юзера, пытаемся найти пользователя с таким же email-адресом
            if($user_id === false){
                //проверяем, чтобы email, полученный от ulogin, был подтвержден
                if($data['verified_email'] == '1') {
                    $sql->db_Select("user", "user_id", "user_email = '" . $email . "'");
                    $row = $sql->db_Fetch();
                    if (isset($row['user_id'])) {
                        $user_id = $row['user_id'];
                    }
                }
            }
            
            //Add new user 
            if(!$user_id){
                 
                $user = array();
                $name = $tp->toDB(strip_tags(isset($data['nickname']) ? $data['nickname'] : strtr($data['first_name'] , $iso)));

                $sql->db_Select("user", "user_id", "user_loginname = '".$name."'");
                $result = $sql->db_Fetch();
                while($result){
                    $sql->db_Select("user", "user_id", "email = '" . $email . "' or username = '".$name."'");
                    $result = $sql->db_Fetch();
                    $name = $tp->toDB(strip_tags(isset($data['nickname']) ? $data['nickname'] : strtr($data['first_name'] , $iso)).'_'.  random(4));
                }
                $user['user_email'] = $email;
                $user['user_loginname'] = $name;
                $user['user_name'] = $name;
                $user['user_login'] = $tp->toDB(strip_tags(strtr($data['first_name'].' '.$data['last_name'], $iso)));
                $user['user_image'] = uploadPhoto($data['photo'], $name);
                $user['user_join'] = time();
                $user['user_hideemail'] = 1;
                $user['user_ban'] = 0;
                $user['user_lastvisit'] = time();
                $user['user_password'] = md5(md5($data['identity']). $token);
                
                $user_id = $sql->db_Insert('user', $user);
                if ($user_id && $exist){
                    $sql->db_Update('ulogin_user','uid = '.$user_id.', token = "'.$token.'" WHERE identity = "'.$data['identity'].'"');
                }else if ($user_id){
                    $sql->db_Insert('ulogin_user', array('uid' => $user_id, 'identity' => $data['identity'], 'token' => $token));
                }
                
            }
            
            //Login user
            $sql->db_Select("user", "user_loginname", "user_id = ".$user_id);
            $result = $sql->db_Fetch();
            if (isset($result['user_loginname'])){
                $login = $result['user_loginname'];
                $sql->db_Select("ulogin_user", "token", "identity = '".$data['identity']."'");
                $result = $sql->db_Fetch();
                if (isset($result['token'])){
                    $password = md5($data['identity']).$result['token'];
                    e107_require_once(e_HANDLER."login.php");
                    $usr = new userlogin($login, $password, false);
                }
            }
        }
         
        $sql->db_Close();      
    }
    header('Location: '. SITEURL);
}
