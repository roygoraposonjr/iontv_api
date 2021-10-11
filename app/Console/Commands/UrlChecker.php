<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Channel;

class UrlChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'url:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $channels = Channel::where('active',1)->get();
        $_tot = sizeof($channels);
        foreach ($channels as $key => $channel) {
            $this->info("Checking channel {$key}/{$_tot} : {$channel->name}");
            $this->check_link($channel->url);
        }
        return 0;
    }


    protected function check_link($url) {
        $handle = fopen($url, "r");

        if ($handle) {
            fclose($handle); 
            $this->info("Link available");
            return true; 
        }
        $this->info("Link broken");
        return false;
     }
    
}
