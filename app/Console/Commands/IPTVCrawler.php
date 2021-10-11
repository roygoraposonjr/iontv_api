<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Channel;
use App\Models\Category;
use App\Models\ChannelCategory;
use App\Jobs\AddChannelJob;
class IPTVCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iptv:crawl';
    protected $base_url = null;
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
        $this->base_url =env("IPTVORG_URL");
        parent::__construct();
    }

    protected function check_link($url) {
        try {
            $handle = fopen($url, "r");
            if ($handle) {
                fclose($handle); 
                $this->info("Link available");
                return true; 
            }
        } catch (\Throwable $th) {
            return false;
        }
        $this->info("Link broken");
        return false;
        
     }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $channels = Http::acceptJson()->get($this->base_url)->json();
        $total = sizeof($channels);
        $cur = 0;
        foreach ($channels as $key => $rchannel) {
            $cur++;
            $this->info("Processing {$cur}/{$total}");
            $t_channel = [
                'name'=> $rchannel['name'],
                'logo'=> $rchannel['logo'],
                'crawler'=> 'IPTV',
                'type'=>"application/x-mpegURL",
                'description'=> null,
                'url'=>$rchannel['url'],
                'active'=>true,
                'category'=>$rchannel['category'],
            ];

            $job = new AddChannelJob($t_channel);

            dispatch($job);

        }
        return 0;
    }
}
