<?php


namespace components\interaction;


class Login extends Interaction
{
    public function login()
    {
        $account  = array('email' => $this->login, 'pass' => $this->password, 'submit' => 'Войти');
        $startUrl = 'https://m.vk.com';
        /**
         * @var Curl $curl
         */
        $curl = $this->transport;
        $this->debug('Заходим на главную страницу...');
        preg_match('~post" action="(.*?)"~s', $curl->doRequest($startUrl), $urlStepTwo);
        $this->debug('Зашли, пытаемся залогиниться');
        preg_match('~service_msg_warning">(.*?)<a~s', $curl->doRequest($urlStepTwo[1], $account), $errors);
        $this->debug('Залогинились, проверяем на ошибки...');
        preg_match('~data\-name="(.*?)"~s', $curl->doRequest($startUrl), $matches);

        if (isset($matches[1])) {
            $this->debug('Зашли как ' . $matches[1]);
        } else {
            throw new \LogicException('Не смогли залогиниться: ' . strip_tags($errors[1]));
        }
    }
} 