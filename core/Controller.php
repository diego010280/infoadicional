<?php
class Controller {
    public function view($view, $options = []) {
        require_once "views/$view.php";
    }
}