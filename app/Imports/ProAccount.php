<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class ProAccount implements ToCollection, WithProgressBar, WithHeadingRow
{
    use Importable;

    private $list = [];

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            if (strtolower($row['membership_status']) !== 'cancelled' && $row['purchase_id']) {
                $this->list[] = [
                    'purchase_id' => $row['purchase_id'],
                    'status'      => $row['membership_status'],
                    'email'       => $row['customer_email'],
                    'expired_at'  => $row['expired_date'] == '-' ?
                        Carbon::parse($row['next_billing_date'])->format('Y-m-d 23:59:59') :
                        Carbon::parse($row['expired_date'])->format('Y-m-d 23:59:59'),
                ];
            }
        }
    }

    /**
     * Get Neighborhood Code -> Model Id relation list
     */
    public function getList()
    {
        return $this->list;
    }
}
