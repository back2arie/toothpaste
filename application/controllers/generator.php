<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Generator extends CI_Controller {
    
    private $base = 'http://search.twitter.com/search.json';
    
    public function index() {
        $api = $this->api_call("?q=%23nowplaying&rpp=100");
        $i = 0;
        
        while(isset($api['results'])) {
            foreach($api['results'] as $row) {
                $valid = $this->is_valid_format($row['text']);
                if(!$valid) {
                    continue;                    
                }
                else {
                    $chart[$i] = $valid;
                    $i++;
                }
            }
            
            if(isset($api['next_page'])) {
                $api = $this->api_call($api['next_page']);
            } else {
                $api = NULL;
            }
        }
        
        file_put_contents(FCPATH.'data/chart.json', json_encode($chart));
    }
    
    private function api_call($q = '') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base.$q);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if($status == 200) {
            return json_decode($res, TRUE);
        }
        else {
            return NULL;
        }
        
        curl_close($ch);
    }
    
    private function is_valid_format($text = '') {
        $text = strtolower($text);
        $parts = explode(' - ', $text);
        if(count($parts) != 2) return FALSE;
        $artist = explode(' ', $parts[0]);
        if(count($artist) != 2) return FALSE;
        if($artist[0] != "#nowplaying") return FALSE;
        return ucfirst($artist[1]).' - '.ucfirst($parts[1]);
    }
}

/* End of file generator.php */
/* Location: ./application/controllers/generator.php */