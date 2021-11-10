<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script : novalnet.php
 *
 */

ob_start();
require('includes/application_top.php');
if (file_exists(DIR_WS_INCLUDES . 'template_top.php')) {
    require(DIR_WS_INCLUDES . 'template_top.php');
} else {
    require(DIR_WS_INCLUDES . 'header.php');
}

require_once(DIR_FS_ADMIN . 'includes/classes/class.novalnet.php');
require_once(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '/modules/novalnet/novalnet.php');
$process_result = '';
$request        = $_REQUEST;
$datas          = NovalnetAdmin::getNovalnetTransDetails($request['oID']);
if (isset($request['process']) && $request['process'] == 'map') {
    echo showNovalnetAdminPortal();
} elseif (empty($request['oID'])) {
    header('Location: ' . DIR_WS_CATALOG . 'admin/orders.php?page=1');
    exit;
} else {
    require(DIR_WS_CLASSES . 'order.php');

    if (!file_exists(DIR_WS_INCLUDES . 'template_top.php')) {
        ?>

        <html <?php echo HTML_PARAMS; ?> >

            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
                <title> <?php echo TITLE; ?></title>
                <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
                <script language="javascript" src="includes/general.js"></script>
            </head>

            <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">

                <table border="0" width="100%" cellspacing="2" cellpadding="2">
                    <tr>
                          <td width="<?php echo BOX_WIDTH; ?>" valign="top">
                                <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
                                    <!-- left_navigation //-->
                                    <?php
                                        require(DIR_WS_INCLUDES . 'column_left.php');
                                    ?>
                                    <!-- left_navigation_eof //-->
                                </table>
                        </td>
                        <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                    </tr>
                </table>


        <?php
    }
    $order = new order($request['oID']);
    if (!preg_match("/novalnet/i", $datas['payment_type'])) {
        header('Location: ' . DIR_WS_CATALOG . 'admin/orders.php?page=1');
        exit;
    }

    if (!empty($request['message'])) {
        ?>
        <div class='novalnet_error_message'>
            <?php echo $request['message']; ?>
        </div>
        <?php
    }

    if (!empty($request['trans_confirm'])) {
        doTransConfirm($request, $datas, $messageStack);
        ?>

        <!-- Transaction Confirm Block -->
        <div class="boxCenter">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr>
                        <td class="pageHeading"><?php echo MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_TITLE; ?></td>
                        <td class="pageHeading" align="right">
                            <img width="1" height="40" border="0" alt="" src="images/pixel_trans.gif">
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid']; ?></td>
                    </tr>
                </tbody>
            </table>


        <form id="novalnet_status_change" name='novalnet_status_change' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
            <input type='hidden' name='trans_confirm' value='1' />
            <input type='hidden' name='oID' value='<?php echo $request['oID']; ?>' />

            <table class="novalnet_orders_table">
                <tr>
                    <td class="main" style="padding:11px;"><?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_SELECT_STATUS_TEXT); ?>:</td>
                    <td class="main">
                        <select name="trans_status" id='trans_status'>
                            <option value=''><?php echo MODULE_PAYMENT_NOVALNET_SELECT_STATUS_OPTION; ?></option>
                            <option value='100'><?php echo MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT; ?></option>
                            <option value='103'><?php echo MODULE_PAYMENT_NOVALNET_CANCEL_TEXT; ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <input type='submit' name='nn_trans_confirm' class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-primary" value='<?php echo MODULE_PAYMENT_NOVALNET_UPDATE_TEXT; ?>' onclick="return validate_status_change();" />
                        <input type='button' name='trans_confirm'class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-primary"  value='<?php echo MODULE_PAYMENT_NOVALNET_BACK_TEXT; ?>' onclick="redirect_orders_list_page();" />
                    </td>
                </tr>
            </table>

            <div class="loader" id="loader" style="display:none"></div>
        </form>

        <?php
    }

    if (!empty($request['subs_cancel'])) {
        doSubscriptionCancel($request, $datas, $messageStack);
        ?>

        <!-- Subscription Cancel Block -->
        <div class="boxCenter">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr>
                        <td class="pageHeading"><?php echo MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_TITLE; ?></td>
                        <td class="pageHeading" align="right">
                            <img width="1" height="40" border="0" alt="" src="images/pixel_trans.gif">
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid']; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form id="novalnet_unsubscribe" name='novalnet_unsubscribe' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
            <input type='hidden' name='subs_cancel' value='1' />
            <input type='hidden' name='oID' value='<?php echo $request['oID']; ?>' />
            <table class="novalnet_orders_table">
                <tr>
                    <td class="main"><?php echo MODULE_PAYMENT_NOVALNET_SUBS_SELECT_REASON; ?>:</td>
                    <td class="main">
                        <?php $subs_termination_reason = array(
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_1,
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_2,
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_3,
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_4,
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_5,
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_6,
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_7,
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_8,
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_9,
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_10,
                            MODULE_PAYMENT_NOVALNET_SUBS_REASON_11);
                        ?>
                       <select name="subscribe_termination_reason" id ="subscribe_termination_reason">
                            <option value=''><?php echo MODULE_PAYMENT_NOVALNET_SELECT_STATUS_OPTION; ?></option>
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
                    <td colspan='2'>
                        <input type='submit' name='nn_trans_confirm' class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-primary" value='<?php echo MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT; ?>' onclick="return validate_unsubscribe_form();" />
                        <input type='button' name='trans_confirm' class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-primary" value='<?php echo MODULE_PAYMENT_NOVALNET_BACK_TEXT; ?>' onclick="redirect_orders_list_page();" />
                    </td>
                </tr>
            </table>

            <div class="loader" id="loader" style="display:none"></div>
        </form>
        <?php
    }

    if (!empty($request['amount_refund'])) {	
        dorefundTransAmount($request, $datas, $messageStack, $order);
        ?>

        <!--Refund amount block -->
        <div class="boxCenter">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr>
                        <td class="pageHeading"><?php echo MODULE_PAYMENT_NOVALNET_REFUND_TITLE; ?></td>
                         <td class="pageHeading" align="right">
                            <img width="1" height="40" border="0" alt="" src="images/pixel_trans.gif">
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid']; ?></td>
                    </tr>
                </tbody>
            </table>

            <form id="novalnet_trans_refund" name='novalnet_trans_refund' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
                <input type='hidden' name='amount_refund' value='1' />
                <input type='hidden' name='oID' value='<?php echo $request['oID']; ?>' />
                <input type='hidden' name='payment_refund' value='<?php echo 'True'; ?>' />
                <table class="novalnet_orders_table">
                    <?php if (in_array($datas['payment_id'], array(27, 33, 49, 41, 69, 50))) { ?>
                    <tr>
                        <td class="main" style="padding:0px;"><?php echo MODULE_PAYMENT_NOVALNET_REFUND_PAYMENTTYPE_TITLE; ?>:</td>
                        <td class="main">
                            <input type='radio' name='refund_payment_type' id='refund_payment_type_none' value='NONE' checked onclick="refund_payment_type_element_handle('none');"/> <?php echo MODULE_PAYMENT_NOVALNET_PAYMENTTYPE_NONE; ?>
                           <input type='radio' name='refund_payment_type' id='refund_payment_type_sepa' value='SEPA' onclick="refund_payment_type_element_handle();"/> <?php echo MODULE_PAYMENT_NOVALNET_SEPA_TEXT_TITLE; ?>
                       </td>
                    </tr>
                    <?php
                        }
                        $order_date = strtotime(date("Y-m-d", strtotime($datas['date'])));
                        if (strtotime(date('Y-m-d')) > $order_date) {
                            ?>
                            <tr>
                                <td class="main"><?php echo MODULE_PAYMENT_NOVALNET_REFUND_REFERENCE_TEXT; ?>:</td>
                                <td class="main">
                                    <input type='text' style='width:200px;' name='refund_ref' id='refund_ref' autocomplete="off" value=''/>
                                </td>
                            </tr>
                            <?php
                        
						}						
                    ?>
                </table>
                <?php if (in_array($datas['payment_id'], array(27, 33, 49, 41, 69, 50))) { ?>
                    <table id="direct_debit_sepa_tabletr" class="novalnet_orders_table">
                        <tr>
                            <td class="main" style="width:200px;"><?php echo MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER; ?></td>
                            <td class="main">
                                <input type='text' name='refund_payment_type_accountholder' id='refund_payment_type_accountholder' autocomplete="off" value='<?php echo $order->customer['name']; ?>'/>
                            </td>
                        </tr>
                        <tr>
                            <td class="main" style="width:200px;"><?php echo MODULE_PAYMENT_NOVALNET_IBAN; ?></td>
                            <td class="main">
                                <input type='text' name='refund_payment_type_iban' id='refund_payment_type_iban' autocomplete="off" value=''/>
                            </td>
                        </tr>
                        <tr>
                            <td class="main" style="width:200px;"><?php echo MODULE_PAYMENT_NOVALNET_BIC; ?></td>
                            <td class="main">
                                <input type='text' name='refund_payment_type_bic' id='refund_payment_type_bic' autocomplete="off" value=''/>
                            </td>
                        </tr>
                    </table>
                 <?php }?>
                <table class="novalnet_orders_table">
                    <?php
                        if ($datas['amount'] != 0) {
                    ?>
                    <tr>
                        <td class="main" style="padding:0px; width:210px;"><?php echo MODULE_PAYMENT_NOVALNET_REFUND_AMT_TITLE; ?>:</td>
                        <td class="main"><?php $amount = $datas['amount']; ?>
                            <input type='text' style='width:100px;' name='refund_trans_amount' id='refund_trans_amount' onkeypress='return novalnetAllowNumeric(event)' autocomplete="off" value='<?php echo $datas['amount']; ?>' /> <?php echo MODULE_PAYMENT_NOVALNET_AMOUNT_EX; ?>
                        </td>
                    </tr>
                </table>
                        <?php
                        }
                        ?>
                <table class="novalnet_orders_table">
                    <tr>
                        <td colspan='2'>
                            <input type='submit' name='nn_trans_confirm' class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-primary" value='<?php echo MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT; ?>' onclick="return validate_refund_amount();" />
                            <input type='button' name='trans_confirm' class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-primary" value='<?php echo MODULE_PAYMENT_NOVALNET_BACK_TEXT; ?>' onclick="redirect_orders_list_page();" />
                        </td>
                    </tr>
                </table>

                <div class="loader" id="loader" style="display:none"></div>
            </form>
        <?php
    }

    if (!empty($request['amount_change'])) {
        doAmountUpdate($request, $datas, $messageStack, $order);
        if (in_array($datas['payment_id'], array(27, 41))) {
            $duedate        = NovalnetAdmin::getInvPrePaymentDetails($datas['tid']);
            $input_due_date = !empty($duedate['due_date']) ? $duedate['due_date'] : $datas['payment_details']['due_date'];
            $input_day      = $input_month = $input_year = '';
            if ($input_due_date != '0000-00-00') {
                $strtotime_input_date = strtotime($input_due_date);
                $input_day            = date('d', $strtotime_input_date);
                $input_month          = date('m', $strtotime_input_date);
                $input_year           = date('Y', $strtotime_input_date);
            }
        }
        ?>

        <!-- Amount update process block -->
        <div class="boxCenter">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr>
                        <td class="pageHeading"><?php echo (in_array($datas['payment_id'], array(37, 40))) ? MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_TITLE : MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_BUTTON; ?></td>
                        <td class="pageHeading" align="right">
                            <img width="1" height="40" border="0" alt="" src="images/pixel_trans.gif">
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid']; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form id="novalnet_amount_change" name='novalnet_amount_change' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
            <input type='hidden' name='amount_change' value='1' />
            <input type='hidden' name='oID' value='<?php echo $request['oID']; ?>' />
            <table class="novalnet_orders_table">
                <tr>
                    <td class="main" style="padding:11px;"><?php echo MODULE_PAYMENT_NOVALNET_TRANS_AMOUNT_TITLE; ?>:</td>
                    <td class="main">
                        <input type='text' name='new_amount' id='new_amount' onkeypress='return novalnetAllowNumeric(event)' autocomplete="off" value='<?php echo $datas['amount']; ?>' /> <?php echo MODULE_PAYMENT_NOVALNET_AMOUNT_EX; ?>
                   </td>
                </tr>
                <?php
                    $invoice_payment = 0;
                    if (in_array($datas['payment_id'], array(27, 41))) {
                        $invoice_payment = 1;
                ?>
                <tr>
                    <td class="main" style="padding:11px;"><?php echo MODULE_PAYMENT_NOVALNET_TRANS_DUE_DATE_TITLE; ?>:</td>
                    <td class="main">
                        <select name='amount_change_day' id='amount_change_day'>
                            <?php
                                for ($i = 1; $i <= 31; $i++) {
                                    ?>
                                    <option <?php echo (($input_day == $i) ? 'selected' : ''); ?> value="<?php echo (($i < 10) ? '0' . $i : $i); ?>"><?php echo (($i < 10) ? '0' . $i : $i); ?></option>
                                    <?php
                                }
                            ?>
                        </select>

                        <select name='amount_change_month' id='amount_change_month'>
                            <?php
                                for ($i = 1; $i <= 12; $i++) {
                                    ?>
                                    <option <?php echo (($input_month == $i) ? 'selected' : ''); ?> value="<?php echo (($i < 10) ? '0' . $i : $i); ?>"><?php echo (($i < 10) ? '0' . $i : $i); ?></option>
                                    <?php
                                }
                            ?>
                       </select>

                        <select name='amount_change_year' id='amount_change_year'>
                            <?php
                                $year_val = date('Y');
                                for ($i = $year_val; $i <= ($year_val + 1); $i++) {
                                    ?>
                                    <option <?php echo (($input_year == $i) ? 'selected' : ''); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php
                                }
                            ?>
                       </select>
                    </td>
                </tr>
                <?php

                    }
                ?>

                <input type='hidden' id='invoice_payment' value='<?php echo $invoice_payment; ?>'>
                <tr>
                    <td colspan='2'><input type='submit' name='nn_trans_confirm'  class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-primary" value='<?php echo MODULE_PAYMENT_NOVALNET_UPDATE_TEXT; ?>' onclick="return validate_amount_change();" />
                        <input type='button' name='trans_confirm'  class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-primary" value='<?php echo MODULE_PAYMENT_NOVALNET_BACK_TEXT; ?>' onclick="redirect_orders_list_page();" />
                    </td>
                </tr>
            </table>
            <div class="loader" id="loader" style="display:none"></div>
        </form>
        <?php
    }

    if (!empty($request['book_amount'])) {
        doTransactionBook($request, $datas, $messageStack, $order);
        ?>

        <!-- Transaction Booking process block -->
        <div class="boxCenter">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr>
                        <td class="pageHeading"><?php echo MODULE_PAYMENT_NOVALNET_BOOK_TITLE; ?></td>
                         <td class="pageHeading" align="right">
                            <img width="1" height="40" border="0" alt="" src="images/pixel_trans.gif">
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="main"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid']; ?></td>
                    </tr>
                </tbody>
            </table>


        <form id="novalnet_book_amount" name='novalnet_book_amount' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
            <input type='hidden' name='process_book_amount' value='1' />
            <input type='hidden' name='oID' value='<?php echo $request['oID']; ?>' />
            <table class="novalnet_orders_table">
                <tr>
                    <?php
                        if ($datas['amount'] == 0) {
                            ?>
                    <td class="main" style="padding:11px;"><?php echo MODULE_PAYMENT_NOVALNET_BOOK_AMT_TITLE; ?>:</td>
                    <td class="main">
                        <input type='text' style='width:100px;' name='book_amount' id='book_amount' onkeypress='return novalnetAllowNumeric(event)' autocomplete="off" value='<?php echo $datas['total_amount']; ?>' /> <?php echo MODULE_PAYMENT_NOVALNET_AMOUNT_EX; ?>
                   </td>
                </tr>
                <?php
                        }
                ?>
                <tr>
                    <td colspan='2'>
                        <input type='submit' name='nn_trans_confirm' class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-primary" value='<?php echo MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT; ?>' onclick="return validate_book_amount(event);" />
                        <input type='button' name='trans_confirm' class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-primary" value='<?php echo MODULE_PAYMENT_NOVALNET_BACK_TEXT; ?>' onclick="redirect_orders_list_page();" />
                    </td>
                </tr>
            </table>
            <div class="loader" id="loader" style="display:none"></div>
        </form>
        <?php
    }
}
        ?>
    </body>

    <script type='text/javascript'>

        /**
         * Validates numeric key
         *
         */
        function novalnetAllowNumeric(evt) {
            var keycode = ('which' in evt) ? evt.which : evt.keyCode;
            var reg = /^(?:[0-9]+$)/;
            return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || (evt.ctrlKey == true && keycode == 114)) ? true : false;
        }

        /**
         * Validates confirm / cancel
         *
         */
        function validate_status_change() {
            if (document.getElementById('trans_status').value == '') {
                alert('<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_SELECT_STATUS_TEXT); ?>');
                return false;
            } else {
                display_text = document.getElementById('trans_status').value == '100' ? '<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_SELECT_CONFIRM_TEXT); ?>' : '<?php echo MODULE_PAYMENT_NOVALNET_SELECT_CANCEL_TEXT; ?>';
                if (!confirm(display_text)) {
                    return false;
                }
                document.getElementById('loader').style.display='block';
            }
            return true;
        }

        /**
         * Validates Subscription
         *
         */
        function validate_unsubscribe_form() {
            if (document.getElementById('subscribe_termination_reason').value =='') {
                alert('<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_REASON_TITLE); ?>');
                return false;
            }
            display_text = '<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_TEXT); ?>';
            if (!confirm(display_text)) {
                return false;
            }
            document.getElementById('loader').style.display='block';
            return true;
        }

        /**
         * Manages the refund type
         *
         */
        function refund_payment_type_element_handle() {
            if (document.getElementById('refund_payment_type_sepa') && document.getElementById('refund_payment_type_sepa').checked) {
                if(document.getElementById('direct_debit_sepa_tabletr'))
                document.getElementById('direct_debit_sepa_tabletr').style.display="block";
            } else {
                if(document.getElementById('direct_debit_sepa_tabletr'))
                document.getElementById('direct_debit_sepa_tabletr').style.display="none";
            }
        }
        refund_payment_type_element_handle();

        /**
         * Validates amount for Refund process
         *
         */
        function validate_refund_amount() {
            if (document.getElementById('refund_ref') != null) {
                var refund_ref = document.getElementById('refund_ref').value;
                refund_ref = refund_ref.trim();
                var re = /[\/\\#,+!^()$~%.":*?<>{}]/g;
                if (re.test(refund_ref)) {
                    evt.preventDefault();
                    alert('<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR); ?>');
                    return false;
                }
            }
            else {
                var amount = document.getElementById('refund_trans_amount').value;
                if (amount.trim() == '' || amount == 0) {
                    alert('<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE); ?>');
                    return false;
                }
            }

            if (document.getElementById('refund_payment_type_sepa') && document.getElementById('refund_payment_type_sepa').checked) {
                var accholder = document.getElementById('refund_payment_type_accountholder').value;
                var iban = document.getElementById('refund_payment_type_iban').value;
                var bic = document.getElementById('refund_payment_type_bic').value;
                if (accholder.trim() == '' || iban.trim() == '' ||  bic.trim()== '') {
                    alert('<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR); ?>');
                    return false;
                }
            }
            display_text = '<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_REFUND_AMOUNT_TEXT); ?>';
            if (!confirm(display_text)) {
                return false;
            }
            document.getElementById('loader').style.display='block';
            document.getElementById('novalnet_trans_refund').submit();
        }

        /**
         * Validates amount for Amount update
         *
         */
        function validate_amount_change() {
            var changeamount = (document.getElementById('new_amount').value).trim();
            if (changeamount == '' || changeamount <= 0 || isNaN(changeamount)) {
                alert('<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE); ?>');
                return false;
            }
            display_text =  document.getElementById('invoice_payment').value == 1 ? '<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_ORDER_AMT_DATE_UPDATE_TEXT); ?>' : '<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_ORDER_AMT_UPDATE_TEXT); ?>';
            if (!confirm(display_text)) {
                return false;
            }
            document.getElementById('loader').style.display='block';
            return true;
        }

        /**
         * Validates Transaction Booking amount
         *
         */
        function validate_book_amount(evt) {
            var bookamount = document.getElementById('book_amount').value;
            if (bookamount.trim() == '' || bookamount == 0) {
                evt.preventDefault();
                alert('<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE); ?>');
                return false;
            }
            display_text = '<?php echo NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_BOOK_AMOUNT_TEXT); ?>';
            if (!confirm(display_text)) {
                return false;
            }
            document.getElementById('loader').style.display='block';
            document.getElementById('novalnet_book_amount').submit();
        }

        /**
         * Redirect to Order page
         *
         */
        function redirect_orders_list_page() {
            window.location="<?php echo DIR_WS_CATALOG . "admin/orders.php"; ?>";
        }

    </script>

</html>

<?php
    if (file_exists(DIR_WS_INCLUDES . 'template_bottom.php')) {
        require(DIR_WS_INCLUDES . 'template_bottom.php');
    } else {
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
        background: url('../images/icons/novalnet/novalnet_loader.gif') 50% 50% no-repeat;
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

<?php
    /**
     * Show admin portal message
     *
     */
    function showNovalnetAdminPortal()
    {
        return '<div class="novalnet_guide">
                <div class="nn_map_header">' . MODULE_PAYMENT_NOVALNET_MAP_PAGE_HEADER . '</div>
                <div>
                  <iframe src="https://admin.novalnet.de" style="width:1400px;height:500px;" frameborder="0"></iframe>
                </div>
              </div>';
    }

    /**
     * Novalnet onhold transaction confirmation / cancellation process
     * @param $request
     * @param $datas
     * @param $messageStack
     *
     * @return none
     */
    function doTransConfirm($request, $datas, $messageStack)
    {
        if (isset($request['nn_trans_confirm'])) {
            if (!empty($request['trans_status'])) {
                $process_result = NovalnetAdmin::onholdTransConfirm(array(
                    'tid'        => $datas['tid'],
                    'status'     => $request['trans_status'],
                    'payment_id' => $datas['payment_id'],
                    'vendor'     => $datas['vendor'],
                    'product'    => $datas['product'],
                    'tariff'     => $datas['tariff'],
                    'auth_code'  => $datas['auth_code'],
                    'order_id'   => $request['oID']
                ));

                displayMessageText($request, $process_result, $messageStack, 'trans_confirm');
            }
        }
    }

    /**
     * Novalnet subscription transaction cancellation process
     * @param $request
     * @param $datas
     * @param $messageStack
     *
     * @return none
     */
    function doSubscriptionCancel($request, $datas, $messageStack)
    {
        if (isset($request['nn_trans_confirm'])) {
            if (!empty($request['subscribe_termination_reason'])) {
                $process_result = NovalnetAdmin::subscriptionTransStop(array(
                    'tid'                => $datas['tid'],
                    'payment_id'         => $datas['payment_id'],
                    'termination_reason' => $request['subscribe_termination_reason'],
                    'vendor'             => $datas['vendor'],
                    'product'            => $datas['product'],
                    'tariff_id'          => $datas['tariff'],
                    'auth_code'          => $datas['auth_code'],
                    'order_id'           => $request['oID']
                ));
                displayMessageText($request, $process_result, $messageStack, 'subs_cancel');
            }
        }
    }

    /**
     * Novalnet Refund process
     * @param $request
     * @param $datas
     * @param $messageStack
     * @param $order
     *
     * @return none
     */
    function dorefundTransAmount($request, $datas, $messageStack, $order)
    {
        if (isset($request['nn_trans_confirm'])) {
            if (!empty($request['payment_refund'])) {
                $account_holder = $iban = $bic = '';
                if (isset($request['refund_payment_type']) && $request['refund_payment_type'] == 'SEPA') {
                    $account_holder = $request['refund_payment_type_accountholder'];
                    $iban           = $request['refund_payment_type_iban'];
                    $bic            = $request['refund_payment_type_bic'];
                }
                if (!empty($request['refund_trans_amount'])) {
                    $process_result = NovalnetAdmin::refundTransAmount(array(
                        'tid'                          => $datas['tid'],
                        'refund_ref'                   => isset($request['refund_ref']) ? trim($request['refund_ref']) : '',
                        'refund_trans_amount'          => isset($request['refund_trans_amount']) ? trim($request['refund_trans_amount']) : '',
                        'refund_trans_amount_currency' => $order->info['currency'],
                        'payment_id'                   => $datas['payment_id'],
                        'payment_type'                 => $datas['payment_type'],
                        'vendor'                       => $datas['vendor'],
                        'product'                      => $datas['product'],
                        'tariff'                       => $datas['tariff'],
                        'auth_code'                    => $datas['auth_code'],
                        'test_mode'                    => $datas['test_mode'],
                        'order_id'                     => $request['oID'],
                        'subs_id'                      => $datas['subs_id'],
                        'refund_payment_type'          => ((!empty($request['refund_payment_type'])) ? $request['refund_payment_type'] : 'NONE'),
                        'account_holder'               => $account_holder,
                        'iban'                         => $iban,
                        'bic'                          => $bic
                    ));

                    displayMessageText($request, $process_result, $messageStack, 'amount_refund');
                }
            }
        }
    }

    /**
     * Novalnet transaction amount and due_date updation process
     * @param $request
     * @param $datas
     * @param $messageStack
     * @param $order
     *
     * @return none
     */
    function doAmountUpdate($request, $datas, $messageStack, $order)
    {
        if (!in_array($datas['payment_id'], array(27, 37, 40, 41))) {
            header('Location: ' . DIR_WS_CATALOG . 'admin/orders.php?page=1&oID=' . $request['oID'] . '&action=edit');
            exit;
        }

        if (!empty($request['new_amount'])) {
            $input_due_date = '0000-00-00';

            if ($request['amount_change_year'] != '' && $request['amount_change_month'] != '' && $request['amount_change_day'] != '') {
                $input_due_date = $request['amount_change_year'] . '-' . $request['amount_change_month'] . '-' . $request['amount_change_day'];
            } else {
                $input_due_date = $datas['due_date'];
            }

            if (in_array($datas['payment_type'], array('novalnet_invoice', 'novalnet_prepayment')) && !checkmydate($input_due_date)) {
                displayMessageText($request, NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_INVALID_DATE), $messageStack, 'amount_change');
            }
            if ($input_due_date != '0000-00-00' && strtotime($input_due_date) < strtotime(date('Y-m-d')) && in_array($datas['payment_type'], array('novalnet_invoice', 'novalnet_prepayment'))) {
                $process_result = MODULE_PAYMENT_NOVALNET_VALID_DUEDATE_MESSAGE;
            } else {
                $process_result = NovalnetAdmin::updateTransAmount(array(
                    'tid'             => $datas['tid'],
                    'status'          => $datas['gateway_status'],
                    'payment_id'      => $datas['payment_id'],
                    'vendor'          => $datas['vendor'],
                    'product'         => $datas['product'],
                    'tariff'          => $datas['tariff'],
                    'auth_code'       => $datas['auth_code'],
                    'order_id'        => $request['oID'],
                    'due_date'        => $input_due_date,
                    'amount'          => $request['new_amount'],
                    'amount_currency' => $order->info['currency']
                ));
            }
            displayMessageText($request, $process_result, $messageStack, 'amount_change');
        }
    }

    /**
     * Validate transaction due date
     * @param $date
     *
     * @return boolean
     */
    function checkmydate($date)
    {
        $tempDate = explode('-', $date);
        if (checkdate($tempDate[1], $tempDate[2], $tempDate[0])) { //checkdate(month, day, year)
            return true;
        } else {
            return false;
        }
    }

    /**
     * Novalnet book process
     * @param $request
     * @param $datas
     * @param $messageStack
     * @param $order
     *
     * @return none
     */
    function doTransactionBook($request, $datas, $messageStack, $order)
    {
        if (isset($request['nn_trans_confirm'])) {
            if (!empty($request['process_book_amount']) && !empty($request['book_amount'])) {
                $process_result = NovalnetAdmin::bookTransAmount(array(
                    'vendor'          => $datas['vendor'],
                    'product'         => $datas['product'],
                    'tariff_id'       => $datas['tariff'],
                    'auth_code'       => $datas['auth_code'],
                    'tid'             => $datas['tid'],
                    'amount_currency' => $order->info['currency'],
                    'book_amount'     => isset($request['book_amount']) ? trim($request['book_amount']) : '',
                    'order_id'        => $request['oID']
                ));
                displayMessageText($request, $process_result, $messageStack, 'book_amount');
            }
        }
    }

    /**
     * Display order update message
     * @param $request
     * @param $content
     * @param $messageStack
     * @param $type
     *
     * @return none
     */
    function displayMessageText($request, $content, $messageStack, $type)
    {
        if ($content == '') {
            $messageStack->add_session(MODULE_PAYMENT_NOVALNET_ORDER_UPDATE, 'success');
            header('Location: ' . DIR_WS_CATALOG . 'admin/orders.php?page=1&oID=' . $request['oID'] . '&action=edit&message=' . $content);
            exit;
        } else {
            header('Location: ' . DIR_WS_CATALOG . 'admin/novalnet.php?' . $type . '=1&oID=' . $request['oID'] . '&action=edit&message=' . $content);
            exit;
        }
    }
?>
