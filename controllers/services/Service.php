<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\controllers\services;

use Yii;

/**
 * Description of Servicde
 *
 * @author Swedge
 */
class Service {
    //put your code here
    public function generateRandomString($length=32){
        return Yii::$app->getSecurity()->generateRandomString($length); //default is also 32
    }
    
    public function hashPassword($password){
        return Yii::$app->getSecurity()->generatePasswordHash($password);
    }
    
    public function sendEmail($to, $subject, $params, $viewFile='', $layoutFile=''){
        return;
        Yii::$app->mailer->htmlLayout = 'layouts/' . $layoutFile;
        Yii::$app->mailer->compose($viewFile, $params)
            //->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            //->setTextBody($message)
            //->setHtmlBody($message)
            ->send();
    }
}
