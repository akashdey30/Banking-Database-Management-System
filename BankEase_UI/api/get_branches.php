<?php
require_once __DIR__ . '/../helpers.php';
try { json_response(['success'=>true,'data'=>db_select_all('SELECT branch_id,branch_name,address,status FROM branches ORDER BY branch_name')]); }
catch(Throwable $e) { json_response(['success'=>false,'message'=>exception_message($e)],500); }
