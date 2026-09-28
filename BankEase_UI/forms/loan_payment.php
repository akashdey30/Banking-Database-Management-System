<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Loan Payment';
$base = '..';
$errors = [];
if(is_post()){
 try{
  $loan=(int)posted('loan_id');$install=(int)posted('installment_number');$due=posted('due_date');$principal=posted('scheduled_principal')?:'0';$interest=posted('scheduled_interest')?:'0';$paid=posted('paid_amount');$paidDate=posted_or_null('paid_date');$status=posted('status');$txnAccount=(int)posted('payment_account_id');
  if($loan<=0)$errors[]='Loan is required.';if($install<=0)$errors[]='Installment number must be positive.';if($due==='')$errors[]='Due date is required.';if(!is_numeric($principal)||$principal<0)$errors[]='Scheduled principal is invalid.';if(!is_numeric($interest)||$interest<0)$errors[]='Scheduled interest is invalid.';if(!is_numeric($paid)||$paid<0)$errors[]='Paid amount is invalid.';if(!in_array($status,enum_values('payment_status'),true))$errors[]='Invalid payment status.';
  if(!$errors){db_begin();try{
   $loanRow=db_select_one('SELECT outstanding_balance FROM loans WHERE loan_id=:id FOR UPDATE',[':id'=>$loan]);
   if(!$loanRow)throw new Exception('Loan not found.');
   $txnId=null;
   if((float)$paid>0){if($txnAccount<=0)throw new Exception('Payment account is required when paid amount is greater than zero.');$txnId=post_transaction($txnAccount,'withdrawal',$paid,'Loan repayment - Loan #'.$loan);}
   db_insert('INSERT INTO loan_payments(loan_id,installment_number,due_date,scheduled_principal,scheduled_interest,paid_amount,paid_date,status,related_transaction_id) VALUES(:l,:i,:d,:p,:r,:paid,:pd,:s,:txn)',
    [':l'=>$loan,':i'=>$install,':d'=>$due,':p'=>$principal,':r'=>$interest,':paid'=>$paid,':pd'=>$paidDate,':s'=>$status,':txn'=>$txnId]);
   $newOutstanding=max(0,(float)$loanRow['outstanding_balance']-(float)$principal);
   db_execute('UPDATE loans SET outstanding_balance=:o,status=CASE WHEN :o2=0 THEN "closed" ELSE status END WHERE loan_id=:l',[':o'=>$newOutstanding,':o2'=>$newOutstanding,':l'=>$loan]);
   db_commit();flash_set('success','Loan payment recorded successfully.');redirect('loan_payment.php');
  }catch(Throwable $e){db_rollback();throw $e;}}
 }catch(Throwable $e){$errors[]=exception_message($e);}}

include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1><a class="btn secondary" href="../index.php">Dashboard</a></div>
<?php show_errors($errors); ?>
<form method="post" class="form-card"><div class="form-grid"><div class="field"><label>Loan *</label><?php
$opts = db_select_all("SELECT loan_id,CONCAT('#',loan_id,' - ',loan_type,' - ',customer_id) AS label FROM loans ORDER BY loan_id DESC");
$current = posted('loan_id');
?><select name="loan_id" id="loan_id" required><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['loan_id']) ?>" <?= (string)$current === (string)$opt['loan_id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label for="installment_number">Installment Number *</label><input type="number" name="installment_number" id="installment_number" value="<?= e(posted('installment_number')) ?>" min="1" required></div><div class="field"><label for="due_date">Due Date *</label><input type="date" name="due_date" id="due_date" value="<?= e(posted('due_date')) ?>" required></div><div class="field"><label for="scheduled_principal">Scheduled Principal *</label><input type="number" name="scheduled_principal" id="scheduled_principal" value="<?= e(posted('scheduled_principal')) ?>" step="0.01" min="0" required></div><div class="field"><label for="scheduled_interest">Scheduled Interest *</label><input type="number" name="scheduled_interest" id="scheduled_interest" value="<?= e(posted('scheduled_interest')) ?>" step="0.01" min="0" required></div><div class="field"><label for="paid_amount">Paid Amount *</label><input type="number" name="paid_amount" id="paid_amount" value="<?= e(posted('paid_amount')) ?>" step="0.01" min="0" required></div><div class="field"><label for="paid_date">Paid Date</label><input type="date" name="paid_date" id="paid_date" value="<?= e(posted('paid_date')) ?>"></div><div class="field"><label>Payment Account</label><?php
$opts = db_select_all("SELECT account_id,CONCAT(account_number,' - ',account_title) AS label FROM accounts WHERE status='active' ORDER BY account_number");
$current = posted('payment_account_id');
?><select name="payment_account_id" id="payment_account_id"><option value="">-- Select --</option><?php foreach ($opts as $opt): ?>
<option value="<?= e($opt['account_id']) ?>" <?= (string)$current === (string)$opt['account_id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
<?php endforeach; ?></select></div><div class="field"><label>Status *</label><?php $current = posted('status'); ?><select name="status" id="status" required><option value="">-- Select --</option><?php foreach (enum_values('payment_status') as $opt): ?><option value="<?= e($opt) ?>" <?= $current === $opt ? 'selected' : '' ?>><?= e(pretty_enum($opt)) ?></option><?php endforeach; ?></select></div></div><div class="actions"><button type="submit">Save</button><a class="btn secondary" href="../index.php">Cancel</a></div></form>
<?php include __DIR__ . '/../partials/footer.php'; ?>