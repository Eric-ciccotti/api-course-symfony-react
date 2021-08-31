<?php

namespace App\Controller;

use App\Entity\Invoice;

class InvoiceIncrementalController
{
    public function __invoke(Invoice $data)
    {
        dd($data);
    }
}
