<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Channel;
use App\Models\Category;
use App\Models\ChannelCategory;

class AddChannelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $channel = null;
    protected $custom_queue="addchannel";
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($channel)
    {
        $this->onConnection('database');
        $this->onQueue($this->custom_queue);
        $this->channel = $channel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $channel = $this->channel;

        echo("Processing {$channel['name']} \n");
        /**
         * 
         * [
         *   'name'=> $channel['name'],
         *   'logo'=> $channel['logo'],
         *   'crawler'=> $channel['crawler'],
         *   'type'=>$channel['type'],
         *   'url'=>$channel['url'],
         *   'description'=>$channel['description'],
         *   'active'=>$channel['active'],
         *   'category'=>$channel['category']
         * ]
         */
        $nchannel = Channel::firstOrCreate([
            'name'=> $channel['name'],
            'crawler'=> $channel['crawler']
        ],[
            'name'=> $channel['name'],
            'logo'=> $channel['logo'],
            'crawler'=> $channel['crawler'],
            'type'=>$channel['type'],
            'url'=>$channel['url'],
            'description'=>$channel['description'],
            'active'=>$channel['active'],
        ]);

        $category = Category::firstOrCreate(['name'=> $channel['category']?:"Unknown" ],[]);
        ChannelCategory::firstOrCreate([
            'channel_id'=>$nchannel->id,
            'category_id'=>$category->id,
        ],[]);

        if( $nchannel->url!= $channel['url']){
            echo("URL changed {$nchannel->name} \n");

            if($this->check_link($nchannel->url) && $this->check_link($channel['url'])){
                echo("Both links are working.. Please check \n");
            }elseif ($this->check_link($channel['url'])) {
                echo("Updated url link \n");
                $nchannel->url = $channel['url'];
                $nchannel->save();
            }elseif( !$this->check_link($nchannel->url)){
                echo("Both links are not working.. Setting active to false \n");
                $nchannel->active = false;
                $nchannel->save();
            }
        }
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
}
