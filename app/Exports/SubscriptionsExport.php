<?php

namespace App\Exports;

use App\Models\Subscription;
use Martensite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscriptionsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;


    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }


    public function query()
    {
        $query = Subscription::with(['user', 'product']);

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['from_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['from_date']);
        }

        if (isset($this->filters['to_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['to_date']);
        }

        if (isset($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        return $query;
    }


    public function headings(): array
    {
        return [
            'ID',
            'User Name',
            'User Email',
            'Product Title',
            'Product Price',
            'Status',
            'Expires At',
            'Created At',
        ];
    }


    public function map($subscription): array
    {
        return [
            $subscription->id,
            $subscription->user->name ?? '',
            $subscription->user->email ?? '',
            $subscription->product->title ?? '',
            $subscription->product->price ?? '',
            $subscription->status,
            $subscription->expires_at?->format('Y-m-d H:i:s') ?? '',
            $subscription->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
