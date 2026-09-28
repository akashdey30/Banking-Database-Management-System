<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Card';
$base = '..';
$errors = [];
if(is_post()){
 try{$customer=(int)posted('customer_id');$account=(int)posted('account_id');$type=posted('card_type');$masked=posted('masked_number');$hash=posted('card_number_hash');$limit=posted('credit_limit')?:'0';$issue=posted('issue_date');$expiry=posted('expiry_date');$status=posted('status');
 foreach(['customer_id','account_id','masked_number','card_number_hash','issue_date','expiry_date'] as $x)if(posted($x)==='')$errors[]=ucwords(str_replace('_',' ',$x)).' is required.';
 if(!in_array($type,enum_values('card_type'),true))$errors[]='Invalid card type.';if(!in_array($status,enum_values('card_status'),true))$errors[]='Invalid card status.';if(!is_numeric($limit)||$limit<0)$errors[]='Credit limit must be zero or greater.';
 if(!$errors){db_insert('INSERT INTO cards(customer_id,account_id,card_type,masked_number,card_number_hash,credit_limit,issue_date,expiry_date,status) VALUES(:c,:a,:t,:m,:h,:l,:i,:e,:s)',[
 ':c'=>$customer,':a'=>$account,':t'=>$type,':m'=>$masked,':h'=>$hash,':l'=>$limit,':i'=>$issue,':e'=>$expiry,':s'=>$status]);flash_set('success','Card added successfully.');redirect('card.php');}}
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
<?php endforeach; ?></select></div><div class="field"><label>Account *</label><?php
$opts = db_select_all("SELECT account_id,CONCAT(account_number,' - ',account_title) AS label FROM accounts ORDER BY account_number");
$current = posted('account_id');
?><select name="account_id" id="account_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['account_id']) ?>" <?= (string)$current === (string)$opt['account_id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label>Card Type *</label><?php $current = posted('card_type'); ?><select name="card_type" id="card_type" required><option value="">-- Select --</option><?php foreach (enum_values('card_type') as $opt): ?><option value="<?= e($opt) ?>" <?= $current === $opt ? 'selected' : '' ?>><?= e(pretty_enum($opt)) ?></option><?php endforeach; ?></select></div><div class="field"><label for="masked_number">Masked Number *</label><input type="text" name="masked_number" id="masked_number" value="<?= e(posted('masked_number')) ?>" maxlength="20" required></div><div class="field"><label for="card_number_hash">Card Number Hash *</label><input type="text" name="card_number_hash" id="card_number_hash" value="<?= e(posted('card_number_hash')) ?>" maxlength="255" required></div><div class="field"><label for="credit_limit">Credit Limit</label><input type="number" name="credit_limit" id="credit_limit" value="<?= e(posted('credit_limit')) ?>" step="0.01" min="0"></div><div class="field"><label for="issue_date">Issue Date *</label><input type="date" name="issue_date" id="issue_date" value="<?= e(posted('issue_date')) ?>" required></div><div class="field"><label for="expiry_date">Expiry Date *</label><input type="date" name="expiry_date" id="expiry_date" value="<?= e(posted('expiry_date')) ?>" required></div><div class="field"><label>Status *</label><?php $current = posted('status'); ?><select name="status" id="status" required><option value="">-- Select --</option><?php foreach (enum_values('card_status') as $opt): ?><option value="<?= e($opt) ?>" <?= $current === $opt ? 'selected' : '' ?>><?= e(pretty_enum($opt)) ?></option><?php endforeach; ?></select></div></div><div class="actions"><button type="submit">Save</button><a class="btn secondary" href="../index.php">Cancel</a></div></form>
<?php include __DIR__ . '/../partials/footer.php'; ?>