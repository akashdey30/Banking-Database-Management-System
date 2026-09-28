<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Account';
$base = '..';
$errors = [];
if(is_post()){
 try{$number=posted('account_number');$title=posted('account_title');$customer=posted('customer_id');$type=posted('account_type_id');$branch=posted('branch_id');$balance=posted('balance')?:'0';$currency=posted('currency')?:'BDT';$status=posted('status');$opening=posted('opening_date');$closing=posted_or_null('closing_date');
 foreach(['account_number','account_title','customer_id','account_type_id','branch_id','opening_date'] as $x)if(posted($x)==='')$errors[]=ucwords(str_replace('_',' ',$x)).' is required.';
 if(!in_array($status,enum_values('account_status'),true))$errors[]='Invalid account status.';if(!is_numeric($balance)||$balance<0)$errors[]='Balance must be zero or greater.';
 if($closing!==null&&$closing<$opening)$errors[]='Closing date cannot be before opening date.';
 if(!$errors){db_insert('INSERT INTO accounts(account_number,account_title,customer_id,account_type_id,branch_id,balance,currency,status,opening_date,closing_date) VALUES(:n,:t,:c,:at,:b,:bal,:cur,:s,:o,:cl)',[
 ':n'=>$number,':t'=>$title,':c'=>(int)$customer,':at'=>(int)$type,':b'=>(int)$branch,':bal'=>$balance,':cur'=>$currency,':s'=>$status,':o'=>$opening,':cl'=>$closing]);flash_set('success','Account added successfully.');redirect('account.php');}}
 catch(Throwable $e){$errors[]=exception_message($e);}}

include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1><a class="btn secondary" href="../index.php">Dashboard</a></div>
<?php show_errors($errors); ?>
<form method="post" class="form-card"><div class="form-grid"><div class="field"><label for="account_number">Account Number *</label><input type="text" name="account_number" id="account_number" value="<?= e(posted('account_number')) ?>" maxlength="20" required></div><div class="field"><label for="account_title">Account Title *</label><input type="text" name="account_title" id="account_title" value="<?= e(posted('account_title')) ?>" maxlength="150" required></div><div class="field"><label>Customer *</label><?php
$opts = db_select_all("SELECT customer_id, CONCAT(cif_number,' - ',full_name) AS label FROM customers ORDER BY full_name");
$current = posted('customer_id');
?><select name="customer_id" id="customer_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['customer_id']) ?>" <?= (string)$current === (string)$opt['customer_id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label>Account Type *</label><?php
$opts = db_select_all('SELECT account_type_id,type_name FROM account_types ORDER BY type_name');
$current = posted('account_type_id');
?><select name="account_type_id" id="account_type_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['account_type_id']) ?>" <?= (string)$current === (string)$opt['account_type_id'] ? 'selected' : '' ?>><?= e($opt['type_name']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label>Branch *</label><?php
$opts = db_select_all('SELECT branch_id,branch_name FROM branches ORDER BY branch_name');
$current = posted('branch_id');
?><select name="branch_id" id="branch_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['branch_id']) ?>" <?= (string)$current === (string)$opt['branch_id'] ? 'selected' : '' ?>><?= e($opt['branch_name']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label for="balance">Opening Balance</label><input type="number" name="balance" id="balance" value="<?= e(posted('balance')) ?>" step="0.01" min="0"></div><div class="field"><label for="currency">Currency *</label><input type="text" name="currency" id="currency" value="<?= e(posted('currency')) ?>" maxlength="10" required></div><div class="field"><label>Status *</label><?php $current = posted('status'); ?><select name="status" id="status" required><option value="">-- Select --</option><?php foreach (enum_values('account_status') as $opt): ?><option value="<?= e($opt) ?>" <?= $current === $opt ? 'selected' : '' ?>><?= e(pretty_enum($opt)) ?></option><?php endforeach; ?></select></div><div class="field"><label for="opening_date">Opening Date *</label><input type="date" name="opening_date" id="opening_date" value="<?= e(posted('opening_date')) ?>" required></div><div class="field"><label for="closing_date">Closing Date</label><input type="date" name="closing_date" id="closing_date" value="<?= e(posted('closing_date')) ?>"></div></div><div class="actions"><button type="submit">Save</button><a class="btn secondary" href="../index.php">Cancel</a></div></form>
<?php include __DIR__ . '/../partials/footer.php'; ?>