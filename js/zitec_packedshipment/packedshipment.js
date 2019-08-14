/**
 * Functionality for sending packages grouped
 *
 **/


var ps_totalItemsToShip = 0;
var ps_qtyInputs;
var ps_parcelsInput;
var ps_edit_form;
var ps_data;
var runAddressValidation = true;
var modalDialog1;

var zitecPSVars = {
    modalDialog1Title: "",
    parcelColumnTitle: " ",
    cancelLabel: "",
    continueLabel: "",
    submitLabel: "",
    useDescriptionsInsteadOfReferences: false,
    parcelReferenceLabel: "",
    parcelDescriptionLabel: ""
}


/**
 * Vamos a extender la clase varienps_edit_form para que el submit lance un evento que podamos capturar
 *
 **/
//when subclassing, specify the class you want to inherit from
var varienForm = Class.create(varienForm, {
    // redefine the submit method
    submit: function ($super, url, doSubmit) {
        Event.fire($(this.formId), 'packedshipment:submit');
        if (!doSubmit) {
            return false;
        } else {
            return $super(url);
        }
    }
});


function initNumberOfParcels() {
    ps_qtyInputs = $$("#ship_items_container .grid input");
    ps_qtyInputs.each(function (input) {
        Event.observe(input, 'change', updateMaxParcels);
    });
    ps_parcelsInput = $("packedshipment_parcels_number");
    Event.observe(ps_parcelsInput, 'change', validateParcelsInput);

    ps_edit_form = $('edit_form');

    updateMaxParcels();
}

/**
 * Counts the totall number of parcels to send
 */
function updateMaxParcels() {
    /*
     In the case the carrier's rules force us to send all products in one parcel, we leave empty the field with total
     number of parcels empty. Otherwise, we initialize it with the total number of items that will be shipped
     */

    if (!mustSendShipmentInOneParcel) { // mustSendShipmentInOneParcel is defined in sales/order/shipment/create/items.phtml
        ps_totalItemsToShip = 0;
        ps_qtyInputs.each(function (input) {
            ps_totalItemsToShip = parseInt(input.value) + ps_totalItemsToShip;
        });

        ps_parcelsInput.value = ps_totalItemsToShip;
        ps_parcelsInput.enable();
    }
    else {
        ps_parcelsInput.value = 1;
        ps_parcelsInput.disable();
    }
}

/**
 *
 * Validate the number of parcels. It must be between 1 and the total number of products.
 */
function validateParcelsInput(event) {
    //The total number of parcels cannot be 0 or greater then the number of products
    var valid = true;
    var input = event.target;
    var value = input.getValue();

    if (isNaN(value) || value < 1 || value > ps_totalItemsToShip) {
        Dialog.alert("The total number of parcels cannot be 0 or greater then the number of products",
            {
                width: 300,
                okLabel: "accept",
                className: "magento",
                ok: function (win) {
                    Form.Element.activate(input);
                    input.value = ps_totalItemsToShip;
                    return true;
                }
            }
        );
    }

}

function _cancelModals() {
    Event.fire(ps_edit_form, 'packedshipment:cancel');
    $$(".packedshipment_added").each(function (el) {
        el.remove();
    });
}

function _doEditFormSubmit() {
    ps_data.each(function (el, index) {
        //create a hidden element for each product id
        el.ids.each(function (id) {
            var input = new Element('input', {
                'type': 'hidden',
                'name': 'packages[' + el.package + '][ids][]',
                'value': id,
                'class': 'packedshipment_hidden'
            });

            ps_edit_form.insert(input);
        });
    });

    for (var i = 0; i < ps_parcelsInput.value; i++) {
        var packageIndex = i + 1;
        var data = ps_data[i];
        if (data.ids.length > 0) {
            var input = new Element('input', {
                'type': 'hidden',
                'name': 'packages[' + packageIndex + '][ref]',
                'value': ps_data[i].ref,
                'class': 'packedshipment_hidden'
            });
        }

        ps_edit_form.insert(input);
    }

    ps_edit_form.submit();
}

