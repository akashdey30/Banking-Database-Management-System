<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Beneficiary';
$base = '..';
$errors = [];
if(is_post()){
 try{$customer=(int)posted('customer_id');$account=(int)posted('target_account_id');$nick=posted_or_null('nickname');
 if($customer<=0)$errors[]='Customer is required.';if($account<=0)$errors[]='Target account is required.';
 if(!$errors){db_insert('INSERT INTO beneficiaries(customer_id,nickname,target_account_id) VALUES(:c,:n,:a)', [':c'=>$customer,':n'=>$nick,':a'=>$account]);flash_set('success','Beneficiary added successfully.');redirect('beneficiary.php');}}
 catch(Throwable $e){$errors[]=exception_message($e);}}

include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1><a class="btn secondary" href="../index.php">Dashboard</a></div>
<?php show_errors($errors); ?>
<form method="post" class="form-card"><div class="form-grid"><div class="field"><label>Customer *</label><?php
$opts = db_select_all("SELECT customer_id,CONCAT(cif_number,' - ',full_name) AS label FROM customers ORDER BY full_name");
$current = posted('customer_id');
?><select name="customer_id" id="customer_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['customer_id']) ?>" <?= (string)$current === (string)$opt['customer_id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label for="nickname">Nickname</label><input type="text" name="nickname" id="nickname" value="<?= e(posted('nickname')) ?>" maxlength="50"></div><div class="field"><label>Target Account *</label><?php
$opts = db_select_all("SELECT account_id,CONCAT(account_number,' - ',account_title) AS label FROM accounts ORDER BY account_number");
$current = posted('target_account_id');
?><select name="target_account_id" id="target_account_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['account_id']) ?>" <?= (string)$current === (string)$opt['account_id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
<?php endforeach; ?></select></div></div><div class="actions"><button type="submit">Save</button><a class="btn secondary" href="../index.php">Cancel</a></div></form>
<?php include __DIR__ . '/../partials/footer.php'; ?>