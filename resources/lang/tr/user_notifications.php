<?php
return [
    "renew_order_notification" => "<a href='" . route("portal.users.notifications.redirect", ["notificationId" => ":notification_id", "routeName" => "portal.orders.show"]) . "'>#:order_id</a> nolu siparişinizin, <a href='" . route("portal.users.notifications.redirect", ["notificationId" => ":notification_id", "routeName" => "portal.invoices.show"]) . "'>#:invoice_number</a> nolu yenileme faturası oluşturuldu.",
    "invoice_checkout_confirmed_notification" => "<a href='" . route("portal.users.notifications.redirect", ["notificationId" => ":notification_id", "routeName" => "portal.invoices.show"]) . "'>#:invoice_number</a> nolu fatura ödendi.",
    "upcoming_invoice_payment_notification" => "<a href='" . route("portal.users.notifications.redirect", ["notificationId" => ":notification_id", "routeName" => "portal.invoices.show"]) . "'>#:invoice_number</a> nolu faturanızın son ödeme tarihi yaklaşıyor.",
    "support_answered_notification" => "<a href='" . route("portal.users.notifications.redirect", ["notificationId" => ":notification_id", "routeName" => "portal.supports.show"]) . "'>#:support_id</a> nolu destek talebiniz yanıtlandı.",
];
