<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Channel;
use App\Models\Category;
use App\Models\ChannelCategory;
use Alaouy\Youtube\Facades\Youtube;
use Illuminate\Support\Arr;


class ABSCBNLiveYoutubeCrawler extends Command
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:abscbnlivecrawler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $channelName = "ABS-CBN";
    protected $channelId ="<id>";
    protected $playlistId ="PLPcB0_P-Zlj5Iz0QnreDNgyicqzC_odXJ";
    protected $q = null;
    protected $type = "video";
    protected $eventType = null;
    protected $maxResults = 5;
    protected $category = "Youtube TV";
    protected $api_keys = [];


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

        $api_keys = explode(',', env('YOUTUBE_API_KEYS'));
        $key = Arr::random($api_keys, 1);

        Youtube::setApiKey($key[0]);
        parent::__construct();
    }

    

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Starting script");
        $this->info("Used youtube key: ".Youtube::getApiKey());
        
        $search = Youtube::getPlaylistItemsByPlaylistId($this->playlistId);
       
        if($search['results'] ){
            $_tot =count($search['results']);
            $cur = 0;
            $this->info("Found {$_tot} results.");
            foreach ($search['results'] as $key => $r) {
                $cur++;
                $this->info("Processing {$cur}/{$_tot}.");
                $videoId = $r->snippet->resourceId->videoId;
                $title = $r->snippet->title;
                $t_channel = [
                    'name'=> $this->channelName . ($cur>1 ? " {$cur}":''),
                    'logo'=> "https://img.youtube.com/vi/".$videoId."/default.jpg",
                    'crawler'=> 'Youtube',
                    'type'=>"video/youtube",
                    'description'=> $title,
                    'playlist'=> $this->playlistId,
                    'playlistOrder'=> $cur,
                    'url'=>"https://www.youtube.com/watch?v=".$videoId,
                    'active'=>true,
                    'category'=>$this->category,
                ];

                $channel = Channel::where([
                    'name'=> $t_channel['name'],
                    'crawler'=> 'Youtube',
                    'playlist'=> $t_channel['playlist'],
                    'playlistOrder'=> $t_channel['playlistOrder'],
                ])->firstOr(function () use($t_channel,$cur) {
                    $nchannel =Channel::create([
                        'name'=> $t_channel['name'],
                        'crawler'=> 'Youtube',
                        'type'=>"video/youtube",
                        'url'=>$t_channel['url'],
                        'playlist'=>$t_channel['playlist'],
                        'playlistOrder'=>$t_channel['playlistOrder'],
                        'description'=>$t_channel['description'],
                        'active'=>true,
                    ]);
                    $category = Category::firstOrCreate(['name'=> $t_channel['category']],[]);
                    ChannelCategory::firstOrCreate([
                        'channel_id'=>$nchannel->id,
                        'category_id'=>$category->id,
                    ],[]);
                 
                    return $nchannel;
                });

                if($channel->url != $t_channel['url'] || !$channel->active){
                    $channel->url =$t_channel['url'];
                    $channel->description =$t_channel['description'];
                    $channel->active =true;
                    $channel->save();
                }


            }
            $oldChannels = Channel::where('crawler','Youtube')->where('playlist',$this->playlistId)->where('playlistOrder','>',$cur)->get();
            if(count($oldChannels) > 0){
                $this->info("Inactivating old channels");
                foreach ($oldChannels as $key => $chnl) {
                    $chnl->active=false;
                    $channel->save();
                }
            }
            
            // $this->addChannels($search);
        }
        
    }

    

}