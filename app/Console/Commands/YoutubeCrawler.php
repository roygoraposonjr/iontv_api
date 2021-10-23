<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Channel;
use App\Models\Category;
use App\Models\ChannelCategory;
use Alaouy\Youtube\Facades\Youtube;

class YoutubeCrawler extends Command
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:crawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $channelName = "Yotube Channel";
    protected $channelId ="<id>";
    protected $q = null;
    protected $type = "video";
    protected $eventType = null;
    protected $maxResults = 5;
    protected $category = "Youtube";
    protected $api_keys = [];


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
        // https://www.youtube.com/c/abscbnentertainment/live
        // $videoId = Youtube::parseVidFromURL('https://www.youtube.com/c/abscbnentertainment/live');
        // $channel = Youtube::getChannelByName('abscbnnews');
        // //  $videoList = Youtube::listChannelVideos($channel->id, 40);

        // $this->info(json_encode($channel));
        // $this->info(json_encode($videoList));
        // UCzggCZVkynvnjNV29L9EccA cinemaOnePH
        // UCO_NrryDfh_sjeQSJ75qvKQ abscbnentertainment
        //https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&event_type=live&channelId=UCO_NrryDfh_sjeQSJ75qvKQ&key=AIzaSyCBLW5JMOyuUCDcm1Geim29Bp4UFzxqFu8
        $params = [
            'channelId' => 'UCO_NrryDfh_sjeQSJ75qvKQ',
            // 'q'=> 'Kapamilya Online Live October 16, 2021',
            // 'channelType' => 'any',
            'eventType' => 'live',
            'type' => 'video',
            'maxResults'    => 5
        ];
        
        // // Make intial call. with second argument to reveal page info such as page tokens
        $search = Youtube::searchAdvanced($params, true);
        $this->info(json_encode($search));
        if($search['results'] ){
            $this->addChannels($search);
        }
        
    }

    protected function addChannels($result){

        $cur = 0;
        foreach ($result['results'] as $key => $rchannel) {
            $cur++;
            $t_channel = [
                'name'=> $this->channelName . ($cur>1 ? " {$cur}":''),
                'logo'=> "https://img.youtube.com/vi/".$rchannel->id->videoId."/default.jpg",
                'crawler'=> 'Youtube',
                'type'=>"video/youtube",
                'description'=> null,
                'url'=>"https://www.youtube.com/watch?v=".$rchannel->id->videoId,
                'active'=>true,
                'category'=>$this->category,
            ];
            $this->info(json_encode($t_channel));

        }

    }

}