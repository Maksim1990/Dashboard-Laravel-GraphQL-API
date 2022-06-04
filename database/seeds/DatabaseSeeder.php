<?php

use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Un Guard model
        Model::unguard();

        //USERS SEEDING
        if ($this->command->confirm('Do you want to seed Users with fake data ?', true)) {
            $numberOfUser = $this->command->ask('How many users do you need ?', 0);
            if (!empty($numberOfUser)) {
                $this->command->info("Creating {$numberOfUser} users, each will have a channel associated.");
                User::factory()->count((int)$numberOfUser)->create();
                $this->command->line("Users table were successfully seeded");
            }
        }

        //MESSAGES SEEDING
        if ($this->command->confirm('Do you want to seed Messages with fake data ?', true)) {
            $messagesNum = $this->command->ask('How many messages you want to create ?', '20');
            if (!empty($messagesNum)) {
                Message::factory()->count((int)$messagesNum)->create();
                $this->command->line("Messages was successfully seeded");
                $this->command->info("Start messages chats syncing");
                $this->command->call('chat:sync');
                $this->command->line("Chats were successfully synchronized");
            }
        }

        //POSTS SEEDING
        if ($this->command->confirm('Do you want to seed Posts with fake data ?', true)) {
            $postsNum = $this->command->ask('How many posts you want to create ?', '20');
            if (!empty($postsNum)) {
                Post::factory()->count((int)$postsNum)->create();
                $this->command->line("Messages were successfully seeded");
            }
        }


        // Re-guard model
        Model::reguard();
    }
}
