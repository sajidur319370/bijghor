<?php

namespace App\Payments;

use App\Models\Order;

/**
 * One interface, two drivers: BkashSandbox (bKash test API) and DemoGateway (nothing leaves the server).
 * Both return bKash-shaped arrays, so controllers, tables and statuses never care which one is on.
 *
 * A timeout or connection failure THROWS; it never returns an error array.
 */
interface PaymentGateway
{
    /**
     * Starts a payment for the order's total.
     *
     * @return array{paymentID?: string, bkashURL?: string, statusCode?: string, statusMessage?: string}
     */
    public function create(Order $order): array;

    /**
     * Completes the payment after the customer approved it.
     *
     * @return array{paymentID?: string, trxID?: string, transactionStatus?: string, amount?: string, customerMsisdn?: string, statusCode?: string, statusMessage?: string}
     */
    public function execute(string $paymentId): array;

    /**
     * Asks what really happened to a payment (used when execute gave no clear answer).
     *
     * @return array{paymentID?: string, trxID?: string, transactionStatus?: string, amount?: string, customerMsisdn?: string, statusCode?: string, statusMessage?: string}
     */
    public function status(string $paymentId): array;
}
