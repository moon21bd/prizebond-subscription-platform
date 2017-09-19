<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-03-22
 */
//http://www.binarytides.com/monitor-progress-long-running-php-scripts-html5-server-sent-events/
set_time_limit(0);
//error_reporting(0);
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once 'Main.php';

class Maintenance extends Main {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('maintenance_model');
    }
    
    public function index() {
        
    }
    
    public function refactorDrawResultImpactedOnSystem()
    {
        //ob_start();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
        $progress = 0;
        $messageId = time();
        
        $drawId = $this->input->post_get('drawId', TRUE);
        
        $progress = 1;
        $this->sendMessageToClient($messageId, 'Retriving draw Info from server', $progress);
        $drawResultInfo = $this->maintenance_model->getDrawDetailInfoById($drawId);
        
        if($drawResultInfo)
        {
            $progress = 2;
            $this->sendMessageToClient($messageId, 'Retriving draw Info from server : Success. Draw Result number : '.$drawResultInfo->result_number.' found', $progress);
        }
        else
        {
            $progress = 100;
            $this->sendMessageToClient($messageId, 'Retriving draw Info from server : Failed. No Draw Result found', $progress);
            $this->sendMessageToClient($messageId, 'TERMINATE', '');
            die();
        }
        
        if($drawResultInfo)
        {
            //$bondSeries = join("','", array_map('trim', explode(',', $drawResultInfo->result_series)));
            $bondSeries = $this->processBondSeries($drawResultInfo->result_series);
            
            $prizes['1st'] = $drawResultInfo->first_prize_value_tk;
            $prizes['2nd'] = $drawResultInfo->second_prize_value_tk;
            $prizes['3rd'] = $drawResultInfo->third_prize_value_tk;
            $prizes['4th'] = $drawResultInfo->fourth_prize_value_tk;
            $prizes['5th'] = $drawResultInfo->fifth_prize_value_tk;
            
            $this->db->trans_start();
            
            $progress = 3;
            $this->sendMessageToClient($messageId, 'Searching for user bonds which may matched with Draw Result '.$drawResultInfo->result_number.'\'s bonds  ', $progress);
            $matchedUserBondList = $this->maintenance_model->getMatchedUserBondListWithDrawResultNumber($drawResultInfo->result_number);
            
            if(count($matchedUserBondList))
            {
                $progress = 4;
                $this->sendMessageToClient($messageId, 'Total : '.count($matchedUserBondList).' user bonds found matched with Draw Result '.$drawResultInfo->result_number.'\'s bonds', $progress);
            }
            else 
            {
                $progress = 4;
                $this->sendMessageToClient($messageId, 'Total : '.count($matchedUserBondList).' user bonds found matched with Draw Result '.$drawResultInfo->result_number.'\'s bonds. No action was taken.', $progress);
            }
            
            if(count($matchedUserBondList))
            {
                $progress = 5;
                $this->sendMessageToClient($messageId, 'Removing Draw Result '.$drawResultInfo->result_number.'\'s info from DB Table 1 : user_prizebond_list and DB Table 2 : user_prizebond_won_list', $progress);
                foreach ($matchedUserBondList as $matchedUserBondInfo)
                {
                    $this->maintenance_model->removeDrawResultInfoFromUserBonds($matchedUserBondInfo->id);
                }
                $progress = 7;
                $this->sendMessageToClient($messageId, 'Success', $progress);
            }
            
            $progress = 8;
            $this->sendMessageToClient($messageId, 'Generating Winner Bond List based on Draw Result '.$drawResultInfo->result_number.'\'s bonds. It will takes some times, please wait... ', $progress);
            $sumOfAffectedRows = 0;
            foreach ($drawResultInfo->bonds as $bond)
            {
                $bondNumber = $bond['bond_number'];
                $prizePosition = $bond['prize_position'];
                $prizes[$prizePosition];
                
                $this->sendMessageToClient($messageId, 'Apply changes for bond number : '.$bondNumber.' of Draw Result '.$drawResultInfo->result_number.', waiting...', $progress);
                $totalAffectedRows = $this->maintenance_model->applyDrawResultToUserBonds($bondNumber, $bondSeries, $drawResultInfo->result_number, $prizePosition, $prizes[$prizePosition] , $drawResultInfo->id);
                $progress = $progress + 2;
                $sumOfAffectedRows = $sumOfAffectedRows + $totalAffectedRows;
                
                sleep(1);
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE)
            {
                // generate an error... or use the log_message() function to log your error
                $this->sendMessageToClient($messageId, 'DB error occured. transaction rollback', '');
                $this->sendMessageToClient($messageId, 'TERMINATE', '');
            }
            else 
            {
                $this->sendMessageToClient($messageId, 'Task completed, Total afftected rows = '.$sumOfAffectedRows , $progress);
                $this->sendMessageToClient($messageId, 'TERMINATE', '');
            }
            
            
            
        }
        
        
    }
    
    public function reassembleWinnerBonds($drawId)
    {
        //$drawId = $this->input->post_get('drawId', TRUE);
        $data = array();
        $data['drawId'] = $drawId;
        $this->load->view('admin/draw_info/refactor_process',$data);
        
        ?>
<!--            <!DOCTYPE html>-->
<!--            <html>
                <head>
                    <meta charset="utf-8" />
                    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script>

                    <script>
                            var source = 'THE SOURCE';

                            function start_task()
                            {
                                source = new EventSource('http://api.example.com/maintenance/refactorDrawResultImpactedOnSystem?drawId=' + <?php echo $drawId;?>);

                                //a message is received
                                source.addEventListener('message' , function(e) 
                                {
                                    var result = JSON.parse( e.data );
                                    
                                    if(e.data.search('TERMINATE') != -1)
                                    {
                                        //add_log('Received TERMINATE closing');
                                        source.close();
                                    }
                                    else 
                                    {
                                        add_log(result.message);
                                        document.getElementById('progressor').style.width = result.progress + "%";
                                    }
                                    
                                });

                                source.addEventListener('error' , function(e)
                                {
                                    add_log('Error occured');
                                    //kill the object ?
                                    source.close();
                                });
                            }

                            function stop_task()
                            {
                                source.close();
                                add_log('Interrupted');
                            }

                            function add_log(message)
                            {
                                var r = document.getElementById('results');
                                r.innerHTML += message + '<br>';
                                r.scrollTop = r.scrollHeight;
                            }
                    </script>
                </head>
                <body>
                    
                    <br />
                    Please do not close the browser window until task completed.
                    <br />
                    <br />
                    
                    <script>
                        $( document ).ready(function() {
                            console.log( "ready!" );
                            start_task();
                        });
                    </script>
                    
                    Progress
                    <br />
                    <div style="border:1px solid #ccc; width:95%; height:20px; overflow:auto; background:#eee;">
                        <div id="progressor" style="background:#07c; width:0%; height:100%;"></div>
                    </div>
                    <br />
                    <br />
                    Logs
                    <br />
                    <div id="results" style="border:1px solid #000; padding:10px; width:95%; height:50%; overflow:auto; background:#eee;"></div>
                    <br />

                    

                </body>
            </html> -->
        <?php
    }

    

    private function processBondSeries($bondSeries)
    {
        $finalArray = array();
        $bondSeriesArray = array_map('trim', explode(',', $bondSeries));
        foreach ($bondSeriesArray as $bondSeries)
        {
            $first_char = mb_substr($bondSeries, 0, 1);
            $second_char = mb_substr($bondSeries, 1, 1);
            $finalArray[] = $first_char.' '.$second_char;
        }
        
        return join("','", $finalArray);
    }

    private function sendMessageToClient($id, $message, $progress)
    {
        //ob_start();
        /**
            Constructs the SSE data format and flushes that data to the client.
        */
        $d = array('message' => $message , 'progress' => $progress);

        echo "id: $id" . PHP_EOL;
        echo "data: " . json_encode($d) . PHP_EOL;
        echo PHP_EOL;

        //PUSH THE data out by all FORCE POSSIBLE
        //ob_flush();
        flush();
    }
    
    
    
}

