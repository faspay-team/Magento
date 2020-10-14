<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 27/03/2018
 * Time: 16:22
 */

namespace Faspay\Debit\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Faspay\Debit\Helper\Data as Helper;
use Faspay\Debit\Model\ConfigProvider;

class Result extends Template
{

    protected $helper;
    protected $config;
    
    public function __construct(Context $context,
                                Helper $helper,
                                ConfigProvider $configProvider,
                                array $data = [])
    {

        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->config = $configProvider;


    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getHelper(){

      return $this->helper;
    }

    public function getOrder(){

        return $this->helper->getOrder();
    }

    public function getFaspayOrder(){

        return $this->helper->getFaspayOrder();
    }

    public function getFaspayChannel(){

        return $this->helper->getFaspayChannel();
    }

    public function getImage($code){
        return $this->config->getIcon($code);
    }

    public function getExpiry(){

        //get expiry minute from ADMIN and set timezone
        $expiry = $this->helper->getExpiry();
        $timeZone = new \DateTimeZone('Asia/Jakarta');

        //parse to datetime
        $date = new \DateTime( $this->getOrder()->getCreatedAt(), new \DateTimeZone('UTC'));
        $dateNow = new \DateTime( date('Y-m-d H:i:s', time()), new \DateTimeZone('UTC'));

        //set the datetime with the timezone
        $date->setTimeZone($timeZone);
        $dateNow->setTimezone($timeZone);

        //set to hour minute second format
        $timecreated = $date->format('H:i:s');
        $timenow = $dateNow->format('H:i:s');

        //set time the code will be expired
        $timestamp = strtotime($timecreated) + 60 * $expiry;
        $timeexpired = date("H:i:s",$timestamp);

        //set the remaining time
        $timeleft = strtotime($timeexpired)-strtotime($timenow);

        return date("H:i:s",$timeleft);
    }

    public function getExpiredOrder(){

        //get expiry minutes
        $expiry = $this->helper->getExpiry();

        //gate the date
        $date = new \DateTime( $this->getOrder()->getCreatedAt(), new \DateTimeZone('UTC'));
        $dateNow = new \DateTime( date('Y-m-d H:i:s', time()), new \DateTimeZone('UTC'));

        //set to hour minute second format
        $timecreated = $date->format('H:i:s');
        $timenow = $dateNow->format('H:i:s');

        //to strotime
        $to = strtotime($timecreated) + 60 * $expiry;
        $from = strtotime($timenow);

        $seconds = $to - $from - 2;

        return $seconds;
    }
}