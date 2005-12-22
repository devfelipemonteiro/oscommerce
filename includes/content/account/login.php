<?php
/*
  $Id:login.php 188 2005-09-15 02:25:52 +0200 (Do, 15 Sep 2005) hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/

  require('includes/classes/account.php');

  class osC_Account_Login extends osC_Template {

/* Private variables */

    var $_module = 'login',
        $_group = 'account',
        $_page_title = HEADING_TITLE_LOGIN,
        $_page_contents = 'login.php';

/* Class constructor */

    function osC_Account_Login() {
      global $osC_Services, $breadcrumb;

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
      if (osc_empty(session_id())) {
        tep_redirect(tep_href_link(FILENAME_INFO, 'cookie', 'AUTO'));
      }

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add(NAVBAR_TITLE_LOGIN, tep_href_link(FILENAME_ACCOUNT, $this->_module, 'SSL'));
      }

      if ($_GET[$this->_module] == 'process') {
        $this->_process();
      }
    }

/* Private methods */

    function _process() {
      global $osC_Database, $osC_Session, $messageStack, $osC_Customer, $osC_NavigationHistory;

      if (osC_Account::checkEntry($_POST['email_address'])) {
        if (osC_Account::checkPassword($_POST['password'], $_POST['email_address'])) {
          if (SERVICE_SESSION_REGENERATE_ID == 'True') {
            $osC_Session->recreate();
          }

          $osC_Customer->setCustomerData(osC_Account::getID($_POST['email_address']));

          $Qupdate = $osC_Database->query('update :table_customers_info set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = :customers_info_id');
          $Qupdate->bindTable(':table_customers_info', TABLE_CUSTOMERS_INFO);
          $Qupdate->bindInt(':customers_info_id', $osC_Customer->getID());
          $Qupdate->execute();

          $_SESSION['cart']->restore_contents();

          $osC_NavigationHistory->removeCurrentPage();

          if ($osC_NavigationHistory->hasSnapshot()) {
            $osC_NavigationHistory->redirectToSnapshot();
          } else {
            tep_redirect(tep_href_link(FILENAME_DEFAULT, '', 'AUTO'));
          }
        } else {
          $messageStack->add('login', TEXT_LOGIN_ERROR);
        }
      } else {
        $messageStack->add('login', TEXT_LOGIN_ERROR);
      }
    }
  }
?>