<?php

class ApiController {
    public function fetch_products() {
        $products = ["vape","pc","mobile","steam deck", "rog ally x"];
        echo json_encode($products);
    }
}