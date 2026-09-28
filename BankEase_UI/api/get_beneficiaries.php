<?php
require_once __DIR__ . '/../helpers.php';
try { json_response(['success'=>true,'data'=>db_select_all('SELECT b.beneficiary_id,b.customer_id,b.nickname,b.target_account_id,a.account_number FROM beneficiaries b JOIN accounts a ON a.account_id=b.target_account_id ORDER BY b.beneficiary_id DESC')]); }
catch(Throwable $e) { json_response(['success'=>false,'message'=>exception_message($e)],500); }
