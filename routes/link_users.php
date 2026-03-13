<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Employee;



Route::get('/link-users', function () {
    $linked = 0;
    foreach (User::all() as $user) {
        $employee = Employee::where('email', $user->email)->first();
        if ($employee && !$employee->user_id) {
            $employee->user_id = $user->id;
            $employee->save();
            $linked++;
            echo "Linked: {$user->email} -> {$employee->first_name} {$employee->last_name}<br>";
        }
    }
    
    if ($linked > 0) {
        echo "<br>Successfully linked {$linked} user(s)!";
    } else {
        echo "No new links were made.";
    }
});
