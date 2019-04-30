<?php

class AuthController extends AppController {

    public function getClientToken() {
        // Just generating a random client token and returning it
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff )
        );
    }

    public function start()
    {
        $this->loadModel('User');
        $username = $this->params['url']['username'];
        $password = $this->params['url']['password'];
        if(isset($username) && isset($password)){
            if(!empty($username) && !empty($password))
            {
                $user = $this->User->getAllFromUser($username);
                if($user['password'] == $password){
                    $userId = $user['id'];
                    if(empty($user['auth-uuid'])){
                        $uuid = md5($user["pseudo"]);
                        $this->User->setToUser("auth-uuid", $uuid, $userId);
                    }
                    $accessToken = md5(uniqid(rand(), true));
                    $clientToken = $this->getClientToken();
                    $this->User->read(null, $userId);
                    $this->User->set(array(
                            'auth-accessToken' => $accessToken,
                            'auth-clientToken' => $clientToken
                    ));
                    $this->User->save();
                    echo 'success_ok';
                }else{
                    echo 'error_password';
                }
            }else{
                echo 'Empty Get';
            }
        }else{
            echo 'Error Set';
        }
        exit;
    }

    public function getDataLauncher()
    {
        $this->loadModel('User');
        $this->loadModel('Auth.Rank');
        $username = $this->params['url']['username'];
        $password = $this->params['url']['password'];
        if(isset($username) && isset($password)){
            if(!empty($username) && !empty($password))
            {
                $user = $this->User->getAllFromUser($username);
                if($user['password'] == $password){
                    unset($user['password']);
                    $rank = $user['rank'];
                    switch($rank){
                        case 1:{
                          $user['rank'] = "Membre";
                          break;
                        }
                        case 2:{
                          $user['rank'] = "Modérateur";
                          break;
                        }
                        case 3:{
                          $user['rank'] = "Administrateur";
                          break;
                        }
                        case 4:{
                          $user['rank'] = "Administrateur";
                          break;
                        }
                        default:{
                          $user['rank'] = "not_found";
                          break;
                        }
                    }
                    $conditions = array("Rank.rank_id"  => array($rank));
                    $rank_found = $this->Rank->find('first', array('conditions' => $conditions));
                    if(!empty($rank_found) && $user['rank'] == "not_found"){
                        $user['rank'] = $rank_found['Rank']['name'];
                    }else if(empty($rank_found) && $user['rank'] == "not_found"){
                        $user['rank'] = "Inconnus";
                    }
                    echo json_encode($user);
                }else{
                    echo 'error_password';
                }
            }else{
                echo 'Empty Get';
            }
        }else{
            echo 'Error Set';
        }
        exit;
    }

    public function getDataIngame()
    {
        $this->loadModel('User');
        $this->loadModel('Auth.Rank');
        $accessToken = $this->params['url']['accessToken'];
        $conditions = array("User.auth-accessToken"  => array($accessToken));
        $userFound = $this->User->find('first', array('conditions' => $conditions));
        unset($userFound['User']['password']);
        $rank = $userFound['User']['rank'];
        switch($rank){
            case 1:{
              $userFound['User']['rank'] = "Membre";
              break;
            }
            case 2:{
              $userFound['User']['rank'] = "Modérateur";
              break;
            }
            case 3:{
              $userFound['User']['rank'] = "Administrateur";
              break;
            }
            case 4:{
              $userFound['User']['rank'] = "Administrateur";
              break;
            }
            default:{
              $userFound['User']['rank'] = "not_found";
              break;
            }
        }
        $conditions = array("Rank.rank_id"  => array($rank));
        $rank_found = $this->Rank->find('first', array('conditions' => $conditions));
        if(!empty($rank_found) && $userFound['User']['rank'] == "not_found"){
            $userFound['User']['rank'] = $rank_found['Rank']['name'];
        }else if(empty($rank_found) && $userFound['User']['rank'] == "not_found"){
            $userFound['User']['rank'] = "Inconnus";
        }
        echo json_encode($userFound['User']);
        exit;
    }

    public function version()
    {
        $plugins = $this->EyPlugin->pluginsLoaded;
        $version = $plugins->{'empiredev.auth'}->{'version'};
        echo 'AuthMineweb OK - Version '.$version;
        exit;
    }

}
