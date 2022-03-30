<?php

namespace App\Imports;

use App\Models\UserMaster;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $collection)
    {
        //
    }
    // /**
    // * @param array $row
    // *
    // * @return \Illuminate\Database\Eloquent\Model|null
    // */
    // public function model(array $row)
    // {
    //     return new UserMaster([
    //         'name'  => $row['name'],
    //         'email' => $row['email'],
    //     ]);
    // }
}
