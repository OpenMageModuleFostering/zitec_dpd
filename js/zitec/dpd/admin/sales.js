/**
 * fixed admin order sales refresh order totals after payment method switched
 * @param method
 */
AdminOrder.prototype.switchPaymentMethod = function(method){
    this.setPaymentMethod(method);
    var data = {};
    data['order[payment_method]'] = method;
    this.loadArea(['card_validation','totals'], true, data);
};

