<?php

class ControllerExtensionModuleISSSupersyncCron extends Controller {
    private $jobs_file='supersync_crondata.json';
    private $intervalHours=1;
    private $secret="tabarakasmuk";
    private $doneJobs=[];
    private $tasklist=[
        [
            'id'=>'csvParse',
            'model'=>'extension/module/iss_supersync/parse',
            'method'=>'initParser',
            'arguments'=>[10,'detect_unchanged_entries']
        ],
        [
            'id'=>'csvImport',
            'model'=>'extension/module/iss_supersync/import',
            'method'=>'importStart',
            'arguments'=>[20,10,null,1]
        ],
        [
            'id'=>'deleteAbsentProducts1',
            'model'=>'extension/module/iss_supersync/import',
            'method'=>'deleteAbsentProducts',
            'arguments'=>[2]
        ]
    ];
    public function index(){
        if( empty($this->request->get['secret']) || $this->secret != $this->request->get['secret'] ){
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