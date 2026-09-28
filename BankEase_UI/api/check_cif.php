<?php
require_once __DIR__ . '/../helpers.php';
$cif = posted('cif_number');
if ($cif === '') json_response(['success'=>false,'message'=>'CIF number is required.'],400);
try {
  $row = db_select_one('SELECT customer_id,cif_number,full_name,status,kyc_status FROM customers WHERE cif_number=:cif',[':cif'=>$cif]);
  json_response(['success'=>true,'exists'=>$row!==null,'customer'=>$row]);
} catch(Throwable $e) {
  json_response(['success'=>false,'message'=>exception_message($e)],500);
}
