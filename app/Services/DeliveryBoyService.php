<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\DeliveryBoyCollection;
use App\Models\DeliveryBoyPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Every write to delivery boys and their payment/collection histories. */
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
     * @throws \DomainException while the boy still has unpaid payouts or uncollected cash on record
     */
    public function delete(User $boy): void
    {
        DB::transaction(function () use ($boy) {
            $payments = DeliveryBoyPayment::where('delivery_boy_id', $boy->id)->count();
            $collections = DeliveryBoyCollection::where('delivery_boy_id', $boy->id)->count();

            if ($payments > 0 || $collections > 0) {
                throw new \DomainException(sprintf(
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
}
