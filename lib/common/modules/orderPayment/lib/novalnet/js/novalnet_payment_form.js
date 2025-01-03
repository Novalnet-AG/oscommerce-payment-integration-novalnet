/**
 * This script is used for loading seamless payment form from Novalnet
 *
 * @author     Novalnet
 * @copyright  Copyright (c) Novalnet
 * @license    https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Script : novalnet_payment_form.js
 *
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('.payment_class_novalnet_payments label').style.display = 'none';
    var lineItemsvalue = document.getElementById('nn_line_items').value;
    var lineItems = lineItemsvalue != '' ? JSON.parse(lineItemsvalue) : '';
    const novalnetPaymentIframe = new NovalnetPaymentForm();
    const paymentFormRequestObj = {
        iframe: '#novalnet_iframe',
        initForm: {
            orderInformation: {
                lineItems: lineItems,
            },
            setWalletPending: true,
            showButton: false
        }
    };
    // Initiate the payment form in iframe
    novalnetPaymentIframe.initiate(paymentFormRequestObj);

    // Wallet payments response callback
    novalnetPaymentIframe.walletResponse({
        onProcessCompletion: (data) => {
            if (data.result.status == 'SUCCESS') {
                document.getElementById('nn_payment_details').value = JSON.stringify(data);
                document.querySelector('#frmCheckout').submit();
                return { status: 'SUCCESS', statusText: 'successful' };
            } else {
                return { status: 'FAILURE', statusText: 'failure' };
            }
        }
    });

    // Gives selected payment method
    novalnetPaymentIframe.selectedPayment((data) => {
        document.querySelector('.payment_class_novalnet_payments input').checked = true;
        checkout.data_changed('payment_changed');
        onPaymentChangeBlockHandler();
        if (data.payment_details.type == 'GOOGLEPAY' || data.payment_details.type == 'APPLEPAY') {
            document.querySelector('.btn-2.btn-next').style.display = 'none';
        }
    });

    // To uncheck novalnet payments when other payments selected
    document.addEventListener('click', function (event) {
        if (event.target.name === 'payment' && event.target.checked) {
            novalnetPaymentIframe.uncheckPayment();
        }
    });

    //To get payment response from iframe
    document.querySelector('.w-checkout-continue-btn button').onclick = function (event) {
        if (document.querySelector('#nn_payment_details').value == '') {
            event.preventDefault();
            event.stopImmediatePropagation();
            novalnetPaymentIframe.getPayment((data) => {
                document.getElementById('nn_payment_details').value = JSON.stringify(data);
                document.querySelector('#frmCheckout').submit();
                return true;
            });
        }
    };

    //Reinitiate form if any changes in checkout page
    try {
        window.toggleSubFields_novalnet_payments = function () {
            lineItemsvalue = document.getElementById('nn_line_items').value;
            lineItems = lineItemsvalue != '' ? JSON.parse(lineItemsvalue) : '';
            paymentFormRequestObj.initForm.orderInformation.lineItems = lineItems;
            document.querySelector('.payment_class_novalnet_payments label').style.display = 'none';
            novalnetPaymentIframe.initiate(paymentFormRequestObj);
        }
        checkout_payment_changed.set('window.toggleSubFields_novalnet_payments');
    } catch (e) {
        console.log(e);
    }
});