function _getTotalWeightAllParcels() {
    //Calculate the total weight of parcels
    var parcelsWeightsArr = document.getElementsByName('lineWeight');
    var sumWeights = 0;
    for (var arrIdx = 0; arrIdx < parcelsWeightsArr.length; arrIdx++) {
        sumWeights += parseFloat($(parcelsWeightsArr[arrIdx]).innerHTML);
    }

    return sumWeights;
}

function _recalculateTotalParcelsWeights() {
    // We start the total weight for each parcel at 0
    var numberOfParcels = ps_parcelsInput.value;
    for (var parcelIdx = 1; parcelIdx <= numberOfParcels; parcelIdx++) {
        $('parcel_total_weight_' + parcelIdx).innerHTML = '0.0000';
    }

    //Calculate the total weight for each parcel
    $$(modalDialog1 ? "#" + modalDialog1.getId() + " .packedshipment_input" : "input.packedshipment_input").each(function (el) {
        if ((el.type == 'radio') && (el.checked)) {
            var parcelIndex = el.getValue();
            var eltName = el.readAttribute('name');
            var eltNameParts = eltName.split("_");
            var productId = eltNameParts[0];

            // The weight of each product is at the beginning of each row with ID
            // lineWeight_<product_id>_<row_num>.
            // There may be several elements with the same product ID, which will have the same weight as the first

            var productWeightEls = $$('[id^="lineWeight_' + productId + '"]');
            var productWeightEl = productWeightEls[0];
            var productWeight = parseFloat(productWeightEl.innerHTML);
            var parcelTotalWeight = $('parcel_total_weight_' + parcelIndex);
            parcelTotalWeight.innerHTML = (parseFloat(parcelTotalWeight.innerHTML) + productWeight).toFixed(4);
        }
    })
}

// If the system need to call the api to obtain shipping cost amount
// we calculate it based on the configuration of packages and zip code / city
// Current.
// Important: This feature requires that we have already calculated the weights of the
// parcels, making a call to the function _recalculateTotalParcelsWeights.
function _recalculateShippingCost() {
    if (!$('zitecShippingCostTable')) {
        return;
    }

    var ajaxParameters = [];
    ajaxParameters['order'] = zitecShipmentOrderId;

    ajaxParameters['city'] = zitecShippingAddressCity ? zitecShippingAddressCity : '';
    ajaxParameters['postcode'] = zitecShippingAddressPostcode ? zitecShippingAddressPostcode : '';

    // Sacamos un array de los pesos de los bultos que actualmente contienen algo.
    $$(modalDialog1 ? "#" + modalDialog1.getId() + " .packedshipment_input" : "input.packedshipment_input").each(function (el) {
        if ((el.type == 'radio') && (el.checked)) {
            var parcelIndex = el.getValue();
            ajaxParameters['weightsParcels[' + parcelIndex + ']'] = $('parcel_total_weight_' + parcelIndex).innerHTML;
        }
    });


    new Ajax.Request(zitecShippingCostUrl, {
        method: 'post',
        parameters: ajaxParameters,
        onSuccess: function (transport) {
            var jsonResponse = transport.responseJSON;
            if (jsonResponse.shippingcost) {
                $('zitecShippingCost').innerHTML = jsonResponse.shippingcost;
                $('zitecShippingProfit').innerHTML = jsonResponse.profit;
                $('zitecShippingProfit').style.color = jsonResponse.profitcolor;
                $('zitecShippingResportsShippingCost').setValue(jsonResponse.shippingreportsshippingcost);
            }
        }
    });


}

function _recalculateTotals() {
    // We calculate the actual weight and cost for parcels
    _recalculateTotalParcelsWeights();

    // We calculate the shipping cost
    _recalculateShippingCost();
}

