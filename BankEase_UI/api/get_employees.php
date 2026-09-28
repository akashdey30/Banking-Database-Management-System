<?php
require_once __DIR__ . '/../helpers.php';
try { json_response(['success'=>true,'data'=>db_select_all('SELECT employee_id,full_name,position,branch_id,employment_status FROM employees ORDER BY full_name')]); }
catch(Throwable $e) { json_response(['success'=>false,'message'=>exception_message($e)],500); }
