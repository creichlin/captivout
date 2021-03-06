<?php
require_once ("include/setup.inc.php");
require_once ("service/sms.inc.php");

$smsConfig = $config->get("sms_gateway")[0];

$view->setLanguage($smsConfig["language"]);

$qs = $_SERVER['QUERY_STRING'];
$qs = explode("[DEL]", $qs);
if(count($qs) == 2) {
  $_SESSION['pa'] = $qs[0];
  $_SESSION['pr'] = $qs[1];
} else if(count($qs) == 1) {
  if($qs[0] == "error") {
    $view->addWarning("authentication-error");
  }
}

$view->set("portalAction", $_SESSION['pa']);
$view->set("portalRedirect", $_SESSION['pr']);

if(isset($_POST['submit'])) {
  $sms = new \sms\Sms($smsConfig, $_POST['number']);

  if($sms->isValid()) {
    try {
      $sms->send();
      $view->addInfo("sendt-sms");
    } catch(\sms\NoUnusedTicketsException $e) {
      $view->addInfo("no-unused-tickets");
    } catch(\sms\NumberIsLockedException $e) {
      $view->addInfo("number-is-blocked");
    } catch(\sms\GatewayErrorException $e) {
      $view->addInfo("gateway-error");
    }
  } else {
    $view->addWarning("invalid-mobile-number");
  }
}


$view->render('requestSms.html');

?>
