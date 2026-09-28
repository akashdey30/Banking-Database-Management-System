<?php
require_once __DIR__ . '/../helpers.php';
try { json_response(['success'=>true,'data'=>db_select_all('SELECT account_id,account_number,account_title,customer_id,balance,status FROM accounts ORDER BY account_number')]); }
catch(Throwable $e) { json_response(['success'=>false,'message'=>exception_message($e)],500); }
