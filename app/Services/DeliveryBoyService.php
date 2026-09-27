<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\UserType;
use App\Models\DeliveryBoyCollection;
use App\Models\DeliveryBoyPayment;
use App\Models\Order;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Every write to delivery boys, their money trail, and their order work. */
class DeliveryBoyService
{
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $boy = new User([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
            ]);

            $boy->user_type = UserType::DeliveryBoy;
            $boy->save();

            return $boy;
        });
    }

    public function update(User $boy, array $data): User
    {
        return DB::transaction(function () use ($boy, $data) {
            $boy->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);

            if ($data['password'] !== null) {
                $boy->password = $data['password'];
            }

            $boy->save();

            return $boy;
        });
    }

    /**
     * @throws DomainException while the boy still has unpaid payouts or uncollected cash on record
     */
    public function delete(User $boy): void
    {
        DB::transaction(function () use ($boy) {
            $payments = DeliveryBoyPayment::where('delivery_boy_id', $boy->id)->count();
            $collections = DeliveryBoyCollection::where('delivery_boy_id', $boy->id)->count();

            if ($payments > 0 || $collections > 0) {
                throw new DomainException(sprintf(
                    '%s has %s on record; the history is part of the money trail, so remove the boy only after archiving it.',
                    $boy->name,
                    $payments > 0 ? ($payments === 1 ? '1 payment' : $payments.' payments') : ($collections === 1 ? '1 collection' : $collections.' collections'),
                ));
            }

            $boy->delete();
        });
    }

    public function pay(array $data): DeliveryBoyPayment
    {
        return DB::transaction(fn () => DeliveryBoyPayment::create([
            'delivery_boy_id' => $data['delivery_boy_id'],
            'amount' => $data['amount'],
            'note' => $data['note'],
            'paid_at' => $data['paid_at'] ?? now(),
        ]));
    }

    public function collect(array $data): DeliveryBoyCollection
    {
        return DB::transaction(fn () => DeliveryBoyCollection::create([
            'delivery_boy_id' => $data['delivery_boy_id'],
            'amount' => $data['amount'],
            'note' => $data['note'],
            'collected_at' => $data['collected_at'] ?? now(),
        ]));
    }

    /**
     * Puts an order in a delivery boy's hands, replacing any previous one.
     *
     * @throws DomainException when the user is not a delivery boy
     */
    public function assign(Order $order, User $boy): Order
    {
        if ($boy->user_type !== UserType::DeliveryBoy) {
            throw new DomainException("{$boy->name} is not a delivery boy.");
        }

        return DB::transaction(function () use ($order, $boy) {
            $order->forceFill(['delivery_boy_id' => $boy->id, 'cancel_requested_at' => null]);
            $order->save();

            return $order;
        });
    }

    /** Takes an order back from its delivery boy. */
    public function unassign(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order->forceFill(['delivery_boy_id' => null, 'cancel_requested_at' => null]);
            $order->save();

            return $order;
        });
    }

    /**
     * A delivery boy asks to cancel an assigned order they cannot complete;
     * the admin approves or declines from the cancel-requests list.
     *
     * @throws DomainException when the order is not assigned, or is already done
     */
    public function requestCancel(Order $order): Order
    {
        if ($order->delivery_boy_id === null) {
            throw new DomainException('Only an assigned order can carry a cancel request.');
        }

        if (in_array($order->delivery_status, [DeliveryStatus::Delivered, DeliveryStatus::Cancelled], true)) {
            throw new DomainException('The order is already '.strtolower($order->delivery_status->label()).'.');
        }

        return DB::transaction(function () use ($order) {
            $order->forceFill(['cancel_requested_at' => now()]);
            $order->save();

            return $order;
        });
    }

    /**
     * Approving a cancel request cancels the order through the normal status
     * transition, so stock returns exactly like a manual cancellation.
     *
     * @throws DomainException when the order carries no request, or the status move is refused
     */
    public function approveCancel(Order $order, OrderService $orders): Order
    {
        if ($order->cancel_requested_at === null) {
            throw new DomainException('This order carries no cancel request.');
        }

        return DB::transaction(function () use ($order, $orders) {
            $orders->changeDeliveryStatus($order->withoutRelations(), DeliveryStatus::Cancelled);
            $order->forceFill(['cancel_requested_at' => null]);
            $order->save();

            return $order;
        });
    }

    /**
     * @throws DomainException when the order carries no request
     */
    public function declineCancel(Order $order): Order
    {
        if ($order->cancel_requested_at === null) {
            throw new DomainException('This order carries no cancel request.');
        }

        return DB::transaction(function () use ($order) {
            $order->forceFill(['cancel_requested_at' => null]);
            $order->save();

            return $order;
        });
    }
}
