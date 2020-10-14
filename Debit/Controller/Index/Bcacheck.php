<?php
namespace Faspay\Debit\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Faspay\Debit\Helper\BcaUtility;
use Faspay\Debit\Helper\Data;


use Magento\Sales\Model\OrderFactory;
use Faspay\Debit\Model\FaspayOrderFactory;

class Bcacheck extends Action
{

    protected $bcaUtility;
    protected $data;
    protected $orderFactory;
    protected $faspayOrderFactory;
    /**
     * Index constructor.
     */
    public function __construct(Context $context,
                                BcaUtility $bcaUtility,
                                Data $data,
                                OrderFactory $orderFactory,
                                FaspayOrderFactory $faspayOrderFactory
                                )
    {
        parent::__construct($context);
        $this->bcaUtility = $bcaUtility;
        $this->data = $data;
        $this->orderFactory = $orderFactory;
        $this->faspayOrderFactory = $faspayOrderFactory;
    }

    public function execute()
    {

        if(isset($_GET['trx_id']) && (isset($_GET['signature']) || isset($_GET['authkey'])))
        {
            $dataModel = $this->faspayOrderFactory->create();

            $faspayOrder = $dataModel->load($_GET['trx_id'], 'trx_id');
            $order = $this->orderFactory->create()->load($faspayOrder->getOrderId());

            //change amount format
            $amount = number_format((float)$order->getSubtotal(), 0, '.', '');

            //change date format to BCA Format
            $date = $this->data->getDate7($order->getCreatedAt());
            $bcaDate = $this->bcaUtility->getBcaDate($date->format('Y-m-d H:i:s'));

            $sig        = $this->bcaUtility->genSignature($this->bcaUtility->getBcaCode(),$bcaDate, $_GET['trx_id'], $amount, $order->getBaseCurrencyCode(), $this->bcaUtility->getKeyId());
            $authkey    = $this->bcaUtility->genAuthKey($this->bcaUtility->getBcaCode(), $_GET['trx_id'], $order->getBaseCurrencyCode(), $bcaDate, $this->bcaUtility->getKeyId());

            $reqSignature = isset($_GET['signature']) ? $_GET['signature'] : '';
            $reqAuthkey   = isset($_GET['authkey']) ? $_GET['authkey'] : '';

            echo $this->bcaUtility->getBcaCode();
            echo "\n";
            echo $bcaDate;
            echo "\n";
            echo $_GET['trx_id'];
            echo "\n";
            echo $amount;
            echo "\n";
            echo $order->getBaseCurrencyCode();
            echo "\n";
            echo $this->bcaUtility->getKeyId();
            echo "\n";

            if($sig == $reqSignature || $authkey == $reqAuthkey)
            {
                echo 1;
            }
            else
            {
                echo 0;
            }

        }

        else{
            
            echo 'THIS PAGE IS FOR CHECK AUTHKEY/SIGNATURE ONLY';
        }
    }
}
?>
