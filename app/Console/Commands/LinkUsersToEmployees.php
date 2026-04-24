<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LinkUsersToEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:link-users-to-employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $linked = 0;

        foreach (\App\Models\User::all() as $user) {
            $employee = \App\Models\Employee::where('email', $user->email)->first();

            if ($employee && ! $employee->user_id) {
                $employee->user_id = $user->id;
                $employee->save();

                if (! $user->employee_id) {
                    $user->employee_id = $employee->id;
                    $user->save();
                }

                $linked++;

                $this->info("Linked: {$user->email} → {$employee->first_name} {$employee->last_name}");
            }
        }

        $this->info($linked > 0
            ? "Linked {$linked} user(s)!"
            : "No new links."
        );
    }
}
