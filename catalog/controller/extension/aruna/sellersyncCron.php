<?php

class ControllerExtensionArunaSellersyncCron extends Controller {
    private $jobs_file='baycik_sellersync_crondata.json';
    private $intervalHours=3;
    private $secret="tabarakasmuk";
    private $doneJobs=[];
    private $tasklist=[
        [
            'id'=>'happywearParse',
            'model'=>'extension/aruna/parse',
            'method'=>'initParser',
            'arguments'=>[3,'detect_unchanged_entries']
        ],
        [
            'id'=>'happywearImport',
            'model'=>'extension/aruna/import',
            'method'=>'importSellerProduct',
            'arguments'=>[2,3,null]
        ],
        [
            'id'=>'glemParse',
            'model'=>'extension/aruna/parse',
            'method'=>'initParser',
            'arguments'=>[5,'detect_unchanged_entries']
        ],
        [
            'id'=>'glemImport',
            'model'=>'extension/aruna/import',
            'method'=>'importSellerProduct',
            'arguments'=>[2,5,null]
        ],
        [
            'id'=>'charuttiParse',
            'model'=>'extension/aruna/parse',
            'method'=>'initParser',
            'arguments'=>[8,'detect_unchanged_entries']
        ],
        [
            'id'=>'charuttiImport',
            'model'=>'extension/aruna/import',
            'method'=>'importSellerProduct',
            'arguments'=>[2,8,null]
        ],
        [
            'id'=>'isellParse',
            'model'=>'extension/aruna/parse',
            'method'=>'initParser',
            'arguments'=>[10,'detect_unchanged_entries']
        ],
        [
            'id'=>'isellImport',
            'model'=>'extension/aruna/import',
            'method'=>'importSellerProduct',
            'arguments'=>[20,10,null]
        ],
        [
            'id'=>'autoWorm',
            'model'=>'extension/aruna/autoworm',
            'method'=>'init',
            'arguments'=>[11]
        ],
        [
            'id'=>'autoImport',
            'model'=>'extension/aruna/import',
            'method'=>'importSellerProduct',
            'arguments'=>[51,11,null]
        ],
        [
            'id'=>'deleteAbsentSellerProducts1',
            'model'=>'extension/aruna/import',
            'method'=>'deleteAbsentSellerProducts',
            'arguments'=>[2]
        ],
        [
            'id'=>'deleteAbsentSellerProducts2',
            'model'=>'extension/aruna/import',
            'method'=>'deleteAbsentSellerProducts',
            'arguments'=>[20]
        ],
        [
            'id'=>'deleteAbsentSellerProducts2',
            'model'=>'extension/aruna/import',
            'method'=>'deleteAbsentSellerProducts',
            'arguments'=>[51]
        ]
    ];
    public function index(){
        if( $this->secret != $this->request->get['secret'] ){
            die('access denied');
        }
        header('Content-Type: text/plain; charset=utf-8');
        echo "\nStart loop through tasks...";
        $this->loadDoneJob();
        foreach($this->tasklist as $task){
            if( isset($this->doneJobs[$task['id']]) ){
                $jobdata=$this->doneJobs[$task['id']];
                $interval=empty($task['interval'])?$this->intervalHours*60*60:$task['interval'];
                if( ($jobdata['last_executed']+$interval)>time() ){
                    echo " \nSkipping ".$task['id'];
                    continue;
                }
            }
            if( $this->executeTask($task) ){
                $this->doneJobs[$task['id']]['last_executed']=time();
                $this->saveDoneJob();
                break;//only one job at a time
            }
        }
        die;
    }
    private function executeTask($task){
        echo "\nStart executing Task ".date('d.m.Y H:i:s')." ".$task['id'];
        error_reporting(E_ALL);
        $this->load->model($task['model']);
        return call_user_func_array([$this->{'model_' . str_replace('/', '_', $task['model'])}, $task['method']], $task['arguments']);
    }
    private function loadDoneJob(){
        if( file_exists($this->jobs_file) ){
            $this->doneJobs=json_decode(file_get_contents($this->jobs_file,1),true);
        } else {
            $this->doneJobs=[];
        }
    }
    private function saveDoneJob(){
        file_put_contents($this->jobs_file, json_encode($this->doneJobs));
    }
}