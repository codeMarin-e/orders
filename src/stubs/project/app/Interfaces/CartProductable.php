<?php
    namespace App\Interfaces;

    interface CartProductable {
        public function getQuantity();
        public function setQuantity($newQuantity);
        public function getPrice($withNew = true, $withDiscounts = true, $withVat = true);
        public function getVatPercent();
        public function getVat_In_Price();
        public function getCartProductName();
        public function getReference();
        public function cart_products();
        public function checkQuantity($value);
    };
