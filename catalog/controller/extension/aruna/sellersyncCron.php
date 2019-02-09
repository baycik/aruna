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
        ]
    ];
    public function index(){
        if( $this->secret != $this->request->get['secret'] ){
            die('access denied');
        }
        header("Content-type:text/plain;");
        echo "\nStart loop through tasks...";
        $this->loadDoneJob();
        foreach($this->tasklist as $task){
            if( isset($this->doneJobs[$task['id']]) ){
                $jobdata=$this->doneJobs[$task['id']];
                if( ($jobdata['last_executed']+$this->intervalHours*60*60)>time() ){
                    echo " \nSkipping ".$task['id'];
                    continue;
                }
            }
            echo $this->executeTask($task);
            $this->doneJobs[$task['id']]['last_executed']=time();
            break;//only one job at a time
        }
        $this->saveDoneJob();
        die;
    }
    private function executeTask($task){
        echo "\nStart executing Task".date('d.m.Y H:i:s');
        print_r($task);
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