function _prepareModal1() {
    var numberOfParcels = ps_parcelsInput.value;

    for (var i = 1; i <= numberOfParcels; i++) {
        //for each selected package we have to create a th
        var th = new Element('th', {
            'align': 'center',
            'valign': 'middle',
            'class': 'packedshipment_added',
            'style': 'padding:5px;'
        }).update(zitecPSVars.parcelColumnTitle + i);
        $("packages_modal_1_headings_tr").insert(th);
    }

    var checkedIndex = 1;
    var row = 0;
    $$("#ship_items_container .grid input").each(function (el) {
        var qty = el.getValue();
        var id = el.readAttribute('name').replace("shipment[items][", "");
        id = id.replace("]", "");
        ps_itemData = ps_items[id];

        for (var i = 0; i < qty; i++) {
            var sku = new Element('td', {
                'align': 'center',
                'valign': 'middle',
                'class': 'packedshipment_added',
                'style': 'padding:5px;'
            });
            sku.update(ps_itemData.sku);

            var name = new Element('td', {
                'align': 'center',
                'valign': 'middle',
                'class': 'packedshipment_added',
                'style': 'padding:5px;'
            });
            name.update(ps_itemData.name);

            var lineWeightId = 'lineWeight_' + ps_itemData.productId + '_' + row;
            var weight = new Element('td', {
                'id': lineWeightId,
                name: 'lineWeight',
                'align': 'center',
                'valign': 'middle',
                'class': 'packedshipment_added',
                'style': 'padding:5px;'
            });
            weight.update(ps_itemData.weight);

            var tr = new Element('tr', {
                'class': 'border packages_modal_1_product_tr',
                'packages:item_id': (ps_itemData.productId + '_' + (row + 1))
            });
            tr.insert(sku);
            tr.insert(name);
            tr.insert(weight);

            //Now we add a column for each parcel
            for (var j = 1; j <= numberOfParcels; j++) {
                var input = new Element('input', {
                    'type': 'radio',
                    'name': ps_itemData.productId + '_' + (row + 1),
                    'value': j,
                    'class': 'packedshipment_input',
                    'onclick': '_recalculateTotals()'
                });
                if (checkedIndex == j) {
                    input.writeAttribute('checked', 'checked');
                }

                var td = new Element('td', {
                    'align': 'center',
                    'valign': 'middle',
                    'class': 'packedshipment_added',
                    'style': 'padding:5px;'
                }).update(input);
                tr.insert(td);
            }
            //checkedIndex = (checkedIndex == numberOfParcels) ? 1 : checkedIndex + 1;


            var oddEven = row % 2 ? 'odd' : 'even';
            var tbody = new Element('tbody', {'class': oddEven + ' packedshipment_added'});
            tbody.update(tr);


            $('packages_table_modal_1').insert(tbody);

            row++;
        } // for
    });

    // Add new lines to the totals section

    // Total weight
    var weightTitle = new Element('td', {
        'align': 'right',
        'valign': 'middle',
        'class': 'packedshipment_added',
        'style': 'padding:5px;',
        'colspan': 2
    });
    weightTitle.update('<span class="headings">Total Weight</span>');
    var totalWeightAll = new Element('td', {
        'align': 'center',
        'valign': 'middle',
        'class': 'packedshipment_added',
        'style': 'padding:5px;'
    });
    totalWeightAll.update(_getTotalWeightAllParcels().toFixed(4));
    var weightTotalsRow = new Element('tr', {'class': 'border packages_modal_1_product_tr'});
    weightTotalsRow.insert(weightTitle);
    weightTotalsRow.insert(totalWeightAll);
    for (var j = 1; j <= numberOfParcels; j++) {
        var bultoTotalWeightId = 'parcel_total_weight_' + j;
        var bultoTotalWeight = new Element('td', {
            'id': bultoTotalWeightId,
            'align': 'center',
            'valign': 'middle',
            'class': 'packedshipment_added',
            'style': 'padding:5px;'
        });
        weightTotalsRow.insert(bultoTotalWeight);
    }
    var oddEven = row % 2 ? 'odd' : 'even';
    var tbody = new Element('tbody', {'class': oddEven + ' packedshipment_added'});
    tbody.update(weightTotalsRow);
    $('packages_table_modal_1').insert(tbody);

    modalDialog1 = null;
    _recalculateTotals();

    row++;
}


