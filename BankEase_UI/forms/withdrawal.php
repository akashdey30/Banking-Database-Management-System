<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Withdrawal';
$base = '..';
$errors = [];
if(is_post()){
 try{ $account=(int)posted('account_id');$amount=posted('amount');$description=posted('description');
 if($account<=0)$errors[]='Account is required.';if(!preg_match('/^\d+(\.\d{1,2})?$/',$amount)|| (float)$amount<=0)$errors[]='Amount must be greater than 0 with at most 2 decimal places.';
 if(!$errors){db_begin();try{post_transaction($account,'withdrawal',$amount,$description);db_commit();flash_set('success','Withdrawal completed successfully.');redirect('withdrawal.php');}catch(Throwable $e){db_rollback();throw $e;}}
 }catch(Throwable $e){$errors[]=exception_message($e);}}

include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1><a class="btn secondary" href="../index.php">Dashboard</a></div>
<?php show_errors($errors); ?>
<form method="post" class="form-card"><div class="form-grid"><div class="field"><label>Account *</label><?php
$opts = db_select_all("SELECT account_id,CONCAT(account_number,' - ',account_title) AS label FROM accounts WHERE status='active' ORDER BY account_number");
$current = posted('account_id');
?><select name="account_id" id="account_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['account_id']) ?>" <?= (string)$current === (string)$opt['account_id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label for="amount">Amount *</label><input type="number" name="amount" id="amount" value="<?= e(posted('amount')) ?>" step="0.01" min="0.01" required></div><div class="field full"><label for="description">Description</label><input type="text" name="description" id="description" value="<?= e(posted('description')) ?>" maxlength="255"></div></div><div class="actions"><button type="submit">Save</button><a class="btn secondary" href="../index.php">Cancel</a></div></form>
<?php include __DIR__ . '/../partials/footer.php'; ?>