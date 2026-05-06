$(document).ready(function () {
    let subTotalArea = $('[data-kt-element="sub-total"]'),
        vatTotalArea = $('[data-kt-element="vat-total"]'),
        grandTotalArea = $('[data-kt-element="grand-total"]');

    const priceOrVatChange = () => {
        let subTotal = 0.00,
            vatTotal = 0.00,
            grandTotal = 0.00;

        $('[data-kt-element="item"]').map((index, item) => {
            let price = priceFormat.from($(item).find('[data-kt-element="price"]').val()),
                vat_percent = parseFloat($(item).find('[data-kt-element="vat_percent"]').val()),
                vat = price * vat_percent / 100,
                total = price + vat;

            $(item).find('[data-kt-element="total"]').val(priceFormat.to(total))

            subTotal += price;
            vatTotal += vat;
            grandTotal += total;
        })

        subTotalArea.text(priceFormat.to(subTotal));
        vatTotalArea.text(priceFormat.to(vatTotal));
        grandTotalArea.text(priceFormat.to(grandTotal));
    }
    const totalChange = () => {
        let subTotal = 0.00,
            vatTotal = 0.00,
            grandTotal = 0.00;

        $('[data-kt-element="item"]').map((index, item) => {
            let vat_percent = parseFloat($(item).find('[data-kt-element="vat_percent"]').val()),
                total = priceFormat.from($(item).find('[data-kt-element="total"]').val()),
                price = total / (1 + (vat_percent / 100));

            $(item).find('[data-kt-element="price"]').val(priceFormat.to(price))

            subTotal += price;
            vatTotal += (price * vat_percent / 100);
            grandTotal += total;
        })

        subTotalArea.text(priceFormat.to(subTotal));
        vatTotalArea.text(priceFormat.to(vatTotal));
        grandTotalArea.text(priceFormat.to(grandTotal));
    }

    $(document).on("change", '[data-kt-element="price"], [data-kt-element="vat_percent"]', function () {
        priceOrVatChange()
    })

    $(document).on("change", '[data-kt-element="total"]', function () {
        totalChange()
    })
})
