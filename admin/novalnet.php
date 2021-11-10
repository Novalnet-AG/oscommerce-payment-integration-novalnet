<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet AG
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script : novalnet.php
 */
  ob_start();
  require('includes/application_top.php');
  if(file_exists(DIR_WS_INCLUDES . 'template_top.php')) {
	require(DIR_WS_INCLUDES . 'template_top.php');
  }else{
	require(DIR_WS_INCLUDES . 'header.php');
  }
  require_once(DIR_FS_CATALOG . 'includes/classes/novalnet/class.Novalnet.php');
  NovalnetCore::loadConstants();
  if ( !empty($_REQUEST['guide']) && !empty($_REQUEST['process'])) {
  ?>
    <div class='novalnet_guide'>
	  <div class='nn_map_header'> <?php echo MODULE_PAYMENT_NOVALNET_MAP_PAGE_HEADER; ?> </div>
      <div style='height:100%'>
	    <iframe src='https://admin.novalnet.de' style='width:100%;height:580px;' frameborder='0'></iframe>
	  </div>
    </div><?php
  }

  $order_id = tep_db_prepare_input($_REQUEST['oID']);
  if (!is_numeric($order_id) && empty($_REQUEST['guide'])) {
	  header('Location: '.DIR_WS_CATALOG.'admin/orders.php');
	  exit;
  }
  require (DIR_WS_CLASSES.'order.php');
  if(!file_exists(DIR_WS_INCLUDES .'template_top.php')) {
  ?>

  <html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2"></table>
  <?php
}
  $order = new order($order_id);
  $local_nn_trans_info = NovalnetCore::getNovalnetTransDetails($order_id);
  if (!empty($local_nn_trans_info['payment_type'])) {
	$local_nn_trans_info = NovalnetCore::getNovalnetTransDetails($order_id);
	$payment_key = $local_nn_trans_info['payment_id'];
	$data['nn_vendor'] = $local_nn_trans_info['vendor'];
    $data['nn_auth_code'] = $local_nn_trans_info['auth_code'];
    $data['nn_product'] = $local_nn_trans_info['product'];
    $data['tid'] = $local_nn_trans_info['tid'];
    $trans_details = NovalnetInterface::getTransDetails($data);
    NovalnetInterface::apiAmountCalculation($local_nn_trans_info, (array)$trans_details);
	$local_nn_trans_info['org_amount'] = $local_nn_trans_info['total_amount'];
	if ($local_nn_trans_info['payment_id'] == 27) {
	  $local_nn_trans_info['total_amount'] = NovalnetCore::getNovalnetCallbackAmount($order_id);
	}
  }

  // Novalnet onhold transaction confirmation / cancellation process
  if ( !empty($_REQUEST['trans_confirm']) ) {
	// Updation Begin
	$process_result = '';
	if ( !empty($_REQUEST['trans_status']) ) {
	  $process_result = NovalnetInterface::onholdTransConfirm(array(
		  	'tid' 		   => $local_nn_trans_info['tid'],
			'status' 	   => $_REQUEST['trans_status'],
			'payment_id'   => $local_nn_trans_info['payment_id'],
			'order_id' 	   => $order_id,
			'vendor'       => $local_nn_trans_info['vendor'],
			'product'      => $local_nn_trans_info['product'],
			'tariff'  	   => $local_nn_trans_info['tariff'],
			'auth_code'    => $local_nn_trans_info['auth_code'],
			'payment_type' => $local_nn_trans_info['payment_type']
		  ));
	  if ($process_result == '') {
	    $messageStack->add_session(MODULE_PAYMENT_NOVALNET_ORDER_UPDATE, 'success');
	    header('Location: ' . DIR_WS_CATALOG . 'admin/orders.php?page=1&oID='.$order_id.'&action=edit');
	    exit;
	  }
    }
    // Updation End
?>
	<div class="boxCenter">
	  <table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tbody>
		  <tr>
			<td width="160" rowspan="2"><img src="<?php echo DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'; ?>" alt="Novalnet" border="0"/></td>
			<td class="pageHeading"><?php echo MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_MESSAGE_HEADING; ?></td>
		  </tr>
		  <tr>
			<td valign="top" class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID; ?> <?php echo $local_nn_trans_info['tid']; ?></td>
		  </tr>
		</tbody>
	  </table>
	  <form id="novalnet_status_change" name='novalnet_status_change' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
		<input type='hidden' name='trans_confirm' value='1' />
		<input type='hidden' name='nn_form_submit' value='1' />
		<input type='hidden' name='oID' value='<?php echo $order_id; ?>' />
		<div class='novalnet_error_message'>
		<?php
			if (!empty($process_result)) {
			  echo html_entity_decode($process_result, ENT_QUOTES, "UTF-8");
			}
		?>
		</div>
		<table class="novalnet_orders_table">
		  <tr>
			<td class="main"><?php echo MODULE_PAYMENT_NOVALNET_SELECT_STATUS_MESSAGE; ?> :</td>
			<td class="main">
				<select name="trans_status" id='trans_status'>
					<option value=''><?php echo MODULE_PAYMENT_NOVALNET_SELECT_STATUS_OPTION; ?></option>
					<option value='100'><?php echo MODULE_PAYMENT_NOVALNET_CONFIRM_MESSAGE; ?></option>
					<option value='103'><?php echo MODULE_PAYMENT_NOVALNET_CANCEL_MESSAGE; ?></option>
				</select>
		    </td>
		  </tr>
		  <tr>
		    <td colspan='2'>
				<input type='button' name='trans_confirm' value='<?php echo MODULE_PAYMENT_NOVALNET_UPDATE_BTN; ?>' onclick="validate_status_change();" />
				<input type='button' name='trans_confirm' value='<?php echo MODULE_PAYMENT_NOVALNET_BACK_BTN; ?>' onclick="redirect_orders_list_page();" /></td>
		    </td>
		  </tr>
	    </table>
	  </form>
	  <div class="loader" id="loader" style="display:none"></div>
	  <script type='text/javascript'>
	    function validate_status_change() {
		  if (document.getElementById('trans_status').value =='') {
			alert('<?php echo html_entity_decode(MODULE_PAYMENT_NOVALNET_SELECT_STATUS_ERROR_MESSAGE, ENT_QUOTES, "UTF-8"); ?>');
			return false;
		  } else {
			document.getElementById('loader').style.display='block';
			document.getElementById('novalnet_status_change').submit();
		  }
	    }
	  </script>
	</div>
<?php
  }

  // Novalnet transaction amount and due_date updation process
  if ( !empty($_REQUEST['amount_change']) ) {
	// Updation Begin
	if (!in_array($payment_key, array(27,37))) {
	  header('Location: '.DIR_WS_CATALOG.'admin/orders.php?page=1&oID='.$order_id.'&action=edit');
	  exit;
	}
	if ( isset($_REQUEST['amount_change_newamount']) ) {
	  $input_due_date = '0000-00-00';
	  if($payment_key == 27) {
		  if (($_REQUEST['amount_change_year'] != '' && $_REQUEST['amount_change_month'] != '' && $_REQUEST['amount_change_day'] != '') ) {
			$input_due_date = $_REQUEST['amount_change_year'].'-'.$_REQUEST['amount_change_month'].'-'.$_REQUEST['amount_change_day'];
		  } else {
			$invoice_info = NovalnetCore::getPreInvoiceAcccountInfo($local_nn_trans_info['tid']);
			$input_due_date = $invoice_info['due_date'];
		  }
	  }
	  if(in_array($local_nn_trans_info['payment_type'], array('novalnet_invoice','novalnet_prepayment')) && ($input_due_date != '0000-00-00' && strtotime($input_due_date) < strtotime(date('Y-m-d'))) ) {
		$process_result = MODULE_PAYMENT_NOVALNET_VALID_DUEDATE_MESSAGE;
	  }elseif(in_array($local_nn_trans_info['payment_type'], array('novalnet_invoice','novalnet_prepayment')) && ( date('Y-m-d', strtotime($input_due_date)) != $input_due_date )){
		$process_result = MODULE_PAYMENT_NOVALNET_INVALID_DUE_DATE;
	  } else {
		$process_result = NovalnetInterface::updateTransAmount(array(
					'tid' 			  => $local_nn_trans_info['tid'],
					'status' 		  => $local_nn_trans_info['gateway_status'],
					'payment_id' 	  => $local_nn_trans_info['payment_id'],
					'payment_type' 	  => $local_nn_trans_info['payment_type'],
					'vendor'          => $local_nn_trans_info['vendor'],
					'product'         => $local_nn_trans_info['product'],
					'tariff'       	  => $local_nn_trans_info['tariff'],
					'auth_code'       => $local_nn_trans_info['auth_code'],
					'order_id' 		  => $order_id,
					'due_date' 		  => $input_due_date,
					'total_amount' 	  => $local_nn_trans_info['total_amount'],
					'amount' 		  => $_REQUEST['amount_change_newamount'],
					'amount_currency' => $order->info['currency']
					));
		if ($process_result == '') {
		  $messageStack->add_session(MODULE_PAYMENT_NOVALNET_ORDER_UPDATE, 'success');
		  header('Location: '.DIR_WS_CATALOG.'admin/orders.php?page=1&oID='.$order_id.'&action=edit');
		  exit;
		}
	  }
	}
	// Updation End
	if ($payment_key == 27) {
	  $invoice_info = NovalnetCore::getPreInvoiceAcccountInfo($local_nn_trans_info['tid']);
	  $input_due_date = $invoice_info['due_date'];
	  $input_day = $input_month = $input_year = '';
	  if ($input_due_date != '0000-00-00') {
		$strtotime_input_date = strtotime($input_due_date);
		$input_day = date('d',$strtotime_input_date);
		$input_month = date('m',$strtotime_input_date);
		$input_year = date('Y',$strtotime_input_date);
	  }
	} else {
	  $invoice_info = $local_nn_trans_info;
	}
	$input_amount = ( $local_nn_trans_info['total_amount'] != 0 && $local_nn_trans_info['payment_id'] == 27 ) ? $local_nn_trans_info['org_amount'] - $local_nn_trans_info['total_amount'] : $local_nn_trans_info['amount'];
?>
    <div class="boxCenter">
    <table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tbody>
        <tr>
          <td width="160" rowspan="2"><img src="<?php echo DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'; ?>" alt="Novalnet" border="0"/></td>
          <td class="pageHeading">
          <?php
          $heading = ($payment_key == 37) ? MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_MESSAGE : MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_MESSAGE;
          echo $heading;
          ?>
          </td>
        </tr>
        <tr>
          <td valign="top" class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID; ?> <?php echo $local_nn_trans_info['tid']; ?></td>
        </tr>
      </tbody>
    </table>
    <form id="novalnet_amount_change" name='novalnet_amount_change' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
	  <input type='hidden' name='payment_status' value='<?php echo $local_nn_trans_info['gateway_status']; ?>' />
	  <input type='hidden' name='amount_change' value='1' />
	  <input type='hidden' name='nn_form_submit' value='1' />
	  <input type='hidden' name='oID' value='<?php echo $order_id; ?>' />
	  <div class='novalnet_error_message'>
	  <?php
		if (!empty($process_result)) {
		  echo html_entity_decode($process_result, ENT_QUOTES, "UTF-8");
		}
	  ?>
	  </div>
	  <table class="novalnet_orders_table">
		<tr>
		  <td class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANS_AMOUNT_TEXT; ?> :</td>
		  <td class="main">
		    <input type='text' name='amount_change_newamount' id='amount_change_newamount' autocomplete='off' onkeypress='return novalnetAllowNumeric(event)' value='<?php echo $input_amount; ?>' /> <?php echo MODULE_PAYMENT_NOVALNET_CP_REFUND_AMOUNT_EX; ?>
		  </td>
		</tr>
	  <?php
		$invpre_amount_change = 0;
		if ($payment_key == 27) {
		  $invpre_amount_change = 1;
	  ?>
	    <tr>
		  <td class="main"><p><?php echo MODULE_PAYMENT_NOVALNET_TRANS_DUE_DATE_MESSAGE; ?> :</p></td>
		  <td class="main">
			<select name='amount_change_day' id='amount_change_day'>
        <?php
        for($i = 1; $i <= 31; $i++) {
        ?>
        <option <?php echo (($input_day == $i)?'selected':''); ?> value="<?php echo (($i < 10)?'0'.$i:$i); ?>"><?php echo (($i < 10)?'0'.$i:$i); ?></option>
        <?php
        }
        ?>
		  </select>
		  <select name='amount_change_month' id='amount_change_month'>
			<?php
			for($i = 1; $i <= 12; $i++) {
			?>
			<option <?php echo (($input_month == $i)?'selected':''); ?> value="<?php echo (($i < 10)?'0'.$i:$i); ?>"><?php echo (($i < 10)?'0'.$i:$i); ?></option>
			<?php
			}
			?>
		  </select>
		  <select name='amount_change_year' id='amount_change_year'>
			<?php
			$year_val = date('Y');
			for($i = $year_val; $i <= ($year_val+1); $i++) {
			?>
			<option <?php echo (($input_year == $i)?'selected':''); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
			<?php
			}
			?>
		  </select>
		  </td>
        </tr>
	    <?php
		  }
	    ?>
        <tr>
	      <input type='hidden' name='invpre_amount_change' id = 'invpre_amount_change' value='<?php echo $invpre_amount_change;?>' />
		  <td colspan='2'>
			  <input type='button' name='trans_confirm' value='<?php echo MODULE_PAYMENT_NOVALNET_UPDATE_BTN; ?>' onclick="validate_amount_change();" />
			  <input type='button' name='trans_confirm' value='<?php echo MODULE_PAYMENT_NOVALNET_BACK_BTN; ?>' onclick="redirect_orders_list_page();" /></td>
          </td>
		</tr>
      </table>
    </form>
    <div class="loader" id="loader" style="display:none"></div>
    <script type='text/javascript'>
	  function validate_amount_change() {
 	    var invpre_amount_change = document.getElementById('invpre_amount_change').value;
	    var changeamount = (document.getElementById('amount_change_newamount').value)/100;
	    changeamount = parseFloat(changeamount).toFixed(2);
	    if (changeamount.trim() == '' || changeamount.trim() <= 0 || isNaN(changeamount)) {
		  alert('<?php echo MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE; ?>');
		  return false;
	    }
	    if (invpre_amount_change == 1) {
		  var amount_change_year = document.getElementById('amount_change_year').value;
		  var amount_change_month = document.getElementById('amount_change_month').value;
		  var amount_change_day = document.getElementById('amount_change_day').value;
		  if (!confirm('<?php echo html_entity_decode(MODULE_PAYMENT_NOVALNET_ORDER_AMT_DUEDATE_UPDATE_TEXT, ENT_QUOTES, "UTF-8");?>')) {
		    return false;
		  }
	    } else {
		  if (!confirm('<?php echo html_entity_decode(MODULE_PAYMENT_NOVALNET_ORDER_AMT_UPDATE_TEXT, ENT_QUOTES, "UTF-8"); ?>')) {
		    return false;
		  }
	    }
	    document.getElementById('loader').style.display='block';
	    document.getElementById('novalnet_amount_change').submit();
	  }
    </script>
  </div>
<?php
}