function openModal1() {

    _prepareModal1();

    var content = $('packages_modal_1').innerHTML;

    modalDialog1 = Dialog.confirm(
        content,
        {
            className: "magento",
            title: zitecPSVars.modalDialog1Title,
            minWidth: 600,
            minHeight: 300,
            width: 960,
            height: 400,
            resizable: true,
            closable: true,
            minimizable: false,
            maximizable: false,
            draggable: true,
            width: 400,
            okLabel: zitecPSVars.continueLabel,
            cancelLabel: zitecPSVars.cancelLabel,
            onOk: _onModal1Ok,
            onCancel: _cancelModals,
            onClose: _cancelModals,
            buttonClass: "scalable"
        });


}

function _onModal1Ok(win) {
    ps_data = [];
    for (var i = 0; i < ps_parcelsInput.value; i++) {
        ps_data[i] = {'package': i + 1, 'ids': [], 'ref': ''};
    }

    $$("#" + win.getId() + " .packedshipment_input").each(function (el) {
        if (el.checked) {
            var parcelIndex = el.getValue();
            var tmp = el.readAttribute('name').split("_");
            var productoId = tmp[0];
            ps_data[(parcelIndex - 1)].ids.push(productoId);
            ps_data[(parcelIndex - 1)].ref += el.up().up().firstDescendant().innerHTML + " ";
        }
    })

    this.close();
    openModal2();
    return false;
}


function _prepareModal2() {
    ps_data.each(function (el) {
        if (el.ids.length > 0) {
            var input = new Element('textarea', {
                'id': 'package_' + el.package + '_ref',
                'style': "height:6em; width:99%;",
                'cols': '5',
                'rows': '3',
                'name': 'packages[' + el.package + '][ref]'
            });
            input.update(el.ref);

            var label = new Element('label', {
                'for': 'package_' + el.package + '_ref',
                'class': 'bold'
            });
            var parcelReferenceLabel = zitecPSVars.useDescriptionsInsteadOfReferences ? zitecPSVars.parcelDescriptionLabel : zitecPSVars.parcelReferenceLabel;
            label.update(parcelReferenceLabel + el.package);

            var span = new Element('span', {
                'class': 'field-row packedshipment_added'
            });
            span.insert(label);
            span.insert(input);

            $("packages_modal_2_input_container").insert(span);
        }
    });

}


function openModal2() {

    _prepareModal2();

    var content = $('packages_modal_2').innerHTML;

    Dialog.confirm(
        content,
        {
            className: "magento",
            //parent:            ps_edit_form,
            title: zitecPSVars.modalDialog1Title,
            minWidth: 600,
            minHeight: 300,
            height: 400,
            resizable: true,
            closable: true,
            minimizable: false,
            maximizable: false,
            draggable: true,
            width: 400,
            okLabel: zitecPSVars.submitLabel,
            cancelLabel: zitecPSVars.cancelLabel,
            onOk: _onModal2Ok,
            onCancel: _cancelModals,
            onClose: _cancelModals,
            buttonClass: "scalable"
        });

}


function _onModal2Ok(win) {
    $$("#" + win.getId() + " textarea").each(function (el) {

        var tmp = el.readAttribute('id');
        tmp = tmp.replace("package_", "");
        var parcelIndex = tmp.replace("_ref", "");
        ps_data[(parcelIndex - 1)].ref = el.getValue();

    });

    _doEditFormSubmit();
    this.close();
    return false;
}

