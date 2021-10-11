<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Channel;
use App\Models\Category;
use App\Models\ChannelCategory;
class FlixNetCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flixnet:crawl';
    protected $base_url = null;
    protected $api_key = null;

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
        $this->base_url =env("FLIXNET_URL");
        $this->api_key =env("FLIXNET_API_KEY");
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->get_channels(7);// TV Series
        $this->get_channels(8);//  movies
        $this->get_channels(9);// Pinoy Movies 
        $this->get_channels(10);// Animation
        return 0;
    }

    protected function get($category,$page,$count,$sort="n.video_title ASC"){
        $response = Http::acceptJson()->get($this->base_url,[
            "id" => $category,
            "page" => $page,
            "count"=>$count,
            "sort"=> $sort,
            "api_key" => $this->api_key
        ]);
        return $response;
    }
    protected function check_link($url) {
        $handle = fopen($url, "r");

        if ($handle) {
            echo Mimetypes::from($url);
            fclose($handle); 
            $this->info("Link available");
            return true; 
        }
        $this->info("Link broken");
        return false;
     }
    
     protected function get_mime($url){
        // $mime = mime_content_type($url);
       
        $this->info("mime: {$mime}");
         return $mime;
     }

    protected function get_channels($category_id,$mimeType = null){
        $total = $this->get($category_id,1,0)['count_total'];
        $this->info($total);
        $response = $this->get($category_id,1,$total);
        $cur= 0;
        foreach ($response['posts'] as $key => $rchannel) {
            // $this->info("Checking ".$channel['video_url']);
            // $this->check_link($channel['video_url']);
            // $this->get_mime($channel['video_url']);
            $cur++;
            $this->info("Processing {$cur}/{$total}");

            $channel = Channel::where([
                'name'=> $rchannel['video_title'],
                'crawler'=> 'Flixnet'
            ])->firstOr(function () use($rchannel,$mimeType) {
                $nchannel =Channel::create([
                    'name'=> $rchannel['video_title'],
                    'crawler'=> 'Flixnet',
                    'type'=>$mimeType?:"video/mp4",
                    'url'=>$rchannel['video_url'],
                    'description'=>$rchannel['video_description'],
                    'active'=>true,
                ]);
                $category = Category::firstOrCreate(['name'=> $rchannel['category_name']],[]);
                ChannelCategory::firstOrCreate([
                    'channel_id'=>$nchannel->id,
                    'category_id'=>$category->id,
                ],[]);
             
                return $nchannel;
            });

            if( $channel->url!= $rchannel['video_url']){
                $this->warn("Duplicate entry for {$channel->name}");
            }
        }
        $this->info("Done");

    }
}