/**
 * Novalnet Refund process
 */
if ( !empty($_REQUEST['amount_refund']) ) {
  // Updation Begin
  if( !empty($_REQUEST['payment_refund']) ) {
    $account_holder = $iban = $bic = '';
    if ( isset($_REQUEST['refund_payment_type']) && trim($_REQUEST['refund_payment_type']) == 'SEPA' ) {
	  $account_holder = ( ($_REQUEST['refund_payment_type_accountholder']!='') ? $_REQUEST['refund_payment_type_accountholder'] : $order->customer['name'] );
	  $iban = $_REQUEST['refund_payment_type_iban'];
	  $bic = $_REQUEST['refund_payment_type_bic'];
    }
    $account_holder = (($account_holder == '') ? $order->customer['name'] : $account_holder);
    $process_result = NovalnetInterface::refundTransAmount(array(
		'tid' 						   => $local_nn_trans_info['tid'],
		'refund_ref'                   => isset($_REQUEST['refund_ref']) ? trim($_REQUEST['refund_ref']) : '',
		'vendor'                       => $local_nn_trans_info['vendor'],
		'product'                      => $local_nn_trans_info['product'],
		'tariff'                       => $local_nn_trans_info['tariff'],
		'auth_code'                    => $local_nn_trans_info['auth_code'],
		'refund_trans_amount' 		   => $_REQUEST['refund_trans_amount'],
		'refund_trans_amount_currency' => $order->info['currency'],
		'payment_id' 				   => $local_nn_trans_info['payment_id'],
		'payment_type' 				   => $local_nn_trans_info['payment_type'],
		'refund_amount' 			   => $local_nn_trans_info['refund_amount'],
		'test_mode' 				   => $local_nn_trans_info['test_mode'],
		'total_amount' 				   => $local_nn_trans_info['total_amount'],
		'orig_orderamount'			   => $local_nn_trans_info['amount'],
		'order_id' 					   => $order_id,
		'subs_id' 					   => $local_nn_trans_info['subs_id'],
		'additional_note' 			   => $_REQUEST['additional_note'],
		'account_holder' 			   => $account_holder,
		'refund_paymenttype' 		   => ((isset($_REQUEST['refund_payment_type'])) ? strtolower($_REQUEST['refund_payment_type']) : 'NO'),
		'iban' 						   => $iban,
		'bic' 						   => $bic
	  ));

    if ($process_result == '') {
	  $messageStack->add_session(MODULE_PAYMENT_NOVALNET_ORDER_UPDATE, 'success');
	  header('Location: '.DIR_WS_CATALOG.'admin/orders.php?page=1&oID='.$order_id.'&action=edit');
	  exit;
    }
  }
  // Updation End
?>
  <div class="boxCenter">
    <table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tbody>
        <tr>
          <td width="160" rowspan="2"><img src="<?php echo DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'; ?>" alt="Novalnet" border="0"/></td>
          <td class="pageHeading"><?php echo MODULE_PAYMENT_NOVALNET_TRANS_REFUND_MESSAGE; ?></td>
        </tr>
        <tr>
          <td valign="top" class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID; ?> <?php echo $local_nn_trans_info['tid']; ?></td>
        </tr>
      </tbody>
    </table>
    <form id="novalnet_trans_refund" name='novalnet_trans_refund' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
	  <input type='hidden' name='payment_type' value='<?php echo $local_nn_trans_info['payment_type']; ?>' />
	  <input type='hidden' name='payment_id' value='<?php echo $payment_key; ?>' />
	  <input type='hidden' name='amount_refund' value='1' />
	  <input type='hidden' name='nn_form_submit' value='1' />
	  <input type='hidden' name='oID' value='<?php echo $order_id; ?>' />
	  <input type='hidden' name='payment_refund' value='<?php echo 'True'; ?>' />
	  <input type='hidden' name='additional_note' value='<?php echo $local_nn_trans_info['additional_note']; ?>' />
	  <input type='hidden' name='account_holder' value='<?php echo $local_nn_trans_info['account_holder']; ?>' />
	  <input type='hidden' name='payment_currency' value='<?php echo $order->info['currency']; ?>' />
	  <div class='novalnet_error_message'>
	  <?php
		if (!empty($process_result)) {
		  echo $process_result;
	    }
	  ?>
	  </div>
	  <table class="novalnet_orders_table">
	  <?php
		if (in_array($payment_key, array(27,33,49))) {
	  ?>
		  <tr>
		    <td class="main"><?php echo MODULE_PAYMENT_NOVALNET_CP_REFUND_PAYMENTTYPE_MESSAGE; ?> :</td>
		    <td class="main">
		      <input type='radio' name='refund_payment_type' id='refund_payment_type_none' value='NONE' checked onclick="refund_payment_type_element_handle('none');"/> <?php echo 'None'; ?>
		      <input type='radio' name='refund_payment_type' id='refund_payment_type_sepa' value='SEPA' onclick="refund_payment_type_element_handle();"/> <?php echo MODULE_PAYMENT_NOVALNET_SEPA_TEXT_TITLE; ?>
		    </td>
          </tr>
          <?php
			$order_date = strtotime(date("Y-m-d",strtotime($local_nn_trans_info['date'])));
			if ( strtotime(date('Y-m-d')) > $order_date ) { ?>
			  <tr>
			    <td class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANS_REFUND_REFERENCE; ?> :</td>
		        <td class="main">
				  <input type='text' style='width:100px;' autocomplete='off' name='refund_ref' id='refund_ref' value=''/>
			    </td>
	          </tr>
		  <?php } ?>
          <tr>
            <td class="main" colspan="2">
			  <table>
			    <tr id="direct_debit_sepa_tabletr_accountholder">
				  <td class="main" style='width:200px;'>
				    <?php echo MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER; ?> :</td>
				  <td class="main">
				    <input type='text' name='refund_payment_type_accountholder' id='refund_payment_type_accountholder' autocomplete='off' value='<?php echo (($local_nn_trans_info['account_holder']!='') ? $local_nn_trans_info['account_holder'] : $order->customer['name']); ?>'/>
				  </td>
		        </tr>
			    <tr id="direct_debit_sepa_tabletr_iban">
			      <td class="main" style='width:200px;'><?php echo MODULE_PAYMENT_NOVALNET_IBAN; ?> :</td>
				  <td class="main">
 				    <input type='text' name='refund_payment_type_iban' id='refund_payment_type_iban' autocomplete='off' value='' />
				  </td>
			    </tr>
			    <tr id="direct_debit_sepa_tabletr_bic">
				  <td class="main" style='width:200px;'><?php echo MODULE_PAYMENT_NOVALNET_BIC; ?> :</td>
				  <td class="main">
				    <input type='text' name='refund_payment_type_bic' id='refund_payment_type_bic' autocomplete='off' value='' />
				  </td>
			    </tr>
			  </table>
		    </td>
		  </tr>
		  <script type='text/javascript'>
		    function refund_payment_type_element_handle() {
		      if (document.getElementById('refund_payment_type_sepa').checked) {
			    document.getElementById('direct_debit_sepa_tabletr_accountholder').style.display="block";
			    document.getElementById('direct_debit_sepa_tabletr_iban').style.display="block";
			    document.getElementById('direct_debit_sepa_tabletr_bic').style.display="block";
		      } else {
			    document.getElementById('direct_debit_sepa_tabletr_accountholder').style.display="none";
			    document.getElementById('direct_debit_sepa_tabletr_iban').style.display="none";
			    document.getElementById('direct_debit_sepa_tabletr_bic').style.display="none";
		      }
            }
            refund_payment_type_element_handle();
          </script>
      <?php
        }
      ?>
        <tr>
		  <?php if ($local_nn_trans_info['total_amount'] != 0) {?>
            <td class="main"><?php echo MODULE_PAYMENT_NOVALNET_CP_PARTIAL_REFUND_AMT_MESSAGE; ?> :</td>
            <td class="main">
		      <?php $amount = (($local_nn_trans_info['payment_id'] == 6) ? ($local_nn_trans_info['total_amount'] - $local_nn_trans_info['refund_amount']) : $local_nn_trans_info['amount']);
		      ?>
              <input type='text' style='width:100px;' name='refund_trans_amount' id='refund_trans_amount' autocomplete='off' onkeypress="return novalnetAllowNumeric(event)"; value='<?php echo $amount; ?>' /> <?php echo MODULE_PAYMENT_NOVALNET_CP_REFUND_AMOUNT_EX;?>
            </td>
          <?php } ?>
        </tr>
        <tr>
          <td colspan='2'>
			  <input type='button' name='trans_confirm' value='<?php echo MODULE_PAYMENT_NOVALNET_CONFIRM_BTN; ?>' onclick="validate_refund_amount();" />
			  <input type='button' name='trans_confirm' value='<?php echo MODULE_PAYMENT_NOVALNET_BACK_BTN; ?>' onclick="redirect_orders_list_page();" />
          </td>
        </tr>
      </table>
    </form>
    <div class="loader" id="loader" style="display:none"></div>
    <script type='text/javascript'>
	  function validate_refund_amount() {
		if(document.getElementById('refund_ref') != null) {
          var refund_ref = document.getElementById('refund_ref').value;
          refund_ref = refund_ref.trim();
          var re = /[\/\\#,+!^()$~%.":*?<>{}]/g;
          if(re.test(refund_ref)) {
            alert('<?php echo MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR; ?>');
            return false;
          }
        }
        var refund_trans_amount = (document.getElementById('refund_trans_amount').value)/100;
	    refund_trans_amount = parseFloat(refund_trans_amount).toFixed(2);
	    if (refund_trans_amount.trim() == '' || refund_trans_amount.trim() <= 0 || isNaN(refund_trans_amount)) {
		  alert('<?php echo html_entity_decode(MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE, ENT_QUOTES, "UTF-8"); ?>');
		  return false;
	    }
		if (document.getElementById('refund_payment_type_sepa') && document.getElementById('refund_payment_type_sepa').checked) {
		  var accholder = document.getElementById('refund_payment_type_accountholder').value;
		  var iban = document.getElementById('refund_payment_type_iban').value;
		  var bic = document.getElementById('refund_payment_type_bic').value;
		  if ( accholder.trim() == '' || iban.trim() == '' ||  bic.trim()== '') {
			alert('<?php echo MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR; ?>');
			return false;
		  }
		}
		document.getElementById('loader').style.display='block';
		document.getElementById('novalnet_trans_refund').submit();
	  }
    </script>
  </div>
<?php
}

// Novalnet subscription transaction cancellation process
if (!empty($_REQUEST['subs_cancel'])) {
  // Updation Begin
  if (!empty($_REQUEST['subscribe_termination_reason'])) {
	$process_result = NovalnetInterface::subscriptionTransStop(array(
		'tid' 				 => $local_nn_trans_info['tid'],
		'vendor'             => $local_nn_trans_info['vendor'],
		'product'            => $local_nn_trans_info['product'],
		'tariff'          	 => $local_nn_trans_info['tariff'],
		'auth_code'          => $local_nn_trans_info['auth_code'],
		'payment_id' 		 => $local_nn_trans_info['payment_id'],
		'termination_reason' => $_REQUEST['subscribe_termination_reason'],
		'order_id' 			 => $_REQUEST['oID'],
		'payment_type' 		 => $local_nn_trans_info['payment_type']
	));
	if ($process_result == '') {
	  $messageStack->add_session(MODULE_PAYMENT_NOVALNET_ORDER_UPDATE, 'success');
	  header('Location: '.DIR_WS_CATALOG.'admin/orders.php?page=1&oID='.$order_id.'&action=edit');
	  exit;
	}
  }
  // Updation End
?>
  <div class="boxCenter">
    <table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tbody>
        <tr>
          <td width="160" rowspan="2"><img src="<?php echo DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'; ?>" alt="Novalnet" border="0"/></td>
          <td class="pageHeading"><?php echo MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_MESSAGE; ?></td>
        </tr>
        <tr>
          <td valign="top" class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID; ?> <?php echo $local_nn_trans_info['tid']; ?></td>
        </tr>
      </tbody>
    </table>
    <form id="novalnet_unsubscribe" name='novalnet_unsubscribe' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
      <input type='hidden' name='subs_cancel' value='1' />
      <input type='hidden' name='nn_form_submit' value='1' />
      <input type='hidden' name='oID' value='<?php echo $order_id; ?>' />
      <div class='novalnet_error_message'>
      <?php

        if (!empty($process_result)) {
	      echo html_entity_decode($process_result, ENT_QUOTES, "UTF-8");
        }
      ?>
      </div>
      <table class="novalnet_orders_table">
        <tr>
          <td class="main"><?php echo MODULE_PAYMENT_NOVALNET_PLEASE_SELECT_TER_REASON_MESSAGE; ?> :</td>
          <td class="main">
          <?php
            $subs_termination_reason = array(MODULE_PAYMENT_NOVALNET_SUBS_OFFER_TOO_EXPENSIVE,MODULE_PAYMENT_NOVALNET_SUBS_FRAUD,MODULE_PAYMENT_NOVALNET_SUBS_PARTNER_HAS_INTERVENED,MODULE_PAYMENT_NOVALNET_SUBS_FINANCIAL_DIFFICULTIES,MODULE_PAYMENT_NOVALNET_SUBS_CONTENT_DIDNOT_MEET_EXPECT,MODULE_PAYMENT_NOVALNET_SUBS_CONTENT_NOT_SUFFICIENT,MODULE_PAYMENT_NOVALNET_SUBS_INTEREST_ONLY_TEST_ACCESS,MODULE_PAYMENT_NOVALNET_SUBS_PAGE_TOO_SLOW,MODULE_PAYMENT_NOVALNET_SUBS_SATISFIED_CUSTOMER,MODULE_PAYMENT_NOVALNET_SUBS_ACCESS_PROBLEMS,MODULE_PAYMENT_NOVALNET_SUBS_OTHER);
          ?>
            <select name="subscribe_termination_reason" id ="subscribe_termination_reason">
			  <option value=''><?php echo MODULE_PAYMENT_NOVALNET_SELECT_REASON_MESSAGE; ?></option>
			  <?php
			    foreach ($subs_termination_reason as $val) {
			  ?>
				  <option value='<?php echo $val; ?>'><?php echo $val; ?></option>
			  <?php
			    }
              ?>
            </select>
		  </td>
        </tr>
        <tr>
          <td colspan='2'><input type='button' name='trans_confirm' value='<?php echo MODULE_PAYMENT_NOVALNET_CONFIRM_BTN; ?>' onclick="validate_unsubscribe_form();" />
            <input type='button' name='trans_confirm' value='<?php echo MODULE_PAYMENT_NOVALNET_BACK_BTN; ?>' onclick="redirect_orders_list_page();" />
          </td>
        </tr>
      </table>
    </form>
    <div class="loader" id="loader" style="display:none"></div>
    <script type='text/javascript'>
      function validate_unsubscribe_form() {
	    if (document.getElementById('subscribe_termination_reason').value =='') {
		  alert('<?php echo html_entity_decode(MODULE_PAYMENT_NOVALNET_PLEASE_SELECT_TER_REASON_MESSAGE, ENT_QUOTES, "UTF-8"); ?>');
		  return false;
	    }
	    document.getElementById('loader').style.display='block';
	    document.getElementById('novalnet_unsubscribe').submit();
      }
    </script>
  </div>
<?php
}
?>

<?php
/*
* Novalnet book process
*/
if(!empty($_REQUEST['book_amount'])) {
  
  // Updation begins
  if(!empty($_REQUEST['process_book_amount'])) {
    $process_result = NovalnetInterface::bookTransAmount(array(
	  'vendor'          => $local_nn_trans_info['vendor'],
      'product'         => $local_nn_trans_info['product'],
      'tariff'    	    => $local_nn_trans_info['tariff'],
      'auth_code'       => $local_nn_trans_info['auth_code'],
      'tid'             => $local_nn_trans_info['tid'],
      'book_amount'     => isset($_REQUEST['book_amount']) ? trim($_REQUEST['book_amount']) : '',
      'order_id'        => $order_id,      
	  'amount_currency' => $order->info['currency'],
      'payment_type'    => $local_nn_trans_info['payment_type']
    ));

    if($process_result == '') {
      $messageStack->add_session(MODULE_PAYMENT_NOVALNET_ORDER_UPDATE, 'success');
      header('Location: '.DIR_WS_CATALOG.'admin/orders.php?page=1&oID='.$order_id.'&action=edit');
      exit;
    }
  }
// Updation End
?>
<div class="boxCenter">
    <table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tbody>
        <tr>
          <td width="160" rowspan="2"><img src="<?php echo DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'; ?>" alt="Novalnet" border="0"/></td>
          <td class="pageHeading"><?php echo MODULE_PAYMENT_NOVALNET_BOOK_TITLE; ?></td>
        </tr>
        <tr>
          <td valign="top" class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID; ?> <?php echo $local_nn_trans_info['tid']; ?></td>
        </tr>
      </tbody>
    </table>
  <form id="novalnet_book_amount" name='novalnet_book_amount' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
  <input type='hidden' name='process_book_amount' value='1' />
  <input type='hidden' name='nn_form_submit' value='1' />
  <input type='hidden' name='oID' value='<?php echo $order_id; ?>' />
  <div class='novalnet_error_message'>
  <?php if(isset($process_result)) echo $process_result; ?>
  </div>
  <table class="novalnet_orders_table">
    <tr>
        <?php $amount = NovalnetInterface::getOrderAmount($order_id);
          if($local_nn_trans_info['amount'] == 0) {?>
      <td class="main"><?php echo MODULE_PAYMENT_NOVALNET_BOOK_AMT_TITLE; ?> :</td>
      <td class="main">
     <input type='text' style='width:100px;' name='book_amount' id='book_amount' onkeypress='return novalnetAllowNumeric(event)' autocomplete='off' value='<?php echo $amount; ?>' /> <?php echo MODULE_PAYMENT_NOVALNET_CP_REFUND_AMOUNT_EX;?>
      </td>
    </tr>       
   <?php } ?>
    <tr>
      <td colspan='2'><input type='button' name='trans_confirm' value='<?php echo MODULE_PAYMENT_NOVALNET_CONFIRM_MESSAGE; ?>' onclick="validate_book_amount();" />
      <input type='button' name='trans_confirm' value='<?php echo MODULE_PAYMENT_NOVALNET_CANCEL_MESSAGE; ?>' onclick="redirect_orders_list_page();" /></td>
    </tr>
  </table>
  <div class="loader" id="loader" style="display:none"></div>
  <script type='text/javascript'>
  function validate_book_amount() {
	var bookamount = document.getElementById('book_amount').value;
    if (bookamount.trim() == '' || bookamount == 0) {
	  alert('<?php echo MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE; ?>');
      return false;
    }
    document.getElementById('loader').style.display='block';
    document.getElementById('novalnet_book_amount').submit();
  }
  </script>
</div>
<?php
}
?>

<script type='text/javascript'>
  function redirect_orders_list_page() {
	window.location="<?php echo DIR_WS_CATALOG."admin/orders.php"; ?>";
  }
  function novalnetAllowNumeric(evt) {
	var charCode = evt.keyCode ? evt.keyCode : evt.charCode ? evt.charCode : evt.which;
	if (String.fromCharCode(evt.which) == '.' || String.fromCharCode(evt.which) == "'" || String.fromCharCode(evt.which) == "%" || String.fromCharCode(evt.which) =='#')
		return false;
	if (charCode == 37 || charCode == 39) {
		return true;
	}else if( charCode > 31 && (charCode < 48 || charCode > 57) ) {
		return false;
	}else {
		return true;
	}
  }
</script>

<?php
  if(file_exists(DIR_WS_INCLUDES . 'template_bottom.php')) {
	require(DIR_WS_INCLUDES . 'template_bottom.php');
  }else{
	require(DIR_WS_INCLUDES . 'footer.php');
  }
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
<style type='text/css'>
.novalnet_orders_table {
  border-collapse:collapse;
}

.novalnet_orders_table td {
  padding:5px;
}

.novalnet_error_message {
  font-weight:bold;
  color:red;
  margin-top:15px;
}
.loader {
  position: fixed;
  left: 0px;
  top: 0px;
  width: 100%;
  height: 100%;
  z-index: 9999;
  background: url('../includes/classes/novalnet/img/loader.gif') 50% 50% no-repeat;
}
.boxCenter {
  border: 1px solid #f2f2f2;
  background: none repeat scroll 0 0 #f0f1f1;
  padding: 5px;
  margin: 10px 5px 0 5px;
}
.boxCenter input, select{
	font-size: 12px;
}
.novalnet_guide {
	padding:0 10px 0 10px;
}
.nn_map_header {
	background-color:#0080c9;
	color:#fff;
	font-size:16px;
	font-family:calibri;
	font-weight:bold;
	padding:5px;
	text-align:center;
	margin-top : 5px;
}
</style>
