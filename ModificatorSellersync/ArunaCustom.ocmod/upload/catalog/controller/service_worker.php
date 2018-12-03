<?php

class ControllerServiceWorker extends Controller {

    function index() {
	header("Content-type:application/javascript");
	include DIR_APPLICATION.'view/javascript/aruna/sw.js';
	die();
    }

}
