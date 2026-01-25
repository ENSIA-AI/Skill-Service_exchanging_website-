<?php

class HomeController extends Controller {
    public function index() {
        $this->view('home/index');
    }

    public function about() {
        echo "About Us - (To be migrated)";
        // $this->view('home/about');
    }

    public function contact() {
         echo "Contact Us - (To be migrated)";
        // $this->view('home/contact');
    }
}