function _addressValidationModalOk() {
    var correctedCityInput = $('packagedshipment[corrected_city]');
    var correctedCity = '';
    if (correctedCityInput) {
        if (correctedCityInput.tagName == 'SELECT') {
            correctedCity = correctedCityInput.options[correctedCityInput.selectedIndex].value;
        }
        else {
            correctedCity = correctedCityInput.value;
        }
        zitecShippingAddressCity = correctedCity;
    }

    var correctedPostcodeInput = $('packagedshipment[corrected_postcode]');
    var correctedPostcode = '';
    if (correctedPostcodeInput) {
        if (correctedPostcodeInput.tagName == 'SELECT') {
            correctedPostcode = correctedPostcodeInput.options[correctedPostcodeInput.selectedIndex].value;
        } else {
            correctedPostcode = correctedPostcodeInput.value;
        }
        zitecShippingAddressPostcode = correctedPostcode;

    }

    var dontCorrectAddressInput = $('packagedshipment[dont_correct_address]');
    if (dontCorrectAddressInput && dontCorrectAddressInput.checked) {
        zitecDontCorrectAddress = true;
    } else {
        zitecDontCorrectAddress = false;
    }

    this.close();

    openAddressValidationModal();
}

function openAddressValidationModal() {

    new Ajax.Request(zitecValidateAddressUrl, {
        method: 'post',
        parameters: {
            order: zitecOrderId,
            city: zitecShippingAddressCity,
            postcode: zitecShippingAddressPostcode,
            countryid: zitecShippingAddressCountryId,
            dontcorrectaddress: zitecDontCorrectAddress ? 1 : 0
        },
        onSuccess: function (transport) {
            var jsonResponse = transport.responseJSON;
            if (jsonResponse.dialogHtml) {
                Dialog.confirm(
                    jsonResponse.dialogHtml,
                    {
                        className: "magento",
                        title: "Validation of the shipping address",
                        minWidth: 600,
                        minHeight: 300,
                        height: 400,
                        resizable: true,
                        closable: true,
                        minimizable: false,
                        maximizable: false,
                        draggable: true,
                        width: 400,
                        okLabel: "Continue",
                        cancelLabel: "Cancel",
                        onOk: _addressValidationModalOk,
                        onCancel: _cancelModals,
                        onClose: _cancelModals,
                        buttonClass: "scalable"
                    });
            } else {
                if ($('packagedshipment[city]')) {
                    var cityParentNode = $('packagedshipment[city]').parentNode;
                    cityParentNode.removeChild($('packagedshipment[city]'));
                }
                if ($('packagedshipment[postcode]')) {
                    var postcodeParentNode = $('packagedshipment[postcode]').parentNode;
                    postcodeParentNode.removeChild($('packagedshipment[postcode]'));
                }
                $("packages_modal_2_input_container").insert('<input type="hidden" value="' + zitecShippingAddressCity +
                '" name="packagedshipment[city]" id="packagedshipment[city]" /> ' +
                '<input type="hidden" value="' + zitecShippingAddressPostcode +
                '" name="packagedshipment[postcode]" id="packagedshipment[postcode]" /> ');
                openModal1();
            }
        }
    });

}

function openPackedhipmentModal() {
    // If the user has chosen to send the shipment request to DPD, open the diablog box to group the products in parcels

    // The variable  zitec_packedshipment is defined in zitec_packedshipment/sales/order/shipment/create/items.phtml
    if (communicateShipmentCheckbox && communicateShipmentCheckbox.checked) {
        // The variable zitec_packedshipment is defined zitec_packedshipment/sales/order/shipment/create/address_validation_info_js.phtml
        if (zitecIsAddressValidationAvailable) {
            openAddressValidationModal();
        }
        else {
            openModal1();
        }
    }
    else {
        ps_edit_form.submit();
    }
}

Event.observe(window, 'load', function () {
    if ($('packages_modal_1')) {
        initNumberOfParcels();

        Event.observe(ps_edit_form, 'packedshipment:submit', function (event) {
            openPackedhipmentModal();
        });
    } else {
        Event.observe($('edit_form'), 'packedshipment:submit', function (event) {
            $('edit_form').submit();
        });
    }

});

