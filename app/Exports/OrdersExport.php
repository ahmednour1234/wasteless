<?php
namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
     protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        // Fetch the orders with the necessary relationships
        return Order::with(['brand', 'subbrand', 'model', 'category', 'orderdetails', 'seller'])
                    ->get()
                    ->map(function ($order) {
                        return [
                            'Brand' => $order->brand->name ?? 'N/A',
                            'Sub Brand' => $order->subbrand->name ?? 'N/A',
                            'Model' => $order->model->name ?? 'N/A',
                            'Category' => $order->category->name ?? 'N/A',
                            'Buy Price' => $order->orderdetails->first()->purchase_price ?? 'N/A',
                            'Sell Price' => $order->orderdetails->first()->seller_price ?? 'N/A',
                            'Installment Price' => $order->orderdetails->first()->installment_price ?? 'N/A',
                            'Sales User' => $order->seller->name ?? 'N/A',
                            'Seller User' => $order->orderdetails->first()->seller->name ?? 'N/A',
                            'Status' => $order->type ?? 'N/A',
                        ];
                    });
    }

    public function headings(): array
    {
        return [
            'Brand',
            'Sub Brand',
            'Model',
            'Category',
            'Buy Price',
            'Sell Price',
            'Installment Price',
            'Sales User',
            'Seller User',
            'Status',
        ];
    }
}
